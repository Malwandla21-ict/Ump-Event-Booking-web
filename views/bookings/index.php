<?php
/**
 * @var string $tab
 * @var array $bookings
 * @var array $counts
 */
$tabs = [
    'upcoming' => ['Upcoming', 'calendar'],
    'past' => ['Past', 'history'],
    'cancelled' => ['Cancelled', 'ban'],
];
?>
<div class="container">
  <header class="page-head row">
    <div>
      <h1>My bookings</h1>
      <p>Your tickets, all in one place.</p>
    </div>
    <span class="spacer"></span>
    <a class="btn btn-primary" href="<?= url('/events') ?>"><?= icon('search') ?> Find events</a>
  </header>

  <nav class="tabs" aria-label="Booking filters">
    <?php foreach ($tabs as $key => [$label, $iconName]): ?>
      <a class="tab <?= $tab === $key ? 'is-active' : '' ?>" href="<?= url('/account/bookings', ['tab' => $key === 'upcoming' ? null : $key]) ?>" <?= $tab === $key ? 'aria-current="page"' : '' ?>>
        <?= icon($iconName) ?> <?= $label ?> <span class="count"><?= $counts[$key] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <?php if (!$bookings): ?>
    <?= App\Core\View::partial('empty-state', match ($tab) {
        'upcoming' => ['icon' => 'ticket', 'title' => 'No upcoming bookings', 'message' => 'When you book an event, your ticket will show up here.', 'actionUrl' => url('/events'), 'actionLabel' => 'Browse events'],
        'past' => ['icon' => 'history', 'title' => 'No past events yet', 'message' => 'Events you attended will be listed here.'],
        'cancelled' => ['icon' => 'ban', 'title' => 'No cancelled bookings', 'message' => 'Bookings you cancel will be listed here.'],
    }) ?>
  <?php else: ?>
    <div class="booking-list">
      <?php foreach ($bookings as $b): $start = new DateTimeImmutable($b['starts_at']); ?>
        <article class="card booking-item">
          <div class="thumb"><?= App\Core\View::partial('event-media', ['event' => $b]) ?></div>
          <div class="info">
            <div class="row">
              <span class="ref"><?= e($b['reference']) ?></span>
              <?php if ($b['status'] === 'cancelled'): ?>
                <span class="badge badge-danger">Cancelled</span>
              <?php elseif ($b['event_status'] === 'cancelled'): ?>
                <span class="badge badge-danger">Event cancelled</span>
              <?php elseif ($tab === 'past'): ?>
                <span class="badge">Attended</span>
              <?php else: ?>
                <span class="badge badge-success"><?= icon('check') ?> Confirmed</span>
              <?php endif; ?>
            </div>
            <h3><a href="<?= url('/events/' . $b['slug']) ?>"><?= e($b['title']) ?></a></h3>
            <div class="meta">
              <span><?= icon('calendar') ?> <?= e($start->format('D, j M Y · H:i')) ?></span>
              <span><?= icon('map-pin') ?> <?= e($b['venue']) ?></span>
              <span><?= icon('ticket') ?> <?= pluralize((int) $b['quantity'], 'ticket') ?></span>
            </div>
          </div>
          <div class="actions">
            <a class="btn btn-sm" href="<?= url('/account/bookings/' . $b['reference']) ?>"><?= icon('ticket') ?> View ticket</a>
            <?php if ($tab === 'upcoming'): ?>
              <form method="post" action="<?= url('/account/bookings/' . $b['reference'] . '/cancel') ?>"
                    data-confirm="Cancel your booking for “<?= e($b['title']) ?>”? Your seats will be released to other students."
                    data-confirm-title="Cancel booking?" data-confirm-ok="Yes, cancel">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-danger-ghost btn-block" type="submit"><?= icon('x') ?> Cancel</button>
              </form>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
