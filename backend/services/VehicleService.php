<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/VinDecoderService.php';
require_once __DIR__ . '/../dao/VehicleDao.php';
require_once __DIR__ . '/../dao/BrandDao.php';

class VehicleService extends BaseService
{
    private VehicleDao $vehicles;
    private BrandDao $brands;
    private VinDecoderService $vinDecoder;

    public function __construct()
    {
        $this->vehicles = new VehicleDao();
        $this->brands = new BrandDao();
        $this->vinDecoder = new VinDecoderService();
    }

    public function getAll(): array
    {
        return array_map([$this, 'present'], $this->vehicles->getAll());
    }

    public function getById(int $id): array
    {
        $vehicle = $this->vehicles->getById($id);
        if ($vehicle === null) {
            throw $this->notFound('Vehicle');
        }
        return $this->present($vehicle);
    }

    // Casts database values to the JSON types described in the OpenAPI schema.
    private function present(array $vehicle): array
    {
        $vehicle['id'] = (int) $vehicle['id'];
        $vehicle['brand_id'] = (int) $vehicle['brand_id'];
        $vehicle['year'] = (int) $vehicle['year'];
        $vehicle['price'] = (float) $vehicle['price'];
        $vehicle['mileage'] = (int) $vehicle['mileage'];
        return $vehicle;
    }

    public function create(array $data): array
    {
        $clean = $this->validate($data, true);
        $this->checkBrandAndVin($clean, null);

        $id = $this->vehicles->insert($clean);
        return $this->getById($id);
    }

    public function update(int $id, array $data): array
    {
        $this->getById($id); // 404 if the vehicle does not exist

        $clean = $this->validate($data, false);
        if (empty($clean)) {
            throw $this->invalid(['body' => 'No valid fields to update.']);
        }
        $this->checkBrandAndVin($clean, $id);

        $this->vehicles->update($id, $clean);
        return $this->getById($id);
    }

    public function delete(int $id): void
    {
        $this->getById($id); // 404 if the vehicle does not exist
        $this->vehicles->delete($id);
    }

    // External API feature: factory specs decoded from the vehicle's VIN.
    public function getSpecs(int $id): array
    {
        $vehicle = $this->getById($id);
        return $this->vinDecoder->decode($vehicle['vin']);
    }

    // The brand must exist and the VIN must not belong to another vehicle.
    private function checkBrandAndVin(array $clean, ?int $ownId): void
    {
        $errors = [];

        if (isset($clean['brand_id']) && $this->brands->getById($clean['brand_id']) === null) {
            $errors['brand_id'] = 'Brand does not exist.';
        }

        if (isset($clean['vin'])) {
            $other = $this->vehicles->getByVin($clean['vin']);
            if ($other !== null && (int) $other['id'] !== $ownId) {
                $errors['vin'] = 'A vehicle with this VIN already exists.';
            }
        }

        if (!empty($errors)) {
            throw $this->invalid($errors);
        }
    }

    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];
        $clean = [];

        if ($isCreate || array_key_exists('brand_id', $data)) {
            $clean['brand_id'] = $this->id($data, 'brand_id', $errors);
        }
        if ($isCreate || array_key_exists('model', $data)) {
            $clean['model'] = $this->text($data, 'model', 1, 100, $errors);
        }
        if ($isCreate || array_key_exists('year', $data)) {
            // The database allows 1980-2100; the business rule is: not later than next year.
            $clean['year'] = $this->integer($data, 'year', 1980, (int) date('Y') + 1, $errors);
        }
        if ($isCreate || array_key_exists('price', $data)) {
            $clean['price'] = $this->decimal($data, 'price', 0, 99999999.99, $errors);
        }
        if ($isCreate || array_key_exists('mileage', $data)) {
            $clean['mileage'] = $this->integer($data, 'mileage', 0, 2000000, $errors);
        }
        if ($isCreate || array_key_exists('vin', $data)) {
            $vin = is_string($data['vin'] ?? null) ? strtoupper(trim($data['vin'])) : '';
            // 17 characters, letters and digits, without I, O and Q.
            if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {
                $errors['vin'] = 'vin must be 17 letters or digits (I, O and Q are not allowed).';
            } else {
                $clean['vin'] = $vin;
            }
        }
        if (array_key_exists('status', $data)) {
            $clean['status'] = $this->choice($data, 'status', ['available', 'sold'], $errors);
        } elseif ($isCreate) {
            $clean['status'] = 'available';
        }

        if (!empty($errors)) {
            throw $this->invalid($errors);
        }
        return $clean;
    }
}
