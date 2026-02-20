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
    body {
        font-family: Arial, sans-serif; 
        margin:0; 
        background:#f0f8ff; 
        color:#003366;
    }
    nav {
        background-color: #007bff;
        padding: 12px 20px;
        display: flex;
        justify-content: space-between; /* Gauche = modules, Droite = logout */
        align-items: center;
        flex-wrap: wrap; /* Permet de passer en ligne suivante si écran petit */
    }

    nav select {
        background-color: #0056b3;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 8px 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        margin-right: 15px;
        width: 180px;
        text-align: center;
    }
    nav select option {
        background-color:rgb(114, 167, 247);   /* Fond blanc pour les options */
        color: #000000;
    }
    .logout-btn {
        background-color: #dc3545;
        color: #ffffff;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .logout-btn:hover {
        background-color: #c82333;
        transform: scale(1.05);
    }
    header {
      background:#0056b3;
      color:#fff;
      padding:15px;
      text-align:center;
      border-radius:8px;
    }
  </style>
</head>
<body>
  <header>
    <h1>Bienvenue Administrateur <?php echo $_SESSION['user']; ?> ⚡</h1>
    <p>Vous avez un accès complet au système Appecom</p>
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
    <div class="nav-right">
        <a href="logout.php" class="logout-btn">🚪 Logout</a>
    </div>
</body>
</html>