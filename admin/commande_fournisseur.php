<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Commandes fournisseurs';
$activePage = 'commande_fournisseur';

$notice = '';
$error = '';

$statusOptions = [];
$supplierOptions = [];

if ($adminDb instanceof mysqli) {
    $statusOptions = admin_enum_values($adminDb, 'Commande_Fournisseur', 'etat');
    if (empty($statusOptions)) {
        $statusOptions = ['En attente', 'Recue', 'Annulee'];
    }

    $supRes = $adminDb->query('SELECT id_fournisseur, nom FROM Fournisseur ORDER BY nom ASC');
    if ($supRes) {
        while ($row = $supRes->fetch_assoc()) {
            $supplierOptions[] = $row;
        }
        $supRes->free();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'create') {
        $supplierId = (int) ($_POST['id_fournisseur'] ?? 0);
        $dateCommande = trim((string) ($_POST['date_commande'] ?? ''));
        $etat = trim((string) ($_POST['etat'] ?? ''));

        if ($supplierId <= 0 || $dateCommande === '' || !admin_in_allowed($etat, $statusOptions)) {
            $error = 'Parametres invalides pour creation commande fournisseur.';
        } else {
            $stmt = $adminDb->prepare('INSERT INTO Commande_Fournisseur (date_commande, etat, id_fournisseur) VALUES (?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('ssi', $dateCommande, $etat, $supplierId);
                if ($stmt->execute()) {
                    $notice = 'Commande fournisseur creee.';
                    admin_log_action($adminDb, $adminId, 'Creation commande fournisseur #' . $stmt->insert_id);
                } else {
                    $error = 'Echec creation commande fournisseur.';
                }
                $stmt->close();
            } else {
                $error = 'Requete creation commande fournisseur invalide.';
            }
        }
    } elseif ($action === 'update_status') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $newStatus = trim((string) ($_POST['new_status'] ?? ''));

        if ($orderId <= 0 || !admin_in_allowed($newStatus, $statusOptions)) {
            $error = 'Parametres invalides pour mise a jour etat.';
        } else {
            $stmt = $adminDb->prepare('UPDATE Commande_Fournisseur SET etat = ? WHERE id_commande_f = ?');
            if ($stmt) {
                $stmt->bind_param('si', $newStatus, $orderId);
                if ($stmt->execute()) {
                    $notice = 'Etat commande fournisseur mis a jour.';
                    admin_log_action($adminDb, $adminId, 'Maj etat commande fournisseur #' . $orderId . ' -> ' . $newStatus);
                } else {
                    $error = 'Echec mise a jour etat commande fournisseur.';
                }
                $stmt->close();
            } else {
                $error = 'Requete mise a jour etat invalide.';
            }
        }
    } elseif ($action === 'delete') {
        $orderId = (int) ($_POST['order_id'] ?? 0);

        if ($orderId <= 0) {
            $error = 'Commande fournisseur invalide.';
        } else {
            $stmt = $adminDb->prepare('DELETE FROM Commande_Fournisseur WHERE id_commande_f = ?');
            if ($stmt) {
                $stmt->bind_param('i', $orderId);
                if ($stmt->execute()) {
                    $notice = 'Commande fournisseur supprimee.';
                    admin_log_action($adminDb, $adminId, 'Suppression commande fournisseur #' . $orderId);
                } else {
                    $error = 'Suppression commande fournisseur impossible.';
                }
                $stmt->close();
            } else {
                $error = 'Requete suppression commande fournisseur invalide.';
            }
        }
    }
}

$orders = [];
if ($adminDb instanceof mysqli) {
    $sql = 'SELECT cf.id_commande_f, cf.date_commande, cf.etat, f.nom AS fournisseur_nom FROM Commande_Fournisseur cf LEFT JOIN Fournisseur f ON f.id_fournisseur = cf.id_fournisseur ORDER BY cf.date_commande DESC, cf.id_commande_f DESC';
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
  <h2>Commandes fournisseurs</h2>
  <p class="muted">Creation, suivi et mise a jour des commandes fournisseurs.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Total commandes</div>
      <div class="value"><?php echo count($orders); ?></div>
      <div class="sub">Commandes fournisseurs</div>
    </article>
    <article class="stat">
      <div class="label">En attente</div>
      <div class="value"><?php echo count(array_filter($orders, function ($o) { return stripos((string) $o['etat'], 'attente') !== false; })); ?></div>
      <div class="sub">A receptionner</div>
    </article>
    <article class="stat">
      <div class="label">Recues</div>
      <div class="value"><?php echo count(array_filter($orders, function ($o) { return stripos((string) $o['etat'], 'rec') === 0; })); ?></div>
      <div class="sub">Approvisionnees</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Creer une commande fournisseur</h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="create">

    <div class="grid-3">
      <div class="field">
        <label>Fournisseur</label>
        <select name="id_fournisseur" required>
          <option value="">Selectionner un fournisseur</option>
          <?php foreach ($supplierOptions as $supplier): ?>
            <option value="<?php echo (int) $supplier['id_fournisseur']; ?>"><?php echo admin_h($supplier['nom']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Date commande</label>
        <input type="date" name="date_commande" value="<?php echo date('Y-m-d'); ?>" required>
      </div>
      <div class="field">
        <label>Etat initial</label>
        <select name="etat" required>
          <?php foreach ($statusOptions as $statusOption): ?>
            <option value="<?php echo admin_h($statusOption); ?>"><?php echo admin_h($statusOption); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <button type="submit" class="btn">Creer commande fournisseur</button>
  </form>
</section>

<section class="panel">
  <h3>Liste des commandes fournisseurs</h3>
  <?php if (!empty($orders)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Fournisseur</th>
            <th>Etat</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?php echo (int) $order['id_commande_f']; ?></td>
              <td><?php echo admin_h($order['date_commande']); ?></td>
              <td><?php echo admin_h($order['fournisseur_nom'] ?: '-'); ?></td>
              <td><span class="<?php echo admin_status_class($order['etat']); ?>"><?php echo admin_h($order['etat']); ?></span></td>
              <td>
                <div class="actions-row">
                  <form method="post" class="actions-row">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?php echo (int) $order['id_commande_f']; ?>">
                    <select name="new_status" required>
                      <?php foreach ($statusOptions as $statusOption): ?>
                        <option value="<?php echo admin_h($statusOption); ?>" <?php echo ((string) $order['etat'] === (string) $statusOption) ? 'selected' : ''; ?>>
                          <?php echo admin_h($statusOption); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-secondary">Valider</button>
                  </form>

                  <form method="post" onsubmit="return confirm('Supprimer cette commande fournisseur ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="order_id" value="<?php echo (int) $order['id_commande_f']; ?>">
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
    <div class="empty-state">Aucune commande fournisseur enregistree.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
