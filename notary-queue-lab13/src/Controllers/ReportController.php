<?php
declare(strict_types=1);

final class ReportController extends Controller
{
    /** GET /reports/queue?days=7 — талонҳо аз рӯи хизмат (GROUP BY) */
    public function queue(): void
    {
        $days = (int) ($_GET['days'] ?? 7);
        $days = in_array($days, [1, 7, 30, 365], true) ? $days : 7;
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $rows = (new ServiceRepository())->queueReport($from);

        $totals = ['total' => 0, 'waiting' => 0, 'done' => 0, 'no_show' => 0, 'cancelled' => 0, 'booked' => 0];
        foreach ($rows as $r) {                         // сатри ҷамъбастӣ дар PHP ҳисоб мешавад
            foreach ($totals as $k => $_) {
                $totals[$k] += (int) $r[$k];
            }
        }
        $this->render('reports/queue', [
            'title' => 'Ҳисобот: талонҳо аз рӯи хизмат', 'active' => 'reports',
            'rows' => $rows, 'totals' => $totals, 'days' => $days, 'from' => $from,
        ]);
    }
}
