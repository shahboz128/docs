<?php
/** @var View $this */
$id = $ticket['id'] ?? null;
$action = $id ? '/tickets/' . $id . '/edit' : '/tickets/create';
$val = fn (string $f): string => $this->e($values[$f] ?? '');
$invalid = fn (string $f): string => isset($errors[$f]) ? ' is-invalid' : '';
$error = fn (string $f): string => isset($errors[$f]) ? '<div class="invalid-feedback" id="' . $f . '-error">' . $this->e($errors[$f]) . '</div>' : '<div class="invalid-feedback" id="' . $f . '-error"></div>';
$sel = fn (string $f, mixed $v): string => (string) ($values[$f] ?? '') === (string) $v ? ' selected' : '';
?>
<h1 class="mb-3"><?= $id ? 'Таҳрири талон' : 'Талони нав' ?></h1>
<?php if ($errors): ?><div class="alert alert-danger" role="alert">Хатоҳоро дар форма ислоҳ кунед.</div><?php endif; ?>
<div class="row"><div class="col-12 col-lg-8">
<form class="card card-body" method="post" action="<?= $this->e($action) ?>" novalidate>
  <div class="mb-3">
    <label class="form-label" for="service_id">Хизмат *</label>
    <select class="form-select<?= $invalid('service_id') ?>" id="service_id" name="service_id" required aria-describedby="service_id-error">
      <option value="">Хизматро интихоб кунед</option>
<?php foreach ($services as $s): ?>
      <option value="<?= (int) $s['id'] ?>"<?= $sel('service_id', $s['id']) ?>><?= $this->e($s['name']) ?></option>
<?php endforeach; ?>
    </select>
    <?= $error('service_id') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="client_id">Мизоҷ (ихтиёрӣ)</label>
    <select class="form-select<?= $invalid('client_id') ?>" id="client_id" name="client_id" aria-describedby="client_id-error">
      <option value="">Бе мизоҷи сабтшуда</option>
<?php foreach ($clients as $c): ?>
      <option value="<?= (int) $c['id'] ?>"<?= $sel('client_id', $c['id']) ?>><?= $this->e($c['full_name']) ?> (<?= $this->e($c['phone']) ?>)</option>
<?php endforeach; ?>
    </select>
    <?= $error('client_id') ?>
  </div>
<?php if ($id): ?>
  <div class="mb-3">
    <label class="form-label" for="notary_id">Нотариус</label>
    <select class="form-select<?= $invalid('notary_id') ?>" id="notary_id" name="notary_id" aria-describedby="notary_id-error">
      <option value="">Таъин нашудааст</option>
<?php foreach ($notaries as $n): ?>
      <option value="<?= (int) $n['id'] ?>"<?= $sel('notary_id', $n['id']) ?>><?= $this->e($n['full_name']) ?></option>
<?php endforeach; ?>
    </select>
    <?= $error('notary_id') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="status">Ҳолат *</label>
    <select class="form-select<?= $invalid('status') ?>" id="status" name="status" required aria-describedby="status-error">
<?php foreach (Format::STATUSES as $k => $label): ?>
      <option value="<?= $k ?>"<?= $sel('status', $k) ?>><?= $label ?></option>
<?php endforeach; ?>
    </select>
    <?= $error('status') ?>
  </div>
<?php else: ?>
  <p class="text-muted">Талон ҳамчун «Бе сабт» бо рақами навбатии рӯз сохта мешавад.</p>
<?php endif; ?>
  <div class="d-flex flex-wrap gap-2">
    <button class="btn btn-primary" type="submit">Нигоҳ доштан</button>
    <a class="btn btn-outline-primary" href="<?= $id ? '/tickets/' . (int) $id : '/tickets' ?>">Бекор кардан</a>
  </div>
</form>
</div></div>
