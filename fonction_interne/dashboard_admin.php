<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../fonction/login_admin.php');
    exit();
}

$userName = isset($_SESSION['user']) ? trim((string) $_SESSION['user']) : 'Administrateur';
if ($userName === '') {
    $userName = 'Administrateur';
}

$parts = preg_split('/\s+/', $userName);
$initials = '';
if (is_array($parts)) {
    foreach (array_slice($parts, 0, 2) as $part) {
        if ($part !== '') {
            $initials .= strtoupper(substr($part, 0, 1));
        }
    }
}
if ($initials === '') {
    $initials = 'AD';
}

function dashboard_admin_scalar(mysqli $db, string $sql, $default = 0)
{
    $result = $db->query($sql);
    if (!$result) {
        return $default;
    }

    $row = $result->fetch_row();
    $result->free();

    return $row[0] ?? $default;
}

$stats = [
    'products_total' => 0,
    'clients_total' => 0,
    'orders_today' => 0,
    'revenue_month' => 0.0,
    'users_total' => 0,
    'suppliers_total' => 0,
];

$logs = [];
$recentClientOrders = [];
$dbError = null;

$dbPath = __DIR__ . '/../connexion_bd/connexion_bd.php';
if (file_exists($dbPath)) {
    require $dbPath;

    if (isset($conn) && $conn instanceof mysqli) {
        $stats['products_total'] = (int) dashboard_admin_scalar($conn, 'SELECT COUNT(*) FROM Produit');
        $stats['clients_total'] = (int) dashboard_admin_scalar($conn, 'SELECT COUNT(*) FROM Client');
        $stats['orders_today'] = (int) dashboard_admin_scalar($conn, 'SELECT COUNT(*) FROM Commande_Client WHERE date_commande = CURDATE()');
        $stats['revenue_month'] = (float) dashboard_admin_scalar($conn, "SELECT COALESCE(SUM(montant), 0) FROM Paiement WHERE type = 'Client' AND YEAR(date_paiement) = YEAR(CURDATE()) AND MONTH(date_paiement) = MONTH(CURDATE())");
        $stats['users_total'] = (int) dashboard_admin_scalar($conn, 'SELECT COUNT(*) FROM Utilisateur');
        $stats['suppliers_total'] = (int) dashboard_admin_scalar($conn, 'SELECT COUNT(*) FROM Fournisseur');

        $logsResult = $conn->query('SELECT l.date_action, l.action, u.nom AS user_nom FROM Logs l LEFT JOIN Utilisateur u ON u.id_utilisateur = l.id_utilisateur ORDER BY l.date_action DESC LIMIT 6');
        if ($logsResult) {
            while ($row = $logsResult->fetch_assoc()) {
                $logs[] = $row;
            }
            $logsResult->free();
        }

        $ordersResult = $conn->query('SELECT c.id_commande, c.date_commande, c.statut, cl.nom, cl.prenom, COALESCE(SUM(cc.quantite), 0) AS total_articles FROM Commande_Client c LEFT JOIN Client cl ON cl.id_client = c.id_client LEFT JOIN Concerner_Client cc ON cc.id_commande = c.id_commande GROUP BY c.id_commande, c.date_commande, c.statut, cl.nom, cl.prenom ORDER BY c.date_commande DESC, c.id_commande DESC LIMIT 6');
        if ($ordersResult) {
            while ($row = $ordersResult->fetch_assoc()) {
                $recentClientOrders[] = $row;
            }
            $ordersResult->free();
        }

        $conn->close();
    } else {
        $dbError = 'Connexion base indisponible.';
    }
} else {
    $dbError = 'Fichier de connexion base introuvable.';
}

