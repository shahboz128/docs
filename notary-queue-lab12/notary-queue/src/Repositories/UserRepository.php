<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class UserRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Рӯйхати корбарон аз рӯи нақш (client|notary|admin). Роли аз рӯйхати сафед — на аз корбар. */
    public function byRole(string $role): array
    {
        $stmt = $this->db->prepare('SELECT id, full_name, phone FROM users WHERE role = :role AND is_active = 1 ORDER BY full_name');
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll();
    }

    /** @return int[] */
    public function idsByRole(string $role): array
    {
        return array_map('intval', array_column($this->byRole($role), 'id'));
    }
}
