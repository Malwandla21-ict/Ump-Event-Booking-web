<?php
/**
 * @var array $event
 * @var array $attendees
 */
$confirmed = array_filter($attendees, static fn ($a) => $a['status'] === 'confirmed');
?>
<a class="breadcrumb" href="<?= url('/admin/events') ?>"><?= icon('arrow-left') ?> All events</a>

<div class="row">
  <div>
    <h2 class="m-0"><?= e($event['title']) ?></h2>
    <p class="muted m-0"><?= e(format_date($event['starts_at'], 'l, j F Y · H:i')) ?> · <?= e($event['venue']) ?></p>
  </div>
  <span class="spacer"></span>
  <a class="btn" href="<?= url('/admin/events/' . $event['id'] . '/edit') ?>"><?= icon('pencil') ?> Edit</a>
  <?php if ($attendees): ?>
    <a class="btn btn-primary" href="<?= url('/admin/events/' . $event['id'] . '/attendees.csv') ?>"><?= icon('download') ?> Export CSV</a>
  <?php endif; ?>
</div>

<div class="stats">
  <div class="card stat"><div class="stat-top">Seats booked <span class="stat-icon"><?= icon('ticket') ?></span></div><strong><?= number_format((int) $event['seats_taken']) ?></strong><small>of <?= number_format((int) $event['capacity']) ?> capacity</small></div>
  <div class="card stat"><div class="stat-top">Bookings <span class="stat-icon"><?= icon('users') ?></span></div><strong><?= count($confirmed) ?></strong><small>confirmed</small></div>
  <div class="card stat"><div class="stat-top">Seats left <span class="stat-icon"><?= icon('check-circle') ?></span></div><strong><?= number_format((int) $event['seats_left']) ?></strong><small>available</small></div>
  <div class="card stat"><div class="stat-top">Cancellations <span class="stat-icon"><?= icon('ban') ?></span></div><strong><?= count($attendees) - count($confirmed) ?></strong><small>released seats</small></div>
</div>

<?php if (!$attendees): ?>
  <?= App\Core\View::partial('empty-state', ['icon' => 'users', 'title' => 'No bookings yet', 'message' => 'Attendees will appear here as students book.']) ?>
<?php else: ?>
  <section class="card">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Reference</th><th class="num">Tickets</th><th>Status</th><th>Booked</th></tr></thead>
        <tbody>
          <?php foreach ($attendees as $a): ?>
            <tr>
              <td><div class="row"><span class="avatar"><?= e(initials($a['full_name'])) ?></span> <?= e($a['full_name']) ?></div></td>
              <td><a href="mailto:<?= e($a['email']) ?>"><?= e($a['email']) ?></a></td>
              <td><span class="ref"><?= e($a['reference']) ?></span></td>
              <td class="num"><?= (int) $a['quantity'] ?></td>
              <td><span class="badge <?= $a['status'] === 'confirmed' ? 'badge-success' : 'badge-danger' ?>"><?= e(ucfirst($a['status'])) ?></span></td>
              <td class="subtle"><?= e(format_date($a['created_at'], 'j M Y, H:i')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>
