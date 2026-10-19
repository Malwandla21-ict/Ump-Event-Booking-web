<?php $user = auth(); ?>
<!doctype html>
<html lang="en">
<head>
  <?= App\Core\View::partial('head', get_defined_vars()) ?>
</head>
<body>
<?= App\Core\View::partial('icons') ?>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
  <div class="container">
    <?= App\Core\View::partial('brand') ?>

    <button class="btn btn-ghost btn-icon nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
      <?= icon('menu', 'icon-lg') ?><span class="sr-only">Menu</span>
    </button>

    <nav class="nav" id="site-nav" aria-label="Main">
      <a class="nav-link <?= is_active('/', true) ? 'is-active' : '' ?>" href="<?= url('/') ?>"><?= icon('home') ?> Home</a>
      <a class="nav-link <?= is_active('/events') ? 'is-active' : '' ?>" href="<?= url('/events') ?>"><?= icon('calendar') ?> Events</a>

      <?php if ($user): ?>
        <a class="nav-link <?= is_active('/account/bookings') ? 'is-active' : '' ?>" href="<?= url('/account/bookings') ?>"><?= icon('ticket') ?> My bookings</a>
        <?php if ($user['role'] === 'admin'): ?>
          <a class="nav-link" href="<?= url('/admin') ?>"><?= icon('layout-dashboard') ?> Admin</a>
        <?php endif; ?>
      <?php endif; ?>

      <button class="btn btn-ghost btn-icon theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
        <?= icon('sun', 'icon-sun') ?><?= icon('moon', 'icon-moon') ?>
        <span class="mobile-only">Switch theme</span>
      </button>

      <?php if ($user): ?>
        <details class="user-menu">
          <summary aria-label="Account menu">
            <span class="avatar"><?= e(initials($user['full_name'])) ?></span>
            <span class="mobile-only"><?= e($user['full_name']) ?></span>
            <?= icon('chevron-down') ?>
          </summary>
          <div class="menu">
            <div class="menu-header">
              <strong><?= e($user['full_name']) ?></strong>
              <span><?= e($user['email']) ?></span>
            </div>
            <a href="<?= url('/account/bookings') ?>"><?= icon('ticket') ?> My bookings</a>
            <a href="<?= url('/account/profile') ?>"><?= icon('sliders') ?> Profile &amp; security</a>
            <?php if ($user['role'] === 'admin'): ?>
              <a href="<?= url('/admin') ?>"><?= icon('layout-dashboard') ?> Admin dashboard</a>
            <?php endif; ?>
            <form method="post" action="<?= url('/logout') ?>">
              <?= csrf_field() ?>
              <button type="submit" class="danger"><?= icon('log-out') ?> Sign out</button>
            </form>
          </div>
        </details>
      <?php else: ?>
        <a class="nav-link <?= is_active('/login') ? 'is-active' : '' ?>" href="<?= url('/login') ?>"><?= icon('log-in') ?> Sign in</a>
        <a class="btn btn-primary" href="<?= url('/register') ?>">Create account</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main id="main">
  <?= $content ?>
</main>

<footer class="site-footer">
  <div class="crest-stripe" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
  <div class="container">
    <div>
      <?= App\Core\View::partial('brand') ?>
      <p class="muted">Find, book and manage tickets for talks, festivals, sports and career events across UMP campuses.</p>
    </div>
    <div>
      <h4>Explore</h4>
      <ul>
        <li><a href="<?= url('/events') ?>">Upcoming events</a></li>
        <li><a href="<?= url('/events', ['when' => 'past']) ?>">Past events</a></li>
      </ul>
    </div>
    <div>
      <h4>Account</h4>
      <ul>
        <?php if ($user): ?>
          <li><a href="<?= url('/account/bookings') ?>">My bookings</a></li>
          <li><a href="<?= url('/account/profile') ?>">Profile</a></li>
        <?php else: ?>
          <li><a href="<?= url('/login') ?>">Sign in</a></li>
          <li><a href="<?= url('/register') ?>">Create account</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      <span>&copy; <?= date('Y') ?> UMP Event Booking</span>
      <span>Built with PHP &amp; MySQL</span>
    </div>
  </div>
</footer>

<?= App\Core\View::partial('toast') ?>
<?= App\Core\View::partial('confirm-dialog') ?>
</body>
</html>
