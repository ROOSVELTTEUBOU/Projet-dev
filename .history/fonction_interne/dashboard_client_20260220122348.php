<?php
session_start();
if (!isset($_SESSION['id_client'])) {
    header("Location: login_client.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Client</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f0f8ff; margin:0; padding:20px; }
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
    <h1>Bienvenue <?php echo $_SESSION['prenom']." ".$_SESSION['nom']; ?> 🎉</h1>
    <p>Vous êtes connecté en tant que <strong>Client</strong></p>
    <a href="./logout.php" class="logout">Déconnexion</a>
  </header>

  <main>
    <h2>Votre tableau de bord</h2>
    <ul>
      <li><a href="catalogue.php">Voir le catalogue</a></li>
      <li><a href="panier.php">Accéder au panier</a></li>
      <li><a href="commandes.php">Suivre vos commandes</a></li>
      <li><a href="paiements.php">Consulter vos paiements</a></li>
    </ul>
  </main>
</body>
</html>