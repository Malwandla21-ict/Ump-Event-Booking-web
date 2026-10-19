<?php
/**
 * @var array $featured
 * @var array $stats
 * @var array $categories
 */
$gallery = [
    ['choir.jpg', 'UMP Choir'],
    ['graduation.jpg', 'Graduation'],
    ['sports-day.jpg', 'Sports day'],
    ['festival.jpg', 'Spring festival'],
    ['hackathon.jpg', 'Hackathon'],
    ['heritage.jpg', 'Heritage Day'],
];
?>
<section class="hero">
  <img class="hero-bg" src="<?= asset('assets/img/hero-campus.jpg') ?>" alt="">
  <div class="container">
    <span class="eyebrow"><?= icon('sparkles') ?> University of Mpumalanga</span>
    <h1>What's happening <em>on campus</em> this month</h1>
    <p class="lead">Talks, festivals, sports and career expos — find something worth showing up for and grab your seat in seconds.</p>

    <form class="hero-search" action="<?= url('/events') ?>" method="get" role="search">
      <?= icon('search') ?>
      <label class="sr-only" for="hero-q">Search events</label>
      <input id="hero-q" type="search" name="q" placeholder="Search by event, venue or keyword" maxlength="100">
      <button class="btn btn-primary" type="submit">Find events</button>
    </form>

    <div class="hero-stats">
      <div><strong><?= number_format($stats['upcoming']) ?></strong><span>Upcoming events</span></div>
      <div><strong><?= number_format($stats['booked']) ?></strong><span>Seats booked</span></div>
      <div><strong><?= number_format(max(0, $stats['seats'] - $stats['booked'])) ?></strong><span>Seats still open</span></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Coming up next</h2>
        <p>The next few events on the calendar.</p>
      </div>
      <a class="btn" href="<?= url('/events') ?>">See all events <?= icon('arrow-right') ?></a>
    </div>

    <?php if ($categories): ?>
      <div class="chips chips-spaced">
        <?php foreach ($categories as $key => $count): ?>
          <a class="chip" href="<?= url('/events', ['category' => $key]) ?>">
            <?= icon(category_icon($key)) ?> <?= e(category_label($key)) ?> <span class="count"><?= (int) $count ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($featured): ?>
      <div class="grid-events">
        <?php foreach ($featured as $event): ?>
          <?= App\Core\View::partial('event-card', ['event' => $event]) ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?= App\Core\View::partial('empty-state', [
          'icon' => 'calendar',
          'title' => 'No upcoming events yet',
          'message' => 'New events are added regularly. Check back soon!',
      ]) ?>
    <?php endif; ?>
  </div>
</section>

<section class="section" aria-labelledby="how-title">
  <div class="container">
    <div class="section-head"><div><h2 id="how-title">How it works</h2><p>Three steps from browsing to your seat.</p></div></div>
    <div class="steps">
      <div class="card step">
        <div class="step-icon"><?= icon('search', 'icon-lg') ?></div>
        <h3>Find an event</h3>
        <p>Search or filter by category to discover what's on across campus.</p>
      </div>
      <div class="card step">
        <div class="step-icon"><?= icon('ticket', 'icon-lg') ?></div>
        <h3>Book your seats</h3>
        <p>Choose up to <?= (int) config('app.booking.max_tickets_per_booking') ?> tickets. Seats are reserved instantly, so you never double-book.</p>
      </div>
      <div class="card step">
        <div class="step-icon"><?= icon('check-circle', 'icon-lg') ?></div>
        <h3>Show your ticket</h3>
        <p>Every booking gets a unique reference. Print it or show it on your phone at the door.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="gallery-title">
  <div class="container">
    <div class="section-head"><div><h2 id="gallery-title">Life at UMP</h2><p>Moments from recent campus events.</p></div></div>
    <div class="gallery">
      <?php foreach ($gallery as [$file, $caption]): ?>
        <figure>
          <img src="<?= asset('assets/img/gallery/' . $file) ?>" alt="<?= e($caption) ?>" loading="lazy" decoding="async">
          <figcaption><?= e($caption) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if (!auth()): ?>
<section class="section">
  <div class="container">
    <div class="cta">
      <div>
        <h2>Never miss out again</h2>
        <p>Create a free account to book seats and keep all your tickets in one place.</p>
      </div>
      <a class="btn btn-accent btn-lg" href="<?= url('/register') ?>"><?= icon('user-plus') ?> Create account</a>
    </div>
  </div>
</section>
<?php endif; ?>
