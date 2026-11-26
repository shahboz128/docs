<?php
/** @var \App\Core\View $this */
$map = [
    'created'        => ['success', 'Талон сохта шуд.'],
    'updated'        => ['success', 'Талон нав карда шуд.'],
    'deleted'        => ['success', 'Талон нест карда шуд.'],
    'cancelled'      => ['success', 'Талон бекор карда шуд.'],
    'not_waiting'    => ['warning', 'Бекор кардан мумкин нест: талон дар ҳолати «Интизорӣ» нест.'],
    'busy'           => ['warning', 'Талони «Дар хизмат»-ро нест кардан мумкин нест.'],
    'has_refs'       => ['warning', 'Нест кардан мумкин нест: ба ин талон дигар сабтҳо пайванданд.'],
    'svc_created'    => ['success', 'Хизмат илова шуд.'],
    'svc_deleted'    => ['success', 'Хизмат нест карда шуд.'],
    'svc_has_tickets'=> ['warning', 'Хизматро нест кардан мумкин нест: барои он талонҳо ё сабтҳо мавҷуданд. Аввал онҳоро тоза кунед.'],
];
if (isset($map[$msg ?? ''])): [$type, $text] = $map[$msg]; ?>
<div class="alert alert-<?= $type ?>" role="status"><?= $this->e($text) ?></div>
<?php endif; ?>
