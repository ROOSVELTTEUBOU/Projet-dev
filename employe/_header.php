<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Espace employe';
}
if (!isset($activePage)) {
    $activePage = '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo employe_h($pageTitle); ?></title>
  <link rel="stylesheet" href="../employe/employe-theme.css">
</head>
<body>
  <div class="site-shell">
    <header class="topbar">
      <div class="brand">
        <h1>Espace Employe</h1>
        <p>Operations quotidiennes et suivi metiers</p>
      </div>
      <div class="identity">
        <div>
          <div class="identity-name"><?php echo employe_h($employeName); ?></div>
          <div class="identity-sub">ID utilisateur: <?php echo (int) $employeId; ?></div>
        </div>
        <div class="avatar"><?php echo employe_h($initials); ?></div>
        <a href="../fonction_interne/logout.php" class="btn btn-outline">Deconnexion</a>
      </div>
    </header>

    <nav class="main-nav">
      <?php foreach ($employeNav as $item): ?>
        <a class="nav-link <?php echo $activePage === $item['key'] ? 'active' : ''; ?>" href="<?php echo employe_h($item['href']); ?>">
          <?php echo employe_h($item['label']); ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <main class="page-wrap">
      <?php if ($employeDbError !== null): ?>
        <div class="alert alert-error"><?php echo employe_h($employeDbError); ?></div>
      <?php endif; ?>

