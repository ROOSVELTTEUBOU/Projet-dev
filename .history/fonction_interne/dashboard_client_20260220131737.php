<?php
session_start();

if (!isset($_SESSION['id_client'])) {
    header('Location: ../fonction/login_client.php');
    exit();
}

function fetchScalar(mysqli $conn, string $sql, string $types = '', array $params = [], $default = 0)
{
    $stmt = $conn->prepare($sql);
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

$clientId = (int) $_SESSION['id_client'];
$prenom = isset($_SESSION['prenom']) ? trim((string) $_SESSION['prenom']) : '';
$nom = isset($_SESSION['nom']) ? trim((string) $_SESSION['nom']) : '';
$clientName = trim($prenom . ' ' . $nom);
if ($clientName === '') {
    $clientName = 'Client';
}

$stats = [
    'orders_total' => 0,
    'orders_pending' => 0,
    'orders_delivered' => 0,
    'amount_paid' => 0.0,
    'last_order_date' => null,
];

$clientProfile = [
    'email' => '-',
    'contact' => '-',
    'ville' => '-',
];

$recentOrders = [];
$recentPayments = [];
$dbError = null;

$dbPath = __DIR__ . '/../connexion_bd/connexion_bd.php';
if (file_exists($dbPath)) {
    require $dbPath;

    if (isset($conn) && $conn instanceof mysqli) {
        $stats['orders_total'] = (int) fetchScalar(
            $conn,
            'SELECT COUNT(*) FROM Commande_Client WHERE id_client = ?',
            'i',
            [$clientId],
            0
        );

        $stats['orders_pending'] = (int) fetchScalar(
            $conn,
            "SELECT COUNT(*) FROM Commande_Client WHERE id_client = ? AND statut = 'En attente'",
            'i',
            [$clientId],
            0
        );

        $stats['orders_delivered'] = (int) fetchScalar(
            $conn,
            "SELECT COUNT(*) FROM Commande_Client WHERE id_client = ? AND statut = 'Livree'",
            'i',
            [$clientId],
            0
        );

        $stats['amount_paid'] = (float) fetchScalar(
            $conn,
            "SELECT COALESCE(SUM(p.montant), 0) FROM Paiement p INNER JOIN Commande_Client c ON c.id_commande = p.id_commande WHERE c.id_client = ? AND p.type = 'Client'",
            'i',
            [$clientId],
            0
        );

        $stats['last_order_date'] = fetchScalar(
            $conn,
            'SELECT MAX(date_commande) FROM Commande_Client WHERE id_client = ?',
            'i',
            [$clientId],
            null
        );

        $profileStmt = $conn->prepare('SELECT email, contact, ville FROM Client WHERE id_client = ? LIMIT 1');
        if ($profileStmt) {
            $profileStmt->bind_param('i', $clientId);
            if ($profileStmt->execute()) {
                $profileResult = $profileStmt->get_result();
                if ($profileResult && $profileRow = $profileResult->fetch_assoc()) {
                    $clientProfile['email'] = $profileRow['email'] ?: '-';
                    $clientProfile['contact'] = $profileRow['contact'] ?: '-';
                    $clientProfile['ville'] = $profileRow['ville'] ?: '-';
                }
            }
            $profileStmt->close();
        }

        $ordersStmt = $conn->prepare('SELECT id_commande, date_commande, statut FROM Commande_Client WHERE id_client = ? ORDER BY date_commande DESC, id_commande DESC LIMIT 5');
        if ($ordersStmt) {
            $ordersStmt->bind_param('i', $clientId);
            if ($ordersStmt->execute()) {
                $ordersResult = $ordersStmt->get_result();
                while ($ordersResult && $row = $ordersResult->fetch_assoc()) {
                    $recentOrders[] = $row;
                }
            }
            $ordersStmt->close();
        }

        $paymentsStmt = $conn->prepare("SELECT p.date_paiement, p.montant, p.mode, p.statut FROM Paiement p INNER JOIN Commande_Client c ON c.id_commande = p.id_commande WHERE c.id_client = ? AND p.type = 'Client' ORDER BY p.date_paiement DESC, p.id_paiement DESC LIMIT 5");
        if ($paymentsStmt) {
            $paymentsStmt->bind_param('i', $clientId);
            if ($paymentsStmt->execute()) {
                $paymentsResult = $paymentsStmt->get_result();
                while ($paymentsResult && $row = $paymentsResult->fetch_assoc()) {
                    $recentPayments[] = $row;
                }
            }
            $paymentsStmt->close();
        }

        $conn->close();
    } else {
        $dbError = 'Connexion base indisponible.';
    }
} else {
    $dbError = 'Fichier de connexion base introuvable.';
}

$actions = [
    [
        'title' => 'Catalogue',
        'description' => 'Consulter tous les produits disponibles.',
        'href' => '../acceuil.php',
    ],
    [
        'title' => 'Mon panier',
        'description' => 'Verifier, modifier ou vider votre panier.',
        'href' => '../panier.php',
    ],
    [
        'title' => 'Mes commandes',
        'description' => 'Suivre le statut de vos commandes clients.',
        'href' => '../commande_client.php',
    ],
    [
        'title' => 'Mes paiements',
        'description' => 'Voir les paiements et leur statut.',
        'href' => '../paiement_client.php',
    ],
    [
        'title' => 'Mes factures',
        'description' => 'Consulter et telecharger les factures.',
        'href' => '../facture_client.php',
    ],
    [
        'title' => 'Mon profil',
        'description' => 'Mettre a jour vos informations personnelles.',
        'href' => '../profil_client.php',
    ],
    [
        'title' => 'Support',
        'description' => 'Contacter le service client.',
        'href' => '../support_client.php',
    ],
    [
        'title' => 'Deconnexion',
        'description' => 'Fermer votre session en toute securite.',
        'href' => 'logout.php',
    ],
];

$availableCount = 0;
foreach ($actions as $action) {
    if (file_exists(__DIR__ . '/' . $action['href'])) {
        $availableCount++;
    }
}

$featureItems = [
    'Consulter le catalogue produit',
    'Ajouter et gerer les articles dans le panier',
    'Passer une commande client',
    'Suivre les statuts de commande',
    'Consulter et suivre les paiements',
    'Acceder aux factures',
    'Mettre a jour les informations du compte',
    'Se deconnecter de facon securisee',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Client</title>
  <style>
    :root {
      --white: #ffffff;
      --blue-50: #eef5ff;
      --blue-100: #d7e7ff;
      --blue-400: #2f76ff;
      --blue-600: #0f4dcc;
      --blue-800: #0a2f7a;
      --black: #0c111a;
      --slate: #45556f;
      --border: #d7e2f4;
      --shadow: 0 10px 30px rgba(12, 17, 26, 0.08);
      --radius: 14px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
      color: var(--black);
      background: linear-gradient(170deg, var(--blue-50), #f8fbff 48%, var(--white));
    }

    .topbar {
      background: linear-gradient(90deg, var(--black), var(--blue-800) 55%, var(--blue-600));
      color: var(--white);
      padding: 18px 26px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      flex-wrap: wrap;
    }

    .brand h1 {
      margin: 0;
      font-size: 22px;
      letter-spacing: 0.3px;
    }

    .brand p {
      margin: 4px 0 0;
      color: #c9ddff;
      font-size: 13px;
    }

    .client-box {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: var(--white);
      color: var(--blue-800);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
    }

    .logout-link {
      text-decoration: none;
      color: var(--white);
      border: 1px solid rgba(255, 255, 255, 0.45);
      border-radius: 10px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 600;
      transition: background 0.2s ease;
    }

    .logout-link:hover {
      background: rgba(255, 255, 255, 0.16);
    }

    .container {
      max-width: 1180px;
      margin: 24px auto 40px;
      padding: 0 18px;
      display: grid;
      gap: 18px;
    }

    .panel {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 18px;
    }

    .intro {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 18px;
    }

    .intro h2 {
      margin: 0 0 8px;
      font-size: 22px;
    }

    .intro p {
      margin: 0;
      color: var(--slate);
      line-height: 1.5;
    }

    .mini-list {
      margin: 12px 0 0;
      padding-left: 18px;
      color: var(--slate);
    }

    .mini-list li {
      margin: 6px 0;
    }

    .status-box {
      background: linear-gradient(165deg, #f2f7ff, #e4eeff);
      border: 1px solid #c7dafd;
      border-radius: 12px;
      padding: 14px;
    }

    .status-box .label {
      color: var(--slate);
      font-size: 13px;
      margin-bottom: 6px;
    }

    .status-box .value {
      font-size: 28px;
      font-weight: 700;
      color: var(--blue-800);
    }

    .stats {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
    }

    .stat {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 14px;
    }

    .stat .name {
      color: var(--slate);
      font-size: 13px;
    }

    .stat .num {
      margin-top: 8px;
      font-size: 26px;
      font-weight: 700;
      color: var(--black);
    }

    .stat .sub {
      margin-top: 6px;
      color: var(--slate);
      font-size: 12px;
    }

    .actions-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
    }

    .action {
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 14px;
      background: #fdfefe;
      display: block;
      text-decoration: none;
      color: inherit;
      transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .action:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 18px rgba(16, 51, 110, 0.11);
    }

    .action.disabled {
      opacity: 0.65;
      background: #f4f7fc;
      cursor: not-allowed;
      pointer-events: none;
    }

    .tag {
      display: inline-block;
      margin-bottom: 8px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      padding: 4px 9px;
      letter-spacing: 0.2px;
    }

    .tag.live {
      background: #d9ebff;
      color: #0a4aa0;
    }

    .tag.todo {
      background: #edf2fb;
      color: #4d5c76;
    }

    .action h3 {
      margin: 0;
      font-size: 17px;
      color: var(--black);
    }

    .action p {
      margin: 7px 0 0;
      font-size: 13px;
      line-height: 1.45;
      color: var(--slate);
    }

    .tables {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .table-box h3 {
      margin: 0 0 12px;
      font-size: 17px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    th,
    td {
      border-bottom: 1px solid #e7eefb;
      padding: 9px 6px;
      text-align: left;
    }

    th {
      color: var(--slate);
      font-weight: 600;
    }

    .empty {
      color: var(--slate);
      font-size: 13px;
      margin: 8px 0 0;
    }

    .error {
      border: 1px solid #ffd6d6;
      background: #fff5f5;
      color: #8a1f1f;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 13px;
    }

    @media (max-width: 1050px) {
      .stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .actions-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .intro,
      .tables {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 620px) {
      .stats,
      .actions-grid {
        grid-template-columns: 1fr;
      }

      .topbar {
        padding: 14px;
      }

      .brand h1 {
        font-size: 19px;
      }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <h1>Espace Client</h1>
      <p>Tableau de bord de gestion de compte</p>
    </div>

    <div class="client-box">
      <div style="text-align:right;">
        <div style="font-weight:700;"><?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="font-size:12px;color:#d5e6ff;">ID client: <?php echo $clientId; ?></div>
      </div>
      <div class="avatar"><?php echo strtoupper(substr($clientName, 0, 1)); ?></div>
      <a class="logout-link" href="logout.php">Se deconnecter</a>
    </div>
  </header>

  <main class="container">
    <?php if ($dbError !== null): ?>
      <div class="error"><?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <section class="panel intro">
      <div>
        <h2>Bienvenue sur votre dashboard</h2>
        <p>Ce panneau regroupe les principales actions client: consultation du catalogue, gestion du panier, commandes, paiements, factures et suivi de compte.</p>
        <ul class="mini-list">
          <?php foreach ($featureItems as $item): ?>
            <li><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <aside class="status-box">
        <div class="label">Fonctionnalites disponibles</div>
        <div class="value"><?php echo $availableCount; ?> / <?php echo count($actions); ?></div>
        <div style="margin-top:8px;font-size:13px;color:#34507d;">
          Email: <?php echo htmlspecialchars((string) $clientProfile['email'], ENT_QUOTES, 'UTF-8'); ?><br>
          Contact: <?php echo htmlspecialchars((string) $clientProfile['contact'], ENT_QUOTES, 'UTF-8'); ?><br>
          Ville: <?php echo htmlspecialchars((string) $clientProfile['ville'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
      </aside>
    </section>

    <section class="stats">
      <article class="stat">
        <div class="name">Total commandes</div>
        <div class="num"><?php echo $stats['orders_total']; ?></div>
        <div class="sub">Toutes periodes</div>
      </article>

      <article class="stat">
        <div class="name">Commandes en attente</div>
        <div class="num"><?php echo $stats['orders_pending']; ?></div>
        <div class="sub">A traiter</div>
      </article>

      <article class="stat">
        <div class="name">Commandes livrees</div>
        <div class="num"><?php echo $stats['orders_delivered']; ?></div>
        <div class="sub">Finalisees</div>
      </article>

      <article class="stat">
        <div class="name">Montant paye</div>
        <div class="num"><?php echo number_format((float) $stats['amount_paid'], 0, ',', ' '); ?> FCFA</div>
        <div class="sub">Derniere commande: <?php echo $stats['last_order_date'] ? htmlspecialchars((string) $stats['last_order_date'], ENT_QUOTES, 'UTF-8') : '-'; ?></div>
      </article>
    </section>

    <section class="panel">
      <h2 style="margin:0 0 14px;">Actions client</h2>
      <div class="actions-grid">
        <?php foreach ($actions as $action): ?>
          <?php $isAvailable = file_exists(__DIR__ . '/' . $action['href']); ?>
          <a class="action <?php echo $isAvailable ? '' : 'disabled'; ?>" href="<?php echo htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8'); ?>">
            <span class="tag <?php echo $isAvailable ? 'live' : 'todo'; ?>"><?php echo $isAvailable ? 'Disponible' : 'A implementer'; ?></span>
            <h3><?php echo htmlspecialchars($action['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><?php echo htmlspecialchars($action['description'], ENT_QUOTES, 'UTF-8'); ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="tables">
      <article class="panel table-box">
        <h3>Dernieres commandes</h3>
        <?php if (!empty($recentOrders)): ?>
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $order): ?>
                <tr>
                  <td><?php echo (int) $order['id_commande']; ?></td>
                  <td><?php echo htmlspecialchars((string) $order['date_commande'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars((string) $order['statut'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="empty">Aucune commande enregistree pour le moment.</p>
        <?php endif; ?>
      </article>

      <article class="panel table-box">
        <h3>Derniers paiements</h3>
        <?php if (!empty($recentPayments)): ?>
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Mode</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentPayments as $payment): ?>
                <tr>
                  <td><?php echo htmlspecialchars((string) $payment['date_paiement'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo number_format((float) $payment['montant'], 0, ',', ' '); ?> FCFA</td>
                  <td><?php echo htmlspecialchars((string) ($payment['mode'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars((string) ($payment['statut'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="empty">Aucun paiement enregistre pour ce compte.</p>
        <?php endif; ?>
      </article>
    </section>
  </main>
</body>
</html>
