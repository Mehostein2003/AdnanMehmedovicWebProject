<?php
require_once __DIR__ . '/../config/Database.php';

// Reusable CRUD methods. Each entity DAO extends this class and only says
// which table it uses and which columns may be written.
// Values always go through prepared statements. Column names come from the
// $columns whitelist of the child class, never from user input.
abstract class BaseDao
{
    protected PDO $connection;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $columns;                // columns that insert() and update() may write
    protected string $selectColumns = '*';   // columns that getAll() and getById() return

    public function __construct(string $table, array $columns)
    {
        $this->connection = Database::connect();
        $this->table = $table;
        $this->columns = $columns;
    }

    // Runs a SELECT with a prepared statement and returns all rows.
    protected function query(string $sql, array $params = []): array
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    // Same as query(), but returns only the first row (or null).
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $rows = $this->query($sql, $params);
        return $rows[0] ?? null;
    }

    // Keeps only the columns this DAO is allowed to write.
    protected function filterData(array $data): array
    {
        return array_intersect_key($data, array_flip($this->columns));
    }

    // READ (GET): all rows.
    public function getAll(): array
    {
        return $this->query("SELECT {$this->selectColumns} FROM `{$this->table}`");
    }

    // READ (GET): one row by primary key, or null.
    public function getById(int $id): ?array
    {
        return $this->queryOne(
            "SELECT {$this->selectColumns} FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id",
            [':id' => $id]
        );
    }

    // CREATE (POST): inserts one row and returns the new id.
    public function insert(array $data): int
    {
        $data = $this->filterData($data);
        if (empty($data)) {
            throw new InvalidArgumentException('No valid fields to insert.');
        }

        $names = array_keys($data);
        $columnList = '`' . implode('`, `', $names) . '`';
        $placeholderList = ':' . implode(', :', $names);

        $statement = $this->connection->prepare(
            "INSERT INTO `{$this->table}` ($columnList) VALUES ($placeholderList)"
        );
        $statement->execute($data);

        return (int) $this->connection->lastInsertId();
    }

    // UPDATE (PUT/PATCH): updates the given columns of one row.
    // Returns true if a row with this id exists, false otherwise.
    public function update(int $id, array $data): bool
    {
        $data = $this->filterData($data);
        if (empty($data)) {
            throw new InvalidArgumentException('No valid fields to update.');
        }

        $assignments = [];
        foreach (array_keys($data) as $name) {
            $assignments[] = "`$name` = :$name";
        }

        $statement = $this->connection->prepare(
            "UPDATE `{$this->table}` SET " . implode(', ', $assignments) .
            " WHERE `{$this->primaryKey}` = :primary_key_value"
        );
        $statement->execute($data + [':primary_key_value' => $id]);

        return $statement->rowCount() > 0;
    }

    // DELETE: removes one row. Returns true if a row was deleted.
    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id"
        );
        $statement->execute([':id' => $id]);

        return $statement->rowCount() > 0;
    }
}
