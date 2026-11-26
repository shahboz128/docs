<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Format;
use App\Core\Validator;
use App\Repositories\ServiceRepository;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use PDOException;

final class TicketController extends Controller
{
    private TicketRepository $tickets;
    private ServiceRepository $services;
    private UserRepository $users;

    public function __construct()
    {
        parent::__construct();
        $this->tickets  = new TicketRepository();
        $this->services = new ServiceRepository();
        $this->users    = new UserRepository();
    }

    /** GET /tickets — рӯйхат + ҷустуҷӯ + филтрҳо + саҳифабандӣ */
    public function index(): void
    {
        $filters = $this->filtersFromQuery($_GET);
        $page = (int) ($_GET['page'] ?? 1);             // ?page=abc -> 0, ?page=-1 -> -1; paginate() онро ба 1…pages мебарад
        $result = $this->tickets->paginate($filters, $page);

        $this->render('tickets/index', [
            'title'    => 'Талонҳо',
            'active'   => 'tickets',
            'result'   => $result,
            'filters'  => $filters,
            'services' => $this->services->all(),
            'msg'      => (string) ($_GET['msg'] ?? ''),
        ]);
    }

    /** GET /tickets/{id} */
    public function show(int $id): void
    {
        $ticket = $this->findOrFail($id);
        $this->render('tickets/show', [
            'title' => 'Талони ' . Format::label($ticket['ticket_type'], $ticket['ticket_no']),
            'active' => 'tickets',
            'ticket' => $ticket,
            'msg' => (string) ($_GET['msg'] ?? ''),
        ]);
    }

    /** GET /tickets/create */
    public function create(): void
    {
        $this->renderForm(['service_id' => '', 'client_id' => ''], [], null);
    }

    /** POST /tickets/create — PRG */
    public function store(): void
    {
        [$data, $errors] = $this->validate($_POST, null, null);
        if ($errors) {
            $this->renderForm($_POST, $errors, null, 422);   // форма бо қиматҳои ворид шуда ва хатоҳо
            return;
        }
        $id = $this->tickets->create($data, date('Y-m-d'));
        $this->redirect('/tickets/' . $id . '?msg=created');
    }

    /** GET /tickets/{id}/edit */
    public function edit(int $id): void
    {
        $ticket = $this->findOrFail($id);
        $this->renderForm($ticket, [], $ticket);
    }

    /** POST /tickets/{id}/edit — PRG */
    public function update(int $id): void
    {
        $ticket = $this->findOrFail($id);
        [$data, $errors] = $this->validate($_POST, $ticket, $id);
        if ($errors) {
            $this->renderForm($_POST, $errors, $ticket, 422);
            return;
        }
        $this->tickets->update($id, $data, $ticket, date('Y-m-d H:i:s'));
        $this->redirect('/tickets/' . $id . '?msg=updated');
    }

    /** POST /tickets/{id}/cancel — тугмаи модалӣ (лаб 11) */
    public function cancel(int $id): void
    {
        $this->findOrFail($id);
        $ok = $this->tickets->cancel($id);
        $this->redirect('/tickets/' . $id . '?msg=' . ($ok ? 'cancelled' : 'not_waiting'));
    }

    /** POST /tickets/{id}/delete — танҳо POST (на пайванд!) */
    public function delete(int $id): void
    {
        $ticket = $this->findOrFail($id);
        if ($ticket['status'] === 'in_service') {
            $this->redirect('/tickets/' . $id . '?msg=busy');     // қоидаи тиҷоратӣ: талони дар хизмат нест намешавад
        }
        try {
            $this->tickets->delete($id);
        } catch (PDOException $e) {
            if (Database::isForeignKeyError($e)) {
                $this->redirect('/tickets/' . $id . '?msg=has_refs');
            }
            throw $e;
        }
        $this->redirect('/tickets?msg=deleted');
    }

