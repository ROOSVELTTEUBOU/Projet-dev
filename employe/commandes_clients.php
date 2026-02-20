<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Commandes clients';
$activePage = 'commandes_clients';

$notice = '';
$error = '';

$statusOptions = [];
if ($employeDb instanceof mysqli) {
    $statusOptions = employe_enum_values($employeDb, 'Commande_Client', 'statut');
    if (empty($statusOptions)) {
        $statusResult = $employeDb->query('SELECT DISTINCT statut FROM Commande_Client WHERE statut IS NOT NULL AND statut <> ""');
        if ($statusResult) {
            while ($row = $statusResult->fetch_assoc()) {
                $statusOptions[] = (string) $row['statut'];
            }
            $statusResult->free();
        }
    }
}
if (empty($statusOptions)) {
    $statusOptions = ['En attente', 'Validee', 'Livree', 'Annulee'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = trim((string) ($_POST['new_status'] ?? ''));

    if (!($employeDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($orderId <= 0 || $newStatus === '') {
        $error = 'Parametres invalides pour la mise a jour.';
    } elseif (!employe_status_in_allowed($newStatus, $statusOptions)) {
        $error = 'Statut non autorise.';
    } else {
        $stmt = $employeDb->prepare('UPDATE Commande_Client SET statut = ? WHERE id_commande = ?');
        if ($stmt) {
            $stmt->bind_param('si', $newStatus, $orderId);
            if ($stmt->execute()) {
                $notice = 'Statut de commande client mis a jour.';
            } else {
                $error = 'Echec de mise a jour du statut.';
            }
            $stmt->close();
        } else {
            $error = 'Requete de mise a jour invalide.';
        }
    }
}

$orders = [];
if ($employeDb instanceof mysqli) {
    $sql = 'SELECT c.id_commande, c.date_commande, c.statut, cl.nom, cl.prenom FROM Commande_Client c LEFT JOIN Client cl ON cl.id_client = c.id_client ORDER BY c.date_commande DESC, c.id_commande DESC';
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

<?php if ($notice !== ''): ?>
  <div class="alert alert-success"><?php echo employe_h($notice); ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert alert-error"><?php echo employe_h($error); ?></div>
<?php endif; ?>

<section class="panel">
  <h2>Suivi des commandes clients</h2>
  <p class="muted">Consultez les commandes clients et ajustez leur statut de traitement.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Total commandes</div>
      <div class="value"><?php echo $totalOrders; ?></div>
      <div class="sub">Toutes periodes</div>
    </article>
    <article class="stat">
      <div class="label">En attente</div>
      <div class="value"><?php echo $pendingOrders; ?></div>
      <div class="sub">A traiter</div>
    </article>
    <article class="stat">
      <div class="label">Livrees</div>
      <div class="value"><?php echo $deliveredOrders; ?></div>
      <div class="sub">Finalisees</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Liste des commandes clients</h3>
  <?php if (!empty($orders)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Commande</th>
            <th>Date</th>
            <th>Client</th>
            <th>Statut actuel</th>
            <th>Mise a jour</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td>#<?php echo (int) $order['id_commande']; ?></td>
              <td><?php echo employe_h($order['date_commande']); ?></td>
              <td><?php echo employe_h(trim(((string) $order['prenom']) . ' ' . ((string) $order['nom']))); ?></td>
              <td><span class="<?php echo employe_status_class($order['statut']); ?>"><?php echo employe_h(employe_status_text($order['statut'])); ?></span></td>
              <td>
                <form method="post" class="actions-row">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="order_id" value="<?php echo (int) $order['id_commande']; ?>">
                  <select name="new_status" required>
                    <?php foreach ($statusOptions as $statusOption): ?>
                      <option value="<?php echo employe_h($statusOption); ?>" <?php echo ((string) $order['statut'] === (string) $statusOption) ? 'selected' : ''; ?>>
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
    <div class="empty-state">Aucune commande client enregistree.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>

