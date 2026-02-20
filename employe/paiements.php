<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Validation des paiements';
$activePage = 'paiements';

$notice = '';
$error = '';

$statusOptions = [];
if ($employeDb instanceof mysqli) {
    $statusOptions = employe_enum_values($employeDb, 'Paiement', 'statut');
    if (empty($statusOptions)) {
        $statusResult = $employeDb->query('SELECT DISTINCT statut FROM Paiement WHERE statut IS NOT NULL AND statut <> ""');
        if ($statusResult) {
            while ($row = $statusResult->fetch_assoc()) {
                $statusOptions[] = (string) $row['statut'];
            }
            $statusResult->free();
        }
    }
}
if (empty($statusOptions)) {
    $statusOptions = ['Paye', 'En attente', 'Annule'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $paymentId = (int) ($_POST['payment_id'] ?? 0);
    $newStatus = trim((string) ($_POST['new_status'] ?? ''));

    if (!($employeDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($paymentId <= 0 || $newStatus === '') {
        $error = 'Parametres invalides pour la mise a jour.';
    } elseif (!employe_status_in_allowed($newStatus, $statusOptions)) {
        $error = 'Statut de paiement non autorise.';
    } else {
        $stmt = $employeDb->prepare('UPDATE Paiement SET statut = ? WHERE id_paiement = ?');
        if ($stmt) {
            $stmt->bind_param('si', $newStatus, $paymentId);
            if ($stmt->execute()) {
                $notice = 'Statut de paiement mis a jour.';
            } else {
                $error = 'Echec de mise a jour du paiement.';
            }
            $stmt->close();
        } else {
            $error = 'Requete de mise a jour invalide.';
        }
    }
}

$payments = [];
if ($employeDb instanceof mysqli) {
    $result = $employeDb->query('SELECT id_paiement, type, id_commande, montant, mode, date_paiement, statut FROM Paiement ORDER BY date_paiement DESC, id_paiement DESC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        $result->free();
    }
}

$totalPayments = count($payments);
$pendingPayments = 0;
$totalAmount = 0.0;

foreach ($payments as $payment) {
    $status = strtolower((string) $payment['statut']);
    if (strpos($status, 'attente') !== false) {
        $pendingPayments++;
    }
    if (strpos($status, 'pay') === 0) {
        $totalAmount += (float) $payment['montant'];
    }
}

include __DIR__ . '/_header.php';
?>

<?php if ($notice !== ''): ?>
  <div class="alert alert-success"><?php echo employe_h($notice); ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert alert-error"><?php echo employe_h($error); ?></div>
<?php endif; ?>

<section class="panel">
  <h2>Validation des paiements</h2>
  <p class="muted">Controlez les paiements clients et fournisseurs puis mettez a jour leur statut.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Paiements total</div>
      <div class="value"><?php echo $totalPayments; ?></div>
      <div class="sub">Tous types confondus</div>
    </article>
    <article class="stat">
      <div class="label">En attente</div>
      <div class="value"><?php echo $pendingPayments; ?></div>
      <div class="sub">A valider</div>
    </article>
    <article class="stat">
      <div class="label">Montant paye</div>
      <div class="value"><?php echo number_format($totalAmount, 0, ',', ' '); ?> FCFA</div>
      <div class="sub">Somme des paiements valides</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Liste des paiements</h3>
  <?php if (!empty($payments)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Paiement</th>
            <th>Type</th>
            <th>Commande</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Mode</th>
            <th>Statut</th>
            <th>Mise a jour</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment): ?>
            <tr>
              <td>#<?php echo (int) $payment['id_paiement']; ?></td>
              <td><?php echo employe_h($payment['type']); ?></td>
              <td>#<?php echo (int) $payment['id_commande']; ?></td>
              <td><?php echo employe_h($payment['date_paiement']); ?></td>
              <td><?php echo number_format((float) $payment['montant'], 0, ',', ' '); ?> FCFA</td>
              <td><?php echo employe_h($payment['mode']); ?></td>
              <td><span class="<?php echo employe_status_class($payment['statut']); ?>"><?php echo employe_h(employe_status_text($payment['statut'])); ?></span></td>
              <td>
                <form method="post" class="actions-row">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="payment_id" value="<?php echo (int) $payment['id_paiement']; ?>">
                  <select name="new_status" required>
                    <?php foreach ($statusOptions as $statusOption): ?>
                      <option value="<?php echo employe_h($statusOption); ?>" <?php echo ((string) $payment['statut'] === (string) $statusOption) ? 'selected' : ''; ?>>
                        <?php echo employe_h($statusOption); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-secondary">Mettre a jour</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun paiement enregistre.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>

