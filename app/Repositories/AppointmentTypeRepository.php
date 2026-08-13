<?php
namespace App\Repositories;

use App\Models\AppointmentType;
use PDO;

class AppointmentTypeRepository
{
    public function __construct(private PDO $pdo) {}

    public function all(): array
    {
        $st = $this->pdo->query(
            "SELECT id, code, name, active FROM appointment_type ORDER BY name ASC"
        );
        $rows = $st->fetchAll();
        return array_map(fn(array $r) => AppointmentType::fromArray($r), $rows);
    }

    public function allActive(): array
    {
        $st = $this->pdo->query(
            "SELECT id, code, name, active FROM appointment_type WHERE active = 1 ORDER BY name ASC"
        );
        $rows = $st->fetchAll();
        return array_map(fn(array $r) => AppointmentType::fromArray($r), $rows);
    }

    /**
     * Mapa code => name, útil para exibir rótulos sem precisar de JOIN em toda consulta.
     */
    public function allAsMap(): array
    {
        $rows = $this->pdo->query(
            "SELECT code, name FROM appointment_type ORDER BY name ASC"
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        return $rows;
    }

    public function find(int $id): ?AppointmentType
    {
        $st = $this->pdo->prepare(
            "SELECT id, code, name, active FROM appointment_type WHERE id = ?"
        );
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ? AppointmentType::fromArray($row) : null;
    }

    public function create(AppointmentType $t): int
    {
        $st = $this->pdo->prepare(
            "INSERT INTO appointment_type (code, name, active) VALUES (:code, :name, :active)"
        );
        $st->execute([
            ':code'   => $t->code,
            ':name'   => $t->name,
            ':active' => $t->active ? 1 : 0,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(AppointmentType $t): void
    {
        // O UPDATE CASCADE na FK de appointment.type garante que, se o código mudar,
        // os agendamentos existentes continuam apontando para o tipo certo.
        $st = $this->pdo->prepare(
            "UPDATE appointment_type
             SET code = :code, name = :name, active = :active
             WHERE id = :id"
        );
        $st->execute([
            ':code'   => $t->code,
            ':name'   => $t->name,
            ':active' => $t->active ? 1 : 0,
            ':id'     => $t->id,
        ]);
    }

    public function delete(int $id): bool
    {
        // Não permite excluir um tipo que já está em uso por algum agendamento,
        // para não deixar consultas "órfãs" sem tipo válido.
        if ($this->isInUse($id)) {
            return false;
        }

        $st = $this->pdo->prepare("DELETE FROM appointment_type WHERE id = ?");
        $st->execute([$id]);
        return true;
    }

    public function isInUse(int $id): bool
    {
        $st = $this->pdo->prepare(
            "SELECT 1
             FROM appointment a
             JOIN appointment_type t ON t.code = a.type
             WHERE t.id = ?
             LIMIT 1"
        );
        $st->execute([$id]);
        return (bool)$st->fetchColumn();
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM appointment_type WHERE code = ? AND id <> ?"
            );
            $st->execute([$code, $ignoreId]);
        } else {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM appointment_type WHERE code = ?"
            );
            $st->execute([$code]);
        }

        return (bool)$st->fetchColumn();
    }
}
