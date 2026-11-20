<?php
require_once __DIR__ . '/BaseDao.php';

class TestDriveDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct(
            'test_drives',
            ['user_id', 'vehicle_id', 'scheduled_at', 'status']
        );
    }

    public function getByUserId(int $userId): array
    {
        return $this->query(
            'SELECT * FROM test_drives WHERE user_id = :user_id',
            [':user_id' => $userId]
        );
    }

    public function getByVehicleId(int $vehicleId): array
    {
        return $this->query(
            'SELECT * FROM test_drives WHERE vehicle_id = :vehicle_id',
            [':vehicle_id' => $vehicleId]
        );
    }
}
