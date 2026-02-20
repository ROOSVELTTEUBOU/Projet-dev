<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Mon panier';
$activePage = 'panier';

if (!isset($_SESSION['panier']) || !is_array($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'clear_cart') {
        $_SESSION['panier'] = [];
        $message = 'Le panier a ete vide.';
    }
}

if (isset($_GET['remove'])) {
    $removeKey = (string) $_GET['remove'];
    if (array_key_exists($removeKey, $_SESSION['panier'])) {
        unset($_SESSION['panier'][$removeKey]);
        $message = 'Article retire du panier.';
    }
}

$cartRows = [];
$totalItems = 0;
$totalAmount = 0.0;

foreach ($_SESSION['panier'] as $key => $rawItem) {
    $name = 'Produit #' . (string) $key;
    $qty = 1;
    $price = 0.0;

    if (is_array($rawItem)) {
        if (isset($rawItem['libelle'])) {
            $name = (string) $rawItem['libelle'];
        } elseif (isset($rawItem['nom'])) {
            $name = (string) $rawItem['nom'];
        } elseif (isset($rawItem['name'])) {
            $name = (string) $rawItem['name'];
        }

        if (isset($rawItem['quantite'])) {
            $qty = (int) $rawItem['quantite'];
        } elseif (isset($rawItem['quantity'])) {
            $qty = (int) $rawItem['quantity'];
        } elseif (isset($rawItem['qty'])) {
            $qty = (int) $rawItem['qty'];
        }

        if (isset($rawItem['prix'])) {
            $price = (float) $rawItem['prix'];
        } elseif (isset($rawItem['price'])) {
            $price = (float) $rawItem['price'];
        } elseif (isset($rawItem['montant'])) {
            $price = (float) $rawItem['montant'];
        }
    } elseif (is_numeric($rawItem)) {
        $qty = (int) $rawItem;
    }

    if ($qty < 1) {
        $qty = 1;
    }

    $lineTotal = $qty * $price;

    $cartRows[] = [
        'key' => (string) $key,
        'name' => $name,
        'qty' => $qty,
        'price' => $price,
        'line_total' => $lineTotal,
    ];

    $totalItems += $qty;
    $totalAmount += $lineTotal;
}

include __DIR__ . '/_header.php';
?>

<?php if ($message !== ''): ?>
  <div class="alert alert-success"><?php echo client_h($message); ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert alert-error"><?php echo client_h($error); ?></div>
<?php endif; ?>

<section class="panel">
  <h2>Mon panier</h2>
  <p class="muted">Consultez vos articles, verifiez les quantites et preparez votre prochaine commande.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Lignes panier</div>
      <div class="value"><?php echo count($cartRows); ?></div>
      <div class="sub">Articles differents</div>
    </article>
    <article class="stat">
      <div class="label">Quantite totale</div>
      <div class="value"><?php echo $totalItems; ?></div>
      <div class="sub">Unites dans le panier</div>
    </article>
    <article class="stat">
      <div class="label">Montant estime</div>
      <div class="value"><?php echo number_format($totalAmount, 0, ',', ' '); ?> FCFA</div>
      <div class="sub">Estimation a titre indicatif</div>
    </article>
  </div>

  <div class="actions-row" style="margin-top:12px;">
    <a class="btn btn-secondary" href="catalogue.php">Continuer mes achats</a>
    <a class="btn btn-secondary" href="commande_client.php">Voir mes commandes</a>
    <form method="post" style="display:inline;">
      <input type="hidden" name="action" value="clear_cart">
      <button type="submit" class="btn btn-danger">Vider le panier</button>
    </form>
  </div>
</section>

<section class="panel">
  <h3>Detail du panier</h3>
  <?php if (!empty($cartRows)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Reference</th>
            <th>Produit</th>
            <th>Quantite</th>
            <th>Prix unitaire</th>
            <th>Total ligne</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cartRows as $row): ?>
            <tr>
              <td><?php echo client_h($row['key']); ?></td>
              <td><?php echo client_h($row['name']); ?></td>
              <td><?php echo (int) $row['qty']; ?></td>
              <td><?php echo number_format((float) $row['price'], 0, ',', ' '); ?> FCFA</td>
              <td><?php echo number_format((float) $row['line_total'], 0, ',', ' '); ?> FCFA</td>
              <td>
                <a class="btn btn-secondary" href="?remove=<?php echo urlencode($row['key']); ?>">Retirer</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Votre panier est vide. Ajoutez des produits depuis le catalogue.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
