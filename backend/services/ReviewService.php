<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/VehicleDao.php';

class ReviewService extends BaseService
{
    private ReviewDao $reviews;
    private UserDao $users;
    private VehicleDao $vehicles;

    public function __construct()
    {
        $this->reviews = new ReviewDao();
        $this->users = new UserDao();
        $this->vehicles = new VehicleDao();
    }

    public function getAll(): array
    {
        return array_map([$this, 'present'], $this->reviews->getAll());
    }

    public function getById(int $id): array
    {
        $review = $this->reviews->getById($id);
        if ($review === null) {
            throw $this->notFound('Review');
        }
        return $this->present($review);
    }

    // Casts database values to the JSON types described in the OpenAPI schema.
    private function present(array $review): array
    {
        $review['id'] = (int) $review['id'];
        $review['user_id'] = (int) $review['user_id'];
        $review['vehicle_id'] = (int) $review['vehicle_id'];
        $review['rating'] = (int) $review['rating'];
        return $review;
    }

    public function create(array $data): array
    {
        $clean = $this->validate($data, true);

        $errors = [];
        if ($this->users->getById($clean['user_id']) === null) {
            $errors['user_id'] = 'User does not exist.';
        }
        if ($this->vehicles->getById($clean['vehicle_id']) === null) {
            $errors['vehicle_id'] = 'Vehicle does not exist.';
        }
        if (!empty($errors)) {
            throw $this->invalid($errors);
        }

        // Business rule: a user reviews a vehicle only once.
        foreach ($this->reviews->getByUserId($clean['user_id']) as $existing) {
            if ((int) $existing['vehicle_id'] === $clean['vehicle_id']) {
                throw $this->invalid(['vehicle_id' => 'This user has already reviewed this vehicle.']);
            }
        }

        $id = $this->reviews->insert($clean);
        return $this->getById($id);
    }

    // Only rating and comment can be changed. The author and the vehicle stay the same.
    public function update(int $id, array $data): array
    {
        $this->getById($id); // 404 if the review does not exist

        $clean = $this->validate($data, false);
        if (empty($clean)) {
            throw $this->invalid(['body' => 'No valid fields to update.']);
        }

        $this->reviews->update($id, $clean);
        return $this->getById($id);
    }

    public function delete(int $id): void
    {
        $this->getById($id); // 404 if the review does not exist
        $this->reviews->delete($id);
    }

    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];
        $clean = [];

        if ($isCreate) {
            $clean['user_id'] = $this->id($data, 'user_id', $errors);
            $clean['vehicle_id'] = $this->id($data, 'vehicle_id', $errors);
        }

        if ($isCreate || array_key_exists('rating', $data)) {
            $clean['rating'] = $this->integer($data, 'rating', 1, 5, $errors);
        }

        // The comment is optional. An empty comment is stored as NULL.
        if (array_key_exists('comment', $data)) {
            $comment = $data['comment'];
            if ($comment !== null && !is_string($comment)) {
                $errors['comment'] = 'comment must be text.';
            } elseif (is_string($comment) && mb_strlen(trim($comment)) > 1000) {
                $errors['comment'] = 'comment must be at most 1000 characters.';
            } else {
                $clean['comment'] = ($comment === null || trim($comment) === '') ? null : trim($comment);
            }
        }

        if (!empty($errors)) {
            throw $this->invalid($errors);
        }
        return $clean;
    }
}
