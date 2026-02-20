<?php
session_start();

function payment_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function payment_db_connect(): ?mysqli
{
    $dbPath = __DIR__ . '/connexion_bd/connexion_bd.php';
    if (!file_exists($dbPath)) {
        return null;
    }

    require $dbPath;
    if (isset($conn) && $conn instanceof mysqli) {
        return $conn;
    }

    return null;
}

function payment_config(): array
{
    return [
        'provider' => 'CINETPAY',
        'api_key' => trim((string) getenv('CINETPAY_API_KEY')),
        'site_id' => trim((string) getenv('CINETPAY_SITE_ID')),
        'currency' => trim((string) (getenv('CINETPAY_CURRENCY') ?: 'XOF')),
        'init_url' => 'https://api-checkout.cinetpay.com/v2/payment',
        'check_url' => 'https://api-checkout.cinetpay.com/v2/payment/check',
    ];
}

function payment_config_ready(array $cfg): bool
{
    return $cfg['api_key'] !== '' && $cfg['site_id'] !== '';
}

function payment_base_url(): string
{
    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/paiement.php';
    $dir = str_replace('\\', '/', dirname($scriptName));
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $dir = '';
    }

    return $scheme . '://' . $host . $dir . '/paiement.php';
}

function payment_post_json(string $url, array $payload): array
{
    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'http_code' => 0,
            'body' => [],
            'error' => 'cURL non disponible.',
            'raw' => '',
        ];
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return [
            'ok' => false,
            'http_code' => 0,
            'body' => [],
            'error' => 'Impossible d initialiser cURL.',
            'raw' => '',
        ];
    }

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_TIMEOUT => 30,
    ]);

    $raw = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = [];
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }
    }

    return [
        'ok' => $curlError === '' && $httpCode >= 200 && $httpCode < 500,
        'http_code' => $httpCode,
        'body' => $decoded,
        'error' => $curlError,
        'raw' => (string) $raw,
    ];
}

function payment_enum_values(mysqli $db, string $table, string $column): array
{
    $tableSafe = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    $columnSafe = preg_replace('/[^A-Za-z0-9_]/', '', $column);
    if ($tableSafe === '' || $columnSafe === '') {
        return [];
    }

    $sql = "SHOW COLUMNS FROM `$tableSafe` LIKE ?";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('s', $columnSafe);
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return [];
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row || !isset($row['Type'])) {
        return [];
    }

    $type = (string) $row['Type'];
    if (strpos($type, 'enum(') !== 0) {
        return [];
    }

    preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $type, $matches);
    if (empty($matches[1])) {
        return [];
    }

    return array_map(static function ($raw) {
        return stripcslashes((string) $raw);
    }, $matches[1]);
}

function payment_pick_enum(array $allowed, array $needles, string $fallback): string
{
    foreach ($allowed as $value) {
        $lower = strtolower((string) $value);
        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($lower, $needle) !== false) {
                return (string) $value;
            }
        }
    }

    if (!empty($allowed)) {
        return (string) $allowed[0];
    }

    return $fallback;
}

