<?php /** @var string $name */ ?>
<?php if ($message = error($name)): ?>
  <p class="field-error" id="<?= e($name) ?>-error"><?= icon('alert-circle') ?> <?= e($message) ?></p>
<?php endif; ?>
