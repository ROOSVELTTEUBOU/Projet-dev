<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Mes paiements';
$activePage = 'paiements';

$payments = [];
if ($clientDb instanceof mysqli) {
    $stmt = $clientDb->prepare("SELECT p.id_paiement, p.id_commande, p.date_paiement, p.montant, p.mode, p.statut FROM Paiement p INNER JOIN Commande_Client c ON c.id_commande = p.id_commande WHERE c.id_client = ? AND p.type = 'Client' ORDER BY p.date_paiement DESC, p.id_paiement DESC");
    if ($stmt) {
        $stmt->bind_param('i', $clientId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($result && $row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
        }
        $stmt->close();
    }
}

$totalPaid = 0.0;
$paidCount = 0;
$pendingCount = 0;
$firstPending = null;

foreach ($payments as $payment) {
    $totalPaid += (float) $payment['montant'];
    $status = strtolower((string) $payment['statut']);
    if (strpos($status, 'pay') === 0) {
        $paidCount++;
    }
    if (strpos($status, 'attente') !== false) {
        $pendingCount++;
        if ($firstPending === null) {
            $firstPending = $payment;
        }
    }
}

include __DIR__ . '/_header.php';
?>

<section class="panel">
  <h2>Mes paiements</h2>
  <p class="muted">Visualisez tous vos paiements clients et leur statut de validation.</p>
  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Paiements enregistres</div>
      <div class="value"><?php echo count($payments); ?></div>
      <div class="sub">Transactions client</div>
    </article>
    <article class="stat">
      <div class="label">Montant cumule</div>
      <div class="value"><?php echo number_format($totalPaid, 0, ',', ' '); ?> FCFA</div>
      <div class="sub">Somme des paiements</div>
    </article>
    <article class="stat">
      <div class="label">En attente</div>
      <div class="value"><?php echo $pendingCount; ?></div>
      <div class="sub">Paiements a confirmer</div>
    </article>
  </div>
</section>

<section class="panel">
  <?php if ($firstPending !== null): ?>
    <div class="actions-row" style="margin-bottom:12px;">
      <a class="btn" href="../paiement.php?order_id=<?php echo (int) $firstPending['id_commande']; ?>&payment_id=<?php echo (int) $firstPending['id_paiement']; ?>">
        Finaliser mon paiement en attente
      </a>
    </div>
  <?php endif; ?>

  <h3>Historique des paiements</h3>
  <?php if (!empty($payments)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Paiement</th>
            <th>Commande</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Mode</th>
            <th>Statut</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment): ?>
            <?php $isPending = strpos(strtolower((string) $payment['statut']), 'attente') !== false; ?>
            <tr>
              <td>#<?php echo (int) $payment['id_paiement']; ?></td>
              <td>#<?php echo (int) $payment['id_commande']; ?></td>
              <td><?php echo client_h($payment['date_paiement']); ?></td>
              <td><?php echo number_format((float) $payment['montant'], 0, ',', ' '); ?> FCFA</td>
              <td><?php echo client_h($payment['mode']); ?></td>
              <td><span class="<?php echo client_status_class($payment['statut']); ?>"><?php echo client_h(client_status_short($payment['statut'])); ?></span></td>
              <td>
                <?php if ($isPending): ?>
                  <a class="btn btn-secondary" href="../paiement.php?order_id=<?php echo (int) $payment['id_commande']; ?>&payment_id=<?php echo (int) $payment['id_paiement']; ?>">Payer</a>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun paiement client enregistre.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
