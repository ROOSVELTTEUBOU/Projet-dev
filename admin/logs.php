<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Journal des logs';
$activePage = 'logs';

$notice = '';
$error = '';
$search = trim((string) ($_GET['q'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!($adminDb instanceof mysqli)) {
        $error = 'Base de donnees indisponible.';
    } elseif ($action === 'add_log') {
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($message === '') {
            $error = 'Le message de log est obligatoire.';
        } else {
            admin_log_action($adminDb, $adminId, $message);
            $notice = 'Log enregistre avec succes.';
        }
    }
}

$logs = [];
if ($adminDb instanceof mysqli) {
    if ($search !== '') {
        $stmt = $adminDb->prepare('SELECT l.id_log, l.date_action, l.action, u.nom AS user_nom FROM Logs l LEFT JOIN Utilisateur u ON u.id_utilisateur = l.id_utilisateur WHERE l.action LIKE ? OR u.nom LIKE ? ORDER BY l.date_action DESC LIMIT 200');
        if ($stmt) {
            $pattern = '%' . $search . '%';
            $stmt->bind_param('ss', $pattern, $pattern);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                while ($res && $row = $res->fetch_assoc()) {
                    $logs[] = $row;
                }
            }
            $stmt->close();
        }
    } else {
        $res = $adminDb->query('SELECT l.id_log, l.date_action, l.action, u.nom AS user_nom FROM Logs l LEFT JOIN Utilisateur u ON u.id_utilisateur = l.id_utilisateur ORDER BY l.date_action DESC LIMIT 200');
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $logs[] = $row;
            }
            $res->free();
        }
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
  <h2>Journal des logs</h2>
  <p class="muted">Consultez les actions recentes et ajoutez des entrees de suivi administrateur.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Logs affiches</div>
      <div class="value"><?php echo count($logs); ?></div>
      <div class="sub">Maximum 200 lignes</div>
    </article>
    <article class="stat">
      <div class="label">Recherche active</div>
      <div class="value" style="font-size:16px; margin-top:11px;"><?php echo $search !== '' ? admin_h($search) : 'Aucune'; ?></div>
      <div class="sub">Filtre sur action/utilisateur</div>
    </article>
    <article class="stat">
      <div class="label">Lien rapide</div>
      <div class="value" style="font-size:16px; margin-top:11px;"><a href="../fonction_interne/dashboard_admin.php" class="btn btn-secondary">Retour dashboard</a></div>
      <div class="sub">Vue administrateur</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Ajouter un log manuel</h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="add_log">

    <div class="field">
      <label>Message</label>
      <textarea name="message" maxlength="255" placeholder="Exemple: Validation complete des paiements du jour" required></textarea>
    </div>

    <button type="submit" class="btn">Enregistrer log</button>
  </form>
</section>

<section class="panel">
  <h3>Rechercher dans les logs</h3>
  <form method="get" class="actions-row" style="margin-top:12px;">
    <input type="text" name="q" value="<?php echo admin_h($search); ?>" placeholder="Rechercher un mot-cle" style="min-width:260px;">
    <button type="submit" class="btn btn-secondary">Rechercher</button>
    <?php if ($search !== ''): ?>
      <a href="logs.php" class="btn btn-secondary">Reinitialiser</a>
    <?php endif; ?>
  </form>
</section>

<section class="panel">
  <h3>Historique des logs</h3>
  <?php if (!empty($logs)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td><?php echo (int) $log['id_log']; ?></td>
              <td><?php echo admin_h($log['date_action']); ?></td>
              <td><span class="badge info"><?php echo admin_h($log['user_nom'] ?: 'Systeme'); ?></span></td>
              <td><?php echo admin_h($log['action']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucun log trouve.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