$modules = [
    [
        'title' => 'Familles',
        'description' => 'Administrer les familles de produits.',
        'href' => '../admin/familles.php',
        'check' => __DIR__ . '/../admin/familles.php',
    ],
    [
        'title' => 'Produits',
        'description' => 'Gerer le catalogue de produits.',
        'href' => '../admin/produits.php',
        'check' => __DIR__ . '/../admin/produits.php',
    ],
    [
        'title' => 'Clients',
        'description' => 'Administrer les comptes clients.',
        'href' => '../admin/clients.php',
        'check' => __DIR__ . '/../admin/clients.php',
    ],
    [
        'title' => 'Fournisseurs',
        'description' => 'Gerer les partenaires fournisseurs.',
        'href' => '../admin/fournisseurs.php',
        'check' => __DIR__ . '/../admin/fournisseurs.php',
    ],
    [
        'title' => 'Utilisateurs',
        'description' => 'Piloter les comptes admin et employes.',
        'href' => '../admin/utilisateurs.php',
        'check' => __DIR__ . '/../admin/utilisateurs.php',
    ],
    [
        'title' => 'Commandes clients',
        'description' => 'Suivre et modifier les commandes clients.',
        'href' => '../admin/commande_client.php',
        'check' => __DIR__ . '/../admin/commande_client.php',
    ],
    [
        'title' => 'Commandes fournisseurs',
        'description' => 'Suivre les commandes fournisseurs.',
        'href' => '../admin/commande_fournisseur.php',
        'check' => __DIR__ . '/../admin/commande_fournisseur.php',
    ],
    [
        'title' => 'Paiements clients',
        'description' => 'Valider et gerer les paiements clients.',
        'href' => '../admin/paiement_client.php',
        'check' => __DIR__ . '/../admin/paiement_client.php',
    ],
    [
        'title' => 'Logs',
        'description' => 'Consulter le journal des actions.',
        'href' => '../admin/logs.php',
        'check' => __DIR__ . '/../admin/logs.php',
    ],
    [
        'title' => 'Dashboard employe',
        'description' => 'Acceder au panneau operations employe.',
        'href' => 'dashboard_employe.php',
        'check' => __DIR__ . '/dashboard_employe.php',
    ],
    [
        'title' => 'Dashboard client',
        'description' => 'Acceder au panneau de suivi client.',
        'href' => 'dashboard_client.php',
        'check' => __DIR__ . '/dashboard_client.php',
    ],
    [
        'title' => 'Accueil public',
        'description' => 'Voir la vitrine du site e-commerce.',
        'href' => '../acceuil.php',
        'check' => __DIR__ . '/../acceuil.php',
    ],
    [
        'title' => 'Deconnexion',
        'description' => 'Terminer la session administrateur.',
        'href' => 'logout.php',
        'check' => __DIR__ . '/logout.php',
    ],
];

