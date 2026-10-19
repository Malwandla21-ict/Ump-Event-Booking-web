<?php
/** @var array $event */
$start = new DateTimeImmutable($event['starts_at']);
$isPast = $start < new DateTimeImmutable();
$left = (int) $event['seats_left'];
?>
<article class="event-card">
  <div class="event-media">
    <?= App\Core\View::partial('event-media', ['event' => $event]) ?>
    <div class="date-badge" aria-hidden="true">
      <small><?= $start->format('M') ?></small>
      <b><?= $start->format('j') ?></b>
    </div>
    <div class="media-tags">
      <?php if ($event['status'] === 'cancelled'): ?>
        <span class="badge badge-danger"><?= icon('ban') ?> Cancelled</span>
      <?php elseif ($isPast): ?>
        <span class="badge badge-solid"><?= icon('history') ?> Ended</span>
      <?php elseif ($left === 0): ?>
        <span class="badge badge-danger"><?= icon('ban') ?> Sold out</span>
      <?php elseif ($left <= max(5, $event['capacity'] * 0.15)): ?>
        <span class="badge badge-warning"><?= icon('alert-triangle') ?> Few seats left</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="event-body">
    <span class="badge badge-info"><?= icon(category_icon($event['category'])) ?> <?= e(category_label($event['category'])) ?></span>
    <h3><a href="<?= url('/events/' . $event['slug']) ?>"><?= e($event['title']) ?></a></h3>
    <div class="meta">
      <span><?= icon('clock') ?> <?= e($start->format('D, j M · H:i')) ?></span>
      <span><?= icon('map-pin') ?> <?= e($event['venue']) ?></span>
    </div>
    <?php if (!$isPast && $event['status'] !== 'cancelled'): ?>
      <div class="event-foot"><?= App\Core\View::partial('capacity', ['event' => $event]) ?></div>
    <?php endif; ?>
  </div>
</article>
