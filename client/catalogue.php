<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Catalogue client';
$activePage = 'catalogue';

$products = [];
if ($clientDb instanceof mysqli) {
    $sql = "SELECT p.id_produit, p.libelle, p.quantite, COALESCE(f.libelle, 'Non classe') AS famille FROM Produit p LEFT JOIN Famille f ON f.id_famille = p.id_famille ORDER BY p.id_produit DESC";
    $result = $clientDb->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $result->free();
    }
}

include __DIR__ . '/_header.php';
?>

<section class="panel">
  <h2>Catalogue produits</h2>
  <p class="muted">Consultez les produits disponibles et verifiez leur stock actuel.</p>
  <div class="grid-4" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Produits total</div>
      <div class="value"><?php echo count($products); ?></div>
      <div class="sub">References en base</div>
    </article>
    <article class="stat">
      <div class="label">Stock disponible</div>
      <div class="value"><?php echo count(array_filter($products, function ($p) { return (int) $p['quantite'] > 0; })); ?></div>
      <div class="sub">Produits commandables</div>
    </article>
    <article class="stat">
      <div class="label">Stock faible</div>
      <div class="value"><?php echo count(array_filter($products, function ($p) { return (int) $p['quantite'] > 0 && (int) $p['quantite'] <= 5; })); ?></div>
      <div class="sub">Reapprovisionnement proche</div>
    </article>
    <article class="stat">
      <div class="label">Raccourci boutique</div>
      <div class="value" style="font-size:16px; margin-top:11px;"><a href="../acceuil.php" class="btn btn-secondary">Aller a l'accueil</a></div>
      <div class="sub">Version vitrine</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Liste des produits</h3>
  <?php if (!empty($products)): ?>
    <div class="cards-grid" style="margin-top:12px;">
      <?php foreach ($products as $product): ?>
        <?php
          $qty = (int) $product['quantite'];
          $stockLabel = $qty <= 0 ? 'Rupture' : ($qty <= 5 ? 'Stock faible' : 'Disponible');
          $stockClass = $qty <= 0 ? 'danger' : ($qty <= 5 ? 'warning' : 'success');
        ?>
        <article class="mini-card">
          <div class="card-top">
            <span class="badge info">#<?php echo (int) $product['id_produit']; ?></span>
            <span class="badge <?php echo $stockClass; ?>"><?php echo client_h($stockLabel); ?></span>
          </div>
          <h3><?php echo client_h($product['libelle']); ?></h3>
          <p>Famille: <?php echo client_h($product['famille']); ?></p>
          <p style="margin-top:8px;">Quantite en stock: <strong><?php echo $qty; ?></strong></p>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun produit trouve pour le moment.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
