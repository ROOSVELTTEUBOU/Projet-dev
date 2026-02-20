<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Commandes clients';
$activePage = 'commande_client';

$notice = '';
$error = '';

$statusOptions = [];
$clientOptions = [];

if ($adminDb instanceof mysqli) {
    $statusOptions = admin_enum_values($adminDb, 'Commande_Client', 'statut');
    if (empty($statusOptions)) {
        $statusOptions = ['En attente', 'Validee', 'Livree', 'Annulee'];
    }

    $clientRes = $adminDb->query('SELECT id_client, nom, prenom FROM Client ORDER BY prenom ASC, nom ASC');
    if ($clientRes) {
        while ($row = $clientRes->fetch_assoc()) {
            $clientOptions[] = $row;
        }
        $clientRes->free();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'create') {
        $clientId = (int) ($_POST['id_client'] ?? 0);
        $dateCommande = trim((string) ($_POST['date_commande'] ?? ''));
        $statut = trim((string) ($_POST['statut'] ?? ''));

        if ($clientId <= 0 || $dateCommande === '' || !admin_in_allowed($statut, $statusOptions)) {
            $error = 'Parametres invalides pour creation commande.';
        } else {
            $stmt = $adminDb->prepare('INSERT INTO Commande_Client (date_commande, id_client, statut) VALUES (?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('sis', $dateCommande, $clientId, $statut);
                if ($stmt->execute()) {
                    $notice = 'Commande client creee.';
                    admin_log_action($adminDb, $adminId, 'Creation commande client #' . $stmt->insert_id);
                } else {
                    $error = 'Echec creation commande client.';
                }
                $stmt->close();
            } else {
                $error = 'Requete creation commande invalide.';
            }
        }
    } elseif ($action === 'update_status') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $newStatus = trim((string) ($_POST['new_status'] ?? ''));

        if ($orderId <= 0 || !admin_in_allowed($newStatus, $statusOptions)) {
            $error = 'Parametres invalides pour mise a jour statut.';
        } else {
            $stmt = $adminDb->prepare('UPDATE Commande_Client SET statut = ? WHERE id_commande = ?');
            if ($stmt) {
                $stmt->bind_param('si', $newStatus, $orderId);
                if ($stmt->execute()) {
                    $notice = 'Statut commande client mis a jour.';
                    admin_log_action($adminDb, $adminId, 'Maj statut commande client #' . $orderId . ' -> ' . $newStatus);
                } else {
                    $error = 'Echec mise a jour statut commande.';
                }
                $stmt->close();
            } else {
                $error = 'Requete mise a jour statut invalide.';
            }
        }
    } elseif ($action === 'delete') {
        $orderId = (int) ($_POST['order_id'] ?? 0);

        if ($orderId <= 0) {
            $error = 'Commande invalide.';
        } else {
            $stmt = $adminDb->prepare('DELETE FROM Commande_Client WHERE id_commande = ?');
            if ($stmt) {
                $stmt->bind_param('i', $orderId);
                if ($stmt->execute()) {
                    $notice = 'Commande client supprimee.';
                    admin_log_action($adminDb, $adminId, 'Suppression commande client #' . $orderId);
                } else {
                    $error = 'Suppression commande impossible.';
                }
                $stmt->close();
            } else {
                $error = 'Requete suppression commande invalide.';
            }
        }
    }
}

$orders = [];
if ($adminDb instanceof mysqli) {
    $sql = 'SELECT c.id_commande, c.date_commande, c.statut, c.id_client, cl.nom, cl.prenom, COALESCE(SUM(cc.quantite), 0) AS total_articles FROM Commande_Client c LEFT JOIN Client cl ON cl.id_client = c.id_client LEFT JOIN Concerner_Client cc ON cc.id_commande = c.id_commande GROUP BY c.id_commande, c.date_commande, c.statut, c.id_client, cl.nom, cl.prenom ORDER BY c.date_commande DESC, c.id_commande DESC';
    $res = $adminDb->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $orders[] = $row;
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
  <h2>Commandes clients</h2>
  <p class="muted">Creation, suivi et mise a jour des commandes clients.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Total commandes</div>
      <div class="value"><?php echo count($orders); ?></div>
      <div class="sub">Commandes enregistrees</div>
    </article>
    <article class="stat">
      <div class="label">En attente</div>
      <div class="value"><?php echo count(array_filter($orders, function ($o) { return stripos((string) $o['statut'], 'attente') !== false; })); ?></div>
      <div class="sub">A valider</div>
    </article>
    <article class="stat">
      <div class="label">Livrees</div>
      <div class="value"><?php echo count(array_filter($orders, function ($o) { return stripos((string) $o['statut'], 'liv') === 0; })); ?></div>
      <div class="sub">Finalisees</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Creer une commande client</h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="create">

    <div class="grid-3">
      <div class="field">
        <label>Client</label>
        <select name="id_client" required>
          <option value="">Selectionner un client</option>
          <?php foreach ($clientOptions as $client): ?>
            <option value="<?php echo (int) $client['id_client']; ?>"><?php echo admin_h(trim(((string) $client['prenom']) . ' ' . ((string) $client['nom']))); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Date commande</label>
        <input type="date" name="date_commande" value="<?php echo date('Y-m-d'); ?>" required>
      </div>
      <div class="field">
        <label>Statut initial</label>
        <select name="statut" required>
          <?php foreach ($statusOptions as $statusOption): ?>
            <option value="<?php echo admin_h($statusOption); ?>"><?php echo admin_h($statusOption); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <button type="submit" class="btn">Creer commande</button>
  </form>
</section>

<section class="panel">
  <h3>Liste des commandes clients</h3>
  <?php if (!empty($orders)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Client</th>
            <th>Articles</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?php echo (int) $order['id_commande']; ?></td>
              <td><?php echo admin_h($order['date_commande']); ?></td>
              <td><?php echo admin_h(trim(((string) $order['prenom']) . ' ' . ((string) $order['nom']))); ?></td>
              <td><?php echo (int) $order['total_articles']; ?></td>
              <td><span class="<?php echo admin_status_class($order['statut']); ?>"><?php echo admin_h($order['statut']); ?></span></td>
              <td>
                <div class="actions-row">
                  <form method="post" class="actions-row">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?php echo (int) $order['id_commande']; ?>">
                    <select name="new_status" required>
                      <?php foreach ($statusOptions as $statusOption): ?>
                        <option value="<?php echo admin_h($statusOption); ?>" <?php echo ((string) $order['statut'] === (string) $statusOption) ? 'selected' : ''; ?>>
                          <?php echo admin_h($statusOption); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-secondary">Valider</button>
                  </form>

                  <form method="post" onsubmit="return confirm('Supprimer cette commande client ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="order_id" value="<?php echo (int) $order['id_commande']; ?>">
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
    <div class="empty-state">Aucune commande client enregistree.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
