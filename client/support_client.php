<?php
require __DIR__ . '/_layout.php';

$pageTitle = 'Support client';
$activePage = 'support';

if (!isset($_SESSION['client_support_tickets']) || !is_array($_SESSION['client_support_tickets'])) {
    $_SESSION['client_support_tickets'] = [];
}

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim((string) ($_POST['category'] ?? 'General'));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $content = trim((string) ($_POST['message'] ?? ''));

    if ($subject === '' || $content === '') {
        $error = 'Veuillez renseigner le sujet et le message.';
    } elseif (strlen($subject) > 120) {
        $error = 'Le sujet ne doit pas depasser 120 caracteres.';
    } else {
        $_SESSION['client_support_tickets'][] = [
            'date' => date('Y-m-d H:i'),
            'category' => $category,
            'subject' => $subject,
            'message' => $content,
            'status' => 'Envoye',
        ];

        if (count($_SESSION['client_support_tickets']) > 20) {
            $_SESSION['client_support_tickets'] = array_slice($_SESSION['client_support_tickets'], -20);
        }

        $notice = 'Votre message a ete envoye au support.';
    }
}

$tickets = array_reverse($_SESSION['client_support_tickets']);

include __DIR__ . '/_header.php';
?>

<?php if ($notice !== ''): ?>
  <div class="alert alert-success"><?php echo client_h($notice); ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <div class="alert alert-error"><?php echo client_h($error); ?></div>
<?php endif; ?>

<section class="panel">
  <h2>Support client</h2>
  <p class="muted">Envoyez une demande, suivez vos derniers messages et utilisez les canaux d'assistance.</p>

  <div class="grid-3" style="margin-top:12px;">
    <article class="stat">
      <div class="label">Demandes envoyees</div>
      <div class="value"><?php echo count($_SESSION['client_support_tickets']); ?></div>
      <div class="sub">Historique local de session</div>
    </article>
    <article class="stat">
      <div class="label">Email support</div>
      <div class="value" style="font-size:16px; margin-top:11px;">support@appecom.local</div>
      <div class="sub">Reponse sous 24h ouvrees</div>
    </article>
    <article class="stat">
      <div class="label">Telephone</div>
      <div class="value" style="font-size:16px; margin-top:11px;">+225 07 00 00 00 00</div>
      <div class="sub">Du lundi au vendredi</div>
    </article>
  </div>
</section>

<section class="panel">
  <h3>Nouvelle demande</h3>
  <form method="post" class="form-grid" style="margin-top:12px;">
    <div class="grid-2">
      <div class="field">
        <label>Categorie</label>
        <select name="category">
          <option value="General">General</option>
          <option value="Commande">Commande</option>
          <option value="Paiement">Paiement</option>
          <option value="Compte">Compte client</option>
          <option value="Technique">Technique</option>
        </select>
      </div>
      <div class="field">
        <label>Sujet</label>
        <input type="text" name="subject" maxlength="120" placeholder="Sujet de la demande" required>
      </div>
    </div>

    <div class="field">
      <label>Message</label>
      <textarea name="message" placeholder="Decrivez clairement votre besoin" required></textarea>
    </div>

    <div class="actions-row">
      <button type="submit" class="btn">Envoyer au support</button>
    </div>
  </form>
</section>

<section class="panel">
  <h3>Mes derniers messages</h3>
  <?php if (!empty($tickets)): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Categorie</th>
            <th>Sujet</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tickets as $ticket): ?>
            <tr>
              <td><?php echo client_h($ticket['date']); ?></td>
              <td><?php echo client_h($ticket['category']); ?></td>
              <td><?php echo client_h($ticket['subject']); ?></td>
              <td><span class="badge info"><?php echo client_h($ticket['status']); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">Aucune demande envoyee pour le moment.</div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_footer.php'; ?>
