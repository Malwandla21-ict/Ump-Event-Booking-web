<?php
/**
 * @var string $icon
 * @var string $title
 * @var string $message
 * @var string|null $actionUrl
 * @var string|null $actionLabel
 */
?>
<div class="empty">
  <div class="empty-icon"><?= icon($icon) ?></div>
  <h3><?= e($title) ?></h3>
  <p><?= e($message) ?></p>
  <?php if (!empty($actionUrl)): ?>
    <a class="btn btn-primary" href="<?= e($actionUrl) ?>"><?= e($actionLabel) ?> <?= icon('arrow-right') ?></a>
  <?php endif; ?>
</div>
