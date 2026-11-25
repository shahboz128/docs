<?php
declare(strict_types=1);

final class ServiceRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function all(): array
    {
        return $this->db->query('SELECT id, name, duration_min, is_active FROM services ORDER BY name')->fetchAll();
    }

    /** @return int[] */
    public function ids(): array
    {
        return array_map('intval', $this->db->query('SELECT id FROM services')->fetchAll(PDO::FETCH_COLUMN));
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, duration_min, is_active FROM services WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(string $name, int $duration): int
    {
        $stmt = $this->db->prepare('INSERT INTO services (name, duration_min, is_active) VALUES (:name, :duration, 1)');
        $stmt->execute([':name' => $name, ':duration' => $duration]);
        return (int) $this->db->lastInsertId();
    }

    /** Агар ба хизмат талон/сабт пайваст бошад — PDOException (FK, MySQL 1451) */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM services WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** Рӯйхати хизматҳо бо шумораи талонҳо (LEFT JOIN: хизмати бе талон ҳам намоён аст) */
    public function allWithTicketCount(): array
    {
        $sql = 'SELECT s.id, s.name, s.duration_min, COUNT(t.id) AS tickets_count
                FROM services s
                LEFT JOIN tickets t ON t.service_id = s.id
                GROUP BY s.id, s.name, s.duration_min
                ORDER BY s.name';
        return $this->db->query($sql)->fetchAll();
    }

    /** Ҳисобот: талонҳо аз рӯи хизмат ва ҳолат (шарти санаи оғоз дар ON, на WHERE — хизматҳои холӣ намеғоянд) */
    public function queueReport(string $from): array
    {
        $sql = "SELECT s.id, s.name,
                       COUNT(t.id)                                   AS total,
                       COALESCE(SUM(t.status = 'waiting'), 0)        AS waiting,
                       COALESCE(SUM(t.status = 'done'), 0)           AS done,
                       COALESCE(SUM(t.status = 'no_show'), 0)        AS no_show,
                       COALESCE(SUM(t.status = 'cancelled'), 0)      AS cancelled,
                       COALESCE(SUM(t.ticket_type = 'booked'), 0)    AS booked
                FROM services s
                LEFT JOIN tickets t ON t.service_id = s.id AND t.ticket_date >= :from
                GROUP BY s.id, s.name
                ORDER BY s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':from' => $from]);
        return $stmt->fetchAll();
    }
}
