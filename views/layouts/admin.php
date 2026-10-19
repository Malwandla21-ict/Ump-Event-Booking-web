<?php $user = auth(); ?>
<!doctype html>
<html lang="en">
<head>
  <?= App\Core\View::partial('head', get_defined_vars()) ?>
</head>
<body>
<?= App\Core\View::partial('icons') ?>
<a class="skip-link" href="#main">Skip to content</a>

<div class="admin">
  <aside class="sidebar" id="admin-sidebar" aria-label="Admin">
    <?= App\Core\View::partial('brand') ?>

    <nav class="side-nav">
      <span class="label">Manage</span>
      <a href="<?= url('/admin') ?>" class="<?= is_active('/admin', true) ? 'is-active' : '' ?>"><?= icon('layout-dashboard') ?> Dashboard</a>
      <a href="<?= url('/admin/events') ?>" class="<?= is_active('/admin/events') && !is_active('/admin/events/create') ? 'is-active' : '' ?>"><?= icon('calendar') ?> Events</a>
      <a href="<?= url('/admin/events/create') ?>" class="<?= is_active('/admin/events/create') ? 'is-active' : '' ?>"><?= icon('plus') ?> New event</a>

      <span class="label">Site</span>
      <a href="<?= url('/events') ?>"><?= icon('external-link') ?> View public site</a>
      <a href="<?= url('/account/bookings') ?>"><?= icon('ticket') ?> My bookings</a>
    </nav>

    <div class="sidebar-foot">
      <div class="user">
        <span class="avatar"><?= e(initials($user['full_name'])) ?></span>
        <div>
          <strong><?= e($user['full_name']) ?></strong>
          <span><?= e($user['email']) ?></span>
        </div>
      </div>
      <form method="post" action="<?= url('/logout') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-ghost btn-sm btn-block"><?= icon('log-out') ?> Sign out</button>
      </form>
    </div>
  </aside>
  <div class="sidebar-backdrop" data-sidebar-close hidden></div>

  <div class="admin-main">
    <header class="admin-top">
      <button class="btn btn-ghost btn-icon sidebar-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false" data-sidebar-toggle>
        <?= icon('menu', 'icon-lg') ?><span class="sr-only">Open navigation</span>
      </button>
      <h1><?= e($pageTitle ?? 'Admin') ?></h1>
      <span class="spacer"></span>
      <button class="btn btn-ghost btn-icon theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
        <?= icon('sun', 'icon-sun') ?><?= icon('moon', 'icon-moon') ?>
      </button>
    </header>

    <main class="admin-content" id="main">
      <?= $content ?>
    </main>
  </div>
</div>

<?= App\Core\View::partial('toast') ?>
<?= App\Core\View::partial('confirm-dialog') ?>
</body>
</html>
