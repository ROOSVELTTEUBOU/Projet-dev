<?php
require __DIR__ . '/../employe/_layout.php';

$pageTitle = 'Dashboard employe';
$activePage = 'dashboard';

function dashboard_employe_scalar(mysqli $db, string $sql, string $types = '', array $params = [], $default = 0)
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

$stats = [
    'products_total' => 0,
    'stock_low' => 0,
    'client_orders_pending' => 0,
    'supplier_orders_pending' => 0,
    'payments_pending' => 0,
    'movements_today' => 0,
];

$recentClientOrders = [];
$recentSupplierOrders = [];
$recentMovements = [];

if ($employeDb instanceof mysqli) {
    $stats['products_total'] = (int) dashboard_employe_scalar($employeDb, 'SELECT COUNT(*) FROM Produit');
    $stats['stock_low'] = (int) dashboard_employe_scalar($employeDb, 'SELECT COUNT(*) FROM Produit WHERE quantite BETWEEN 0 AND 5');
    $stats['client_orders_pending'] = (int) dashboard_employe_scalar($employeDb, "SELECT COUNT(*) FROM Commande_Client WHERE statut LIKE '%attente%'");
    $stats['supplier_orders_pending'] = (int) dashboard_employe_scalar($employeDb, "SELECT COUNT(*) FROM Commande_Fournisseur WHERE etat LIKE '%attente%'");
    $stats['payments_pending'] = (int) dashboard_employe_scalar($employeDb, "SELECT COUNT(*) FROM Paiement WHERE statut LIKE '%attente%'");
    $stats['movements_today'] = (int) dashboard_employe_scalar($employeDb, 'SELECT COUNT(*) FROM Stock_Mouvement WHERE DATE(date_mouvement) = CURDATE()');

    $clientOrdersResult = $employeDb->query('SELECT c.id_commande, c.date_commande, c.statut, cl.nom, cl.prenom FROM Commande_Client c LEFT JOIN Client cl ON cl.id_client = c.id_client ORDER BY c.date_commande DESC, c.id_commande DESC LIMIT 5');
    if ($clientOrdersResult) {
        while ($row = $clientOrdersResult->fetch_assoc()) {
            $recentClientOrders[] = $row;
        }
        $clientOrdersResult->free();
    }

    $supplierOrdersResult = $employeDb->query('SELECT cf.id_commande_f, cf.date_commande, cf.etat, f.nom AS fournisseur_nom FROM Commande_Fournisseur cf LEFT JOIN Fournisseur f ON f.id_fournisseur = cf.id_fournisseur ORDER BY cf.date_commande DESC, cf.id_commande_f DESC LIMIT 5');
    if ($supplierOrdersResult) {
        while ($row = $supplierOrdersResult->fetch_assoc()) {
            $recentSupplierOrders[] = $row;
        }
        $supplierOrdersResult->free();
    }

    $movementsResult = $employeDb->query('SELECT sm.id_mouvement, sm.date_mouvement, sm.type, sm.quantite, p.libelle FROM Stock_Mouvement sm LEFT JOIN Produit p ON p.id_produit = sm.id_produit ORDER BY sm.date_mouvement DESC, sm.id_mouvement DESC LIMIT 5');
    if ($movementsResult) {
        while ($row = $movementsResult->fetch_assoc()) {
            $recentMovements[] = $row;
        }
        $movementsResult->free();
    }
}

$actions = [
    [
        'title' => 'Gestion du stock',
        'description' => 'Enregistrer les entrees/sorties et verifier les niveaux de stock.',
        'href' => '../employe/stock.php',
        'check' => __DIR__ . '/../employe/stock.php',
    ],
    [
        'title' => 'Commandes clients',
        'description' => 'Suivre et mettre a jour les statuts des commandes clients.',
        'href' => '../employe/commandes_clients.php',
        'check' => __DIR__ . '/../employe/commandes_clients.php',
    ],
    [
        'title' => 'Commandes fournisseurs',
        'description' => 'Piloter les approvisionnements et receptions fournisseurs.',
        'href' => '../employe/commandes_fournisseurs.php',
        'check' => __DIR__ . '/../employe/commandes_fournisseurs.php',
    ],
    [
        'title' => 'Validation paiements',
        'description' => 'Verifier les paiements en attente et valider leur statut.',
        'href' => '../employe/paiements.php',
        'check' => __DIR__ . '/../employe/paiements.php',
    ],
    [
        'title' => 'Dashboard client',
        'description' => 'Consulter le panneau de suivi client.',
        'href' => 'dashboard_client.php',
        'check' => __DIR__ . '/dashboard_client.php',
    ],
    [
        'title' => 'Dashboard admin',
        'description' => 'Acceder a la vue globale de supervision.',
        'href' => 'dashboard_admin.php',
        'check' => __DIR__ . '/dashboard_admin.php',
    ],
    [
        'title' => 'Deconnexion',
        'description' => 'Fermer la session employe en securite.',
        'href' => 'logout.php',
        'check' => __DIR__ . '/logout.php',
    ],
];

