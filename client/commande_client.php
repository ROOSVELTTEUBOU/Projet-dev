<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Mes commandes';
$activePage = 'commandes';

$orders = [];
if ($clientDb instanceof mysqli) {
    $stmt = $clientDb->prepare('SELECT c.id_commande, c.date_commande, c.statut, COALESCE(SUM(cc.quantite), 0) AS total_articles FROM Commande_Client c LEFT JOIN Concerner_Client cc ON cc.id_commande = c.id_commande WHERE c.id_client = ? GROUP BY c.id_commande, c.date_commande, c.statut ORDER BY c.date_commande DESC, c.id_commande DESC');
    if ($stmt) {
        $stmt->bind_param('i', $clientId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($result && $row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        $stmt->close();
    }
}

$totalOrders = count($orders);
$pendingOrders = 0;
$deliveredOrders = 0;

foreach ($orders as $order) {
    $status = strtolower((string) $order['statut']);
    if (strpos($status, 'attente') !== false) {
        $pendingOrders++;
    }
    if (strpos($status, 'liv') === 0) {
        $deliveredOrders++;
    }
}

include __DIR__ . '/_header.php';
?>

<section class="panel">
  <h2>Mes commandes</h2>
  <p class="muted">Suivez l'evolution de vos commandes et leur niveau de traitement.</p>
  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Commandes total</div>
      <div class="value"><?php echo $totalOrders; ?></div>
      <div class="sub">Toutes periodes</div>
    </article>
    <article class="stat">
      <div class="label">En attente</div>
      <div class="value"><?php echo $pendingOrders; ?></div>
      <div class="sub">A valider</div>
    </article>
    <article class="stat">
      <div class="label">Livrees</div>
      <div class="value"><?php echo $deliveredOrders; ?></div>
      <div class="sub">Finalisees</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Historique des commandes</h3>
  <?php if (!empty($orders)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Commande</th>
            <th>Date</th>
            <th>Articles</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td>#<?php echo (int) $order['id_commande']; ?></td>
              <td><?php echo client_h($order['date_commande']); ?></td>
              <td><?php echo (int) $order['total_articles']; ?></td>
              <td><span class="<?php echo client_status_class($order['statut']); ?>"><?php echo client_h(client_status_short($order['statut'])); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucune commande enregistree pour ce compte.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