$availableModules = 0;
foreach ($modules as $module) {
    if (file_exists($module['check'])) {
        $availableModules++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Administrateur</title>
  <style>
    :root {
      --white: #ffffff;
      --blue-50: #eaf2ff;
      --blue-100: #d7e6ff;
      --blue-400: #4ba0ff;
      --blue-500: #1f88ff;
      --blue-600: #0066ff;
      --blue-700: #0051d6;
      --blue-800: #082f8f;
      --blue-900: #051937;
      --black: #070d18;
      --slate: #42577d;
      --border: #cbddff;
      --shadow: 0 12px 32px rgba(7, 13, 24, 0.1);
      --blue-glow: 0 12px 30px rgba(0, 102, 255, 0.22);
      --radius: 14px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
      color: var(--black);
      background:
        radial-gradient(circle at 12% -12%, rgba(0, 102, 255, 0.24), transparent 34%),
        radial-gradient(circle at 86% 4%, rgba(31, 136, 255, 0.22), transparent 30%),
        linear-gradient(160deg, var(--blue-50), #f4f9ff 45%, var(--white));
    }

    .topbar {
      background: linear-gradient(94deg, #040a16 0%, var(--blue-800) 46%, var(--blue-600) 100%);
      color: var(--white);
      padding: 18px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      box-shadow: 0 15px 34px rgba(0, 74, 190, 0.3);
    }

    .brand h1 {
      margin: 0;
      font-size: 22px;
      letter-spacing: 0.3px;
      text-shadow: 0 0 14px rgba(191, 215, 255, 0.42);
    }

    .brand p {
      margin: 6px 0 0;
      font-size: 13px;
      color: #d6e8ff;
    }

    .identity {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: linear-gradient(160deg, #ffffff, #e7f1ff);
      color: var(--blue-700);
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid rgba(0, 102, 255, 0.34);
      box-shadow: 0 0 0 4px rgba(0, 102, 255, 0.14);
    }

    .logout-link {
      text-decoration: none;
      color: var(--white);
      border: 1px solid rgba(190, 218, 255, 0.95);
      border-radius: 10px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.18s ease;
    }

    .logout-link:hover {
      background: var(--blue-600);
      border-color: var(--blue-600);
      box-shadow: var(--blue-glow);
      transform: translateY(-1px);
    }

    .container {
      width: 100%;
      max-width: 1220px;
      margin: 22px auto;
      padding: 0 18px 28px;
      display: grid;
      gap: 16px;
    }

    .panel {
      background: linear-gradient(180deg, #ffffff, #f3f8ff);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 18px;
    }

    .panel h2,
    .panel h3 {
      margin-top: 0;
    }

    .muted {
      color: var(--slate);
    }

    .grid-4 {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
    }

    .grid-2 {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
    }

    .cards-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
    }

    .stat {
      border: 1px solid #c4dbff;
      border-radius: 12px;
      padding: 14px;
      background: linear-gradient(180deg, #ffffff, #edf5ff);
      transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }

    .stat:hover {
      transform: translateY(-2px);
      border-color: #8eb9ff;
      box-shadow: 0 10px 24px rgba(0, 81, 214, 0.2);
    }

    .stat .label {
      color: var(--slate);
      font-size: 13px;
    }

    .stat .value {
      margin-top: 6px;
      font-size: 24px;
      font-weight: 700;
      color: var(--blue-700);
    }

    .stat .sub {
      margin-top: 4px;
      color: var(--slate);
      font-size: 12px;
    }

    .action-card {
      border: 1px solid #c6dbff;
      border-radius: 12px;
      padding: 14px;
      background: linear-gradient(180deg, #ffffff, #f1f7ff);
      transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
      text-decoration: none;
      color: inherit;
      position: relative;
      overflow: hidden;
      display: block;
    }

    .action-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #68a9ff, var(--blue-600));
    }

    .action-card:hover {
      transform: translateY(-3px);
      border-color: #8db8ff;
      box-shadow: 0 14px 28px rgba(0, 81, 214, 0.18);
    }

    .action-card.disabled {
      opacity: 0.68;
      pointer-events: none;
      filter: grayscale(0.1);
    }

    .module-meta {
      display: inline-block;
      margin-bottom: 8px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      padding: 4px 9px;
      background: #dcebff;
      color: #0f49b2;
    }

    .action-card h3 {
      margin: 8px 0;
      font-size: 17px;
    }

    .action-card p {
      margin: 0;
      font-size: 13px;
      color: var(--slate);
    }

    .table-wrap {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
      background: #ffffff;
      border-radius: 10px;
      overflow: hidden;
    }

    th,
    td {
      border-bottom: 1px solid #e6eefb;
      padding: 10px 8px;
      text-align: left;
      vertical-align: middle;
    }

    th {
      color: var(--slate);
      font-weight: 600;
      background: #edf4ff;
    }

    tbody tr:hover {
      background: #f3f8ff;
    }

    .badge {
      display: inline-block;
      border-radius: 999px;
      padding: 4px 9px;
      font-size: 11px;
      font-weight: 700;
      color: #0f49b2;
      background: #dcebff;
    }

    .alert {
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 13px;
      border: 1px solid #f2c7c7;
      background: #fff2f2;
      color: #8c2424;
    }

    @media (max-width: 1050px) {
      .grid-4,
      .cards-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .grid-2 {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 700px) {
      .grid-4,
      .cards-grid {
        grid-template-columns: 1fr;
      }

      .topbar {
        padding: 14px;
      }

      .container {
        padding: 0 14px 24px;
      }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <h1>Dashboard Administrateur</h1>
      <p>Supervision globale des operations et modules</p>
    </div>

    <div class="identity">
      <div style="text-align:right;">
        <div style="font-weight:700;"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="font-size:12px;color:#d6e5ff;">Session active</div>
      </div>
      <div class="avatar"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
      <a href="logout.php" class="logout-link">Se deconnecter</a>
    </div>
  </header>

  <main class="container">
    <?php if ($dbError !== null): ?>
      <div class="alert"><?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <section class="panel">
      <h2>Vue d ensemble administrateur</h2>
      <p class="muted">Suivi des indicateurs cles de la plateforme et acces rapide aux modules metier.</p>

      <div class="grid-4" style="margin-top:12px;">
        <article class="stat">
          <div class="label">Produits</div>
          <div class="value"><?php echo $stats['products_total']; ?></div>
          <div class="sub">Catalogue actif</div>
        </article>
        <article class="stat">
          <div class="label">Clients</div>
          <div class="value"><?php echo $stats['clients_total']; ?></div>
          <div class="sub">Comptes clients</div>
        </article>
        <article class="stat">
          <div class="label">Commandes du jour</div>
          <div class="value"><?php echo $stats['orders_today']; ?></div>
          <div class="sub">Date: <?php echo date('Y-m-d'); ?></div>
        </article>
        <article class="stat">
          <div class="label">CA du mois</div>
          <div class="value"><?php echo number_format((float) $stats['revenue_month'], 0, ',', ' '); ?> FCFA</div>
          <div class="sub">Paiements clients</div>
        </article>
      </div>

      <div class="grid-2" style="margin-top:14px;">
        <article class="stat">
          <div class="label">Utilisateurs internes</div>
          <div class="value"><?php echo $stats['users_total']; ?></div>
          <div class="sub">Admin + employes</div>
        </article>
        <article class="stat">
          <div class="label">Fournisseurs</div>
          <div class="value"><?php echo $stats['suppliers_total']; ?></div>
          <div class="sub">Partenaires actifs</div>
        </article>
      </div>
    </section>

    <section class="panel">
      <h3>Modules de supervision</h3>
      <p class="muted">Modules disponibles: <?php echo $availableModules; ?> / <?php echo count($modules); ?></p>

      <div class="cards-grid" style="margin-top:12px;">
        <?php foreach ($modules as $module): ?>
          <?php $isAvailable = file_exists($module['check']); ?>
          <a class="action-card <?php echo $isAvailable ? '' : 'disabled'; ?>" href="<?php echo htmlspecialchars($module['href'], ENT_QUOTES, 'UTF-8'); ?>">
            <span class="module-meta"><?php echo $isAvailable ? 'Disponible' : 'A implementer'; ?></span>
            <h3><?php echo htmlspecialchars($module['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><?php echo htmlspecialchars($module['description'], ENT_QUOTES, 'UTF-8'); ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="panel">
      <h3>Dernieres commandes clients</h3>
      <?php if (!empty($recentClientOrders)): ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Client</th>
                <th>Articles</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentClientOrders as $order): ?>
                <tr>
                  <td><?php echo (int) $order['id_commande']; ?></td>
                  <td><?php echo htmlspecialchars((string) $order['date_commande'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><span class="badge"><?php echo htmlspecialchars(trim(((string) ($order['prenom'] ?? '')) . ' ' . ((string) ($order['nom'] ?? ''))), ENT_QUOTES, 'UTF-8'); ?></span></td>
                  <td><?php echo (int) $order['total_articles']; ?></td>
                  <td><?php echo htmlspecialchars((string) $order['statut'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="muted">Aucune commande client recente.</p>
      <?php endif; ?>
    </section>

    <section class="panel">
      <h3>Derniers logs systeme</h3>
      <?php if (!empty($logs)): ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Utilisateur</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td><?php echo htmlspecialchars((string) $log['date_action'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><span class="badge"><?php echo htmlspecialchars((string) ($log['user_nom'] ?: 'Systeme'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                  <td><?php echo htmlspecialchars((string) $log['action'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="muted">Aucun log recent disponible.</p>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>

