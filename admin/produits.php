<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Gestion produits';
$activePage = 'produits';

$notice = '';
$error = '';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

$families = [];
if ($adminDb instanceof mysqli) {
    $famRes = $adminDb->query('SELECT id_famille, libelle FROM Famille ORDER BY libelle ASC');
    if ($famRes) {
        while ($row = $famRes->fetch_assoc()) {
            $families[] = $row;
        }
        $famRes->free();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'create') {
        $libelle = trim((string) ($_POST['libelle'] ?? ''));
        $quantite = (int) ($_POST['quantite'] ?? 0);
        $familleId = (int) ($_POST['id_famille'] ?? 0);
        $familleNull = $familleId > 0 ? $familleId : null;

        if ($libelle === '' || $quantite < 0) {
            $error = 'Libelle ou quantite invalide.';
        } else {
            $stmt = $adminDb->prepare('INSERT INTO Produit (libelle, quantite, id_famille) VALUES (?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('sii', $libelle, $quantite, $familleNull);
                if ($stmt->execute()) {
                    $notice = 'Produit ajoute avec succes.';
                    admin_log_action($adminDb, $adminId, 'Ajout produit: ' . $libelle);
                } else {
                    $error = 'Echec ajout produit: ' . $adminDb->error;
                }
                $stmt->close();
            } else {
                $error = 'Requete de creation produit invalide.';
            }
        }
    } elseif ($action === 'update') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $libelle = trim((string) ($_POST['libelle'] ?? ''));
        $quantite = (int) ($_POST['quantite'] ?? 0);
        $familleId = (int) ($_POST['id_famille'] ?? 0);
        $familleNull = $familleId > 0 ? $familleId : null;

        if ($productId <= 0 || $libelle === '' || $quantite < 0) {
            $error = 'Parametres de mise a jour invalides.';
        } else {
            $stmt = $adminDb->prepare('UPDATE Produit SET libelle = ?, quantite = ?, id_famille = ? WHERE id_produit = ?');
            if ($stmt) {
                $stmt->bind_param('siii', $libelle, $quantite, $familleNull, $productId);
                if ($stmt->execute()) {
                    $notice = 'Produit mis a jour.';
                    admin_log_action($adminDb, $adminId, 'Mise a jour produit #' . $productId);
                    $editId = 0;
                } else {
                    $error = 'Echec mise a jour produit.';
                }
                $stmt->close();
            } else {
                $error = 'Requete de mise a jour produit invalide.';
            }
        }
    } elseif ($action === 'delete') {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            $error = 'Produit invalide.';
        } else {
            $stmt = $adminDb->prepare('DELETE FROM Produit WHERE id_produit = ?');
            if ($stmt) {
                $stmt->bind_param('i', $productId);
                if ($stmt->execute()) {
                    $notice = 'Produit supprime.';
                    admin_log_action($adminDb, $adminId, 'Suppression produit #' . $productId);
                } else {
                    $error = 'Suppression produit impossible.';
                }
                $stmt->close();
            } else {
                $error = 'Requete suppression produit invalide.';
            }
        }
    }
}

$products = [];
if ($adminDb instanceof mysqli) {
    $result = $adminDb->query("SELECT p.id_produit, p.libelle, p.quantite, p.id_famille, COALESCE(f.libelle, 'Non classe') AS famille FROM Produit p LEFT JOIN Famille f ON f.id_famille = p.id_famille ORDER BY p.id_produit DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $result->free();
    }
}

$currentProduct = null;
if ($editId > 0 && $adminDb instanceof mysqli) {
    $stmt = $adminDb->prepare('SELECT id_produit, libelle, quantite, id_famille FROM Produit WHERE id_produit = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $currentProduct = $res->fetch_assoc();
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
  <h2>Gestion des produits</h2>
  <p class="muted">Administrez le catalogue et les quantites disponibles en stock.</p>

  <div class="grid-4" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Produits total</div>
      <div class="value"><?php echo count($products); ?></div>
      <div class="sub">References en base</div>
    </article>
    <article class="stat">
      <div class="label">Stock faible</div>
      <div class="value"><?php echo count(array_filter($products, function ($p) { return (int) $p['quantite'] > 0 && (int) $p['quantite'] <= 5; })); ?></div>
      <div class="sub">Quantite <= 5</div>
    </article>
    <article class="stat">
      <div class="label">Rupture</div>
      <div class="value"><?php echo count(array_filter($products, function ($p) { return (int) $p['quantite'] <= 0; })); ?></div>
      <div class="sub">Produits non disponibles</div>
    </article>
    <article class="stat">
      <div class="label">Familles</div>
      <div class="value"><?php echo count($families); ?></div>
      <div class="sub">Categories actives</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3><?php echo $currentProduct ? 'Modifier un produit' : 'Ajouter un produit'; ?></h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="<?php echo $currentProduct ? 'update' : 'create'; ?>">
    <?php if ($currentProduct): ?>
      <input type="hidden" name="product_id" value="<?php echo (int) $currentProduct['id_produit']; ?>">
    <?php endif; ?>

    <div class="grid-3">
      <div class="field">
        <label>Libelle</label>
        <input type="text" name="libelle" maxlength="50" required value="<?php echo admin_h($currentProduct['libelle'] ?? ''); ?>">
      </div>

      <div class="field">
        <label>Quantite</label>
        <input type="number" name="quantite" min="0" required value="<?php echo admin_h($currentProduct['quantite'] ?? 0); ?>">
      </div>

      <div class="field">
        <label>Famille</label>
        <select name="id_famille">
          <option value="0">Non classe</option>
          <?php foreach ($families as $family): ?>
            <option value="<?php echo (int) $family['id_famille']; ?>" <?php echo ((int) ($currentProduct['id_famille'] ?? 0) === (int) $family['id_famille']) ? 'selected' : ''; ?>>
              <?php echo admin_h($family['libelle']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="actions-row">
      <button type="submit" class="btn"><?php echo $currentProduct ? 'Enregistrer la mise a jour' : 'Ajouter le produit'; ?></button>
      <?php if ($currentProduct): ?>
        <a href="produits.php" class="btn btn-secondary">Annuler edition</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h3>Liste des produits</h3>
  <?php if (!empty($products)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Libelle</th>
            <th>Famille</th>
            <th>Quantite</th>
            <th>Etat</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <?php
              $qty = (int) $product['quantite'];
              if ($qty <= 0) {
                  $stockText = 'Rupture';
                  $stockClass = 'badge danger';
              } elseif ($qty <= 5) {
                  $stockText = 'Stock faible';
                  $stockClass = 'badge warning';
              } else {
                  $stockText = 'Disponible';
                  $stockClass = 'badge success';
              }
            ?>
            <tr>
              <td><?php echo (int) $product['id_produit']; ?></td>
              <td><?php echo admin_h($product['libelle']); ?></td>
              <td><?php echo admin_h($product['famille']); ?></td>
              <td><?php echo $qty; ?></td>
              <td><span class="<?php echo $stockClass; ?>"><?php echo admin_h($stockText); ?></span></td>
              <td>
                <div class="actions-row">
                  <a class="btn btn-secondary" href="?edit=<?php echo (int) $product['id_produit']; ?>">Modifier</a>
                  <form method="post" onsubmit="return confirm('Supprimer ce produit ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id_produit']; ?>">
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
    <div class="empty-state">Aucun produit enregistre.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
