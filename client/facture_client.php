<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Mes factures';
$activePage = 'factures';

$invoices = [];
if ($clientDb instanceof mysqli) {
    $stmt = $clientDb->prepare("SELECT c.id_commande, c.date_commande, c.statut, COALESCE(SUM(CASE WHEN p.type = 'Client' THEN p.montant ELSE 0 END), 0) AS montant_regle, MAX(CASE WHEN p.type = 'Client' THEN p.date_paiement END) AS derniere_date_paiement, SUM(CASE WHEN p.type = 'Client' THEN 1 ELSE 0 END) AS nb_paiements FROM Commande_Client c LEFT JOIN Paiement p ON p.id_commande = c.id_commande WHERE c.id_client = ? GROUP BY c.id_commande, c.date_commande, c.statut ORDER BY c.date_commande DESC, c.id_commande DESC");
    if ($stmt) {
        $stmt->bind_param('i', $clientId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($result && $row = $result->fetch_assoc()) {
                $invoices[] = $row;
            }
        }
        $stmt->close();
    }
}

$totalBilled = 0.0;
foreach ($invoices as $invoice) {
    $totalBilled += (float) $invoice['montant_regle'];
}

include __DIR__ . '/_header.php';
?>

<section class="panel">
  <h2>Mes factures</h2>
  <p class="muted">Retrouvez vos references de facturation et les montants deja enregistres.</p>
  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Factures generees</div>
      <div class="value"><?php echo count($invoices); ?></div>
      <div class="sub">Base sur vos commandes</div>
    </article>
    <article class="stat">
      <div class="label">Montant cumule</div>
      <div class="value"><?php echo number_format($totalBilled, 0, ',', ' '); ?> FCFA</div>
      <div class="sub">Paiements rattaches</div>
    </article>
    <article class="stat">
      <div class="label">Action rapide</div>
      <div class="value" style="font-size:16px; margin-top:11px;"><a href="paiement_client.php" class="btn btn-secondary">Voir mes paiements</a></div>
      <div class="sub">Suivi des transactions</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>References de facture</h3>
  <?php if (!empty($invoices)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Facture</th>
            <th>Commande</th>
            <th>Date commande</th>
            <th>Dernier paiement</th>
            <th>Montant regle</th>
            <th>Etat</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $invoice): ?>
            <?php
              $dateKey = strtotime((string) $invoice['date_commande']);
              $prefixYear = $dateKey ? date('Y', $dateKey) : date('Y');
              $ref = 'FAC-' . $prefixYear . '-' . str_pad((string) ((int) $invoice['id_commande']), 5, '0', STR_PAD_LEFT);
              $statusText = (float) $invoice['montant_regle'] > 0 ? 'Paiement enregistre' : 'En attente de paiement';
              $statusClass = (float) $invoice['montant_regle'] > 0 ? 'badge success' : 'badge warning';
            ?>
            <tr>
              <td><?php echo client_h($ref); ?></td>
              <td>#<?php echo (int) $invoice['id_commande']; ?></td>
              <td><?php echo client_h($invoice['date_commande']); ?></td>
              <td><?php echo client_h($invoice['derniere_date_paiement'] ?: '-'); ?></td>
              <td><?php echo number_format((float) $invoice['montant_regle'], 0, ',', ' '); ?> FCFA</td>
              <td><span class="<?php echo $statusClass; ?>"><?php echo client_h($statusText); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucune facture disponible pour le moment.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
