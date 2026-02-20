<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Gestion clients';
$activePage = 'clients';

$notice = '';
$error = '';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'create') {
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $ville = trim((string) ($_POST['ville'] ?? ''));
        $sexe = strtoupper(trim((string) ($_POST['sexe'] ?? '')));
        $contact = trim((string) ($_POST['contact'] ?? ''));
        $dateNaissance = trim((string) ($_POST['date_naissance'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['mot_de_passe'] ?? ''));

        if ($nom === '' || $prenom === '' || $email === '') {
            $error = 'Nom, prenom et email sont obligatoires.';
        } else {
            $dateValue = $dateNaissance !== '' ? $dateNaissance : null;
            $sexeValue = in_array($sexe, ['M', 'F'], true) ? $sexe : null;

            $stmt = $adminDb->prepare('INSERT INTO Client (nom, prenom, ville, sexe, contact, date_naissance, email, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('ssssssss', $nom, $prenom, $ville, $sexeValue, $contact, $dateValue, $email, $password);
                if ($stmt->execute()) {
                    $notice = 'Client ajoute avec succes.';
                    admin_log_action($adminDb, $adminId, 'Ajout client: ' . $email);
                } else {
                    $error = 'Echec ajout client: ' . $adminDb->error;
                }
                $stmt->close();
            } else {
                $error = 'Requete creation client invalide.';
            }
        }
    } elseif ($action === 'update') {
        $clientId = (int) ($_POST['client_id'] ?? 0);
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $ville = trim((string) ($_POST['ville'] ?? ''));
        $sexe = strtoupper(trim((string) ($_POST['sexe'] ?? '')));
        $contact = trim((string) ($_POST['contact'] ?? ''));
        $dateNaissance = trim((string) ($_POST['date_naissance'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['mot_de_passe'] ?? ''));

        if ($clientId <= 0 || $nom === '' || $prenom === '' || $email === '') {
            $error = 'Parametres de mise a jour invalides.';
        } else {
            $dateValue = $dateNaissance !== '' ? $dateNaissance : null;
            $sexeValue = in_array($sexe, ['M', 'F'], true) ? $sexe : null;

            if ($password !== '') {
                $stmt = $adminDb->prepare('UPDATE Client SET nom = ?, prenom = ?, ville = ?, sexe = ?, contact = ?, date_naissance = ?, email = ?, mot_de_passe = ? WHERE id_client = ?');
                if ($stmt) {
                    $stmt->bind_param('ssssssssi', $nom, $prenom, $ville, $sexeValue, $contact, $dateValue, $email, $password, $clientId);
                }
            } else {
                $stmt = $adminDb->prepare('UPDATE Client SET nom = ?, prenom = ?, ville = ?, sexe = ?, contact = ?, date_naissance = ?, email = ? WHERE id_client = ?');
                if ($stmt) {
                    $stmt->bind_param('sssssssi', $nom, $prenom, $ville, $sexeValue, $contact, $dateValue, $email, $clientId);
                }
            }

            if (!isset($stmt) || !$stmt) {
                $error = 'Requete mise a jour client invalide.';
            } else {
                if ($stmt->execute()) {
                    $notice = 'Client mis a jour.';
                    admin_log_action($adminDb, $adminId, 'Mise a jour client #' . $clientId);
                    $editId = 0;
                } else {
                    $error = 'Echec mise a jour client: ' . $adminDb->error;
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $clientId = (int) ($_POST['client_id'] ?? 0);

        if ($clientId <= 0) {
            $error = 'Client invalide.';
        } else {
            $stmt = $adminDb->prepare('DELETE FROM Client WHERE id_client = ?');
            if ($stmt) {
                $stmt->bind_param('i', $clientId);
                if ($stmt->execute()) {
                    $notice = 'Client supprime.';
                    admin_log_action($adminDb, $adminId, 'Suppression client #' . $clientId);
                } else {
                    $error = 'Suppression client impossible.';
                }
                $stmt->close();
            } else {
                $error = 'Requete suppression client invalide.';
            }
        }
    }
}

$clients = [];
if ($adminDb instanceof mysqli) {
    $result = $adminDb->query('SELECT id_client, nom, prenom, ville, sexe, contact, date_naissance, email FROM Client ORDER BY id_client DESC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
        $result->free();
    }
}

$currentClient = null;
if ($editId > 0 && $adminDb instanceof mysqli) {
    $stmt = $adminDb->prepare('SELECT id_client, nom, prenom, ville, sexe, contact, date_naissance, email FROM Client WHERE id_client = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $currentClient = $res->fetch_assoc();
            }
        }
        $stmt->close();
    }
}

include __DIR__ . '/_header.php';
?>

<?php if ($notice !== ''): ?>
  <div class="alert alert-success"><?php echo admin_h($notice); ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert alert-error"><?php echo admin_h($error); ?></div>
<?php endif; ?>

<section class="panel">
  <h2>Gestion des clients</h2>
  <p class="muted">Administrez les comptes clients et leurs informations de contact.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Clients total</div>
      <div class="value"><?php echo count($clients); ?></div>
      <div class="sub">Comptes en base</div>
    </article>
    <article class="stat">
      <div class="label">Villes couvertes</div>
      <div class="value"><?php echo count(array_unique(array_filter(array_map(function ($c) { return (string) $c['ville']; }, $clients)))); ?></div>
      <div class="sub">Diversite geographique</div>
    </article>
    <article class="stat">
      <div class="label">Formulaire</div>
      <div class="value" style="font-size:16px; margin-top:11px;"><?php echo $currentClient ? 'Edition' : 'Creation'; ?></div>
      <div class="sub">Mode actif</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3><?php echo $currentClient ? 'Modifier un client' : 'Ajouter un client'; ?></h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="<?php echo $currentClient ? 'update' : 'create'; ?>">
    <?php if ($currentClient): ?>
      <input type="hidden" name="client_id" value="<?php echo (int) $currentClient['id_client']; ?>">
    <?php endif; ?>

    <div class="grid-3">
      <div class="field">
        <label>Nom</label>
        <input type="text" name="nom" maxlength="50" required value="<?php echo admin_h($currentClient['nom'] ?? ''); ?>">
      </div>
      <div class="field">
        <label>Prenom</label>
        <input type="text" name="prenom" maxlength="50" required value="<?php echo admin_h($currentClient['prenom'] ?? ''); ?>">
      </div>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" maxlength="100" required value="<?php echo admin_h($currentClient['email'] ?? ''); ?>">
      </div>
    </div>

    <div class="grid-3">
      <div class="field">
        <label>Ville</label>
        <input type="text" name="ville" maxlength="50" value="<?php echo admin_h($currentClient['ville'] ?? ''); ?>">
      </div>
      <div class="field">
        <label>Contact</label>
        <input type="text" name="contact" maxlength="20" value="<?php echo admin_h($currentClient['contact'] ?? ''); ?>">
      </div>
      <div class="field">
        <label>Date naissance</label>
        <input type="date" name="date_naissance" value="<?php echo admin_h($currentClient['date_naissance'] ?? ''); ?>">
      </div>
    </div>

    <div class="grid-3">
      <div class="field">
        <label>Sexe</label>
        <select name="sexe">
          <option value="">Non renseigne</option>
          <option value="M" <?php echo (($currentClient['sexe'] ?? '') === 'M') ? 'selected' : ''; ?>>M</option>
          <option value="F" <?php echo (($currentClient['sexe'] ?? '') === 'F') ? 'selected' : ''; ?>>F</option>
        </select>
      </div>
      <div class="field">
        <label>Mot de passe <?php echo $currentClient ? '(laisser vide pour ne pas changer)' : ''; ?></label>
        <input type="text" name="mot_de_passe" maxlength="255" <?php echo $currentClient ? '' : 'required'; ?>>
      </div>
      <div class="field" style="align-self:end;">
        <div class="actions-row">
          <button type="submit" class="btn"><?php echo $currentClient ? 'Mettre a jour client' : 'Ajouter client'; ?></button>
          <?php if ($currentClient): ?>
            <a href="clients.php" class="btn btn-secondary">Annuler edition</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </form>
</section>

<section class="panel">
  <h3>Liste des clients</h3>
  <?php if (!empty($clients)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Ville</th>
            <th>Contact</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clients as $client): ?>
            <tr>
              <td><?php echo (int) $client['id_client']; ?></td>
              <td><?php echo admin_h(trim(((string) $client['prenom']) . ' ' . ((string) $client['nom']))); ?></td>
              <td><?php echo admin_h($client['email']); ?></td>
              <td><?php echo admin_h($client['ville'] ?: '-'); ?></td>
              <td><?php echo admin_h($client['contact'] ?: '-'); ?></td>
              <td>
                <div class="actions-row">
                  <a class="btn btn-secondary" href="?edit=<?php echo (int) $client['id_client']; ?>">Modifier</a>
                  <form method="post" onsubmit="return confirm('Supprimer ce client ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="client_id" value="<?php echo (int) $client['id_client']; ?>">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun client enregistre.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
