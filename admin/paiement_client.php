<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Paiements clients';
$activePage = 'paiement_client';

$notice = '';
$error = '';

$modeOptions = [];
$statusOptions = [];
$orderOptions = [];

if ($adminDb instanceof mysqli) {
    $modeOptions = admin_enum_values($adminDb, 'Paiement', 'mode');
    $statusOptions = admin_enum_values($adminDb, 'Paiement', 'statut');
    if (empty($modeOptions)) {
        $modeOptions = ['Cash', 'Carte', 'MobileMoney', 'Virement'];
    }
    if (empty($statusOptions)) {
        $statusOptions = ['Paye', 'En attente', 'Annule'];
    }

    $ordRes = $adminDb->query('SELECT c.id_commande, c.date_commande, cl.nom, cl.prenom FROM Commande_Client c LEFT JOIN Client cl ON cl.id_client = c.id_client ORDER BY c.id_commande DESC');
    if ($ordRes) {
        while ($row = $ordRes->fetch_assoc()) {
            $orderOptions[] = $row;
        }
        $ordRes->free();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'create') {
        $orderId = (int) ($_POST['id_commande'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $mode = trim((string) ($_POST['mode'] ?? ''));
        $datePaiement = trim((string) ($_POST['date_paiement'] ?? ''));
        $statut = trim((string) ($_POST['statut'] ?? ''));

        if ($orderId <= 0 || $montant <= 0 || $datePaiement === '' || !admin_in_allowed($mode, $modeOptions) || !admin_in_allowed($statut, $statusOptions)) {
            $error = 'Parametres invalides pour creation paiement client.';
        } else {
            $type = 'Client';
            $stmt = $adminDb->prepare('INSERT INTO Paiement (type, id_commande, montant, mode, date_paiement, statut) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('sidsss', $type, $orderId, $montant, $mode, $datePaiement, $statut);
                if ($stmt->execute()) {
                    $notice = 'Paiement client enregistre.';
                    admin_log_action($adminDb, $adminId, 'Ajout paiement client #' . $stmt->insert_id);
                } else {
                    $error = 'Echec creation paiement client.';
                }
                $stmt->close();
            } else {
                $error = 'Requete creation paiement invalide.';
            }
        }
    } elseif ($action === 'update_status') {
        $paymentId = (int) ($_POST['payment_id'] ?? 0);
        $newStatus = trim((string) ($_POST['new_status'] ?? ''));

        if ($paymentId <= 0 || !admin_in_allowed($newStatus, $statusOptions)) {
            $error = 'Parametres invalides pour mise a jour paiement.';
        } else {
            $stmt = $adminDb->prepare('UPDATE Paiement SET statut = ? WHERE id_paiement = ? AND type = "Client"');
            if ($stmt) {
                $stmt->bind_param('si', $newStatus, $paymentId);
                if ($stmt->execute()) {
                    $notice = 'Statut paiement client mis a jour.';
                    admin_log_action($adminDb, $adminId, 'Maj statut paiement client #' . $paymentId . ' -> ' . $newStatus);
                } else {
                    $error = 'Echec mise a jour statut paiement.';
                }
                $stmt->close();
            } else {
                $error = 'Requete mise a jour paiement invalide.';
            }
        }
    } elseif ($action === 'delete') {
        $paymentId = (int) ($_POST['payment_id'] ?? 0);

        if ($paymentId <= 0) {
            $error = 'Paiement invalide.';
        } else {
            $stmt = $adminDb->prepare('DELETE FROM Paiement WHERE id_paiement = ? AND type = "Client"');
            if ($stmt) {
                $stmt->bind_param('i', $paymentId);
                if ($stmt->execute()) {
                    $notice = 'Paiement client supprime.';
                    admin_log_action($adminDb, $adminId, 'Suppression paiement client #' . $paymentId);
                } else {
                    $error = 'Suppression paiement impossible.';
                }
                $stmt->close();
            } else {
                $error = 'Requete suppression paiement invalide.';
            }
        }
    }
}

$payments = [];
if ($adminDb instanceof mysqli) {
    $sql = 'SELECT p.id_paiement, p.id_commande, p.montant, p.mode, p.date_paiement, p.statut, cl.nom, cl.prenom FROM Paiement p LEFT JOIN Commande_Client c ON c.id_commande = p.id_commande LEFT JOIN Client cl ON cl.id_client = c.id_client WHERE p.type = "Client" ORDER BY p.date_paiement DESC, p.id_paiement DESC';
    $res = $adminDb->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $payments[] = $row;
        }
        $res->free();
    }
}

