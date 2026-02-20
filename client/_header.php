<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Espace client';
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
  <title><?php echo client_h($pageTitle); ?></title>
  <link rel="stylesheet" href="client-theme.css">
</head>
<body>
  <div class="site-shell">
    <header class="topbar">
      <div class="brand">
        <h1>Espace Client</h1>
        <p>Gestion de compte et suivi des operations</p>
      </div>
      <div class="identity">
        <div>
          <div class="identity-name"><?php echo client_h($clientName); ?></div>
          <div class="identity-sub">ID client: <?php echo (int) $clientId; ?></div>
        </div>
        <div class="avatar"><?php echo client_h($clientInitial); ?></div>
        <a href="../fonction_interne/logout.php" class="btn btn-outline">Deconnexion</a>
      </div>
    </header>

    <nav class="main-nav">
      <?php foreach ($clientNav as $item): ?>
        <a
          class="nav-link <?php echo $activePage === $item['key'] ? 'active' : ''; ?>"
          href="<?php echo client_h($item['href']); ?>"
        >
          <?php echo client_h($item['label']); ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <main class="page-wrap">
      <?php if ($clientDbError !== null): ?>
        <div class="alert alert-error"><?php echo client_h($clientDbError); ?></div>
      <?php endif; ?>
