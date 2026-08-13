<?php
namespace App\Repositories;

use App\Models\ServiceType;
use PDO;

class ServiceTypeRepository
{
    public function __construct(private PDO $pdo) {}

    public function all(): array
    {
        $st = $this->pdo->query(
            "SELECT id, code, name, description, default_price, active
             FROM service_type ORDER BY name ASC"
        );
        return array_map(fn(array $r) => ServiceType::fromArray($r), $st->fetchAll());
    }

    public function allActive(): array
    {
        $st = $this->pdo->query(
            "SELECT id, code, name, description, default_price, active
             FROM service_type WHERE active = 1 ORDER BY name ASC"
        );
        return array_map(fn(array $r) => ServiceType::fromArray($r), $st->fetchAll());
    }

    public function find(int $id): ?ServiceType
    {
        $st = $this->pdo->prepare(
            "SELECT id, code, name, description, default_price, active
             FROM service_type WHERE id = ?"
        );
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ? ServiceType::fromArray($row) : null;
    }

    public function create(ServiceType $s): int
    {
        $st = $this->pdo->prepare(
            "INSERT INTO service_type (code, name, description, default_price, active)
             VALUES (:code, :name, :description, :price, :active)"
        );
        $st->execute([
            ':code'        => $s->code,
            ':name'        => $s->name,
            ':description' => $s->description,
            ':price'       => $s->defaultPrice,
            ':active'      => $s->active ? 1 : 0,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(ServiceType $s): void
    {
        $st = $this->pdo->prepare(
            "UPDATE service_type
             SET code = :code, name = :name, description = :description,
                 default_price = :price, active = :active
             WHERE id = :id"
        );
        $st->execute([
            ':code'        => $s->code,
            ':name'        => $s->name,
            ':description' => $s->description,
            ':price'       => $s->defaultPrice,
            ':active'      => $s->active ? 1 : 0,
            ':id'          => $s->id,
        ]);
    }

    public function delete(int $id): bool
    {
        if ($this->isInUse($id)) {
            return false;
        }
        $st = $this->pdo->prepare("DELETE FROM service_type WHERE id = ?");
        $st->execute([$id]);
        return true;
    }

    public function isInUse(int $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM contract WHERE service_type_id = ? LIMIT 1");
        $st->execute([$id]);
        return (bool)$st->fetchColumn();
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $st = $this->pdo->prepare("SELECT 1 FROM service_type WHERE code = ? AND id <> ?");
            $st->execute([$code, $ignoreId]);
        } else {
            $st = $this->pdo->prepare("SELECT 1 FROM service_type WHERE code = ?");
            $st->execute([$code]);
        }
        return (bool)$st->fetchColumn();
    }
}
