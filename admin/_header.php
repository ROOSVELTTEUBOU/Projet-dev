<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Espace administrateur';
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
  <title><?php echo admin_h($pageTitle); ?></title>
  <link rel="stylesheet" href="admin-theme.css">
</head>
<body>
  <div class="site-shell">
    <header class="topbar">
      <div class="brand">
        <h1>Espace Administrateur</h1>
        <p>Configuration, supervision et controle de la plateforme</p>
      </div>
      <div class="identity">
        <div>
          <div class="identity-name"><?php echo admin_h($adminName); ?></div>
          <div class="identity-sub">ID admin: <?php echo (int) $adminId; ?></div>
        </div>
        <div class="avatar"><?php echo admin_h($adminInitials); ?></div>
        <a href="../fonction_interne/logout.php" class="btn btn-outline">Deconnexion</a>
      </div>
    </header>

    <nav class="main-nav">
      <?php foreach ($adminNav as $item): ?>
        <a class="nav-link <?php echo $activePage === $item['key'] ? 'active' : ''; ?>" href="<?php echo admin_h($item['href']); ?>">
          <?php echo admin_h($item['label']); ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <main class="page-wrap">
      <?php if ($adminDbError !== null): ?>
        <div class="alert alert-error"><?php echo admin_h($adminDbError); ?></div>
      <?php endif; ?>
