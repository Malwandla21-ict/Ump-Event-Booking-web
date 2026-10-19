<?php
/**
 * @var array $stats
 * @var int $students
 * @var int $ticketsSold
 * @var array $upcoming
 * @var array $recent
 */
$fillRate = $stats['seats'] > 0 ? (int) round($stats['booked'] / $stats['seats'] * 100) : 0;
?>
<div class="row">
  <div>
    <h2 class="m-0">Good <?= (int) date('G') < 12 ? 'morning' : ((int) date('G') < 18 ? 'afternoon' : 'evening') ?>, <?= e(explode(' ', auth()['full_name'])[0]) ?></h2>
    <p class="muted m-0">Here's what's happening with your events.</p>
  </div>
  <span class="spacer"></span>
  <a class="btn btn-primary" href="<?= url('/admin/events/create') ?>"><?= icon('plus') ?> New event</a>
</div>

<div class="stats">
  <div class="card stat">
    <div class="stat-top">Upcoming events <span class="stat-icon"><?= icon('calendar') ?></span></div>
    <strong><?= number_format($stats['upcoming']) ?></strong>
    <small><?= number_format($stats['total']) ?> events in total</small>
  </div>
  <div class="card stat">
    <div class="stat-top">Tickets booked <span class="stat-icon"><?= icon('ticket') ?></span></div>
    <strong><?= number_format($ticketsSold) ?></strong>
    <small>Across all events</small>
  </div>
  <div class="card stat">
    <div class="stat-top">Fill rate <span class="stat-icon"><?= icon('bar-chart') ?></span></div>
    <strong><?= $fillRate ?>%</strong>
    <small><?= number_format($stats['booked']) ?> of <?= number_format($stats['seats']) ?> upcoming seats</small>
  </div>
  <div class="card stat">
    <div class="stat-top">Students <span class="stat-icon"><?= icon('users') ?></span></div>
    <strong><?= number_format($students) ?></strong>
    <small>Registered accounts</small>
  </div>
</div>

<div class="admin-grid">
  <section class="card">
    <div class="card-head">
      <h2>Upcoming events</h2>
      <a class="btn btn-sm btn-ghost" href="<?= url('/admin/events') ?>">View all <?= icon('arrow-right') ?></a>
    </div>
    <?php if ($upcoming): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Event</th><th>Seats</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($upcoming as $event): ?>
              <tr>
                <td>
                  <div class="cell-event">
                    <div class="mini"><?= App\Core\View::partial('event-media', ['event' => $event]) ?></div>
                    <div>
                      <a href="<?= url('/admin/events/' . $event['id'] . '/edit') ?>"><?= e($event['title']) ?></a>
                      <small><?= e(format_date($event['starts_at'], 'D, j M · H:i')) ?></small>
                    </div>
                  </div>
                </td>
                <td><?= App\Core\View::partial('capacity', ['event' => $event]) ?></td>
                <td class="actions"><a class="btn btn-sm btn-ghost" href="<?= url('/admin/events/' . $event['id'] . '/attendees') ?>"><?= icon('users') ?></a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="card-body"><p class="muted m-0">No upcoming events. <a href="<?= url('/admin/events/create') ?>">Create one</a>.</p></div>
    <?php endif; ?>
  </section>

  <section class="card">
    <div class="card-head"><h2>Recent bookings</h2></div>
    <?php if ($recent): ?>
      <ul class="list">
        <?php foreach ($recent as $r): ?>
          <li>
            <span class="avatar"><?= e(initials($r['full_name'])) ?></span>
            <div>
              <strong><?= e($r['full_name']) ?></strong>
              <span><?= e($r['title']) ?> · <?= pluralize((int) $r['quantity'], 'ticket') ?></span>
            </div>
            <?php if ($r['status'] === 'cancelled'): ?>
              <span class="badge badge-danger">Cancelled</span>
            <?php else: ?>
              <span class="subtle"><?= e(format_date($r['created_at'], 'j M')) ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="card-body"><p class="muted m-0">No bookings yet.</p></div>
    <?php endif; ?>
  </section>
</div>