include __DIR__ . '/_header.php';
?>

<?php if ($notice !== ''): ?>
  <div class="alert alert-success"><?php echo admin_h($notice); ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert alert-error"><?php echo admin_h($error); ?></div>
<?php endif; ?>

<section class="panel">
  <h2>Paiements clients</h2>
  <p class="muted">Gestion des paiements clients: creation, validation des statuts et suppression.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Total paiements</div>
      <div class="value"><?php echo count($payments); ?></div>
      <div class="sub">Paiements clients</div>
    </article>
    <article class="stat">
      <div class="label">En attente</div>
      <div class="value"><?php echo count(array_filter($payments, function ($p) { return stripos((string) $p['statut'], 'attente') !== false; })); ?></div>
      <div class="sub">A valider</div>
    </article>
    <article class="stat">
      <div class="label">Montant valide</div>
      <div class="value"><?php echo number_format(array_sum(array_map(function ($p) { return stripos((string) $p['statut'], 'pay') === 0 ? (float) $p['montant'] : 0; }, $payments)), 0, ',', ' '); ?> FCFA</div>
      <div class="sub">Paiements confirmes</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Enregistrer un paiement client</h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="create">

    <div class="grid-3">
      <div class="field">
        <label>Commande client</label>
        <select name="id_commande" required>
          <option value="">Selectionner une commande</option>
          <?php foreach ($orderOptions as $order): ?>
            <option value="<?php echo (int) $order['id_commande']; ?>">
              #<?php echo (int) $order['id_commande']; ?> - <?php echo admin_h(trim(((string) $order['prenom']) . ' ' . ((string) $order['nom']))); ?> (<?php echo admin_h($order['date_commande']); ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Montant</label>
        <input type="number" name="montant" min="1" step="0.01" required>
      </div>

      <div class="field">
        <label>Mode de paiement</label>
        <select name="mode" required>
          <?php foreach ($modeOptions as $modeOption): ?>
            <option value="<?php echo admin_h($modeOption); ?>"><?php echo admin_h($modeOption); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="grid-2">
      <div class="field">
        <label>Date paiement</label>
        <input type="date" name="date_paiement" value="<?php echo date('Y-m-d'); ?>" required>
      </div>

      <div class="field">
        <label>Statut</label>
        <select name="statut" required>
          <?php foreach ($statusOptions as $statusOption): ?>
            <option value="<?php echo admin_h($statusOption); ?>"><?php echo admin_h($statusOption); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <button type="submit" class="btn">Enregistrer paiement</button>
  </form>
</section>

<section class="panel">
  <h3>Historique des paiements clients</h3>
  <?php if (!empty($payments)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Commande</th>
            <th>Client</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Mode</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment): ?>
            <tr>
              <td><?php echo (int) $payment['id_paiement']; ?></td>
              <td>#<?php echo (int) $payment['id_commande']; ?></td>
              <td><?php echo admin_h(trim(((string) $payment['prenom']) . ' ' . ((string) $payment['nom']))); ?></td>
              <td><?php echo admin_h($payment['date_paiement']); ?></td>
              <td><?php echo number_format((float) $payment['montant'], 0, ',', ' '); ?> FCFA</td>
              <td><?php echo admin_h($payment['mode']); ?></td>
              <td><span class="<?php echo admin_status_class($payment['statut']); ?>"><?php echo admin_h($payment['statut']); ?></span></td>
              <td>
                <div class="actions-row">
                  <form method="post" class="actions-row">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="payment_id" value="<?php echo (int) $payment['id_paiement']; ?>">
                    <select name="new_status" required>
                      <?php foreach ($statusOptions as $statusOption): ?>
                        <option value="<?php echo admin_h($statusOption); ?>" <?php echo ((string) $payment['statut'] === (string) $statusOption) ? 'selected' : ''; ?>>
                          <?php echo admin_h($statusOption); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-secondary">Valider</button>
                  </form>

                  <form method="post" onsubmit="return confirm('Supprimer ce paiement client ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="payment_id" value="<?php echo (int) $payment['id_paiement']; ?>">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                  </form>
                </div>
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
