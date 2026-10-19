<?php
/**
 * @var array $user
 * @var array $counts
 */
$min = (int) config('app.auth.password_min_length');
?>
<div class="container">
  <header class="page-head">
    <h1>Profile &amp; security</h1>
    <p>Manage your personal details and password.</p>
  </header>

  <div class="account-layout">
    <aside class="card card-body profile-card">
      <span class="avatar avatar-lg"><?= e(initials($user['full_name'])) ?></span>
      <h3><?= e($user['full_name']) ?></h3>
      <span class="muted"><?= e($user['email']) ?></span>
      <span class="badge <?= $user['role'] === 'admin' ? 'badge-warning' : 'badge-info' ?>">
        <?= icon($user['role'] === 'admin' ? 'shield-check' : 'user') ?> <?= $user['role'] === 'admin' ? 'Administrator' : 'Student' ?>
      </span>
      <span class="subtle">Member since <?= e(format_date($user['created_at'], 'F Y')) ?></span>
      <div class="profile-stats">
        <div><strong><?= $counts['upcoming'] ?></strong><span>Upcoming</span></div>
        <div><strong><?= $counts['past'] ?></strong><span>Attended</span></div>
        <div><strong><?= $counts['cancelled'] ?></strong><span>Cancelled</span></div>
      </div>
    </aside>

    <div class="stack">
      <section class="card card-body" aria-labelledby="details-title">
        <h2 class="card-title" id="details-title"><?= icon('user') ?> Personal details</h2>
        <form method="post" action="<?= url('/account/profile') ?>" novalidate data-loading-form>
          <?= csrf_field() ?>
          <div class="field-row">
            <div class="field">
              <label for="full_name">Full name</label>
              <input class="input" id="full_name" name="full_name" value="<?= e(old('full_name', $user['full_name'])) ?>" autocomplete="name" maxlength="100" required <?= invalid('full_name') ?>>
              <?= field_error('full_name') ?>
            </div>
            <div class="field">
              <label for="email">Email address</label>
              <input class="input" id="email" name="email" type="email" value="<?= e(old('email', $user['email'])) ?>" autocomplete="email" maxlength="120" required <?= invalid('email') ?>>
              <?= field_error('email') ?>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= icon('check') ?> Save changes</button>
          </div>
        </form>
      </section>

      <section class="card card-body" aria-labelledby="password-title">
        <h2 class="card-title" id="password-title"><?= icon('lock') ?> Change password</h2>
        <form method="post" action="<?= url('/account/password') ?>" novalidate data-loading-form>
          <?= csrf_field() ?>
          <div class="field">
            <label for="current_password">Current password</label>
            <div class="input-group">
              <input class="input" id="current_password" name="current_password" type="password" autocomplete="current-password" required <?= invalid('current_password') ?>>
              <button class="btn btn-ghost reveal" type="button" data-reveal="current_password" aria-label="Show password"><?= icon('eye') ?></button>
            </div>
            <?= field_error('current_password') ?>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="password">New password</label>
              <input class="input" id="password" name="password" type="password" autocomplete="new-password" minlength="<?= $min ?>" maxlength="72" required data-strength="password-strength" <?= invalid('password') ?>>
              <div class="strength" id="password-strength" data-score="0" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
              <?= field_error('password') ?>
            </div>
            <div class="field">
              <label for="password_confirmation">Confirm new password</label>
              <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
          </div>
          <p class="hint mt-sm">At least <?= $min ?> characters, with a letter and a number.</p>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= icon('shield-check') ?> Update password</button>
          </div>
        </form>
      </section>
    </div>
  </div>
</div>
