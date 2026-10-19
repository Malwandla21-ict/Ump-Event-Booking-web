<?php
/**
 * @var array|null $event
 * @var array $categories
 */
$isEdit = $event !== null;
$dt = static fn (?string $v) => $v ? (new DateTimeImmutable($v))->format('Y-m-d\TH:i') : '';
$val = static fn (string $key, string $default = '') => old($key, $isEdit ? (string) ($event[$key] ?? '') : $default);
$action = $isEdit ? url('/admin/events/' . $event['id']) : url('/admin/events');
$image = $isEdit ? event_image($event['image_path']) : null;
$maxMb = config('app.uploads.max_bytes') / 1048576;
?>
<a class="breadcrumb" href="<?= url('/admin/events') ?>"><?= icon('arrow-left') ?> All events</a>

<?php if (errors()): ?>
  <div class="alert alert-error" role="alert"><?= icon('alert-circle') ?><p>Please fix the highlighted fields below.</p></div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data" novalidate data-loading-form id="event-form">
  <?= csrf_field() ?>
  <div class="form-layout">
    <div class="stack">
      <section class="card card-body">
        <h2 class="card-title"><?= icon('info') ?> Basics</h2>
        <div class="field">
          <label for="title">Event title</label>
          <input class="input" id="title" name="title" value="<?= e($val('title')) ?>" maxlength="150" required <?= invalid('title') ?>>
          <?= field_error('title') ?>
        </div>
        <div class="field">
          <label for="description">Description</label>
          <textarea class="input" id="description" name="description" rows="7" maxlength="5000" required <?= invalid('description') ?>><?= e($val('description')) ?></textarea>
          <?= field_error('description') ?>
        </div>
        <div class="field-row">
          <div class="field">
            <label for="category">Category</label>
            <select class="input" id="category" name="category" required <?= invalid('category') ?>>
              <?php foreach ($categories as $key => [$label]): ?>
                <option value="<?= e($key) ?>" <?= $val('category', 'social') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
            <?= field_error('category') ?>
          </div>
          <div class="field">
            <label for="venue">Venue</label>
            <input class="input" id="venue" name="venue" value="<?= e($val('venue')) ?>" maxlength="120" required placeholder="e.g. Great Hall" <?= invalid('venue') ?>>
            <?= field_error('venue') ?>
          </div>
        </div>
      </section>

      <section class="card card-body">
        <h2 class="card-title"><?= icon('calendar') ?> Date &amp; capacity</h2>
        <div class="field-row">
          <div class="field">
            <label for="starts_at">Starts</label>
            <input class="input" id="starts_at" name="starts_at" type="datetime-local" value="<?= e(old('starts_at', $isEdit ? $dt($event['starts_at']) : '')) ?>" required <?= invalid('starts_at') ?>>
            <?= field_error('starts_at') ?>
          </div>
          <div class="field">
            <label for="ends_at">Ends <span class="subtle">(optional)</span></label>
            <input class="input" id="ends_at" name="ends_at" type="datetime-local" value="<?= e(old('ends_at', $isEdit ? $dt($event['ends_at']) : '')) ?>" <?= invalid('ends_at') ?>>
            <?= field_error('ends_at') ?>
          </div>
        </div>
        <div class="field">
          <label for="capacity">Capacity</label>
          <input class="input" id="capacity" name="capacity" type="number" min="1" max="100000" value="<?= e($val('capacity', '100')) ?>" required <?= invalid('capacity') ?>>
          <?php if ($isEdit && (int) $event['seats_taken'] > 0): ?>
            <p class="hint"><?= pluralize((int) $event['seats_taken'], 'seat') ?> already booked — capacity can't go below that.</p>
          <?php endif; ?>
          <?= field_error('capacity') ?>
        </div>
      </section>
    </div>

    <div class="stack">
      <section class="card card-body">
        <h2 class="card-title"><?= icon('sliders') ?> Visibility</h2>
        <div class="field">
          <label for="status">Status</label>
          <select class="input" id="status" name="status" <?= invalid('status') ?>>
            <option value="published" <?= $val('status', 'published') === 'published' ? 'selected' : '' ?>>Published — visible &amp; bookable</option>
            <option value="draft" <?= $val('status') === 'draft' ? 'selected' : '' ?>>Draft — hidden from students</option>
            <option value="cancelled" <?= $val('status') === 'cancelled' ? 'selected' : '' ?>>Cancelled — visible, not bookable</option>
          </select>
          <?= field_error('status') ?>
        </div>
      </section>

      <section class="card card-body">
        <h2 class="card-title"><?= icon('image') ?> Cover image</h2>
        <div class="image-preview" data-image-preview <?= $image ? '' : 'hidden' ?>>
          <img src="<?= e($image ?? '') ?>" alt="Cover preview">
        </div>
        <label class="dropzone mt-sm" data-dropzone>
          <?= icon('upload') ?>
          <span><strong>Click to upload</strong> or drag an image here</span>
          <span class="hint">JPG, PNG or WebP · max <?= $maxMb ?> MB</span>
          <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input <?= invalid('image') ?>>
        </label>
        <?= field_error('image') ?>
        <?php if ($image): ?>
          <label class="checkbox mt-sm"><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
        <?php endif; ?>
      </section>

      <div class="form-actions m-0">
        <button class="btn btn-primary btn-lg btn-block" type="submit"><?= icon('check') ?> <?= $isEdit ? 'Save changes' : 'Create event' ?></button>
        <?php if ($isEdit): ?>
          <a class="btn btn-block" href="<?= url('/events/' . $event['slug']) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?> View public page</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</form>

<?php if ($isEdit): ?>
  <section class="card card-body danger-zone">
    <h2 class="card-title"><?= icon('alert-triangle') ?> Danger zone</h2>
    <div class="row">
      <p class="muted m-0 spacer">Deleting this event also removes its <?= pluralize((int) $event['seats_taken'], 'booked seat') ?>. This cannot be undone. Consider setting the status to <em>Cancelled</em> instead.</p>
      <form method="post" action="<?= url('/admin/events/' . $event['id'] . '/delete') ?>"
            data-confirm="“<?= e($event['title']) ?>” and all of its bookings will be permanently deleted."
            data-confirm-title="Delete event?" data-confirm-ok="Delete event">
        <?= csrf_field() ?>
        <button class="btn btn-danger" type="submit"><?= icon('trash') ?> Delete event</button>
      </form>
    </div>
  </section>
<?php endif; ?>