    /** GET /api/tickets?q=… — JSON бо ҳамон майдонҳои заглушкаи лаб 11 (шартнома бо app.js) */
    public function apiList(): void
    {
        $rows = $this->tickets->search($this->filtersFromQuery($_GET), 50);
        $out = array_map(fn (array $t): array => [
            'id'           => (int) $t['id'],
            'ticket_label' => Format::label($t['ticket_type'], $t['ticket_no']),
            'service'      => $t['service_name'],
            'ticket_type'  => $t['ticket_type'],
            'client'       => $t['client_name'],
            'created_at'   => $t['created_at'],
            'status'       => $t['status'],
        ], $rows);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    // ------------------------------------------------------------------ ёрирасонҳо

    /** Филтрҳоро аз рӯйхати сафед гирифтан: қимати нодуруст — гузаронида мешавад */
    private function filtersFromQuery(array $query): array
    {
        $filters = ['q' => trim((string) ($query['q'] ?? ''))];
        $sid = (int) ($query['service_id'] ?? 0);
        $filters['service_id'] = in_array($sid, $this->services->ids(), true) ? $sid : '';
        $st = (string) ($query['status'] ?? '');
        $filters['status'] = isset(Format::STATUSES[$st]) ? $st : '';
        $ty = (string) ($query['ticket_type'] ?? '');
        $filters['ticket_type'] = isset(Format::TYPES[$ty]) ? $ty : '';
        return $filters;
    }

    private function findOrFail(int $id): array
    {
        $ticket = $this->tickets->find($id);
        if ($ticket === null) {
            $this->abort(404, 'Талон ёфт нашуд');
        }
        return $ticket;
    }

    private function renderForm(array $values, array $errors, ?array $ticket, int $status = 200): void
    {
        $this->render('tickets/form', [
            'title'    => $ticket ? 'Таҳрири талон' : 'Талони нав',
            'active'   => 'tickets',
            'values'   => $values,
            'errors'   => $errors,
            'ticket'   => $ticket,
            'services' => $this->services->all(),
            'clients'  => $this->users->byRole('client'),
            'notaries' => $this->users->byRole('notary'),
        ], $status);
    }

    /** @return array{0: array, 1: array} [тозашуда, хатоҳо] */
    private function validate(array $input, ?array $current, ?int $id): array
    {
        $editing = $current !== null;
        $v = new Validator($input);
        $v->required('service_id', 'Хизмат')->in('service_id', $this->services->ids(), 'Хизмат')
          ->in('client_id', $this->users->idsByRole('client'), 'Мизоҷ');
        if ($editing) {
            $v->in('notary_id', $this->users->idsByRole('notary'), 'Нотариус')
              ->required('status', 'Ҳолат')->in('status', array_keys(Format::STATUSES), 'Ҳолат');
        }

        $clean = static fn (string $f): ?int => ($input[$f] ?? '') === '' ? null : (int) $input[$f];
        $data = [
            'service_id' => $clean('service_id'),
            'client_id'  => $clean('client_id'),
            'notary_id'  => $editing ? $clean('notary_id') : null,
            'status'     => $editing ? (string) ($input['status'] ?? '') : 'waiting',
        ];

        $errors = $v->errors();
        // Қоидаҳои баъд аз асосиҳо
        if ($editing && !isset($errors['notary_id']) && !isset($errors['status'])
            && in_array($data['status'], ['called', 'in_service', 'done'], true) && $data['notary_id'] === null) {
            $v->addError('notary_id', 'Барои ин ҳолат нотариус бояд муайян бошад');
        }
        if (!isset($errors['client_id']) && $data['client_id'] !== null && ($data['status'] === 'waiting')) {
            $date = $editing ? $current['ticket_date'] : date('Y-m-d');
            if ($this->tickets->hasWaitingTicket($data['client_id'], $date, $id ?? 0)) {
                $v->addError('client_id', 'Ин мизоҷ имрӯз аллакай талони «Интизорӣ» дорад');
            }
        }
        return [$data, $v->errors()];
    }
}
