<?php
/**
 * @var array $events
 * @var int $total
 * @var int $page
 * @var int $pages
 * @var array $filters
 * @var array $categories
 */
$isPast = $filters['when'] === 'past';
$hasFilters = $filters['q'] !== '' || $filters['category'] !== null;
?>
<div class="container">
  <header class="page-head">
    <h1><?= $isPast ? 'Past events' : 'Upcoming events' ?></h1>
    <p><?= $isPast ? 'A look back at what happened on campus.' : 'Browse what\'s on and reserve your seat.' ?></p>
  </header>

  <div class="filters">
    <form class="filter-bar" method="get" action="<?= url('/events') ?>" role="search">
      <div class="search-field">
        <?= icon('search') ?>
        <label class="sr-only" for="q">Search events</label>
        <input class="input" id="q" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Search events, venues…" maxlength="100">
      </div>
      <?php if ($filters['category']): ?><input type="hidden" name="category" value="<?= e($filters['category']) ?>"><?php endif; ?>
      <?php if ($isPast): ?><input type="hidden" name="when" value="past"><?php endif; ?>
      <button class="btn btn-primary" type="submit">Search</button>
      <div class="segmented" role="group" aria-label="Time">
        <a href="<?= url('/events', ['q' => $filters['q'], 'category' => $filters['category']]) ?>" class="<?= !$isPast ? 'is-active' : '' ?>">Upcoming</a>
        <a href="<?= url('/events', ['q' => $filters['q'], 'category' => $filters['category'], 'when' => 'past']) ?>" class="<?= $isPast ? 'is-active' : '' ?>">Past</a>
      </div>
    </form>

    <nav class="chips" aria-label="Categories">
      <a class="chip <?= $filters['category'] === null ? 'is-active' : '' ?>" href="<?= url('/events', ['q' => $filters['q'], 'when' => $filters['when']]) ?>">All</a>
      <?php foreach ($categories as $key => [$label, $iconName]): ?>
        <a class="chip <?= $filters['category'] === $key ? 'is-active' : '' ?>"
           href="<?= url('/events', ['q' => $filters['q'], 'when' => $filters['when'], 'category' => $key]) ?>"
           <?= $filters['category'] === $key ? 'aria-current="true"' : '' ?>>
          <?= icon($iconName) ?> <?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <p class="subtle" aria-live="polite">
      <?= pluralize($total, 'event') ?> found
      <?php if ($filters['q'] !== ''): ?> for “<?= e($filters['q']) ?>”<?php endif; ?>
      <?php if ($hasFilters): ?> · <a href="<?= url('/events', ['when' => $filters['when']]) ?>">Clear filters</a><?php endif; ?>
    </p>
  </div>

  <?php if ($events): ?>
    <div class="grid-events">
      <?php foreach ($events as $event): ?>
        <?= App\Core\View::partial('event-card', ['event' => $event]) ?>
      <?php endforeach; ?>
    </div>
    <?= App\Core\View::partial('pagination', ['page' => $page, 'pages' => $pages, 'path' => '/events', 'query' => $filters]) ?>
  <?php else: ?>
    <?= App\Core\View::partial('empty-state', $hasFilters ? [
        'icon' => 'search',
        'title' => 'No matching events',
        'message' => 'Try a different search term or category.',
        'actionUrl' => url('/events', ['when' => $filters['when']]),
        'actionLabel' => 'Clear filters',
    ] : [
        'icon' => 'calendar-x',
        'title' => $isPast ? 'No past events' : 'Nothing scheduled right now',
        'message' => $isPast ? 'Events will appear here after they have taken place.' : 'New events are added regularly — check back soon.',
    ]) ?>
  <?php endif; ?>
</div>
