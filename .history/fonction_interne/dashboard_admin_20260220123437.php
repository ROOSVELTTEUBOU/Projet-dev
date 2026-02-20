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
    <a href="logout.php" class="logout">Déconnexion</a>
  </header>

  <nav>
    <!-- Module 1 : Gestion des entités -->
    <div class="nav-left">
      <select onchange="window.location.href=this.value">
        <option value="">👥 Gestion des entités</option>
        <option value="produits.php">📦 Produits</option>
        <option value="familles.php">🧬 Familles</option>
        <option value="clients.php">🧑‍💼 Clients</option>
        <option value="fournisseurs.php">🏢 Fournisseurs</option>
        <option value="utilisateurs.php">🔐 Utilisateurs</option>
      </select>

      <!-- Module 2 : Gestion des commandes -->
      <select onchange="window.location.href=this.value">
        <option value="">📋 Commandes</option>
        <option value="commande_client.php">🛒 Commande Client</option>
        <option value="commande_fournisseur.php">🚚 Commande Fournisseur</option>
        <option value="approvisionnement.php">🔄 Approvisionnement</option>
      </select>

      <!-- Module 3 : Gestion des paiements -->
      <select onchange="window.location.href=this.value">
        <option value="">💳 Paiements</option>
        <option value="paiement_client.php">👛 Paiement Client</option>
        <option value="paiement_fournisseur.php">💼 Paiement Fournisseur</option>
      </select>

      <!-- Module 4 : Documents et états -->
      <select onchange="window.location.href=this.value">
        <option value="">📄 Documents & États</option>
        <option value="bon_commande.php">📝 Bon de commande</option>
        <option value="bon_livraison.php">📦 Bon de livraison</option>
        <option value="bon_reception.php">📥 Bon de réception</option>
        <option value="facture_client.php">🧾 Facture Client</option>
        <option value="facture_fournisseur.php">📃 Facture Fournisseur</option>
      </select>

      <!-- Module 5 : Administration -->
      <select onchange="window.location.href=this.value">
        <option value="">🛠️ Administration</option>
        <option value="roles.php">🔧 Rôles</option>
        <option value="droits.php">🛡️ Droits</option>
        <option value="logs.php">📚 Journalisation</option>
      </select>

      <!-- Module 7 : Reporting et statistiques -->
      <select onchange="window.location.href=this.value">
        <option value="">📈 Reporting</option>
        <option value="ventes_journalieres.php">📉 Ventes journalières</option>
        <option value="stats_produits.php">📊 Produits les plus vendus</option>
        <option value="stats_clients.php">👥 Clients actifs</option>
      </select>
    </div>
</body>
</html>