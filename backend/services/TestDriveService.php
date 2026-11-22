<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/TestDriveDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/VehicleDao.php';

class TestDriveService extends BaseService
{
    private TestDriveDao $testDrives;
    private UserDao $users;
    private VehicleDao $vehicles;

    public function __construct()
    {
        $this->testDrives = new TestDriveDao();
        $this->users = new UserDao();
        $this->vehicles = new VehicleDao();
    }

    public function getAll(): array
    {
        return array_map([$this, 'present'], $this->testDrives->getAll());
    }

    public function getById(int $id): array
    {
        $testDrive = $this->testDrives->getById($id);
        if ($testDrive === null) {
            throw $this->notFound('Test drive');
        }
        return $this->present($testDrive);
    }

    // Casts database values to the JSON types described in the OpenAPI schema.
    private function present(array $testDrive): array
    {
        $testDrive['id'] = (int) $testDrive['id'];
        $testDrive['user_id'] = (int) $testDrive['user_id'];
        $testDrive['vehicle_id'] = (int) $testDrive['vehicle_id'];
        return $testDrive;
    }

    public function create(array $data): array
    {
        $clean = $this->validate($data, true);

        $errors = [];
        if ($this->users->getById($clean['user_id']) === null) {
            $errors['user_id'] = 'User does not exist.';
        }
        $vehicle = $this->vehicles->getById($clean['vehicle_id']);
        if ($vehicle === null) {
            $errors['vehicle_id'] = 'Vehicle does not exist.';
        } elseif ($vehicle['status'] === 'sold') {
            $errors['vehicle_id'] = 'This vehicle is sold and cannot be booked.';
        }
        if (!empty($errors)) {
            throw $this->invalid($errors);
        }

        $this->assertSlotFree($clean['vehicle_id'], $clean['scheduled_at'], $clean['status'], null);

        $id = $this->testDrives->insert($clean);
        return $this->getById($id);
    }

    // Only scheduled_at and status can be changed. The user and the vehicle stay the same.
    public function update(int $id, array $data): array
    {
        $existing = $this->getById($id); // 404 if the test drive does not exist

        $clean = $this->validate($data, false);
        if (empty($clean)) {
            throw $this->invalid(['body' => 'No valid fields to update.']);
        }

        $this->assertSlotFree(
            (int) $existing['vehicle_id'],
            $clean['scheduled_at'] ?? $existing['scheduled_at'],
            $clean['status'] ?? $existing['status'],
            $id
        );

        $this->testDrives->update($id, $clean);
        return $this->getById($id);
    }

    public function delete(int $id): void
    {
        $this->getById($id); // 404 if the test drive does not exist
        $this->testDrives->delete($id);
    }

    // One vehicle cannot have two active test drives at the same time.
    private function assertSlotFree(int $vehicleId, string $scheduledAt, string $status, ?int $ignoreId): void
    {
        if ($status === 'cancelled') {
            return;
        }

        foreach ($this->testDrives->getByVehicleId($vehicleId) as $other) {
            $isOther = (int) $other['id'] !== $ignoreId;
            if ($isOther && $other['status'] !== 'cancelled' && $other['scheduled_at'] === $scheduledAt) {
                throw $this->invalid(['scheduled_at' => 'This vehicle is already booked at that time.']);
            }
        }
    }

    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];
        $clean = [];

        if ($isCreate) {
            $clean['user_id'] = $this->id($data, 'user_id', $errors);
            $clean['vehicle_id'] = $this->id($data, 'vehicle_id', $errors);
        }

        if ($isCreate || array_key_exists('scheduled_at', $data)) {
            $time = $this->futureDateTime($data['scheduled_at'] ?? null);
            if ($time === null) {
                $errors['scheduled_at'] = 'scheduled_at must be a future date and time, e.g. 2026-12-01 10:00.';
            } else {
                $clean['scheduled_at'] = $time;
            }
        }

        if (array_key_exists('status', $data)) {
            $clean['status'] = $this->choice($data, 'status', ['pending', 'approved', 'cancelled'], $errors);
        } elseif ($isCreate) {
            $clean['status'] = 'pending';
        }

        if (!empty($errors)) {
            throw $this->invalid($errors);
        }
        return $clean;
    }

    // Accepts "2026-12-01 10:00", "2026-12-01T10:00" (with or without seconds).
    // Returns "Y-m-d H:i:s" for MySQL, or null if the value is invalid or not in the future.
    private function futureDateTime($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i'] as $format) {
            $time = DateTime::createFromFormat($format, trim($value));
            if ($time !== false && $time->format($format) === trim($value)) {
                return $time > new DateTime() ? $time->format('Y-m-d H:i:s') : null;
            }
        }
        return null;
    }
}
