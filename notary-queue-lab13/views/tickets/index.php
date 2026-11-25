<?php /** @var View $this */ ?>
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <h1 class="me-auto mb-0">Талонҳо</h1>
  <a class="btn btn-primary" href="/tickets/create">+ Талони нав</a>
</div>

<form class="card card-body mb-3" method="get" action="/tickets" role="search">
  <div class="row g-2">
    <div class="col-12 col-md-3">
      <label class="visually-hidden" for="ticket-search">Ҷустуҷӯ</label>
      <input class="form-control" type="search" id="ticket-search" name="q" value="<?= $this->e($filters['q']) ?>"
             placeholder="Рақам (B-002), мизоҷ, хизмат…" autocomplete="off">
    </div>
    <div class="col-6 col-md-3">
      <label class="visually-hidden" for="service_id">Хизмат</label>
      <select class="form-select" id="service_id" name="service_id">
        <option value="">Ҳамаи хизматҳо</option>
<?php foreach ($services as $s): ?>
        <option value="<?= (int) $s['id'] ?>"<?= (int) $filters['service_id'] === (int) $s['id'] ? ' selected' : '' ?>><?= $this->e($s['name']) ?></option>
<?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="visually-hidden" for="status">Ҳолат</label>
      <select class="form-select" id="status" name="status">
        <option value="">Ҳамаи ҳолатҳо</option>
<?php foreach (Format::STATUSES as $k => $label): ?>
        <option value="<?= $k ?>"<?= $filters['status'] === $k ? ' selected' : '' ?>><?= $label ?></option>
<?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="visually-hidden" for="ticket_type">Намуд</label>
      <select class="form-select" id="ticket_type" name="ticket_type">
        <option value="">Ҳама намудҳо</option>
<?php foreach (Format::TYPES as $k => $label): ?>
        <option value="<?= $k ?>"<?= $filters['ticket_type'] === $k ? ' selected' : '' ?>><?= $label ?></option>
<?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2 d-grid"><button class="btn btn-outline-primary" type="submit">Ёфтан</button></div>
  </div>
</form>
<p id="ticket-search-status" class="small text-muted" aria-live="polite"></p>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle table-cards mb-0">
      <caption class="visually-hidden">Рӯйхати талонҳо</caption>
      <thead>
        <tr><th scope="col">Талон</th><th scope="col">Хизмат</th><th scope="col">Намуд</th><th scope="col">Мизоҷ</th><th scope="col">Сана ва вақт</th><th scope="col">Ҳолат</th><th scope="col"><span class="visually-hidden">Амалҳо</span></th></tr>
      </thead>
      <tbody id="tickets-tbody" data-ticket-url="/tickets/{id}">
<?php if (!$result['items']): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Талонҳо ёфт нашуданд. Ҷустуҷӯро тағйир диҳед.</td></tr>
<?php endif; foreach ($result['items'] as $t): $label = Format::label($t['ticket_type'], $t['ticket_no']); ?>
        <tr>
          <td data-label="Талон"><a class="fw-semibold" href="/tickets/<?= (int) $t['id'] ?>"><?= $this->e($label) ?></a></td>
          <td data-label="Хизмат"><?= $this->e($t['service_name']) ?></td>
          <td data-label="Намуд"><?= $this->e(Format::type($t['ticket_type'])) ?></td>
          <td data-label="Мизоҷ"><?= $this->e($t['client_name'] ?? '—') ?></td>
          <td data-label="Сана ва вақт"><?= $this->e(Format::date($t['ticket_date'])) ?> <?= $this->e(Format::time($t['created_at'])) ?></td>
          <td data-label="Ҳолат"><span class="badge status status-<?= $this->e($t['status']) ?>"><?= $this->e(Format::status($t['status'])) ?></span></td>
          <td class="text-end cell-actions">
            <a class="btn btn-sm btn-outline-primary" href="/tickets/<?= (int) $t['id'] ?>/edit">Таҳрир<span class="visually-hidden"> талони <?= $this->e($label) ?></span></a>
<?php if ($t['status'] === 'waiting'): ?>
            <button type="button" class="btn btn-sm btn-outline-danger ms-1" data-cancel-url="/tickets/<?= (int) $t['id'] ?>/cancel" data-ticket-label="<?= $this->e($label) ?>" data-confirm-action="Бекор кардан">Бекор кардан</button>
<?php endif; ?>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
  <p class="mb-0 text-muted">Ёфт шуд: <?= (int) $result['total'] ?></p>
  <?= $this->partial('pagination', ['result' => $result, 'filters' => $filters, 'base' => '/tickets']) ?>
</div>
