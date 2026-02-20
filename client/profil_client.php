<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Mon profil';
$activePage = 'profil';

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newVille = trim((string) ($_POST['ville'] ?? ''));
    $newContact = trim((string) ($_POST['contact'] ?? ''));

    if (strlen($newVille) > 50) {
        $error = 'La ville ne doit pas depasser 50 caracteres.';
    } elseif (strlen($newContact) > 20) {
        $error = 'Le contact ne doit pas depasser 20 caracteres.';
    } elseif (!($clientDb instanceof mysqli)) {
        $error = 'Mise a jour impossible: base indisponible.';
    } else {
        $stmt = $clientDb->prepare('UPDATE Client SET ville = ?, contact = ? WHERE id_client = ?');
        if ($stmt) {
            $stmt->bind_param('ssi', $newVille, $newContact, $clientId);
            if ($stmt->execute()) {
                $notice = 'Profil mis a jour avec succes.';
            } else {
                $error = 'Echec de la mise a jour du profil.';
            }
            $stmt->close();
        } else {
            $error = 'Requete de mise a jour invalide.';
        }
    }
}

$profile = [
    'nom' => '-',
    'prenom' => '-',
    'email' => '-',
    'ville' => '',
    'contact' => '',
    'sexe' => '-',
    'date_naissance' => '-',
];

if ($clientDb instanceof mysqli) {
    $stmt = $clientDb->prepare('SELECT nom, prenom, email, ville, contact, sexe, date_naissance FROM Client WHERE id_client = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $clientId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $profile['nom'] = $row['nom'] ?: '-';
                $profile['prenom'] = $row['prenom'] ?: '-';
                $profile['email'] = $row['email'] ?: '-';
                $profile['ville'] = $row['ville'] ?: '';
                $profile['contact'] = $row['contact'] ?: '';
                $profile['sexe'] = $row['sexe'] ?: '-';
                $profile['date_naissance'] = $row['date_naissance'] ?: '-';
            }
        }
        $stmt->close();
    }
}

include __DIR__ . '/_header.php';
?>

<?php if ($notice !== ''): ?>
  <div class="alert alert-success"><?php echo client_h($notice); ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert alert-error"><?php echo client_h($error); ?></div>
<?php endif; ?>

<section class="panel">
  <h2>Mon profil</h2>
  <p class="muted">Consultez vos informations et mettez a jour les champs autorises.</p>

  <form method="post" class="form-grid" style="margin-top:12px;">
    <div class="grid-2">
      <div class="field">
        <label>Nom</label>
        <input type="text" value="<?php echo client_h($profile['nom']); ?>" readonly>
      </div>
      <div class="field">
        <label>Prenom</label>
        <input type="text" value="<?php echo client_h($profile['prenom']); ?>" readonly>
      </div>
    </div>

    <div class="grid-2">
      <div class="field">
        <label>Email</label>
        <input type="email" value="<?php echo client_h($profile['email']); ?>" readonly>
      </div>
      <div class="field">
        <label>Date de naissance</label>
        <input type="text" value="<?php echo client_h($profile['date_naissance']); ?>" readonly>
      </div>
    </div>

    <div class="grid-2">
      <div class="field">
        <label>Sexe</label>
        <input type="text" value="<?php echo client_h($profile['sexe']); ?>" readonly>
      </div>
      <div class="field">
        <label>Contact</label>
        <input type="text" name="contact" maxlength="20" value="<?php echo client_h($profile['contact']); ?>" placeholder="Numero de contact">
      </div>
    </div>

    <div class="field">
      <label>Ville</label>
      <input type="text" name="ville" maxlength="50" value="<?php echo client_h($profile['ville']); ?>" placeholder="Votre ville actuelle">
    </div>

    <div class="actions-row">
      <button type="submit" class="btn">Enregistrer les modifications</button>
      <a href="support_client.php" class="btn btn-secondary">Contacter le support</a>
    </div>
  </form>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
