<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/BrandDao.php';
require_once __DIR__ . '/../dao/VehicleDao.php';

class BrandService extends BaseService
{
    private BrandDao $brands;
    private VehicleDao $vehicles;

    public function __construct()
    {
        $this->brands = new BrandDao();
        $this->vehicles = new VehicleDao();
    }

    public function getAll(): array
    {
        return array_map([$this, 'present'], $this->brands->getAll());
    }

    public function getById(int $id): array
    {
        $brand = $this->brands->getById($id);
        if ($brand === null) {
            throw $this->notFound('Brand');
        }
        return $this->present($brand);
    }

    // Casts database values to the JSON types described in the OpenAPI schema.
    private function present(array $brand): array
    {
        $brand['id'] = (int) $brand['id'];
        return $brand;
    }

    public function create(array $data): array
    {
        $clean = $this->validate($data, true);

        if ($this->brands->getByName($clean['name']) !== null) {
            throw $this->invalid(['name' => 'A brand with this name already exists.']);
        }

        $id = $this->brands->insert($clean);
        return $this->getById($id);
    }

    public function update(int $id, array $data): array
    {
        $this->getById($id); // 404 if the brand does not exist

        $clean = $this->validate($data, false);
        if (empty($clean)) {
            throw $this->invalid(['body' => 'No valid fields to update.']);
        }

        if (isset($clean['name'])) {
            $other = $this->brands->getByName($clean['name']);
            if ($other !== null && (int) $other['id'] !== $id) {
                throw $this->invalid(['name' => 'A brand with this name already exists.']);
            }
        }

        $this->brands->update($id, $clean);
        return $this->getById($id);
    }

    // A brand that still has vehicles cannot be deleted (same rule as the database).
    public function delete(int $id): void
    {
        $this->getById($id); // 404 if the brand does not exist

        if (!empty($this->vehicles->getByBrandId($id))) {
            throw new ServiceException('This brand still has vehicles. Delete or move them first.', 400);
        }
        $this->brands->delete($id);
    }

    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];
        $clean = [];

        if ($isCreate || array_key_exists('name', $data)) {
            $clean['name'] = $this->text($data, 'name', 2, 100, $errors);
        }
        if ($isCreate || array_key_exists('country', $data)) {
            $clean['country'] = $this->text($data, 'country', 2, 100, $errors);
        }

        if (!empty($errors)) {
            throw $this->invalid($errors);
        }
        return $clean;
    }
}
