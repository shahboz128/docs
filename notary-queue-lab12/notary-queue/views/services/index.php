<?php /** @var \App\Core\View $this */
$val = fn (string $f): string => $this->e($values[$f] ?? '');
$invalid = fn (string $f): string => isset($errors[$f]) ? ' is-invalid' : '';
$error = fn (string $f): string => '<div class="invalid-feedback" id="' . $f . '-error">' . $this->e($errors[$f] ?? '') . '</div>';
?>
<h1 class="mb-3">Хизматҳо</h1>
<div class="row g-4">
  <section class="col-12 col-lg-8" aria-labelledby="svc-list">
    <h2 class="h4 mb-3" id="svc-list">Рӯйхати хизматҳо</h2>
    <div class="card"><div class="table-responsive">
      <table class="table align-middle table-cards mb-0">
        <caption class="visually-hidden">Хизматҳои нотариалӣ</caption>
        <thead><tr><th scope="col">Ном</th><th scope="col">Давомнокӣ</th><th scope="col">Талонҳо</th><th scope="col"><span class="visually-hidden">Амалҳо</span></th></tr></thead>
        <tbody>
<?php foreach ($services as $s): ?>
          <tr>
            <td data-label="Ном"><?= $this->e($s['name']) ?></td>
            <td data-label="Давомнокӣ"><?= (int) $s['duration_min'] ?> дақ.</td>
            <td data-label="Талонҳо"><?= (int) $s['tickets_count'] ?></td>
            <td class="text-end cell-actions">
              <form method="post" action="<?= $this->url('/services/' . (int) $s['id'] . '/delete') ?>" class="d-inline">
                <button class="btn btn-sm btn-outline-danger" type="submit">Нест кардан<span class="visually-hidden"> «<?= $this->e($s['name']) ?>»</span></button>
              </form>
            </td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </section>
  <section class="col-12 col-lg-4" aria-labelledby="svc-new">
    <h2 class="h4 mb-3" id="svc-new">Хизмати нав</h2>
    <form class="card card-body" method="post" action="<?= $this->url('/services/create') ?>" novalidate>
      <div class="mb-3">
        <label class="form-label" for="name">Ном *</label>
        <input class="form-control<?= $invalid('name') ?>" id="name" name="name" required maxlength="150" value="<?= $val('name') ?>" aria-describedby="name-error">
        <?= $error('name') ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="duration_min">Давомнокӣ (дақиқа) *</label>
        <input class="form-control<?= $invalid('duration_min') ?>" id="duration_min" name="duration_min" type="number" min="1" max="480" required value="<?= $val('duration_min') ?>" aria-describedby="duration_min-error">
        <?= $error('duration_min') ?>
      </div>
      <button class="btn btn-primary" type="submit">Илова кардан</button>
    </form>
  </section>
</div>
