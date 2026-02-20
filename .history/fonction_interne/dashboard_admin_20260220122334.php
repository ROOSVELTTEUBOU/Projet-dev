<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: login_admin.php");
    exit();
}

// Vérification stricte du rôle
// if ($_SESSION['role'] !== 'Administrateur') {
//     header("Location: login_admin.php");
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Administrateur</title>
  <style>
    body { font-family: Arial, sans-serif; background:#eef6ff; margin:0; padding:20px; }
    header { background:#0056b3; color:#fff; padding:15px; text-align:center; border-radius:8px; }
    .logout { display:inline-block; margin-top:15px; padding:10px 20px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px; }
    .logout:hover { background:#003f7f; }
    main { margin-top:20px; }
    ul { list-style:none; padding:0; }
    li { margin:10px 0; }
    a { color:#0056b3; text-decoration:none; font-weight:bold; }
    a:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <header>
    <h1>Bienvenue Administrateur <?php echo $_SESSION['user']; ?> ⚡</h1>
    <p>Vous avez un accès complet au système Appecom</p>
    <a href="./logout.php" class="logout">Déconnexion</a>
  </header>

  <main>
    <h2>Tableau de bord Administrateur</h2>
    <ul>
      <li><a href="gestion_utilisateurs.php">Gérer les utilisateurs</a></li>
      <li><a href="gestion_produits.php">Gérer les produits</a></li>
      <li><a href="gestion_commandes.php">Gérer les commandes</a></li>
      <li><a href="gestion_paiements.php">Gérer les paiements</a></li>
      <li><a href="logs.php">Consulter les logs</a></li>
    </ul>
  </main>
</body>
</html>