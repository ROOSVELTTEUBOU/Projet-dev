<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Gestion fournisseurs';
$activePage = 'fournisseurs';

$notice = '';
$error = '';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'create') {
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $adresse = trim((string) ($_POST['adresse'] ?? ''));
        $ville = trim((string) ($_POST['ville'] ?? ''));
        $contact = trim((string) ($_POST['contact'] ?? ''));

        if ($nom === '') {
            $error = 'Le nom du fournisseur est obligatoire.';
        } else {
            $stmt = $adminDb->prepare('INSERT INTO Fournisseur (nom, adresse, ville, contact) VALUES (?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('ssss', $nom, $adresse, $ville, $contact);
                if ($stmt->execute()) {
                    $notice = 'Fournisseur ajoute.';
                    admin_log_action($adminDb, $adminId, 'Ajout fournisseur: ' . $nom);
                } else {
                    $error = 'Echec ajout fournisseur.';
                }
                $stmt->close();
            } else {
                $error = 'Requete creation fournisseur invalide.';
            }
        }
    } elseif ($action === 'update') {
        $supplierId = (int) ($_POST['supplier_id'] ?? 0);
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $adresse = trim((string) ($_POST['adresse'] ?? ''));
        $ville = trim((string) ($_POST['ville'] ?? ''));
        $contact = trim((string) ($_POST['contact'] ?? ''));

        if ($supplierId <= 0 || $nom === '') {
            $error = 'Parametres de mise a jour invalides.';
        } else {
            $stmt = $adminDb->prepare('UPDATE Fournisseur SET nom = ?, adresse = ?, ville = ?, contact = ? WHERE id_fournisseur = ?');
            if ($stmt) {
                $stmt->bind_param('ssssi', $nom, $adresse, $ville, $contact, $supplierId);
                if ($stmt->execute()) {
                    $notice = 'Fournisseur mis a jour.';
                    admin_log_action($adminDb, $adminId, 'Mise a jour fournisseur #' . $supplierId);
                    $editId = 0;
                } else {
                    $error = 'Echec mise a jour fournisseur.';
                }
                $stmt->close();
            } else {
                $error = 'Requete mise a jour fournisseur invalide.';
            }
        }
    } elseif ($action === 'delete') {
        $supplierId = (int) ($_POST['supplier_id'] ?? 0);

        if ($supplierId <= 0) {
            $error = 'Fournisseur invalide.';
        } else {
            $stmt = $adminDb->prepare('DELETE FROM Fournisseur WHERE id_fournisseur = ?');
            if ($stmt) {
                $stmt->bind_param('i', $supplierId);
                if ($stmt->execute()) {
                    $notice = 'Fournisseur supprime.';
                    admin_log_action($adminDb, $adminId, 'Suppression fournisseur #' . $supplierId);
                } else {
                    $error = 'Suppression fournisseur impossible.';
                }
                $stmt->close();
            } else {
                $error = 'Requete suppression fournisseur invalide.';
            }
        }
    }
}

$suppliers = [];
if ($adminDb instanceof mysqli) {
    $result = $adminDb->query('SELECT id_fournisseur, nom, adresse, ville, contact FROM Fournisseur ORDER BY id_fournisseur DESC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
        $result->free();
    }
}

$currentSupplier = null;
if ($editId > 0 && $adminDb instanceof mysqli) {
    $stmt = $adminDb->prepare('SELECT id_fournisseur, nom, adresse, ville, contact FROM Fournisseur WHERE id_fournisseur = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $currentSupplier = $res->fetch_assoc();
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
  <h2>Gestion des fournisseurs</h2>
  <p class="muted">Maintenez a jour les fournisseurs et leurs contacts.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Fournisseurs total</div>
      <div class="value"><?php echo count($suppliers); ?></div>
      <div class="sub">Partenaires en base</div>
    </article>
    <article class="stat">
      <div class="label">Villes</div>
      <div class="value"><?php echo count(array_unique(array_filter(array_map(function ($s) { return (string) $s['ville']; }, $suppliers)))); ?></div>
      <div class="sub">Couverture geographique</div>
    </article>
    <article class="stat">
      <div class="label">Formulaire</div>
      <div class="value" style="font-size:16px; margin-top:11px;"><?php echo $currentSupplier ? 'Edition' : 'Creation'; ?></div>
      <div class="sub">Mode actif</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3><?php echo $currentSupplier ? 'Modifier un fournisseur' : 'Ajouter un fournisseur'; ?></h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="<?php echo $currentSupplier ? 'update' : 'create'; ?>">
    <?php if ($currentSupplier): ?>
      <input type="hidden" name="supplier_id" value="<?php echo (int) $currentSupplier['id_fournisseur']; ?>">
    <?php endif; ?>

    <div class="grid-2">
      <div class="field">
        <label>Nom</label>
        <input type="text" name="nom" maxlength="50" required value="<?php echo admin_h($currentSupplier['nom'] ?? ''); ?>">
      </div>
      <div class="field">
        <label>Contact</label>
        <input type="text" name="contact" maxlength="20" value="<?php echo admin_h($currentSupplier['contact'] ?? ''); ?>">
      </div>
    </div>

    <div class="grid-2">
      <div class="field">
        <label>Adresse</label>
        <input type="text" name="adresse" maxlength="100" value="<?php echo admin_h($currentSupplier['adresse'] ?? ''); ?>">
      </div>
      <div class="field">
        <label>Ville</label>
        <input type="text" name="ville" maxlength="50" value="<?php echo admin_h($currentSupplier['ville'] ?? ''); ?>">
      </div>
    </div>

    <div class="actions-row">
      <button type="submit" class="btn"><?php echo $currentSupplier ? 'Mettre a jour fournisseur' : 'Ajouter fournisseur'; ?></button>
      <?php if ($currentSupplier): ?>
        <a href="fournisseurs.php" class="btn btn-secondary">Annuler edition</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h3>Liste des fournisseurs</h3>
  <?php if (!empty($suppliers)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Adresse</th>
            <th>Ville</th>
            <th>Contact</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($suppliers as $supplier): ?>
            <tr>
              <td><?php echo (int) $supplier['id_fournisseur']; ?></td>
              <td><?php echo admin_h($supplier['nom']); ?></td>
              <td><?php echo admin_h($supplier['adresse'] ?: '-'); ?></td>
              <td><?php echo admin_h($supplier['ville'] ?: '-'); ?></td>
              <td><?php echo admin_h($supplier['contact'] ?: '-'); ?></td>
              <td>
                <div class="actions-row">
                  <a class="btn btn-secondary" href="?edit=<?php echo (int) $supplier['id_fournisseur']; ?>">Modifier</a>
                  <form method="post" onsubmit="return confirm('Supprimer ce fournisseur ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="supplier_id" value="<?php echo (int) $supplier['id_fournisseur']; ?>">
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
    <div class="empty-state">Aucun fournisseur enregistre.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
