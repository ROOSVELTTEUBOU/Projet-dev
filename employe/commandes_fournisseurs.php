<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Commandes fournisseurs';
$activePage = 'commandes_fournisseurs';

$notice = '';
$error = '';

$statusOptions = [];
if ($employeDb instanceof mysqli) {
    $statusOptions = employe_enum_values($employeDb, 'Commande_Fournisseur', 'etat');
    if (empty($statusOptions)) {
        $statusResult = $employeDb->query('SELECT DISTINCT etat FROM Commande_Fournisseur WHERE etat IS NOT NULL AND etat <> ""');
        if ($statusResult) {
            while ($row = $statusResult->fetch_assoc()) {
                $statusOptions[] = (string) $row['etat'];
            }
            $statusResult->free();
        }
    }
}
if (empty($statusOptions)) {
    $statusOptions = ['En attente', 'Recue', 'Annulee'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = trim((string) ($_POST['new_status'] ?? ''));

    if (!($employeDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($orderId <= 0 || $newStatus === '') {
        $error = 'Parametres invalides pour la mise a jour.';
    } elseif (!employe_status_in_allowed($newStatus, $statusOptions)) {
        $error = 'Etat non autorise.';
    } else {
        $stmt = $employeDb->prepare('UPDATE Commande_Fournisseur SET etat = ? WHERE id_commande_f = ?');
        if ($stmt) {
            $stmt->bind_param('si', $newStatus, $orderId);
            if ($stmt->execute()) {
                $notice = 'Etat de commande fournisseur mis a jour.';
            } else {
                $error = 'Echec de mise a jour de l etat.';
            }
            $stmt->close();
        } else {
            $error = 'Requete de mise a jour invalide.';
        }
    }
}

$orders = [];
if ($employeDb instanceof mysqli) {
    $sql = 'SELECT cf.id_commande_f, cf.date_commande, cf.etat, f.nom AS fournisseur_nom, f.ville AS fournisseur_ville FROM Commande_Fournisseur cf LEFT JOIN Fournisseur f ON f.id_fournisseur = cf.id_fournisseur ORDER BY cf.date_commande DESC, cf.id_commande_f DESC';
    $result = $employeDb->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $result->free();
    }
}

$totalOrders = count($orders);
$pendingOrders = 0;
$receivedOrders = 0;

foreach ($orders as $order) {
    $status = strtolower((string) $order['etat']);
    if (strpos($status, 'attente') !== false) {
        $pendingOrders++;
    }
    if (strpos($status, 'rec') === 0) {
        $receivedOrders++;
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
  <h2>Suivi des commandes fournisseurs</h2>
  <p class="muted">Suivez les approvisionnements et mettez a jour l'etat des commandes fournisseurs.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Total commandes</div>
      <div class="value"><?php echo $totalOrders; ?></div>
      <div class="sub">Tous fournisseurs</div>
    </article>
    <article class="stat">
      <div class="label">En attente</div>
      <div class="value"><?php echo $pendingOrders; ?></div>
      <div class="sub">A receptionner</div>
    </article>
    <article class="stat">
      <div class="label">Recues</div>
      <div class="value"><?php echo $receivedOrders; ?></div>
      <div class="sub">Deja receptionnees</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Liste des commandes fournisseurs</h3>
  <?php if (!empty($orders)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Commande</th>
            <th>Date</th>
            <th>Fournisseur</th>
            <th>Ville</th>
            <th>Etat actuel</th>
            <th>Mise a jour</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td>#<?php echo (int) $order['id_commande_f']; ?></td>
              <td><?php echo employe_h($order['date_commande']); ?></td>
              <td><?php echo employe_h($order['fournisseur_nom'] ?: '-'); ?></td>
              <td><?php echo employe_h($order['fournisseur_ville'] ?: '-'); ?></td>
              <td><span class="<?php echo employe_status_class($order['etat']); ?>"><?php echo employe_h(employe_status_text($order['etat'])); ?></span></td>
              <td>
                <form method="post" class="actions-row">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="order_id" value="<?php echo (int) $order['id_commande_f']; ?>">
                  <select name="new_status" required>
                    <?php foreach ($statusOptions as $statusOption): ?>
                      <option value="<?php echo employe_h($statusOption); ?>" <?php echo ((string) $order['etat'] === (string) $statusOption) ? 'selected' : ''; ?>>
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
    <div class="empty-state">Aucune commande fournisseur enregistree.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>

