<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService
{
    private UserDao $users;

    public function __construct()
    {
        $this->users = new UserDao();
    }

    public function getAll(): array
    {
        return array_map([$this, 'present'], $this->users->getAll());
    }

    public function getById(int $id): array
    {
        $user = $this->users->getById($id);
        if ($user === null) {
            throw $this->notFound('User');
        }
        return $this->present($user);
    }

    // Casts database values to the JSON types described in the OpenAPI schema.
    private function present(array $user): array
    {
        $user['id'] = (int) $user['id'];
        return $user;
    }

    public function create(array $data): array
    {
        $clean = $this->validate($data, true);

        if ($this->users->getByEmail($clean['email']) !== null) {
            throw $this->invalid(['email' => 'This email is already registered.']);
        }

        $id = $this->users->insert($clean);
        return $this->getById($id);
    }

    public function update(int $id, array $data): array
    {
        $this->getById($id); // 404 if the user does not exist

        $clean = $this->validate($data, false);
        if (empty($clean)) {
            throw $this->invalid(['body' => 'No valid fields to update.']);
        }

        if (isset($clean['email'])) {
            $other = $this->users->getByEmail($clean['email']);
            if ($other !== null && (int) $other['id'] !== $id) {
                throw $this->invalid(['email' => 'This email is already registered.']);
            }
        }

        $this->users->update($id, $clean);
        return $this->getById($id);
    }

    public function delete(int $id): void
    {
        $this->getById($id); // 404 if the user does not exist
        $this->users->delete($id);
    }

    // On create every field is checked. On update only the fields that were sent.
    // The plain password is turned into password_hash here and never stored.
    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];
        $clean = [];

        if ($isCreate || array_key_exists('name', $data)) {
            $clean['name'] = $this->text($data, 'name', 2, 100, $errors);
        }

        if ($isCreate || array_key_exists('email', $data)) {
            $email = $this->text($data, 'email', 3, 150, $errors);
            if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'email must be a valid email address.';
            } elseif ($email !== null) {
                $clean['email'] = strtolower($email);
            }
        }

        if ($isCreate || array_key_exists('password', $data)) {
            $password = $data['password'] ?? null;
            // bcrypt only uses the first 72 bytes, so longer passwords are rejected.
            if (!is_string($password) || strlen($password) < 8 || strlen($password) > 72) {
                $errors['password'] = 'password must be between 8 and 72 characters.';
            } else {
                $clean['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        if (array_key_exists('role', $data)) {
            $clean['role'] = $this->choice($data, 'role', ['admin', 'customer'], $errors);
        } elseif ($isCreate) {
            $clean['role'] = 'customer';
        }

        if (!empty($errors)) {
            throw $this->invalid($errors);
        }
        return $clean;
    }
}
