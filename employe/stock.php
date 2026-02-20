<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Gestion du stock';
$activePage = 'stock';

$notice = '';
$error = '';

$movementTypes = [];
if ($employeDb instanceof mysqli) {
    $movementTypes = employe_enum_values($employeDb, 'Stock_Mouvement', 'type');
}
if (empty($movementTypes)) {
    $movementTypes = ['Entree', 'Sortie'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_movement') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $movementType = trim((string) ($_POST['movement_type'] ?? ''));

    if (!($employeDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($productId <= 0 || $quantity <= 0 || $movementType === '') {
        $error = 'Veuillez renseigner un produit, un type et une quantite valide.';
    } elseif (!employe_status_in_allowed($movementType, $movementTypes)) {
        $error = 'Type de mouvement non autorise.';
    } else {
        try {
            $employeDb->begin_transaction();

            $selectStmt = $employeDb->prepare('SELECT quantite FROM Produit WHERE id_produit = ? FOR UPDATE');
            if (!$selectStmt) {
                throw new Exception('Requete produit invalide.');
            }
            $selectStmt->bind_param('i', $productId);
            if (!$selectStmt->execute()) {
                $selectStmt->close();
                throw new Exception('Echec de lecture du stock.');
            }
            $result = $selectStmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $selectStmt->close();

            if (!$row) {
                throw new Exception('Produit introuvable.');
            }

            $currentQty = (int) $row['quantite'];
            $isSortie = stripos($movementType, 'sort') !== false;
            $newQty = $isSortie ? $currentQty - $quantity : $currentQty + $quantity;

            if ($newQty < 0) {
                throw new Exception('Stock insuffisant pour une sortie de cette quantite.');
            }

            $updateStmt = $employeDb->prepare('UPDATE Produit SET quantite = ? WHERE id_produit = ?');
            if (!$updateStmt) {
                throw new Exception('Requete de mise a jour invalide.');
            }
            $updateStmt->bind_param('ii', $newQty, $productId);
            if (!$updateStmt->execute()) {
                $updateStmt->close();
                throw new Exception('Mise a jour du stock echouee.');
            }
            $updateStmt->close();

            $insertStmt = $employeDb->prepare('INSERT INTO Stock_Mouvement (id_produit, type, quantite) VALUES (?, ?, ?)');
            if (!$insertStmt) {
                throw new Exception('Requete de mouvement invalide.');
            }
            $insertStmt->bind_param('isi', $productId, $movementType, $quantity);
            if (!$insertStmt->execute()) {
                $insertStmt->close();
                throw new Exception('Enregistrement du mouvement echoue.');
            }
            $insertStmt->close();

            $employeDb->commit();
            $notice = 'Mouvement enregistre avec succes.';
        } catch (Throwable $e) {
            if ($employeDb->errno || $employeDb->error || $employeDb->insert_id !== null) {
                $employeDb->rollback();
            }
            $error = $e->getMessage();
        }
    }
}

$products = [];
$movements = [];
$todayMovements = 0;

if ($employeDb instanceof mysqli) {
    $productsResult = $employeDb->query("SELECT p.id_produit, p.libelle, p.quantite, COALESCE(f.libelle, 'Non classe') AS famille FROM Produit p LEFT JOIN Famille f ON f.id_famille = p.id_famille ORDER BY p.libelle ASC");
    if ($productsResult) {
        while ($row = $productsResult->fetch_assoc()) {
            $products[] = $row;
        }
        $productsResult->free();
    }

    $movementsResult = $employeDb->query('SELECT sm.id_mouvement, sm.date_mouvement, sm.type, sm.quantite, p.libelle FROM Stock_Mouvement sm INNER JOIN Produit p ON p.id_produit = sm.id_produit ORDER BY sm.date_mouvement DESC, sm.id_mouvement DESC LIMIT 20');
    if ($movementsResult) {
        while ($row = $movementsResult->fetch_assoc()) {
            $movements[] = $row;
        }
        $movementsResult->free();
    }

    $todayResult = $employeDb->query('SELECT COUNT(*) AS total FROM Stock_Mouvement WHERE DATE(date_mouvement) = CURDATE()');
    if ($todayResult && $todayRow = $todayResult->fetch_assoc()) {
        $todayMovements = (int) $todayRow['total'];
    }
    if ($todayResult) {
        $todayResult->free();
    }
}

$totalProducts = count($products);
$lowStock = 0;
$outOfStock = 0;
foreach ($products as $product) {
    $qty = (int) $product['quantite'];
    if ($qty <= 0) {
        $outOfStock++;
    } elseif ($qty <= 5) {
        $lowStock++;
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
  <h2>Gestion du stock</h2>
  <p class="muted">Supervisez les quantites et enregistrez les mouvements d'entree ou de sortie.</p>

  <div class="grid-4" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Produits suivis</div>
      <div class="value"><?php echo $totalProducts; ?></div>
      <div class="sub">References en catalogue</div>
    </article>
    <article class="stat">
      <div class="label">Stock faible</div>
      <div class="value"><?php echo $lowStock; ?></div>
      <div class="sub">Quantite entre 1 et 5</div>
    </article>
    <article class="stat">
      <div class="label">Rupture</div>
      <div class="value"><?php echo $outOfStock; ?></div>
      <div class="sub">Produits indisponibles</div>
    </article>
    <article class="stat">
      <div class="label">Mouvements du jour</div>
      <div class="value"><?php echo $todayMovements; ?></div>
      <div class="sub">Date: <?php echo date('Y-m-d'); ?></div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Nouveau mouvement de stock</h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="add_movement">

    <div class="grid-3">
      <div class="field">
        <label>Produit</label>
        <select name="product_id" required>
          <option value="">Selectionner un produit</option>
          <?php foreach ($products as $product): ?>
            <option value="<?php echo (int) $product['id_produit']; ?>">
              <?php echo employe_h($product['libelle']); ?> (stock: <?php echo (int) $product['quantite']; ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Type de mouvement</label>
        <select name="movement_type" required>
          <?php foreach ($movementTypes as $type): ?>
            <option value="<?php echo employe_h($type); ?>"><?php echo employe_h($type); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Quantite</label>
        <input type="number" name="quantity" min="1" step="1" required>
      </div>
    </div>

    <div class="actions-row">
      <button type="submit" class="btn">Enregistrer le mouvement</button>
      <a href="commandes_fournisseurs.php" class="btn btn-secondary">Voir commandes fournisseurs</a>
    </div>
  </form>
</section>

<section class="panel">
  <h3>Stock courant par produit</h3>
  <?php if (!empty($products)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Produit</th>
            <th>Famille</th>
            <th>Quantite</th>
            <th>Etat</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <?php
              $qty = (int) $product['quantite'];
              if ($qty <= 0) {
                  $state = 'Rupture';
                  $stateClass = 'badge danger';
              } elseif ($qty <= 5) {
                  $state = 'Stock faible';
                  $stateClass = 'badge warning';
              } else {
                  $state = 'Disponible';
                  $stateClass = 'badge success';
              }
            ?>
            <tr>
              <td><?php echo (int) $product['id_produit']; ?></td>
              <td><?php echo employe_h($product['libelle']); ?></td>
              <td><?php echo employe_h($product['famille']); ?></td>
              <td><?php echo $qty; ?></td>
              <td><span class="<?php echo $stateClass; ?>"><?php echo employe_h($state); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun produit disponible dans la base.</div>
  <?php endif; ?>
</section>

<section class="panel">
  <h3>Derniers mouvements de stock</h3>
  <?php if (!empty($movements)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Produit</th>
            <th>Type</th>
            <th>Quantite</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($movements as $movement): ?>
            <tr>
              <td><?php echo (int) $movement['id_mouvement']; ?></td>
              <td><?php echo employe_h($movement['date_mouvement']); ?></td>
              <td><?php echo employe_h($movement['libelle']); ?></td>
              <td><span class="<?php echo employe_status_class($movement['type']); ?>"><?php echo employe_h(employe_status_text($movement['type'])); ?></span></td>
              <td><?php echo (int) $movement['quantite']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun mouvement de stock enregistre.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>

