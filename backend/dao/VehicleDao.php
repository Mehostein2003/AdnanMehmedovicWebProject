<?php
require_once __DIR__ . '/BaseDao.php';

class VehicleDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct(
            'vehicles',
            ['brand_id', 'model', 'year', 'price', 'mileage', 'vin', 'status']
        );
    }

    public function getByBrandId(int $brandId): array
    {
        return $this->query(
            'SELECT * FROM vehicles WHERE brand_id = :brand_id',
            [':brand_id' => $brandId]
        );
    }

    public function getByVin(string $vin): ?array
    {
        return $this->queryOne(
            'SELECT * FROM vehicles WHERE vin = :vin',
            [':vin' => $vin]
        );
    }
}
