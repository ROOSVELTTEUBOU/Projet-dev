<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: login_admin.php');
    exit();
}

// Vérification stricte du rôle
// if ($_SESSION['role'] !== 'Administrateur') {
//     header("Location: login_admin.php");
//     exit();
// }
$userName = isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Admin';
// Initiales pour l'avatar
$initials = implode('', array_map(function($n){return strtoupper($n[0]);}, array_slice(explode(' ', $userName),0,2)));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard Administrateur</title>
  <style>
    :root{
      --bg:#f6f9fc;
      --card:#ffffff;
      --muted:#6b7280;
      --primary:#0d6efd;
      --accent:#0ea5a4;
      --glass: rgba(255,255,255,0.6);
      --shadow: 0 6px 18px rgba(13,40,64,0.08);
      --radius:12px;
      --gap:18px;
      font-family: Inter, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:#0b2545}

    /* Top bar */
    .topbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:18px 24px;
      background:linear-gradient(90deg,#0d6efd 0%, #0ea5a4 100%);
      color:#fff
    }
    .brand{
      display:flex;align-items:center;gap:12px}
    .brand .logo{
      width:44px;height:44px;border-radius:10px;
      background:rgba(255,255,255,0.14);
      display:flex;align-items:center;
      justify-content:center;
      font-weight:700}
    .brand h2{
      margin:0;
      font-size:18px}
    .user-area{
      display:flex;
      align-items:center;gap:12px}
    .avatar{
      width:44px;height:44px;border-radius:50%;
      background:var(--card);color:var(--primary);
      display:flex;align-items:center;justify-content:center;
      font-weight:700;
      box-shadow:var(--shadow)}
    .logout-btn{
      background:transparent;
      border:1px solid rgba(255,255,255,0.18);
      color:#fff;padding:8px 12px;
      border-radius:8px;
      text-decoration:none;
      font-weight:600}

    /* Layout */
    .container{
      max-width:1200px;
      margin:24px auto;
      padding:0 18px}
    .header-card{
      background:var(--card);
      padding:18px;
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px}
    .header-card h1{
      margin:0;
      font-size:20px}
    .header-card p{
      margin:0;
      color:var(--muted)}

    /* Stats */
    .grid{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:var(--gap);
      margin-top:18px}
    .card{
      background:var(--card);
      padding:16px;
      border-radius:12px;
      box-shadow:var(--shadow);
      display:flex;
      flex-direction:column;
      gap:8px}
    .card .label{
      color:var(--muted);
      font-size:13px}
    .card .value{
      font-size:20px;
      font-weight:700}
    .card .icon{
      font-size:22px}

    /* Modules grid */
    .modules{
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:var(--gap);
      margin-top:18px}
    .module{
      background:linear-gradient(180deg,var(--glass),var(--card));
      padding:16px;border-radius:12px;
      border:1px solid rgba(13,40,64,0.04);
      text-decoration:none;
      color:inherit;
      display:flex;
      flex-direction:column;gap:8px}
    .module h3{
      margin:0;
      font-size:16px}
    .module p{
      margin:0;
      color:var(--muted);
      font-size:13px}

    footer{
      margin-top:24px;
      text-align:center;
      color:var(--muted);
      font-size:13px}

    /* Responsive */
    @media (max-width:1000px){
      .grid{grid-template-columns:repeat(2,1fr)}
      .modules{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:640px){
      .grid{grid-template-columns:1fr}
      .modules{grid-template-columns:1fr}
      .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:12px}}
  </style>
</head>
<body>
  <div class="topbar">
    <div class="brand">
      <div class="logo">AE</div>
      <div>
        <div style="font-size:13px;opacity:0.95">Appecom</div>
        <div style="font-size:12px;opacity:0.9">Panneau administrateur</div>
      </div>
    </div>
    <div class="user-area">
      <div style="text-align:right">
        <div style="font-weight:700">Bonjour, <?php echo $userName; ?></div>
        <div style="font-size:12px;opacity:0.9">Connecté(e)</div>
      </div>
      <div class="avatar"><?php echo $initials; ?></div>
      <a class="logout-btn" href="logout.php">Se déconnecter</a>
    </div>
  </div>

  <div class="container">
    <div class="header-card">
      <div>
        <h1>Tableau de bord</h1>
        <p>Vue d'ensemble rapide des indicateurs clés</p>
      </div>
      <div style="display:flex;gap:12px;align-items:center">
        <a href="produits.php" class="logout-btn" style="background:var(--primary);border:none;color:#fff">Gérer les produits</a>
        <a href="clients.php" class="logout-btn" style="background:#fff;color:var(--primary);border:1px solid rgba(13,40,64,0.06)">Voir clients</a>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div class="label">Produits</div>
            <div class="value">128</div>
          </div>
          <div class="icon">📦</div>
        </div>
        <div style="color:var(--muted);font-size:13px">Produits actifs en catalogue</div>
      </div>

      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div class="label">Commandes (aujourd'hui)</div>
            <div class="value">24</div>
          </div>
          <div class="icon">🛒</div>
        </div>
        <div style="color:var(--muted);font-size:13px">Commandes reçues aujourd'hui</div>
      </div>

      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div class="label">Clients</div>
            <div class="value">542</div>
          </div>
          <div class="icon">👥</div>
        </div>
        <div style="color:var(--muted);font-size:13px">Clients enregistrés</div>
      </div>

      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div class="label">Chiffre d'affaires (mois)</div>
            <div class="value">12 420 000 FCFA</div>
          </div>
          <div class="icon">💶</div>
        </div>
        <div style="color:var(--muted);font-size:13px">Total des ventes mensuelles</div>
      </div>
    </div>

    <section class="modules">
      <a class="module" href="produits.php">
        <h3>Produits</h3>
        <p>Ajouter, modifier ou supprimer des produits</p>
      </a>
      <a class="module" href="clients.php">
        <h3>Clients</h3>
        <p>Gérer les comptes clients et leurs commandes</p>
      </a>
      <a class="module" href="commande_client.php">
        <h3>Commandes</h3>
        <p>Suivi et validation des commandes clients</p>
      </a>
      <a class="module" href="paiement_client.php">
        <h3>Paiements</h3>
        <p>Validation et historique des paiements</p>
      </a>
      <a class="module" href="facture_client.php">
        <h3>Factures</h3>
        <p>Générer et consulter les factures</p>
      </a>
      <a class="module" href="logs.php">
        <h3>Logs</h3>
        <p>Consulter les journaux et audits</p>
      </a>
    </section>

    <footer>
      © <?php echo date('Y'); ?> Appecom — Tableau de bord administrateur
    </footer>
  </div>
</body>
</html>