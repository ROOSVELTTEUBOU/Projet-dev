<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../fonction/login_admin.php');
    exit();
}

$adminId = (int) $_SESSION['id_utilisateur'];
$adminName = isset($_SESSION['user']) ? trim((string) $_SESSION['user']) : 'Administrateur';
if ($adminName === '') {
    $adminName = 'Administrateur';
}

$parts = preg_split('/\s+/', $adminName);
$adminInitials = '';
if (is_array($parts)) {
    foreach (array_slice($parts, 0, 2) as $part) {
        if ($part !== '') {
            $adminInitials .= strtoupper(substr($part, 0, 1));
        }
    }
}
if ($adminInitials === '') {
    $adminInitials = 'AD';
}

$adminDb = null;
$adminDbError = null;
$dbPath = __DIR__ . '/../connexion_bd/connexion_bd.php';

if (file_exists($dbPath)) {
    require $dbPath;

    if (isset($conn) && $conn instanceof mysqli) {
        $adminDb = $conn;

        $roleStmt = $adminDb->prepare('SELECT role, nom FROM Utilisateur WHERE id_utilisateur = ? LIMIT 1');
        if ($roleStmt) {
            $roleStmt->bind_param('i', $adminId);
            if ($roleStmt->execute()) {
                $roleResult = $roleStmt->get_result();
                $roleRow = $roleResult ? $roleResult->fetch_assoc() : null;
                if (!$roleRow || strcasecmp((string) ($roleRow['role'] ?? ''), 'Administrateur') !== 0) {
                    $roleStmt->close();
                    session_unset();
                    session_destroy();
                    header('Location: ../fonction/login_admin.php');
                    exit();
                }
                if (!empty($roleRow['nom'])) {
                    $adminName = trim((string) $roleRow['nom']);
                }
            }
            $roleStmt->close();
        }
    } else {
        $adminDbError = 'Connexion base indisponible.';
    }
} else {
    $adminDbError = 'Fichier de connexion base introuvable.';
}

if ($adminName === '') {
    $adminName = 'Administrateur';
}

$parts = preg_split('/\s+/', $adminName);
$adminInitials = '';
if (is_array($parts)) {
    foreach (array_slice($parts, 0, 2) as $part) {
        if ($part !== '') {
            $adminInitials .= strtoupper(substr($part, 0, 1));
        }
    }
}
if ($adminInitials === '') {
    $adminInitials = 'AD';
}

$adminNav = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '../fonction_interne/dashboard_admin.php'],
    ['key' => 'familles', 'label' => 'Familles', 'href' => 'familles.php'],
    ['key' => 'produits', 'label' => 'Produits', 'href' => 'produits.php'],
    ['key' => 'clients', 'label' => 'Clients', 'href' => 'clients.php'],
    ['key' => 'fournisseurs', 'label' => 'Fournisseurs', 'href' => 'fournisseurs.php'],
    ['key' => 'utilisateurs', 'label' => 'Utilisateurs', 'href' => 'utilisateurs.php'],
    ['key' => 'commande_client', 'label' => 'Cmd clients', 'href' => 'commande_client.php'],
    ['key' => 'commande_fournisseur', 'label' => 'Cmd fournisseurs', 'href' => 'commande_fournisseur.php'],
    ['key' => 'paiement_client', 'label' => 'Paiements', 'href' => 'paiement_client.php'],
    ['key' => 'logs', 'label' => 'Logs', 'href' => 'logs.php'],
];

function admin_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function admin_scalar(mysqli $db, string $sql, string $types = '', array $params = [], $default = 0)
{
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return $default;
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        return $default;
    }

    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return $default;
    }

    $row = $result->fetch_row();
    $stmt->close();

    return $row[0] ?? $default;
}

function admin_enum_values(mysqli $db, string $table, string $column): array
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

function admin_in_allowed(string $value, array $allowed): bool
{
    foreach ($allowed as $item) {
        if ((string) $item === $value) {
            return true;
        }
    }

    return false;
}

function admin_status_class($value): string
{
    $status = strtolower(trim((string) $value));

    if ($status === '') {
        return 'badge info';
    }

    if (strpos($status, 'valid') === 0 || strpos($status, 'liv') === 0 || strpos($status, 'rec') === 0 || strpos($status, 'pay') === 0) {
        return 'badge success';
    }

    if (strpos($status, 'attente') !== false) {
        return 'badge warning';
    }

    if (strpos($status, 'annul') === 0 || strpos($status, 'rej') === 0) {
        return 'badge danger';
    }

    return 'badge info';
}

function admin_log_action(mysqli $db, int $userId, string $action): void
{
    $stmt = $db->prepare('INSERT INTO Logs (id_utilisateur, action) VALUES (?, ?)');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('is', $userId, $action);
    $stmt->execute();
    $stmt->close();
}
