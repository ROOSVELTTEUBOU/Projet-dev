<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Gestion utilisateurs';
$activePage = 'utilisateurs';

$notice = '';
$error = '';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

$roleOptions = [];
if ($adminDb instanceof mysqli) {
    $roleOptions = admin_enum_values($adminDb, 'Utilisateur', 'role');
}
if (empty($roleOptions)) {
    $roleOptions = ['Administrateur', 'Employe'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'create') {
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['mot_de_passe'] ?? ''));
        $role = trim((string) ($_POST['role'] ?? ''));

        if ($nom === '' || $email === '' || $password === '' || !admin_in_allowed($role, $roleOptions)) {
            $error = 'Parametres invalides pour creer l utilisateur.';
        } else {
            $stmt = $adminDb->prepare('INSERT INTO Utilisateur (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('ssss', $nom, $email, $password, $role);
                if ($stmt->execute()) {
                    $notice = 'Utilisateur ajoute.';
                    admin_log_action($adminDb, $adminId, 'Ajout utilisateur: ' . $email);
                } else {
                    $error = 'Echec ajout utilisateur: ' . $adminDb->error;
                }
                $stmt->close();
            } else {
                $error = 'Requete creation utilisateur invalide.';
            }
        }
    } elseif ($action === 'update') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['mot_de_passe'] ?? ''));
        $role = trim((string) ($_POST['role'] ?? ''));

        if ($userId <= 0 || $nom === '' || $email === '' || !admin_in_allowed($role, $roleOptions)) {
            $error = 'Parametres invalides pour mise a jour utilisateur.';
        } else {
            if ($password !== '') {
                $stmt = $adminDb->prepare('UPDATE Utilisateur SET nom = ?, email = ?, mot_de_passe = ?, role = ? WHERE id_utilisateur = ?');
                if ($stmt) {
                    $stmt->bind_param('ssssi', $nom, $email, $password, $role, $userId);
                }
            } else {
                $stmt = $adminDb->prepare('UPDATE Utilisateur SET nom = ?, email = ?, role = ? WHERE id_utilisateur = ?');
                if ($stmt) {
                    $stmt->bind_param('sssi', $nom, $email, $role, $userId);
                }
            }

            if (!isset($stmt) || !$stmt) {
                $error = 'Requete mise a jour utilisateur invalide.';
            } else {
                if ($stmt->execute()) {
                    $notice = 'Utilisateur mis a jour.';
                    admin_log_action($adminDb, $adminId, 'Mise a jour utilisateur #' . $userId);
                    $editId = 0;
                } else {
                    $error = 'Echec mise a jour utilisateur: ' . $adminDb->error;
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            $error = 'Utilisateur invalide.';
        } elseif ($userId === $adminId) {
            $error = 'Suppression interdite: vous etes connecte avec ce compte.';
        } else {
            $stmt = $adminDb->prepare('DELETE FROM Utilisateur WHERE id_utilisateur = ?');
            if ($stmt) {
                $stmt->bind_param('i', $userId);
                if ($stmt->execute()) {
                    $notice = 'Utilisateur supprime.';
                    admin_log_action($adminDb, $adminId, 'Suppression utilisateur #' . $userId);
                } else {
                    $error = 'Suppression utilisateur impossible.';
                }
                $stmt->close();
            } else {
                $error = 'Requete suppression utilisateur invalide.';
            }
        }
    }
}

$users = [];
if ($adminDb instanceof mysqli) {
    $result = $adminDb->query('SELECT id_utilisateur, nom, email, role, created_at FROM Utilisateur ORDER BY id_utilisateur DESC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $result->free();
    }
}

$currentUser = null;
if ($editId > 0 && $adminDb instanceof mysqli) {
    $stmt = $adminDb->prepare('SELECT id_utilisateur, nom, email, role FROM Utilisateur WHERE id_utilisateur = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $currentUser = $res->fetch_assoc();
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
  <h2>Gestion des utilisateurs</h2>
  <p class="muted">Gerez les comptes internes (administrateurs et employes).</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Utilisateurs total</div>
      <div class="value"><?php echo count($users); ?></div>
      <div class="sub">Comptes internes</div>
    </article>
    <article class="stat">
      <div class="label">Administrateurs</div>
      <div class="value"><?php echo count(array_filter($users, function ($u) { return strcasecmp((string) $u['role'], 'Administrateur') === 0; })); ?></div>
      <div class="sub">Privileges eleves</div>
    </article>
    <article class="stat">
      <div class="label">Employes</div>
      <div class="value"><?php echo count(array_filter($users, function ($u) { return stripos((string) $u['role'], 'Employe') !== false; })); ?></div>
      <div class="sub">Operations metier</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3><?php echo $currentUser ? 'Modifier un utilisateur' : 'Ajouter un utilisateur'; ?></h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="<?php echo $currentUser ? 'update' : 'create'; ?>">
    <?php if ($currentUser): ?>
      <input type="hidden" name="user_id" value="<?php echo (int) $currentUser['id_utilisateur']; ?>">
    <?php endif; ?>

    <div class="grid-2">
      <div class="field">
        <label>Nom</label>
        <input type="text" name="nom" maxlength="50" required value="<?php echo admin_h($currentUser['nom'] ?? ''); ?>">
      </div>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" maxlength="100" required value="<?php echo admin_h($currentUser['email'] ?? ''); ?>">
      </div>
    </div>

    <div class="grid-2">
      <div class="field">
        <label>Role</label>
        <select name="role" required>
          <?php foreach ($roleOptions as $roleOption): ?>
            <option value="<?php echo admin_h($roleOption); ?>" <?php echo ((string) ($currentUser['role'] ?? '') === (string) $roleOption) ? 'selected' : ''; ?>>
              <?php echo admin_h($roleOption); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Mot de passe <?php echo $currentUser ? '(laisser vide pour ne pas changer)' : ''; ?></label>
        <input type="text" name="mot_de_passe" maxlength="255" <?php echo $currentUser ? '' : 'required'; ?>>
      </div>
    </div>

    <div class="actions-row">
      <button type="submit" class="btn"><?php echo $currentUser ? 'Mettre a jour utilisateur' : 'Ajouter utilisateur'; ?></button>
      <?php if ($currentUser): ?>
        <a href="utilisateurs.php" class="btn btn-secondary">Annuler edition</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h3>Liste des utilisateurs</h3>
  <?php if (!empty($users)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Role</th>
            <th>Creation</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?php echo (int) $user['id_utilisateur']; ?></td>
              <td><?php echo admin_h($user['nom']); ?></td>
              <td><?php echo admin_h($user['email']); ?></td>
              <td><span class="badge info"><?php echo admin_h($user['role']); ?></span></td>
              <td><?php echo admin_h($user['created_at'] ?: '-'); ?></td>
              <td>
                <div class="actions-row">
                  <a class="btn btn-secondary" href="?edit=<?php echo (int) $user['id_utilisateur']; ?>">Modifier</a>
                  <?php if ((int) $user['id_utilisateur'] !== $adminId): ?>
                    <form method="post" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="user_id" value="<?php echo (int) $user['id_utilisateur']; ?>">
                      <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun utilisateur enregistre.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
