<?php
/**
 * @var int $page
 * @var int $pages
 * @var string $path
 * @var array $query
 */
if ($pages <= 1) {
    return;
}
$link = static fn (int $p) => url($path, [...$query, 'page' => $p > 1 ? $p : null]);
$window = array_unique(array_filter([1, $page - 1, $page, $page + 1, $pages], static fn ($p) => $p >= 1 && $p <= $pages));
sort($window);
?>
<nav class="pagination" aria-label="Pagination">
  <?php if ($page > 1): ?>
    <a href="<?= e($link($page - 1)) ?>" rel="prev"><?= icon('chevron-left') ?><span class="sr-only">Previous</span></a>
  <?php else: ?>
    <span class="is-disabled" aria-hidden="true"><?= icon('chevron-left') ?></span>
  <?php endif; ?>

  <?php $prev = 0; foreach ($window as $p): ?>
    <?php if ($p - $prev > 1): ?><span aria-hidden="true">…</span><?php endif; ?>
    <?php if ($p === $page): ?>
      <span class="is-current" aria-current="page"><?= $p ?></span>
    <?php else: ?>
      <a href="<?= e($link($p)) ?>"><?= $p ?></a>
    <?php endif; ?>
  <?php $prev = $p; endforeach; ?>

  <?php if ($page < $pages): ?>
    <a href="<?= e($link($page + 1)) ?>" rel="next"><?= icon('chevron-right') ?><span class="sr-only">Next</span></a>
  <?php else: ?>
    <span class="is-disabled" aria-hidden="true"><?= icon('chevron-right') ?></span>
  <?php endif; ?>
</nav>
