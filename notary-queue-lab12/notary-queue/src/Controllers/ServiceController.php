<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Validator;
use App\Repositories\ServiceRepository;
use PDOException;

final class ServiceController extends Controller
{
    private ServiceRepository $services;

    public function __construct()
    {
        parent::__construct();
        $this->services = new ServiceRepository();
    }

    /** GET /services */
    public function index(): void
    {
        $this->renderList([], [], 200);
    }

    /** POST /services/create — PRG */
    public function store(): void
    {
        $v = new Validator($_POST);
        $v->required('name', 'Ном')->maxLength('name', 150, 'Ном')
          ->required('duration_min', 'Давомнокӣ')->integerBetween('duration_min', 1, 480, 'Давомнокӣ (дақиқа)');
        if ($v->fails()) {
            $this->renderList($_POST, $v->errors(), 422);
            return;
        }
        $this->services->create(trim((string) $_POST['name']), (int) $_POST['duration_min']);
        $this->redirect('/services?msg=svc_created');
    }

    /** POST /services/{id}/delete — агар талонҳо дошта бошад, паёми фаҳмо, на 500 */
    public function delete(int $id): void
    {
        if ($this->services->find($id) === null) {
            $this->abort(404, 'Хизмат ёфт нашуд');
        }
        try {
            $this->services->delete($id);
        } catch (PDOException $e) {
            if (Database::isForeignKeyError($e)) {
                $this->redirect('/services?msg=svc_has_tickets');
            }
            throw $e;
        }
        $this->redirect('/services?msg=svc_deleted');
    }

    private function renderList(array $values, array $errors, int $status): void
    {
        $this->render('services/index', [
            'title'    => 'Хизматҳо',
            'active'   => 'services',
            'services' => $this->services->allWithTicketCount(),
            'values'   => $values,
            'errors'   => $errors,
            'msg'      => (string) ($_GET['msg'] ?? ''),
        ], $status);
    }
}
