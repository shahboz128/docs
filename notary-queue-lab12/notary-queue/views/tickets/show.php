<?php /** @var \App\Core\View $this */ $label = \App\Core\Format::label($ticket['ticket_type'], $ticket['ticket_no']); ?>
<nav aria-label="Занҷираи навигатсия">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= $this->url('/tickets') ?>">Талонҳо</a></li>
    <li class="breadcrumb-item active" aria-current="page">Талони <?= $this->e($label) ?></li>
  </ol>
</nav>
<div class="row g-4">
  <aside class="col-12 col-lg-3" aria-label="Хулосаи талон">
    <div class="card"><div class="card-body text-center">
      <span class="ticket-circle ticket-circle-lg mx-auto mb-3" role="img" aria-label="Рақами талон <?= $this->e($label) ?>"><?= $this->e($label) ?></span>
      <h1 class="h3">Талони <?= $this->e($label) ?></h1>
      <span class="badge status status-<?= $this->e($ticket['status']) ?>"><?= $this->e(\App\Core\Format::status($ticket['status'])) ?></span>
    </div></div>
  </aside>
  <section class="col-12 col-lg-9" aria-labelledby="info-title">
    <h2 class="visually-hidden" id="info-title">Маълумоти талон</h2>
    <div class="card mb-4">
      <div class="card-header">Маълумоти хизматрасонӣ</div>
      <div class="card-body"><dl class="details">
        <dt>Хизмат</dt><dd><?= $this->e($ticket['service_name']) ?> (<?= (int) $ticket['duration_min'] ?> дақ.)</dd>
        <dt>Намуд</dt><dd><?= $this->e(\App\Core\Format::type($ticket['ticket_type'])) ?></dd>
        <dt>Сана</dt><dd><?= $this->e(\App\Core\Format::date($ticket['ticket_date'])) ?></dd>
        <dt>Талон гирифта шуд</dt><dd><?= $this->e(\App\Core\Format::time($ticket['created_at'])) ?></dd>
        <dt>Даъват шуд</dt><dd><?= $this->e(\App\Core\Format::time($ticket['called_at'])) ?></dd>
        <dt>Оғози қабул</dt><dd><?= $this->e(\App\Core\Format::time($ticket['started_at'])) ?></dd>
        <dt>Анҷом</dt><dd><?= $this->e(\App\Core\Format::time($ticket['finished_at'])) ?></dd>
      </dl></div>
    </div>
    <div class="card mb-4">
      <div class="card-header">Мизоҷ ва нотариус</div>
      <div class="card-body"><dl class="details">
        <dt>Мизоҷ</dt><dd><?= $this->e($ticket['client_name'] ?? '—') ?></dd>
        <dt>Телефон</dt><dd><?php if ($ticket['client_phone']): ?><a href="tel:<?= $this->e(preg_replace('/\s+/', '', $ticket['client_phone'])) ?>"><?= $this->e($ticket['client_phone']) ?></a><?php else: ?>—<?php endif; ?></dd>
        <dt>Нотариус</dt><dd><?= $this->e($ticket['notary_name'] ?? '—') ?></dd>
      </dl></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-outline-primary" href="<?= $this->url('/tickets') ?>">← Ба рӯйхат</a>
      <a class="btn btn-primary" href="<?= $this->url('/tickets/' . (int) $ticket['id'] . '/edit') ?>">Таҳрир</a>
      <button type="button" class="btn btn-outline-danger" data-delete-url="<?= $this->url('/tickets/' . (int) $ticket['id'] . '/delete') ?>" data-ticket-label="<?= $this->e($label) ?>" data-confirm-action="Нест кардан">Нест кардан</button>
    </div>
  </section>
</div>
