<?php
session_start();

// Vider les donnees de session
$_SESSION = [];

// Supprimer le cookie de session si present
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Detruire la session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: /site_e-com/index.php');
exit;
?>

