<?php
/**
 * @var array $booking
 * @var array $user
 */
$start = new DateTimeImmutable($booking['starts_at']);
$end = $booking['ends_at'] ? new DateTimeImmutable($booking['ends_at']) : null;
$cancelled = $booking['status'] === 'cancelled';
$isUpcoming = !$cancelled && $start > new DateTimeImmutable();
?>
<div class="container">
  <header class="page-head no-print">
    <a class="breadcrumb" href="<?= url('/account/bookings') ?>"><?= icon('arrow-left') ?> My bookings</a>
  </header>

  <?php if ($booking['event_status'] === 'cancelled' && !$cancelled): ?>
    <div class="alert alert-error no-print mb-lg"><?= icon('alert-triangle') ?><p>The organisers have cancelled this event. Sorry about that!</p></div>
  <?php endif; ?>

  <article class="ticket <?= $cancelled ? 'is-cancelled' : '' ?>">
    <div class="ticket-main">
      <div class="ticket-head">
        <img src="<?= asset('assets/img/logo-wordmark.png') ?>" alt="University of Mpumalanga">
        <?php if ($cancelled): ?>
          <span class="badge badge-danger"><?= icon('ban') ?> Cancelled</span>
        <?php else: ?>
          <span class="badge badge-success"><?= icon('check') ?> Confirmed</span>
        <?php endif; ?>
      </div>
      <div>
        <span class="badge badge-info"><?= icon(category_icon($booking['category'])) ?> <?= e(category_label($booking['category'])) ?></span>
        <h2 class="mt-sm"><?= e($booking['title']) ?></h2>
      </div>
      <div class="ticket-grid">
        <div><small>Date</small><strong><?= e($start->format('D, j M Y')) ?></strong></div>
        <div><small>Time</small><strong><?= e($start->format('H:i')) ?><?= $end ? ' – ' . e($end->format($end->format('Y-m-d') === $start->format('Y-m-d') ? 'H:i' : 'j M, H:i')) : '' ?></strong></div>
        <div><small>Venue</small><strong><?= e($booking['venue']) ?></strong></div>
        <div><small>Tickets</small><strong><?= pluralize((int) $booking['quantity'], 'admission') ?></strong></div>
        <div><small>Name</small><strong><?= e($user['full_name']) ?></strong></div>
        <div><small>Booked</small><strong><?= e(format_date($booking['created_at'], 'j M Y, H:i')) ?></strong></div>
      </div>
    </div>
    <div class="ticket-stub">
      <small class="subtle">Booking reference</small>
      <span class="ticket-code"><?= e($booking['reference']) ?></span>
      <div class="barcode" aria-hidden="true"></div>
      <small class="subtle">Show this at the entrance</small>
    </div>
  </article>

  <div class="form-actions no-print ticket-actions">
    <button class="btn" type="button" data-print><?= icon('printer') ?> Print ticket</button>
    <a class="btn" href="<?= url('/events/' . $booking['slug']) ?>"><?= icon('external-link') ?> Event page</a>
    <?php if ($isUpcoming): ?>
      <form method="post" action="<?= url('/account/bookings/' . $booking['reference'] . '/cancel') ?>"
            data-confirm="Cancel your booking for “<?= e($booking['title']) ?>”? Your seats will be released to other students."
            data-confirm-title="Cancel booking?" data-confirm-ok="Yes, cancel">
        <?= csrf_field() ?>
        <button class="btn btn-danger-ghost" type="submit"><?= icon('x') ?> Cancel booking</button>
      </form>
    <?php endif; ?>
  </div>
</div>
