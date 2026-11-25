<?php
declare(strict_types=1);

final class DashboardController extends Controller
{
    public function index(): void
    {
        $tickets = new TicketRepository();
        $today = date('Y-m-d');
        $this->render('dashboard', [
            'title'  => 'Асосӣ',
            'active' => 'home',
            'counts' => $tickets->countByStatus($today),
            'latest' => $tickets->latest($today, 5),
            'services' => (new ServiceRepository())->allWithTicketCount(),
        ]);
    }
}
