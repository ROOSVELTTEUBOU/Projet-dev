<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['id_client'])) {
    header('Location: ../fonction/login_client.php');
    exit();
}

$clientId = (int) $_SESSION['id_client'];
$prenom = isset($_SESSION['prenom']) ? trim((string) $_SESSION['prenom']) : '';
$nom = isset($_SESSION['nom']) ? trim((string) $_SESSION['nom']) : '';
$clientName = trim($prenom . ' ' . $nom);
if ($clientName === '') {
    $clientName = 'Client';
}

$clientInitial = strtoupper(substr($clientName, 0, 1));

$clientDb = null;
$clientDbError = null;
$dbPath = __DIR__ . '/../connexion_bd/connexion_bd.php';

if (file_exists($dbPath)) {
    require $dbPath;
    if (isset($conn) && $conn instanceof mysqli) {
        $clientDb = $conn;
    } else {
        $clientDbError = 'Connexion base indisponible.';
    }
} else {
    $clientDbError = 'Fichier de connexion base introuvable.';
}

$clientNav = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '../fonction_interne/dashboard_client.php'],
    ['key' => 'catalogue', 'label' => 'Catalogue', 'href' => 'catalogue.php'],
    ['key' => 'panier', 'label' => 'Panier', 'href' => 'panier.php'],
    ['key' => 'commandes', 'label' => 'Commandes', 'href' => 'commande_client.php'],
    ['key' => 'paiements', 'label' => 'Paiements', 'href' => 'paiement_client.php'],
    ['key' => 'factures', 'label' => 'Factures', 'href' => 'facture_client.php'],
    ['key' => 'profil', 'label' => 'Profil', 'href' => 'profil_client.php'],
    ['key' => 'support', 'label' => 'Support', 'href' => 'support_client.php'],
];

function client_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function client_status_class($value)
{
    $status = strtolower(trim((string) $value));
    if ($status === '') {
        return 'badge info';
    }

    if (strpos($status, 'liv') === 0 || strpos($status, 'pay') === 0 || strpos($status, 'valid') === 0) {
        return 'badge success';
    }

    if (strpos($status, 'attente') !== false) {
        return 'badge warning';
    }

    if (strpos($status, 'annul') === 0 || strpos($status, 'rej') === 0) {
        return 'badge danger';
    }

    return 'badge info';
}

function client_status_short($value)
{
    $status = trim((string) $value);
    return $status === '' ? '-' : $status;
}
?>
