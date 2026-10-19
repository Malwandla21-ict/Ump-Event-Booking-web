<dialog class="confirm" id="confirm-dialog" aria-labelledby="confirm-title">
  <form method="dialog">
    <div class="confirm-body">
      <div class="confirm-icon"><?= icon('alert-triangle', 'icon-lg') ?></div>
      <div>
        <h2 id="confirm-title" data-confirm-title>Are you sure?</h2>
        <p data-confirm-message>This action cannot be undone.</p>
      </div>
    </div>
    <div class="confirm-actions">
      <button class="btn" value="cancel">Keep it</button>
      <button class="btn btn-danger" value="confirm" data-confirm-ok>Confirm</button>
    </div>
  </form>
</dialog>
