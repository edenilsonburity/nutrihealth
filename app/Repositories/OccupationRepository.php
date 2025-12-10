<?php
namespace App\Repositories;

use App\Models\Occupation;
use PDO;

class OccupationRepository
{
    public function __construct(private PDO $pdo) {}

    public function all(): array
    {
        $st = $this->pdo->query(
            "SELECT id, code, description_occupation
             FROM occupation
             ORDER BY id DESC"
        );
        $rows = $st->fetchAll();
        return array_map(fn(array $r) => Occupation::fromArray($r), $rows);
    }

    public function find(int $id): ?Occupation
    {
        $st = $this->pdo->prepare(
            "SELECT id, code, description_occupation
             FROM occupation
             WHERE id = ?"
        );
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ? Occupation::fromArray($row) : null;
    }

    public function create(Occupation $occupation): int
    {
        $st = $this->pdo->prepare(
            "INSERT INTO occupation (code, description_occupation)
             VALUES (:code, :description)"
        );
        $st->execute([
            ':code'        => $occupation->code,
            ':description' => $occupation->description,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(Occupation $occupation): void
    {
        $st = $this->pdo->prepare(
            "UPDATE occupation
             SET code = :code,
                 description_occupation = :description
             WHERE id = :id"
        );
        $st->execute([
            ':code'        => $occupation->code,
            ':description' => $occupation->description,
            ':id'          => $occupation->id,
        ]);
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare(
            "DELETE FROM occupation WHERE id = ?"
        );
        $st->execute([$id]);
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM occupation
                 WHERE code = ? AND id <> ?"
            );
            $st->execute([$code, $ignoreId]);
        } else {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM occupation
                 WHERE code = ?"
            );
            $st->execute([$code]);
        }

        return (bool)$st->fetchColumn();
    }
}
