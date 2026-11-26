<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Format;
use PDO;
use Throwable;

/** Тамоми SQL-и ҷадвали tickets дар ҳамин ҷо. Контроллер SELECT наменависад. */
final class TicketRepository
{
    public const PER_PAGE = 10;

    /** Майдонҳое, ки ҳангоми таҳрир сабт мешаванд (рӯйхати сафед: номи сутунҳо ҳеҷ гоҳ аз $_POST намеояд) */
    public const UPDATE_FIELDS = ['service_id', 'client_id', 'notary_id', 'status', 'called_at', 'started_at', 'finished_at'];
    public const CREATE_FIELDS = ['service_id', 'client_id', 'ticket_date', 'ticket_no', 'ticket_type', 'status'];

    /** Қисми умумии SELECT: талон + хизмат + мизоҷ + нотариус */
    private const FROM = '
        FROM tickets t
        JOIN services s    ON s.id = t.service_id
        LEFT JOIN users c  ON c.id = t.client_id
        LEFT JOIN users n  ON n.id = t.notary_id';

    private const SELECT = 'SELECT t.*, s.name AS service_name, s.duration_min,
               c.full_name AS client_name, c.phone AS client_phone, n.full_name AS notary_name';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** % ва _ дар LIKE маънои махсус доранд: бо '!' экран мекунем (ESCAPE '!' дар MySQL ва SQLite кор мекунад) */
    public static function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }

    /** @return array{0: string, 1: array} [WHERE, параметрҳо] — дар матни SQL танҳо НОМИ параметрҳо мераванд */
    private function buildWhere(array $filters): array
    {
        $conditions = [];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            // Номи талон: «B-002», «l3», «L-004» -> навъ + рақам
            $or = [
                "c.full_name LIKE :q1 ESCAPE '!'",
                "c.phone LIKE :q2 ESCAPE '!'",
                "s.name LIKE :q3 ESCAPE '!'",
            ];
            $like = '%' . self::escapeLike($q) . '%';
            $params[':q1'] = $params[':q2'] = $params[':q3'] = $like;
            if (preg_match('/^([BbLl])\s*-?\s*(\d{1,6})$/', $q, $m)) {
                $or[] = '(t.ticket_type = :lt AND t.ticket_no = :ln)';
                $params[':lt'] = strtolower($m[1]) === 'b' ? 'booked' : 'live';
                $params[':ln'] = (int) $m[2];
            }
            $conditions[] = '(' . implode(' OR ', $or) . ')';
        }
        if (!empty($filters['service_id'])) {
            $conditions[] = 't.service_id = :service_id';
            $params[':service_id'] = (int) $filters['service_id'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = 't.status = :status';
            $params[':status'] = (string) $filters['status'];
        }
        if (!empty($filters['ticket_type'])) {
            $conditions[] = 't.ticket_type = :ticket_type';
            $params[':ticket_type'] = (string) $filters['ticket_type'];
        }
        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $params];
    }

    /** @return array{items: array, total: int, page: int, pages: int} */
    public function paginate(array $filters, int $page, int $perPage = self::PER_PAGE): array
    {
        [$where, $params] = $this->buildWhere($filters);

        // 1) чанд сабт ба филтр мувофиқ аст
        $stmt = $this->db->prepare('SELECT COUNT(*) ' . self::FROM . " {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        // 2) рақами саҳифа дар доираи 1…pages
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);

        // 3) саҳифаи лозимӣ; LIMIT/OFFSET бо PARAM_INT
        $sql = self::SELECT . self::FROM . " {$where} ORDER BY t.ticket_date DESC, t.created_at DESC, t.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    /** Барои API-и ҷустуҷӯи зинда: то $limit сабт бе саҳифабандӣ */
    public function search(array $filters, int $limit = 50): array
    {
        return $this->paginate($filters, 1, $limit)['items'];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . self::FROM . ' WHERE t.id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Талони «бесабт» (live) месозад; рақами навбатии рӯз дар транзаксия ҳисоб мешавад. */
    public function create(array $data, string $date): int
    {
        $this->db->beginTransaction();
        try {
            // Дар MySQL сатрҳои ин рӯзро то COMMIT қулф мекунад: ду талони ҳамзамон як рақам намегиранд
            $lock = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
            $stmt = $this->db->prepare(
                "SELECT COALESCE(MAX(ticket_no), 0) + 1 FROM tickets WHERE ticket_date = :d AND ticket_type = 'live'" . $lock
            );
            $stmt->execute([':d' => $date]);
            $no = (int) $stmt->fetchColumn();

            $row = [
                'service_id'  => $data['service_id'],
                'client_id'   => $data['client_id'],
                'ticket_date' => $date,
                'ticket_no'   => $no,
                'ticket_type' => 'live',
                'status'      => 'waiting',
            ];
            $columns = implode(', ', self::CREATE_FIELDS);
            $placeholders = ':' . implode(', :', self::CREATE_FIELDS);
            $stmt = $this->db->prepare("INSERT INTO tickets ({$columns}) VALUES ({$placeholders})");
            $stmt->execute($row);
            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** $current — сатри ҳозираи талон: вақтҳои called_at/started_at/finished_at танҳо як маротиба пур мешаванд */
    public function update(int $id, array $data, array $current, string $now): void
    {
        $row = [
            'service_id'  => $data['service_id'],
            'client_id'   => $data['client_id'],
            'notary_id'   => $data['notary_id'],
            'status'      => $data['status'],
            'called_at'   => $current['called_at'],
            'started_at'  => $current['started_at'],
            'finished_at' => $current['finished_at'],
        ];
        $s = $data['status'];
        if (in_array($s, ['called', 'in_service', 'done'], true))  { $row['called_at']   ??= $now; }
        if (in_array($s, ['in_service', 'done'], true))            { $row['started_at']  ??= $now; }
        if ($s === 'done')                                         { $row['finished_at'] ??= $now; }

        $set = implode(', ', array_map(fn ($f) => "{$f} = :{$f}", self::UPDATE_FIELDS));
        $stmt = $this->db->prepare("UPDATE tickets SET {$set} WHERE id = :id");
        $stmt->execute($row + ['id' => $id]);
    }

    /** Бекор кардан: танҳо талони «waiting». @return bool оё чизе тағйир ёфт */
    public function cancel(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = :id AND status = 'waiting'");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM tickets WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** Мизоҷ дар ин рӯз аллакай талони «waiting» дорад? (қоидаи бо дастрасӣ ба БД) */
    public function hasWaitingTicket(int $clientId, string $date, int $exceptId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tickets WHERE client_id = :c AND ticket_date = :d AND status = 'waiting' AND id <> :id"
        );
        $stmt->execute([':c' => $clientId, ':d' => $date, ':id' => $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** @return array<string,int> шумораи талонҳои рӯз аз рӯи ҳолат (барои саҳифаи асосӣ) */
    public function countByStatus(string $date): array
    {
        $stmt = $this->db->prepare('SELECT status, COUNT(*) AS cnt FROM tickets WHERE ticket_date = :d GROUP BY status');
        $stmt->execute([':d' => $date]);
        $out = array_fill_keys(array_keys(Format::STATUSES), 0);
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['status']] = (int) $row['cnt'];
        }
        return $out;
    }

    /** Охирин талонҳои рӯз */
    public function latest(string $date, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            self::SELECT . self::FROM . ' WHERE t.ticket_date = :d ORDER BY t.created_at DESC, t.id DESC LIMIT :limit'
        );
        $stmt->bindValue(':d', $date);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
