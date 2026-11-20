<?php
namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class BrandFlyRepository
{
    private $db;
    public function __construct(Database $db) { $this->db = $db; }

    public function findById($id)
    {
        $this->db->query('SELECT id FROM __brands WHERE id = ? LIMIT 1', $id);
        $row = $this->db->result();
        return $row ? (int) $row->id : null;
    }

    public function findByName($name)
    {
        $this->db->query('SELECT id, name FROM __brands WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1', $name);
        $row = $this->db->result();
        return $row ? (int) $row->id : null;
    }

    public function create(array $data)
    {
        $this->db->query('INSERT INTO __brands SET ?%', $data);
        $id = (int) $this->db->insert_id();
        if (!$id) throw new RuntimeException('Не удалось создать бренд');
        return $id;
    }
}
