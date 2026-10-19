<?php /** @var array $event */ ?>
<?php if ($src = event_image($event['image_path'] ?? null)): ?>
  <img src="<?= e($src) ?>" alt="" loading="lazy" decoding="async">
<?php else: ?>
  <div class="event-placeholder"><?= icon(category_icon($event['category'] ?? '')) ?></div>
<?php endif; ?>
