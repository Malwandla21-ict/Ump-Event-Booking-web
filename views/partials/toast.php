<?php
use App\Core\Session;

$toast = Session::getFlash('toast');
$icons = ['success' => 'check-circle', 'error' => 'alert-circle', 'info' => 'info'];
?>
<div class="toast-region" aria-live="polite" aria-atomic="true">
  <?php if ($toast): [$type, $message] = $toast; ?>
    <div class="toast toast-<?= e($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>" data-toast>
      <?= icon($icons[$type] ?? 'info') ?>
      <p><?= e($message) ?></p>
      <button type="button" class="toast-close" data-toast-close aria-label="Dismiss"><?= icon('x') ?></button>
    </div>
  <?php endif; ?>
</div>
