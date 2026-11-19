<?php
require_once __DIR__ . '/BaseDao.php';

class ReviewDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct(
            'reviews',
            ['user_id', 'vehicle_id', 'rating', 'comment']
        );
    }

    public function getByUserId(int $userId): array
    {
        return $this->query(
            'SELECT * FROM reviews WHERE user_id = :user_id',
            [':user_id' => $userId]
        );
    }

    public function getByVehicleId(int $vehicleId): array
    {
        return $this->query(
            'SELECT * FROM reviews WHERE vehicle_id = :vehicle_id',
            [':vehicle_id' => $vehicleId]
        );
    }
}
