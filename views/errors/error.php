<?php
/**
 * @var int $status
 * @var string $title
 * @var string $message
 * @var Throwable|null $exception  only set when APP_DEBUG=true
 */
?>
<section class="error-page">
  <div>
    <div class="error-code"><?= (int) $status ?></div>
    <h1><?= e($title) ?></h1>
    <p><?= e($message) ?></p>
    <div class="form-actions justify-center">
      <a class="btn btn-primary" href="<?= url('/') ?>"><?= icon('home') ?> Go home</a>
      <a class="btn" href="<?= url('/events') ?>"><?= icon('calendar') ?> Browse events</a>
    </div>
    <?php if ($exception): ?>
      <pre class="error-trace"><strong><?= e($exception::class) ?>: <?= e($exception->getMessage()) ?></strong>

<?= e($exception->getFile()) ?>:<?= (int) $exception->getLine() ?>

<?= e($exception->getTraceAsString()) ?></pre>
    <?php endif; ?>
  </div>
</section>
