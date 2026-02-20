<?php
session_start();
//include('connexion_bd/connexion_bd.php'); // Connexion à la BD
$host = "localhost";
$user = "root";
$pass = "";
$db = "appecom";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

$error="";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    // Vérification dans la table Client
    if (empty($email) || empty($pass)) {
        $error = "Veuillez remplir tous les champs ❌";
    } else {
        // Préparer la requête SQL sécurisée
        $sql = "SELECT id_client, nom, prenom, mot_de_passe FROM client WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // Vérification du mot de passe
                if ($pass === $row['mot_de_passe']) {
                    // Stockage en session
                    $_SESSION['id_client'] = $row['id_client'];
                    $_SESSION['prenom'] = htmlspecialchars($row['prenom']);
                    $_SESSION['nom'] = htmlspecialchars($row['nom']); // Protection contre XSS

                    // Redirection vers le dashboard client
                    header("Location: ../fonction_interne/dashboard_client.php");
                    exit();
                } else {
                    $error = "❌password_error or email_error";
                }
            } else {
                $error = "❌Connection_error";
            }

            $stmt->close();
        } else {
            $error = "❌Erreur lors de la préparation de la requête";
        }
    }
}
$conn->close();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion Client</title>
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

    .login-container {
      background-color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      width: 350px;
    }

    .login-container h2 {
      text-align: center;
      color: #0056b3;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
      color: #0056b3;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #007bff;
      border-radius: 5px;
      outline: none;
    }

    .form-group input:focus {
      border-color: #0056b3;
      box-shadow: 0 0 5px rgba(0,91,187,0.5);
    }

    .btn {
      width: 100%;
      padding: 12px;
      background-color: #007bff;
      border: none;
      border-radius: 5px;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    .message {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Connexion Client</h2>
    <form action="login_client.php" method="POST">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div class="form-group">
        <label>Mot de passe</label>
        <input type="password" name="password" required>
      </div>
      <?php if (!empty($error)): ?>
        <div class="message">
            <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      <button type="submit" class="btn">Se connecter</button>
    </form>
  </div>
</body>
</html>