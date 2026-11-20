<?php
namespace ProductsImport\Repositories;

use Database;

class BrandMapRepository
{
    private $db;
    public function __construct(Database $db) { $this->db = $db; }
    public function findFlyBrandId($sourceId)
    {
        $this->db->query('SELECT fly_brand_id FROM __rmbt_brand_import_map WHERE sportfly_brand_id = ?', $sourceId);
        $row = $this->db->result();
        return $row ? (int) $row->fly_brand_id : null;
    }
    public function save($sourceId, $targetId)
    {
        $this->db->query('INSERT INTO __rmbt_brand_import_map (sportfly_brand_id, fly_brand_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE fly_brand_id = VALUES(fly_brand_id)', $sourceId, $targetId);
    }
    public function delete($sourceId)
    {
        $this->db->query('DELETE FROM __rmbt_brand_import_map WHERE sportfly_brand_id = ?', $sourceId);
    }
}
