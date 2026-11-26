<?php /** @var \App\Core\View $this */ ?>
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <h1 class="me-auto mb-0">Ҳисобот: талонҳо аз рӯи хизмат</h1>
  <form method="get" action="<?= $this->url('/reports/queue') ?>" class="d-flex gap-2">
    <label class="visually-hidden" for="days">Давра</label>
    <select class="form-select" id="days" name="days">
<?php foreach ([1 => 'Имрӯз', 7 => '7 рӯз', 30 => '30 рӯз', 365 => 'Сол'] as $d => $label): ?>
      <option value="<?= $d ?>"<?= $days === $d ? ' selected' : '' ?>><?= $label ?></option>
<?php endforeach; ?>
    </select>
    <button class="btn btn-outline-primary" type="submit">Нишон додан</button>
  </form>
</div>
<p class="text-muted">Давра: аз <?= $this->e(\App\Core\Format::date($from)) ?> то имрӯз.</p>
<div class="card"><div class="table-responsive">
  <table class="table align-middle mb-0">
    <caption class="visually-hidden">Шумораи талонҳо аз рӯи хизмат ва ҳолат</caption>
    <thead><tr><th scope="col">Хизмат</th><th scope="col" class="text-end">Ҳамагӣ</th><th scope="col" class="text-end">Анҷомёфта</th><th scope="col" class="text-end">Интизорӣ</th><th scope="col" class="text-end">Наомада</th><th scope="col" class="text-end">Бекоршуда</th><th scope="col" class="text-end">Сабти пешакӣ</th></tr></thead>
    <tbody>
<?php foreach ($rows as $r): ?>
      <tr><th scope="row"><?= $this->e($r['name']) ?></th>
        <td class="text-end"><?= (int) $r['total'] ?></td><td class="text-end"><?= (int) $r['done'] ?></td><td class="text-end"><?= (int) $r['waiting'] ?></td>
        <td class="text-end"><?= (int) $r['no_show'] ?></td><td class="text-end"><?= (int) $r['cancelled'] ?></td><td class="text-end"><?= (int) $r['booked'] ?></td></tr>
<?php endforeach; ?>
    </tbody>
    <tfoot><tr class="fw-bold"><th scope="row">Ҷамъ</th>
      <td class="text-end"><?= $totals['total'] ?></td><td class="text-end"><?= $totals['done'] ?></td><td class="text-end"><?= $totals['waiting'] ?></td>
      <td class="text-end"><?= $totals['no_show'] ?></td><td class="text-end"><?= $totals['cancelled'] ?></td><td class="text-end"><?= $totals['booked'] ?></td></tr></tfoot>
  </table>
</div></div>
