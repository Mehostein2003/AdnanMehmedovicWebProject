<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('users', ['name', 'email', 'password_hash', 'role']);
        // The password hash is never returned by getAll() and getById().
        $this->selectColumns = 'id, name, email, role, created_at';
    }

    // Returns the full row including password_hash. Needed for login (Milestone 4).
    public function getByEmail(string $email): ?array
    {
        return $this->queryOne(
            'SELECT * FROM users WHERE email = :email',
            [':email' => $email]
        );
    }
}
