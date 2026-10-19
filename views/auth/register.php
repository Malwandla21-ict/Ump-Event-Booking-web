<?php $min = (int) config('app.auth.password_min_length'); ?>
<h1>Create your account</h1>
<p>It's free and takes less than a minute.</p>

<form method="post" action="<?= url('/register') ?>" novalidate data-loading-form>
  <?= csrf_field() ?>

  <div class="field">
    <label for="full_name">Full name</label>
    <input class="input" id="full_name" name="full_name" type="text" value="<?= e(old('full_name')) ?>"
           autocomplete="name" maxlength="100" required autofocus <?= invalid('full_name') ?>>
    <?= field_error('full_name') ?>
  </div>

  <div class="field">
    <label for="email">Email address</label>
    <input class="input" id="email" name="email" type="email" value="<?= e(old('email')) ?>"
           autocomplete="email" maxlength="120" required <?= invalid('email') ?>>
    <?= field_error('email') ?>
  </div>

  <div class="field">
    <label for="password">Password</label>
    <div class="input-group">
      <input class="input" id="password" name="password" type="password" autocomplete="new-password"
             minlength="<?= $min ?>" maxlength="72" required data-strength="password-strength"
             aria-describedby="password-hint<?= error('password') ? ' password-error' : '' ?>" <?= error('password') ? 'aria-invalid="true"' : '' ?>>
      <button class="btn btn-ghost reveal" type="button" data-reveal="password" aria-label="Show password"><?= icon('eye') ?></button>
    </div>
    <div class="strength" id="password-strength" data-score="0" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
    <p class="hint" id="password-hint">At least <?= $min ?> characters, with a letter and a number.</p>
    <?= field_error('password') ?>
  </div>

  <div class="field">
    <label for="password_confirmation">Confirm password</label>
    <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
  </div>

  <div class="form-actions">
    <button class="btn btn-primary btn-lg btn-block" type="submit"><?= icon('user-plus') ?> Create account</button>
  </div>
</form>

<p class="auth-switch">Already have an account? <a href="<?= url('/login') ?>">Sign in</a></p>
