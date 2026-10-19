<!doctype html>
<html lang="en">
<head>
  <?= App\Core\View::partial('head', get_defined_vars()) ?>
</head>
<body>
<?= App\Core\View::partial('icons') ?>
<div class="auth">
  <aside class="auth-aside">
    <img class="bg" src="<?= asset('assets/img/hero-campus.jpg') ?>" alt="">
    <?= App\Core\View::partial('brand') ?>
    <div>
      <blockquote>Every great campus memory starts with <em>showing up.</em></blockquote>
      <ul>
        <li><?= icon('check-circle') ?> Book seats in seconds</li>
        <li><?= icon('check-circle') ?> Digital tickets with a booking reference</li>
        <li><?= icon('check-circle') ?> Cancel any time before the event</li>
      </ul>
    </div>
    <span>&copy; <?= date('Y') ?> UMP Event Booking</span>
  </aside>

  <main class="auth-main" id="main">
    <div class="auth-top">
      <a class="btn btn-ghost btn-sm" href="<?= url('/events') ?>"><?= icon('arrow-left') ?> Browse events</a>
      <button class="btn btn-ghost btn-icon theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
        <?= icon('sun', 'icon-sun') ?><?= icon('moon', 'icon-moon') ?>
      </button>
    </div>
    <div class="auth-card">
      <?= App\Core\View::partial('brand') ?>
      <?= $content ?>
    </div>
  </main>
</div>
<?= App\Core\View::partial('toast') ?>
</body>
</html>
