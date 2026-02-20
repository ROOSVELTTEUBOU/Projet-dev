<?php
include('ent_pied/head.php');

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Portail de Connexion</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #007bff, #0056b3);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .portal {
      background-color: #ffffff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      text-align: center;
      width: 400px;
    }

    .portal h2 {
      color: #0056b3;
      margin-bottom: 25px;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 15px;
      margin: 10px 0;
      background-color: #007bff;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    .btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="portal">
    <h2>Bienvenue sur Appecom</h2>
    <a href="fonction/login_client.php" class="btn">Connexion Client</a>
    <a href="fonction/login_admin.php" class="btn">Connexion Administrateur</a>
    <a href="fonction/login_employe.php" class="btn">Connexion Employé</a>
  </div>
</body>
</html>