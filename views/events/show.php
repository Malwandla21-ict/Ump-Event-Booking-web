<?php
/**
 * @var array $event
 * @var array|null $booking
 * @var int $maxTickets
 */
$start = new DateTimeImmutable($event['starts_at']);
$end = $event['ends_at'] ? new DateTimeImmutable($event['ends_at']) : null;
$isPast = $start <= new DateTimeImmutable();
$isCancelled = $event['status'] === 'cancelled';
$soldOut = (int) $event['seats_left'] === 0;
$user = auth();
?>
<section class="event-hero">
  <?php if ($src = event_image($event['image_path'])): ?>
    <img class="bg" src="<?= e($src) ?>" alt="">
  <?php endif; ?>
  <div class="container">
    <a class="breadcrumb" href="<?= url('/events') ?>"><?= icon('arrow-left') ?> All events</a>
    <div class="row">
      <span class="badge badge-solid"><?= icon(category_icon($event['category'])) ?> <?= e(category_label($event['category'])) ?></span>
      <?php if ($isCancelled): ?><span class="badge badge-danger"><?= icon('ban') ?> Cancelled</span>
      <?php elseif ($isPast): ?><span class="badge badge-solid"><?= icon('history') ?> This event has ended</span>
      <?php elseif ($soldOut): ?><span class="badge badge-danger"><?= icon('ban') ?> Sold out</span><?php endif; ?>
    </div>
    <h1><?= e($event['title']) ?></h1>
    <div class="meta">
      <span><?= icon('calendar') ?> <?= e($start->format('l, j F Y')) ?></span>
      <span><?= icon('clock') ?> <?= e($start->format('H:i')) ?><?= $end ? ' – ' . e($end->format($end->format('Y-m-d') === $start->format('Y-m-d') ? 'H:i' : 'j M, H:i')) : '' ?></span>
      <span><?= icon('map-pin') ?> <?= e($event['venue']) ?></span>
    </div>
  </div>
</section>

<div class="container event-layout">
  <article>
    <h2>About this event</h2>
    <div class="prose"><p><?= e($event['description']) ?></p></div>

    <div class="card card-body mt-lg">
      <h3 class="card-title">Event details</h3>
      <dl class="info-list">
        <div><dt><?= icon('calendar') ?></dt><dd><small>Date</small><?= e($start->format('l, j F Y')) ?></dd></div>
        <div><dt><?= icon('clock') ?></dt><dd><small>Time</small><?= e($start->format('H:i')) ?><?= $end ? ' – ' . e($end->format($end->format('Y-m-d') === $start->format('Y-m-d') ? 'H:i' : 'j M, H:i')) : '' ?> (SAST)</dd></div>
        <div><dt><?= icon('map-pin') ?></dt><dd><small>Venue</small><?= e($event['venue']) ?></dd></div>
        <div><dt><?= icon('users') ?></dt><dd><small>Capacity</small><?= number_format((int) $event['capacity']) ?> seats</dd></div>
      </dl>
    </div>
  </article>

  <aside class="card booking-panel" aria-label="Booking">
    <div class="card-body">
      <div class="price"><strong>Free</strong><span class="muted">entry · registration required</span></div>

      <?php if (!$isPast && !$isCancelled): ?>
        <?= App\Core\View::partial('capacity', ['event' => $event]) ?>
      <?php endif; ?>

      <?php if ($booking): ?>
        <div class="going">
          <strong><?= icon('check-circle') ?> You're going!</strong>
          <span><?= pluralize((int) $booking['quantity'], 'ticket') ?> · Ref <code><?= e($booking['reference']) ?></code></span>
        </div>
        <a class="btn btn-primary btn-block" href="<?= url('/account/bookings/' . $booking['reference']) ?>"><?= icon('ticket') ?> View ticket</a>

      <?php elseif ($isCancelled): ?>
        <div class="alert alert-error"><?= icon('ban') ?><p>This event has been cancelled by the organisers.</p></div>

      <?php elseif ($isPast): ?>
        <div class="alert alert-info"><?= icon('history') ?><p>Bookings are closed — this event has already taken place.</p></div>
        <a class="btn btn-block" href="<?= url('/events') ?>">Find upcoming events</a>

      <?php elseif ($soldOut): ?>
        <div class="alert alert-warning"><?= icon('alert-triangle') ?><p>All seats have been booked. Check back later — seats free up when people cancel.</p></div>

      <?php elseif (!$user): ?>
        <p class="muted m-0">Sign in or create a free account to reserve your seat.</p>
        <a class="btn btn-primary btn-block btn-lg" href="<?= url('/login', ['next' => '/events/' . $event['slug']]) ?>"><?= icon('log-in') ?> Sign in to book</a>
        <a class="btn btn-block" href="<?= url('/register', ['next' => '/events/' . $event['slug']]) ?>">Create account</a>

      <?php else: ?>
        <form method="post" action="<?= url('/events/' . $event['slug'] . '/book') ?>" data-loading-form>
          <?= csrf_field() ?>
          <div class="field">
            <label for="quantity">Number of tickets</label>
            <div class="stepper" data-stepper>
              <button type="button" data-step="-1" aria-label="Fewer tickets"><?= icon('minus') ?></button>
              <input id="quantity" name="quantity" type="number" inputmode="numeric" value="1" min="1" max="<?= $maxTickets ?>" required>
              <button type="button" data-step="1" aria-label="More tickets"><?= icon('plus') ?></button>
            </div>
            <p class="hint">Up to <?= pluralize($maxTickets, 'ticket') ?> per booking.</p>
          </div>
          <div class="form-actions">
            <button class="btn btn-primary btn-lg btn-block" type="submit"><?= icon('ticket') ?> Reserve my seat</button>
          </div>
        </form>
      <?php endif; ?>

      <p class="subtle"><?= icon('shield-check') ?> Free cancellation until the event starts.</p>
    </div>
  </aside>
</div>
