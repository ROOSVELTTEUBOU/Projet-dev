<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: fonction/login_admin.php");
    exit();
}

// Vérification stricte du rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrateur') {
    header("Location: fonction/login_admin.php");
    exit();
}
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
    <a href="fonction_interne/logout.php" class="logout">Déconnexion</a>
  </header>

  <main>
    <h2>Tableau de bord Administrateur</h2>
        <ul>
      <li><a href="admin/familles.php">Gerer les familles</a></li>
      <li><a href="admin/produits.php">Gerer les produits</a></li>
      <li><a href="admin/clients.php">Gerer les clients</a></li>
      <li><a href="admin/fournisseurs.php">Gerer les fournisseurs</a></li>
      <li><a href="admin/utilisateurs.php">Gerer les utilisateurs</a></li>
      <li><a href="admin/commande_client.php">Gerer les commandes clients</a></li>
      <li><a href="admin/commande_fournisseur.php">Gerer les commandes fournisseurs</a></li>
      <li><a href="admin/paiement_client.php">Gerer les paiements</a></li>
      <li><a href="admin/logs.php">Consulter les logs</a></li>
      <li><a href="fonction_interne/dashboard_employe.php">Dashboard employe</a></li>
      <li><a href="fonction_interne/dashboard_client.php">Dashboard client</a></li>
      <li><a href="acceuil.php">Retour a l'accueil</a></li>
    </ul>
  </main>
</body>
</html>


