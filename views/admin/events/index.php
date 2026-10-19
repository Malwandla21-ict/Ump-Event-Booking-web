<?php
/**
 * @var array $events
 * @var string $search
 */
$statusBadge = ['published' => 'badge-success', 'draft' => '', 'cancelled' => 'badge-danger'];
?>
<div class="row">
  <form class="filter-bar spacer" method="get" action="<?= url('/admin/events') ?>" role="search">
    <div class="search-field">
      <?= icon('search') ?>
      <label class="sr-only" for="q">Search events</label>
      <input class="input" id="q" type="search" name="q" value="<?= e($search) ?>" placeholder="Search by title or venue" maxlength="100">
    </div>
  </form>
  <a class="btn btn-primary" href="<?= url('/admin/events/create') ?>"><?= icon('plus') ?> New event</a>
</div>

<?php if (!$events): ?>
  <?= App\Core\View::partial('empty-state', $search !== '' ? [
      'icon' => 'search', 'title' => 'No events match your search', 'message' => 'Try a different title or venue.',
      'actionUrl' => url('/admin/events'), 'actionLabel' => 'Clear search',
  ] : [
      'icon' => 'calendar', 'title' => 'No events yet', 'message' => 'Create your first event to start taking bookings.',
      'actionUrl' => url('/admin/events/create'), 'actionLabel' => 'Create event',
  ]) ?>
<?php else: ?>
  <section class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Event</th><th>Category</th><th>Status</th><th>Bookings</th><th class="actions"><span class="sr-only">Actions</span></th></tr>
        </thead>
        <tbody>
          <?php foreach ($events as $event): $isPast = strtotime($event['starts_at']) < time(); ?>
            <tr>
              <td>
                <div class="cell-event">
                  <div class="mini"><?= App\Core\View::partial('event-media', ['event' => $event]) ?></div>
                  <div>
                    <a href="<?= url('/admin/events/' . $event['id'] . '/edit') ?>"><?= e($event['title']) ?></a>
                    <small><?= e(format_date($event['starts_at'], 'D, j M Y · H:i')) ?> · <?= e($event['venue']) ?></small>
                  </div>
                </div>
              </td>
              <td><span class="badge badge-outline"><?= icon(category_icon($event['category'])) ?> <?= e(category_label($event['category'])) ?></span></td>
              <td>
                <span class="badge <?= $statusBadge[$event['status']] ?>"><?= e(ucfirst($event['status'])) ?></span>
                <?php if ($isPast): ?><span class="badge">Ended</span><?php endif; ?>
              </td>
              <td>
                <div class="mini-meter">
                  <span class="txt"><?= number_format((int) $event['seats_taken']) ?> / <?= number_format((int) $event['capacity']) ?></span>
                  <?php $pct = min(100, (int) round($event['seats_taken'] / max(1, $event['capacity']) * 100)); ?>
                  <div class="meter <?= (int) $event['seats_left'] === 0 ? 'is-full' : '' ?>"><span data-fill="<?= $pct ?>"></span></div>
                </div>
              </td>
              <td class="actions">
                <a class="btn btn-sm btn-ghost" href="<?= url('/admin/events/' . $event['id'] . '/attendees') ?>" title="Attendees"><?= icon('users') ?><span class="sr-only">Attendees</span></a>
                <a class="btn btn-sm btn-ghost" href="<?= url('/admin/events/' . $event['id'] . '/edit') ?>" title="Edit"><?= icon('pencil') ?><span class="sr-only">Edit</span></a>
                <form method="post" action="<?= url('/admin/events/' . $event['id'] . '/delete') ?>"
                      data-confirm="“<?= e($event['title']) ?>” and its <?= pluralize((int) $event['seats_taken'], 'booked seat') ?> will be permanently deleted."
                      data-confirm-title="Delete event?" data-confirm-ok="Delete event">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-danger-ghost" type="submit" title="Delete"><?= icon('trash') ?><span class="sr-only">Delete</span></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>
