<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login_employe.php");
    exit();
}

// Vérification stricte du rôle
if ($_SESSION['role'] !== 'Employe') {
    header("Location: login_employe.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Employé</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f9fbff; margin:0; padding:20px; }
    header { background:#007bff; color:#fff; padding:15px; text-align:center; border-radius:8px; }
    .logout { display:inline-block; margin-top:15px; padding:10px 20px; background:#0056b3; color:#fff; text-decoration:none; border-radius:5px; }
    .logout:hover { background:#003f7f; }
    main { margin-top:20px; }
    ul { list-style:none; padding:0; }
    li { margin:10px 0; }
    a { color:#007bff; text-decoration:none; font-weight:bold; }
    a:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <header>
    <h1>Bienvenue Employé <?php echo $_SESSION['user']; ?> 👔</h1>
    <p>Vous avez accès aux opérations quotidiennes</p>
    <a href="logout.php" class="logout">Déconnexion</a>
  </header>

  <main>
    <h2>Tableau de bord Employé</h2>
    <ul>
      <li><a href="stock.php">Gérer le stock</a></li>
      <li><a href="commandes_clients.php">Suivre les commandes clients</a></li>
      <li><a href="commandes_fournisseurs.php">Suivre les commandes fournisseurs</a></li>
      <li><a href="paiements.php">Valider les paiements</a></li>
    </ul>
  </main>
</body>
</html>