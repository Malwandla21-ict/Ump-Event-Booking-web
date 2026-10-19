<?php
/** @var array $event */
$capacity = max(1, (int) $event['capacity']);
$taken = (int) $event['seats_taken'];
$left = (int) $event['seats_left'];
$pct = min(100, (int) round($taken / $capacity * 100));
$state = $left === 0 ? 'is-full' : ($left <= max(5, $capacity * 0.15) ? 'is-warning' : '');
?>
<div class="capacity">
  <div class="capacity-label">
    <span><?= $left === 0 ? 'Fully booked' : '<strong>' . number_format($left) . '</strong> of ' . number_format($capacity) . ' seats left' ?></span>
    <span><?= $pct ?>%</span>
  </div>
  <div class="meter <?= $state ?>" role="meter" aria-valuemin="0" aria-valuemax="<?= $capacity ?>" aria-valuenow="<?= $taken ?>" aria-label="Seats booked">
    <span data-fill="<?= $pct ?>"></span>
  </div>
</div>
