<?php
/** @var \App\Core\View $this */
// $result: page/pages/total; $filters — барои нигоҳ доштани филтрҳо дар пайвандҳо; $base — роҳ
$page = $result['page']; $pages = $result['pages'];
$link = fn (int $p): string => $this->url($base, $filters + ['page' => $p]);
if ($pages > 1): ?>
<nav aria-label="Саҳифаҳо">
  <ul class="pagination mb-0">
    <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>"><?= $page <= 1 ? '<span class="page-link">Қаблӣ</span>' : '<a class="page-link" href="' . $this->e($link($page - 1)) . '">Қаблӣ</a>' ?></li>
<?php for ($p = 1; $p <= $pages; $p++): ?>
    <li class="page-item<?= $p === $page ? ' active' : '' ?>"<?= $p === $page ? ' aria-current="page"' : '' ?>>
      <?= $p === $page ? '<span class="page-link">' . $p . '</span>' : '<a class="page-link" href="' . $this->e($link($p)) . '">' . $p . '</a>' ?></li>
<?php endfor; ?>
    <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>"><?= $page >= $pages ? '<span class="page-link">Баъдӣ</span>' : '<a class="page-link" href="' . $this->e($link($page + 1)) . '">Баъдӣ</a>' ?></li>
  </ul>
</nav>
<?php endif; ?>
