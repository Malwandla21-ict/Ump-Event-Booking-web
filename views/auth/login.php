<h1>Welcome back</h1>
<p>Sign in to book events and manage your tickets.</p>

<form method="post" action="<?= url('/login') ?>" novalidate data-loading-form>
  <?= csrf_field() ?>

  <div class="field">
    <label for="email">Email address</label>
    <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>"
           autocomplete="email" required autofocus <?= invalid('email') ?>>
    <?= field_error('email') ?>
  </div>

  <div class="field">
    <label for="password">Password</label>
    <div class="input-group">
      <input class="input" id="password" name="password" type="password" autocomplete="current-password" required <?= invalid('password') ?>>
      <button class="btn btn-ghost reveal" type="button" data-reveal="password" aria-label="Show password"><?= icon('eye') ?></button>
    </div>
    <?= field_error('password') ?>
  </div>

  <div class="form-actions">
    <button class="btn btn-primary btn-lg btn-block" type="submit"><?= icon('log-in') ?> Sign in</button>
  </div>
</form>

<p class="auth-switch">New to UMP Events? <a href="<?= url('/register') ?>">Create an account</a></p>

<?php if (config('app.env') !== 'production'): ?>
  <div class="alert alert-info demo-creds">
    <?= icon('info') ?>
    <p><strong>Demo accounts</strong> (password <code>Password123</code>)<br>
      Admin: <code>admin@ump.test</code> · Student: <code>student@ump.test</code></p>
  </div>
<?php endif; ?>