$availableCount = 0;
foreach ($actions as $action) {
    if (file_exists($action['check'])) {
        $availableCount++;
    }
}

include __DIR__ . '/../employe/_header.php';
?>

<style>
  .dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }

  .action-card.disabled {
    opacity: 0.68;
    pointer-events: none;
    filter: grayscale(0.1);
  }

  .actions-meta {
    display: inline-block;
    margin-bottom: 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 9px;
    background: #dcebff;
    color: #0f49b2;
  }

  @media (max-width: 900px) {
    .dashboard-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<section class="panel">
  <h2>Dashboard employe</h2>
  <p class="muted">Ce tableau centralise toutes les operations metier de l employe: stock, commandes clients, commandes fournisseurs et paiements.</p>

  <div class="grid-4" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Produits total</div>
      <div class="value"><?php echo $stats['products_total']; ?></div>
      <div class="sub">Produits suivis</div>
    </article>
    <article class="stat">
      <div class="label">Stock faible</div>
      <div class="value"><?php echo $stats['stock_low']; ?></div>
      <div class="sub">A traiter rapidement</div>
    </article>
    <article class="stat">
      <div class="label">Cmd clients en attente</div>
      <div class="value"><?php echo $stats['client_orders_pending']; ?></div>
      <div class="sub">Validation necessaire</div>
    </article>
    <article class="stat">
      <div class="label">Cmd fournisseurs en attente</div>
      <div class="value"><?php echo $stats['supplier_orders_pending']; ?></div>
      <div class="sub">Approvisionnement a suivre</div>
    </article>
  </div>

  <div class="grid-2" style="margin-top:14px;">
    <article class="stat">
      <div class="label">Paiements en attente</div>
      <div class="value"><?php echo $stats['payments_pending']; ?></div>
      <div class="sub">Controle comptable</div>
    </article>
    <article class="stat">
      <div class="label">Mouvements stock du jour</div>
      <div class="value"><?php echo $stats['movements_today']; ?></div>
      <div class="sub">Date: <?php echo date('Y-m-d'); ?></div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Fonctions employe</h3>
  <p class="muted">Fonctionnalites actives: <?php echo $availableCount; ?> / <?php echo count($actions); ?></p>

  <div class="cards-grid" style="margin-top:12px;">
    <?php foreach ($actions as $action): ?>
      <?php $isAvailable = file_exists($action['check']); ?>
      <a class="action-card <?php echo $isAvailable ? '' : 'disabled'; ?>" href="<?php echo employe_h($action['href']); ?>">
        <span class="actions-meta"><?php echo $isAvailable ? 'Disponible' : 'A implementer'; ?></span>
        <h3><?php echo employe_h($action['title']); ?></h3>
        <p><?php echo employe_h($action['description']); ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="dashboard-grid">
  <article class="panel">
    <h3>Dernieres commandes clients</h3>
    <?php if (!empty($recentClientOrders)): ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Client</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentClientOrders as $order): ?>
              <tr>
                <td><?php echo (int) $order['id_commande']; ?></td>
                <td><?php echo employe_h($order['date_commande']); ?></td>
                <td><?php echo employe_h(trim(((string) $order['prenom']) . ' ' . ((string) $order['nom']))); ?></td>
                <td><span class="<?php echo employe_status_class($order['statut']); ?>"><?php echo employe_h(employe_status_text($order['statut'])); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">Aucune commande client recente.</div>
    <?php endif; ?>
  </article>

  <article class="panel">
    <h3>Dernieres commandes fournisseurs</h3>
    <?php if (!empty($recentSupplierOrders)): ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Fournisseur</th>
              <th>Etat</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentSupplierOrders as $order): ?>
              <tr>
                <td><?php echo (int) $order['id_commande_f']; ?></td>
                <td><?php echo employe_h($order['date_commande']); ?></td>
                <td><?php echo employe_h($order['fournisseur_nom'] ?: '-'); ?></td>
                <td><span class="<?php echo employe_status_class($order['etat']); ?>"><?php echo employe_h(employe_status_text($order['etat'])); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">Aucune commande fournisseur recente.</div>
    <?php endif; ?>
  </article>
</section>

<section class="panel">
  <h3>Derniers mouvements de stock</h3>
  <?php if (!empty($recentMovements)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Produit</th>
            <th>Type</th>
            <th>Quantite</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentMovements as $movement): ?>
            <tr>
              <td><?php echo (int) $movement['id_mouvement']; ?></td>
              <td><?php echo employe_h($movement['date_mouvement']); ?></td>
              <td><?php echo employe_h($movement['libelle'] ?: '-'); ?></td>
              <td><span class="<?php echo employe_status_class($movement['type']); ?>"><?php echo employe_h(employe_status_text($movement['type'])); ?></span></td>
              <td><?php echo (int) $movement['quantite']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun mouvement de stock recent.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../employe/_footer.php'; ?>