function payment_ensure_table(mysqli $db): void
{
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS Paiement_Transaction (
    id_transaction INT AUTO_INCREMENT PRIMARY KEY,
    id_paiement INT NOT NULL,
    id_commande INT NOT NULL,
    id_client INT NOT NULL,
    transaction_id VARCHAR(64) NOT NULL UNIQUE,
    provider VARCHAR(40) NOT NULL,
    operator_name VARCHAR(40) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'XOF',
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    payment_url TEXT DEFAULT NULL,
    payment_token VARCHAR(255) DEFAULT NULL,
    raw_response LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_pt_paiement (id_paiement),
    INDEX idx_pt_commande (id_commande),
    INDEX idx_pt_client (id_client)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL;
    $db->query($sql);
}

function payment_upsert_transaction(mysqli $db, array $tx): bool
{
    $sql = 'INSERT INTO Paiement_Transaction (id_paiement, id_commande, id_client, transaction_id, provider, operator_name, amount, currency, status, payment_url, payment_token, raw_response, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE operator_name = VALUES(operator_name), amount = VALUES(amount), currency = VALUES(currency), status = VALUES(status), payment_url = VALUES(payment_url), payment_token = VALUES(payment_token), raw_response = VALUES(raw_response), updated_at = NOW()';
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        'iiisssdsssss',
        $tx['id_paiement'],
        $tx['id_commande'],
        $tx['id_client'],
        $tx['transaction_id'],
        $tx['provider'],
        $tx['operator_name'],
        $tx['amount'],
        $tx['currency'],
        $tx['status'],
        $tx['payment_url'],
        $tx['payment_token'],
        $tx['raw_response']
    );

    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function payment_get_transaction(mysqli $db, string $transactionId): ?array
{
    $stmt = $db->prepare('SELECT id_transaction, id_paiement, id_commande, id_client, transaction_id, provider, operator_name, amount, currency, status, payment_url, payment_token, raw_response, created_at, updated_at FROM Paiement_Transaction WHERE transaction_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $transactionId);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return is_array($row) ? $row : null;
}

function payment_verify_remote(array $cfg, string $transactionId): array
{
    $payload = [
        'apikey' => $cfg['api_key'],
        'site_id' => $cfg['site_id'],
        'transaction_id' => $transactionId,
    ];

    return payment_post_json($cfg['check_url'], $payload);
}

function payment_remote_state(array $verify): string
{
    $body = $verify['body'] ?? [];
    $data = is_array($body['data'] ?? null) ? $body['data'] : [];

    $rawStatus = strtoupper(trim((string) ($data['status'] ?? $data['payment_status'] ?? $body['status'] ?? '')));

    if (in_array($rawStatus, ['ACCEPTED', 'SUCCESS', 'PAID', 'COMPLETED'], true)) {
        return 'SUCCESS';
    }

    if (in_array($rawStatus, ['REFUSED', 'FAILED', 'CANCELLED', 'CANCELED', 'EXPIRED'], true)) {
        return 'FAILED';
    }

    return 'PENDING';
}

function payment_apply_status(mysqli $db, array $txRow, string $localState, array $verifyPayload): void
{
    $statuses = payment_enum_values($db, 'Paiement', 'statut');
    $statusPaid = payment_pick_enum($statuses, ['pay'], 'Paye');
    $statusPending = payment_pick_enum($statuses, ['attente'], 'En attente');
    $statusCanceled = payment_pick_enum($statuses, ['annul'], 'Annule');

    $targetStatus = $statusPending;
    if ($localState === 'SUCCESS') {
        $targetStatus = $statusPaid;
    } elseif ($localState === 'FAILED') {
        $targetStatus = $statusCanceled;
    }

    $stmt = $db->prepare('UPDATE Paiement SET statut = ?, date_paiement = CURDATE() WHERE id_paiement = ?');
    if ($stmt) {
        $paymentId = (int) $txRow['id_paiement'];
        $stmt->bind_param('si', $targetStatus, $paymentId);
        $stmt->execute();
        $stmt->close();
    }

    $verifyJson = json_encode($verifyPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $operator = null;
    if (is_array($verifyPayload['body'] ?? null)) {
        $body = $verifyPayload['body'];
        if (is_array($body['data'] ?? null) && !empty($body['data']['payment_method'])) {
            $operator = (string) $body['data']['payment_method'];
        }
    }

    $txStatus = $localState;
    $updateTx = $db->prepare('UPDATE Paiement_Transaction SET status = ?, operator_name = COALESCE(?, operator_name), raw_response = ?, updated_at = NOW() WHERE transaction_id = ?');
    if ($updateTx) {
        $txId = (string) $txRow['transaction_id'];
        $updateTx->bind_param('ssss', $txStatus, $operator, $verifyJson, $txId);
        $updateTx->execute();
        $updateTx->close();
    }
}

function payment_handle_notify(): void
{
    $transactionId = trim((string) ($_POST['transaction_id'] ?? $_GET['transaction_id'] ?? ''));
    if ($transactionId === '') {
        http_response_code(400);
        echo 'missing_transaction_id';
        return;
    }

    $cfg = payment_config();
    if (!payment_config_ready($cfg)) {
        http_response_code(500);
        echo 'payment_config_missing';
        return;
    }

    $db = payment_db_connect();
    if (!$db) {
        http_response_code(500);
        echo 'db_unavailable';
        return;
    }

    payment_ensure_table($db);
    $txRow = payment_get_transaction($db, $transactionId);
    if ($txRow === null) {
        $db->close();
        http_response_code(404);
        echo 'transaction_not_found';
        return;
    }

    $verify = payment_verify_remote($cfg, $transactionId);
    $localState = payment_remote_state($verify);
    payment_apply_status($db, $txRow, $localState, $verify);
    $db->close();

    http_response_code(200);
    echo 'ok';
}

function payment_handle_return(): void
{
    $transactionId = trim((string) ($_GET['transaction_id'] ?? $_POST['transaction_id'] ?? ''));
    $message = 'Transaction introuvable.';
    $state = 'PENDING';

    $cfg = payment_config();
    $db = payment_db_connect();
    if ($db) {
        payment_ensure_table($db);
        if ($transactionId !== '' && payment_config_ready($cfg)) {
            $txRow = payment_get_transaction($db, $transactionId);
            if ($txRow !== null) {
                $verify = payment_verify_remote($cfg, $transactionId);
                $state = payment_remote_state($verify);
                payment_apply_status($db, $txRow, $state, $verify);

                if ($state === 'SUCCESS') {
                    $message = 'Paiement confirme avec succes.';
                } elseif ($state === 'FAILED') {
                    $message = 'Paiement refuse ou annule.';
                } else {
                    $message = 'Paiement en attente de confirmation.';
                }
            }
        }
        $db->close();
    }

    http_response_code(200);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Resultat paiement</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #eef5ff; color: #11203a; }
        .wrap { max-width: 760px; margin: 32px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #c8dcff; border-radius: 12px; padding: 18px; box-shadow: 0 12px 28px rgba(17, 32, 58, 0.08); }
        h1 { margin-top: 0; font-size: 22px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-weight: 700; font-size: 12px; }
        .ok { background: #d7f5e5; color: #0d6a3d; }
        .ko { background: #ffe4e4; color: #8a1f1f; }
        .wait { background: #fff3d6; color: #8a5a10; }
        .actions { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
        a { text-decoration: none; border: 1px solid #9fc0f5; color: #15478f; border-radius: 8px; padding: 10px 12px; font-weight: 700; background: #eef5ff; }
      </style>
    </head>
    <body>
      <div class="wrap">
        <section class="card">
          <h1>Resultat du paiement</h1>
          <?php if ($state === 'SUCCESS'): ?>
            <span class="badge ok">Paiement reussi</span>
          <?php elseif ($state === 'FAILED'): ?>
            <span class="badge ko">Paiement echoue</span>
          <?php else: ?>
            <span class="badge wait">Paiement en attente</span>
          <?php endif; ?>
          <p style="margin-top:12px;"><?php echo payment_h($message); ?></p>
          <?php if ($transactionId !== ''): ?>
            <p style="color:#46618c;font-size:13px;">Transaction: <?php echo payment_h($transactionId); ?></p>
          <?php endif; ?>
          <div class="actions">
            <a href="client/paiement_client.php">Mes paiements</a>
            <a href="fonction_interne/dashboard_client.php">Retour dashboard client</a>
          </div>
        </section>
      </div>
    </body>
    </html>
    <?php
}

$action = trim((string) ($_GET['action'] ?? ''));
if ($action === 'notify') {
    payment_handle_notify();
    exit();
}
if ($action === 'return') {
    payment_handle_return();
    exit();
}

if (!isset($_SESSION['id_client'])) {
    header('Location: fonction/login_client.php');
    exit();
}

$clientId = (int) $_SESSION['id_client'];
$clientName = trim(((string) ($_SESSION['prenom'] ?? '')) . ' ' . ((string) ($_SESSION['nom'] ?? '')));
if ($clientName === '') {
    $clientName = 'Client';
}

$cfg = payment_config();
$db = payment_db_connect();

$error = '';
$notice = '';
$orderRow = null;
$transactionHistory = [];

$orderId = (int) ($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
$paymentId = (int) ($_GET['payment_id'] ?? $_POST['payment_id'] ?? 0);

if (!$db) {
    $error = 'Connexion base de donnees indisponible.';
} elseif ($orderId <= 0 || $paymentId <= 0) {
    $error = 'Commande ou paiement invalide.';
} else {
    payment_ensure_table($db);

    $stmt = $db->prepare('SELECT p.id_paiement, p.id_commande, p.montant, p.mode, p.date_paiement, p.statut AS paiement_statut, c.date_commande, c.statut AS commande_statut, cl.nom, cl.prenom, cl.email, cl.contact FROM Paiement p INNER JOIN Commande_Client c ON c.id_commande = p.id_commande INNER JOIN Client cl ON cl.id_client = c.id_client WHERE p.id_paiement = ? AND c.id_commande = ? AND c.id_client = ? AND p.type = "Client" LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('iii', $paymentId, $orderId, $clientId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $orderRow = $result ? $result->fetch_assoc() : null;
        }
        $stmt->close();
    }

    if (!is_array($orderRow)) {
        $error = 'Paiement introuvable pour cette commande.';
    } else {
        $historyStmt = $db->prepare('SELECT transaction_id, operator_name, amount, currency, status, created_at, updated_at FROM Paiement_Transaction WHERE id_paiement = ? ORDER BY id_transaction DESC');
        if ($historyStmt) {
            $historyStmt->bind_param('i', $paymentId);
            if ($historyStmt->execute()) {
                $historyResult = $historyStmt->get_result();
                while ($historyResult && $row = $historyResult->fetch_assoc()) {
                    $transactionHistory[] = $row;
                }
            }
            $historyStmt->close();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formAction = trim((string) ($_POST['form_action'] ?? ''));

            if ($formAction === 'verify_status') {
                $transactionId = trim((string) ($_POST['transaction_id'] ?? ''));
                if ($transactionId === '') {
                    $error = 'Transaction invalide.';
                } elseif (!payment_config_ready($cfg)) {
                    $error = 'Configuration CinetPay manquante.';
                } else {
                    $txRow = payment_get_transaction($db, $transactionId);
                    if (!$txRow) {
                        $error = 'Transaction locale introuvable.';
                    } else {
                        $verify = payment_verify_remote($cfg, $transactionId);
                        $state = payment_remote_state($verify);
                        payment_apply_status($db, $txRow, $state, $verify);

                        if ($state === 'SUCCESS') {
                            $notice = 'Paiement confirme.';
                        } elseif ($state === 'FAILED') {
                            $error = 'Paiement refuse ou annule.';
                        } else {
                            $notice = 'Paiement encore en attente.';
                        }
                    }
                }
            }

            if ($formAction === 'init_payment' && $error === '') {
                if (!payment_config_ready($cfg)) {
                    $error = 'Configuration CinetPay manquante. Definissez les variables d environnement CINETPAY_API_KEY et CINETPAY_SITE_ID.';
                } else {
                    $selectedMethod = trim((string) ($_POST['payment_method'] ?? 'mobile_money'));
                    $phone = preg_replace('/[^0-9+]/', '', (string) ($_POST['phone'] ?? ''));
                    $email = trim((string) ($_POST['email'] ?? (string) ($orderRow['email'] ?? '')));

                    if ($phone === '' || strlen($phone) < 8) {
                        $error = 'Numero de telephone invalide.';
                    } else {
                        $operatorName = $selectedMethod === 'orange_money' ? 'ORANGE_MONEY' : 'MOBILE_MONEY';
                        $transactionId = 'APPECOM-' . $paymentId . '-' . time() . '-' . random_int(100, 999);
                        $transactionId = substr($transactionId, 0, 64);

                        $notifyUrl = payment_base_url() . '?action=notify';
                        $returnUrl = payment_base_url() . '?action=return&transaction_id=' . urlencode($transactionId);

                        $payload = [
                            'apikey' => $cfg['api_key'],
                            'site_id' => $cfg['site_id'],
                            'transaction_id' => $transactionId,
                            'amount' => (int) round((float) $orderRow['montant']),
                            'currency' => $cfg['currency'],
                            'description' => 'Paiement commande client #' . (int) $orderRow['id_commande'],
                            'notify_url' => $notifyUrl,
                            'return_url' => $returnUrl,
                            'channels' => 'MOBILE_MONEY',
                            'customer_id' => (string) $clientId,
                            'customer_name' => (string) ($orderRow['nom'] ?? ''),
                            'customer_surname' => (string) ($orderRow['prenom'] ?? ''),
                            'customer_email' => $email,
                            'customer_phone_number' => $phone,
                            'metadata' => json_encode([
                                'id_paiement' => (int) $orderRow['id_paiement'],
                                'id_commande' => (int) $orderRow['id_commande'],
                                'operator' => $operatorName,
                            ]),
                            'lock_phone_number' => true,
                        ];

                        $initResponse = payment_post_json($cfg['init_url'], $payload);
                        $body = $initResponse['body'];

                        $paymentUrl = '';
                        $paymentToken = '';
                        $apiCode = (string) ($body['code'] ?? '');
                        if (is_array($body['data'] ?? null)) {
                            $paymentUrl = (string) ($body['data']['payment_url'] ?? '');
                            $paymentToken = (string) ($body['data']['payment_token'] ?? '');
                        }

                        if (($apiCode === '201' || $paymentUrl !== '') && $paymentUrl !== '') {
                            $txData = [
                                'id_paiement' => (int) $orderRow['id_paiement'],
                                'id_commande' => (int) $orderRow['id_commande'],
                                'id_client' => $clientId,
                                'transaction_id' => $transactionId,
                                'provider' => $cfg['provider'],
                                'operator_name' => $operatorName,
                                'amount' => (float) $orderRow['montant'],
                                'currency' => $cfg['currency'],
                                'status' => 'PENDING',
                                'payment_url' => $paymentUrl,
                                'payment_token' => $paymentToken,
                                'raw_response' => json_encode($initResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            ];

                            if (payment_upsert_transaction($db, $txData)) {
                                header('Location: ' . $paymentUrl);
                                $db->close();
                                exit();
                            }

                            $error = 'Transaction locale non enregistree. Paiement non lance.';
                        }

                        $apiMessage = (string) ($body['message'] ?? $initResponse['error'] ?? 'Initialisation du paiement echouee.');
                        $error = 'Echec initialisation paiement: ' . $apiMessage;
                    }
                }
            }
        }
    }
}

if ($db) {
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Paiement Mobile Money</title>
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #edf4ff; color: #10223f; }
    .wrap { max-width: 980px; margin: 26px auto; padding: 0 14px; display: grid; gap: 14px; }
    .card { background: #fff; border: 1px solid #c8dcff; border-radius: 12px; box-shadow: 0 10px 26px rgba(16, 34, 63, 0.08); padding: 16px; }
    h1, h2, h3 { margin-top: 0; }
    .muted { color: #4b6288; font-size: 13px; }
    .alert { border-radius: 10px; padding: 10px 12px; font-size: 13px; }
    .alert.error { border: 1px solid #ffd1d1; background: #fff3f3; color: #8f1f1f; }
    .alert.ok { border: 1px solid #c3eccc; background: #f2fff5; color: #1d6d3a; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 10px; }
    .summary-item { border: 1px solid #d6e5ff; border-radius: 10px; background: #f5f9ff; padding: 10px; }
    .summary-item .label { color: #486188; font-size: 12px; }
    .summary-item .value { margin-top: 6px; font-size: 18px; font-weight: 700; color: #0e49b5; }
    .field { display: grid; gap: 6px; margin-bottom: 10px; }
    label { font-size: 13px; font-weight: 700; color: #233e67; }
    input, select { border: 1px solid #bcd3f8; border-radius: 9px; padding: 10px; font-size: 14px; width: 100%; box-sizing: border-box; }
    .btn { border: none; border-radius: 10px; padding: 11px 14px; color: #fff; font-weight: 700; cursor: pointer; background: linear-gradient(110deg, #0078ff, #0252db); }
    .btn.secondary { background: #eef5ff; color: #17458f; border: 1px solid #9fbff0; }
    .btn.small { padding: 8px 10px; font-size: 12px; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
    .table-wrap { overflow-x: auto; margin-top: 10px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; background: #fff; border-radius: 10px; overflow: hidden; }
    th, td { border-bottom: 1px solid #e4eefb; padding: 8px 6px; text-align: left; }
    th { background: #edf4ff; color: #4b6288; }
    .status { display: inline-block; border-radius: 999px; padding: 4px 9px; font-size: 11px; font-weight: 700; }
    .status.ok { background: #d8f3e2; color: #0f6a3f; }
    .status.pending { background: #fff1d5; color: #8d5d11; }
    .status.fail { background: #ffe1e1; color: #912323; }
    a.link { text-decoration: none; color: #15478f; font-weight: 700; }
    @media (max-width: 900px) {
      .grid { grid-template-columns: 1fr; }
      .summary { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <main class="wrap">
    <section class="card">
      <h1>Paiement Mobile Money / Orange Money</h1>
      <p class="muted">Paiement reel via API CinetPay. Configurez <code>CINETPAY_API_KEY</code> et <code>CINETPAY_SITE_ID</code> sur le serveur.</p>
      <div class="actions">
        <a class="link" href="fonction_interne/dashboard_client.php">Retour dashboard client</a>
        <a class="link" href="client/paiement_client.php">Mes paiements</a>
      </div>
    </section>

    <?php if ($error !== ''): ?>
      <div class="alert error"><?php echo payment_h($error); ?></div>
    <?php endif; ?>
    <?php if ($notice !== ''): ?>
      <div class="alert ok"><?php echo payment_h($notice); ?></div>
    <?php endif; ?>

    <?php if (is_array($orderRow)): ?>
      <section class="card">
        <h2>Commande #<?php echo (int) $orderRow['id_commande']; ?></h2>
        <p class="muted">Client: <?php echo payment_h($clientName); ?></p>
        <div class="summary">
          <article class="summary-item">
            <div class="label">Montant a payer</div>
            <div class="value"><?php echo number_format((float) $orderRow['montant'], 0, ',', ' '); ?> FCFA</div>
          </article>
          <article class="summary-item">
            <div class="label">Statut paiement</div>
            <div class="value" style="font-size:14px;"><?php echo payment_h((string) $orderRow['paiement_statut']); ?></div>
          </article>
          <article class="summary-item">
            <div class="label">Date commande</div>
            <div class="value" style="font-size:14px;"><?php echo payment_h((string) $orderRow['date_commande']); ?></div>
          </article>
        </div>
      </section>

      <section class="grid">
        <article class="card">
          <h3>Lancer le paiement</h3>
          <form method="post">
            <input type="hidden" name="order_id" value="<?php echo (int) $orderRow['id_commande']; ?>">
            <input type="hidden" name="payment_id" value="<?php echo (int) $orderRow['id_paiement']; ?>">
            <input type="hidden" name="form_action" value="init_payment">

            <div class="field">
              <label>Methode</label>
              <select name="payment_method" required>
                <option value="mobile_money">Mobile Money</option>
                <option value="orange_money">Orange Money</option>
              </select>
            </div>

            <div class="field">
              <label>Numero de telephone</label>
              <input type="text" name="phone" placeholder="Ex: 0700000000 ou +2250700000000" required>
            </div>

            <div class="field">
              <label>Email</label>
              <input type="email" name="email" value="<?php echo payment_h((string) ($orderRow['email'] ?? '')); ?>" required>
            </div>

            <?php if (!payment_config_ready($cfg)): ?>
              <p class="muted" style="color:#8f1f1f;">Configuration API absente: impossible de lancer un paiement reel tant que les cles CinetPay ne sont pas definies.</p>
            <?php endif; ?>

            <button class="btn" type="submit" <?php echo payment_config_ready($cfg) ? '' : 'disabled'; ?>>Payer maintenant</button>
          </form>
        </article>

        <article class="card">
          <h3>Verifier un paiement</h3>
          <p class="muted">Si le client a deja ete redirige vers CinetPay, utilisez cette verification pour recuperer le statut reel.</p>
          <form method="post">
            <input type="hidden" name="order_id" value="<?php echo (int) $orderRow['id_commande']; ?>">
            <input type="hidden" name="payment_id" value="<?php echo (int) $orderRow['id_paiement']; ?>">
            <input type="hidden" name="form_action" value="verify_status">
            <div class="field">
              <label>ID transaction CinetPay</label>
              <input type="text" name="transaction_id" required>
            </div>
            <button class="btn secondary" type="submit">Verifier statut</button>
          </form>
        </article>
      </section>

      <section class="card">
        <h3>Historique local des transactions</h3>
        <?php if (!empty($transactionHistory)): ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Transaction</th>
                  <th>Operateur</th>
                  <th>Montant</th>
                  <th>Statut</th>
                  <th>Creee</th>
                  <th>Maj</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($transactionHistory as $tx): ?>
                  <?php
                    $txStatus = strtoupper((string) ($tx['status'] ?? 'PENDING'));
                    $statusClass = 'pending';
                    if ($txStatus === 'SUCCESS') {
                        $statusClass = 'ok';
                    } elseif ($txStatus === 'FAILED') {
                        $statusClass = 'fail';
                    }
                  ?>
                  <tr>
                    <td><?php echo payment_h((string) $tx['transaction_id']); ?></td>
                    <td><?php echo payment_h((string) ($tx['operator_name'] ?? '-')); ?></td>
                    <td><?php echo number_format((float) $tx['amount'], 0, ',', ' '); ?> <?php echo payment_h((string) $tx['currency']); ?></td>
                    <td><span class="status <?php echo $statusClass; ?>"><?php echo payment_h($txStatus); ?></span></td>
                    <td><?php echo payment_h((string) $tx['created_at']); ?></td>
                    <td><?php echo payment_h((string) $tx['updated_at']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="muted">Aucune transaction enregistree pour ce paiement.</p>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
