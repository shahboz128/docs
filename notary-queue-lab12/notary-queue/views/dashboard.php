<?php /** @var \App\Core\View $this */ ?>
<h1 class="mb-4">Навбати имрӯза</h1>
<section aria-labelledby="stats-title">
  <h2 class="visually-hidden" id="stats-title">Омори рӯз</h2>
  <div class="row g-3 mb-4">
<?php foreach (['waiting' => 'Дар интизорӣ', 'in_service' => 'Дар хизмат', 'done' => 'Анҷомёфта имрӯз', 'no_show' => 'Наомада (ғоибона)'] as $key => $label): ?>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100"><div class="card-body">
        <div class="stat-number"><?= $this->e($counts[$key]) ?></div><div class="stat-label"><?= $label ?></div>
      </div></div>
    </div>
<?php endforeach; ?>
  </div>
</section>
<section aria-labelledby="latest-title" class="mb-4">
  <h2 class="mb-3" id="latest-title">Охирин талонҳо</h2>
  <div class="card"><ul class="list-group list-group-flush">
<?php if (!$latest): ?>
    <li class="list-group-item text-muted">Имрӯз ҳанӯз талон нест.</li>
<?php endif; foreach ($latest as $t): ?>
    <li class="list-group-item d-flex flex-wrap align-items-center gap-2">
      <a class="fw-semibold" href="<?= $this->url('/tickets/' . (int) $t['id']) ?>"><?= $this->e(\App\Core\Format::label($t['ticket_type'], $t['ticket_no'])) ?></a>
      <span class="me-auto"><?= $this->e($t['service_name']) ?></span>
      <span class="badge status status-<?= $this->e($t['status']) ?>"><?= $this->e(\App\Core\Format::status($t['status'])) ?></span>
    </li>
<?php endforeach; ?>
  </ul></div>
</section>
