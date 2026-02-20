<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../fonction/login_employe.php');
    exit();
}

$employeId = (int) $_SESSION['id_utilisateur'];
$employeName = isset($_SESSION['user']) ? trim((string) $_SESSION['user']) : 'Employe';
if ($employeName === '') {
    $employeName = 'Employe';
}

$parts = preg_split('/\s+/', $employeName);
$initials = '';
if (is_array($parts)) {
    foreach (array_slice($parts, 0, 2) as $part) {
        if ($part !== '') {
            $initials .= strtoupper(substr($part, 0, 1));
        }
    }
}
if ($initials === '') {
    $initials = 'EM';
}

$employeDb = null;
$employeDbError = null;
$dbPath = __DIR__ . '/../connexion_bd/connexion_bd.php';

if (file_exists($dbPath)) {
    require $dbPath;
    if (isset($conn) && $conn instanceof mysqli) {
        $employeDb = $conn;
    } else {
        $employeDbError = 'Connexion base indisponible.';
    }
} else {
    $employeDbError = 'Fichier de connexion base introuvable.';
}

$employeNav = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '../fonction_interne/dashboard_employe.php'],
    ['key' => 'stock', 'label' => 'Stock', 'href' => '../employe/stock.php'],
    ['key' => 'commandes_clients', 'label' => 'Commandes clients', 'href' => '../employe/commandes_clients.php'],
    ['key' => 'commandes_fournisseurs', 'label' => 'Commandes fournisseurs', 'href' => '../employe/commandes_fournisseurs.php'],
    ['key' => 'paiements', 'label' => 'Paiements', 'href' => '../employe/paiements.php'],
];

function employe_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function employe_status_class($value)
{
    $status = strtolower(trim((string) $value));

    if ($status === '') {
        return 'badge info';
    }

    if (strpos($status, 'valid') === 0 || strpos($status, 'liv') === 0 || strpos($status, 'rec') === 0 || strpos($status, 'pay') === 0) {
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

function employe_status_text($value)
{
    $status = trim((string) $value);
    return $status === '' ? '-' : $status;
}

function employe_enum_values(mysqli $db, string $table, string $column): array
{
    $tableSafe = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    $columnSafe = preg_replace('/[^A-Za-z0-9_]/', '', $column);

    if ($tableSafe === '' || $columnSafe === '') {
        return [];
    }

    $sql = "SHOW COLUMNS FROM `$tableSafe` LIKE ?";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('s', $columnSafe);
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return [];
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row || !isset($row['Type'])) {
        return [];
    }

    $type = (string) $row['Type'];
    if (strpos($type, 'enum(') !== 0) {
        return [];
    }

    preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $type, $matches);
    if (empty($matches[1])) {
        return [];
    }

    return array_map(static function ($raw) {
        return stripcslashes((string) $raw);
    }, $matches[1]);
}

function employe_status_in_allowed(string $value, array $allowed): bool
{
    foreach ($allowed as $item) {
        if ((string) $item === $value) {
            return true;
        }
    }

    return false;
}

