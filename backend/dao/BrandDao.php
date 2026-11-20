<?php
require_once __DIR__ . '/BaseDao.php';

class BrandDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('brands', ['name', 'country']);
    }

    public function getByName(string $name): ?array
    {
        return $this->queryOne(
            'SELECT * FROM brands WHERE name = :name',
            [':name' => $name]
        );
    }
}
