<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Gestion familles';
$activePage = 'familles';

$notice = '';
$error = '';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'create') {
        $libelle = trim((string) ($_POST['libelle'] ?? ''));

        if ($libelle === '') {
            $error = 'Le libelle est obligatoire.';
        } else {
            $stmt = $adminDb->prepare('INSERT INTO Famille (libelle) VALUES (?)');
            if ($stmt) {
                $stmt->bind_param('s', $libelle);
                if ($stmt->execute()) {
                    $notice = 'Famille ajoutee avec succes.';
                    admin_log_action($adminDb, $adminId, 'Ajout famille: ' . $libelle);
                } else {
                    $error = 'Echec ajout famille: ' . $adminDb->error;
                }
                $stmt->close();
            } else {
                $error = 'Requete de creation invalide.';
            }
        }
    } elseif ($action === 'update') {
        $familyId = (int) ($_POST['family_id'] ?? 0);
        $libelle = trim((string) ($_POST['libelle'] ?? ''));

        if ($familyId <= 0 || $libelle === '') {
            $error = 'Parametres de mise a jour invalides.';
        } else {
            $stmt = $adminDb->prepare('UPDATE Famille SET libelle = ? WHERE id_famille = ?');
            if ($stmt) {
                $stmt->bind_param('si', $libelle, $familyId);
                if ($stmt->execute()) {
                    $notice = 'Famille mise a jour.';
                    admin_log_action($adminDb, $adminId, 'Mise a jour famille #' . $familyId);
                    $editId = 0;
                } else {
                    $error = 'Echec mise a jour famille.';
                }
                $stmt->close();
            } else {
                $error = 'Requete de mise a jour invalide.';
            }
        }
    } elseif ($action === 'delete') {
        $familyId = (int) ($_POST['family_id'] ?? 0);

        if ($familyId <= 0) {
            $error = 'Famille invalide.';
        } else {
            $stmt = $adminDb->prepare('DELETE FROM Famille WHERE id_famille = ?');
            if ($stmt) {
                $stmt->bind_param('i', $familyId);
                if ($stmt->execute()) {
                    $notice = 'Famille supprimee.';
                    admin_log_action($adminDb, $adminId, 'Suppression famille #' . $familyId);
                } else {
                    $error = 'Suppression impossible.';
                }
                $stmt->close();
            } else {
                $error = 'Requete de suppression invalide.';
            }
        }
    }
}

$families = [];
if ($adminDb instanceof mysqli) {
    $result = $adminDb->query('SELECT id_famille, libelle FROM Famille ORDER BY libelle ASC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $families[] = $row;
        }
        $result->free();
    }
}

$currentFamily = null;
if ($editId > 0 && $adminDb instanceof mysqli) {
    $stmt = $adminDb->prepare('SELECT id_famille, libelle FROM Famille WHERE id_famille = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $currentFamily = $res->fetch_assoc();
            }
        }
        $stmt->close();
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
  <h2>Gestion des familles</h2>
  <p class="muted">Ajoutez, modifiez ou supprimez les familles de produits.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Total familles</div>
      <div class="value"><?php echo count($families); ?></div>
      <div class="sub">Categories disponibles</div>
    </article>
    <article class="stat">
      <div class="label">Formulaire</div>
      <div class="value" style="font-size:16px; margin-top:11px;"><?php echo $currentFamily ? 'Edition' : 'Creation'; ?></div>
      <div class="sub">Mode actif</div>
    </article>
    <article class="stat">
      <div class="label">Lien rapide</div>
      <div class="value" style="font-size:16px; margin-top:11px;"><a class="btn btn-secondary" href="produits.php">Voir produits</a></div>
      <div class="sub">Gestion catalogue</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3><?php echo $currentFamily ? 'Modifier une famille' : 'Ajouter une famille'; ?></h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="<?php echo $currentFamily ? 'update' : 'create'; ?>">
    <?php if ($currentFamily): ?>
      <input type="hidden" name="family_id" value="<?php echo (int) $currentFamily['id_famille']; ?>">
    <?php endif; ?>

    <div class="field">
      <label>Libelle</label>
      <input type="text" name="libelle" maxlength="50" required value="<?php echo admin_h($currentFamily['libelle'] ?? ''); ?>">
    </div>

    <div class="actions-row">
      <button type="submit" class="btn"><?php echo $currentFamily ? 'Enregistrer la mise a jour' : 'Ajouter la famille'; ?></button>
      <?php if ($currentFamily): ?>
        <a href="familles.php" class="btn btn-secondary">Annuler edition</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h3>Liste des familles</h3>
  <?php if (!empty($families)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Libelle</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($families as $family): ?>
            <tr>
              <td><?php echo (int) $family['id_famille']; ?></td>
              <td><?php echo admin_h($family['libelle']); ?></td>
              <td>
                <div class="actions-row">
                  <a class="btn btn-secondary" href="?edit=<?php echo (int) $family['id_famille']; ?>">Modifier</a>
                  <form method="post" onsubmit="return confirm('Supprimer cette famille ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="family_id" value="<?php echo (int) $family['id_famille']; ?>">
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
    <div class="empty-state">Aucune famille enregistree.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
