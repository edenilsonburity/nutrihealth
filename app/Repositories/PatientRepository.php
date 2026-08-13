<?php
namespace App\Repositories;

use App\Models\Patient;
use PDO;

class PatientRepository
{
    public function __construct(private PDO $pdo) {}

    private const BASE_SELECT = "
        SELECT p.id, p.name_patient, p.cpf, p.birth_date, p.phone, p.cellphone, p.email, p.address,
               p.emergency_contact, p.guardian_name, p.status, p.notes,
               p.rg, p.nationality, p.marital_status, p.cep,
               p.idOccupation,
               o.description_occupation AS occupation_description
        FROM patient p
        LEFT JOIN occupation o ON o.id = p.idOccupation
    ";

    public function all(): array
    {
        $st = $this->pdo->query(self::BASE_SELECT . " ORDER BY p.name_patient ASC");
        $rows = $st->fetchAll();

        return array_map(fn($r) => Patient::fromArray($r), $rows);
    }

    public function find(int $id): ?Patient
    {
        $st = $this->pdo->prepare(self::BASE_SELECT . " WHERE p.id = ?");
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ? Patient::fromArray($row) : null;
    }

    public function create(Patient $p): int
    {
        $st = $this->pdo->prepare(
            "INSERT INTO patient
             (name_patient, cpf, birth_date, phone, cellphone, email, address,
              emergency_contact, guardian_name, status, notes,
              rg, nationality, marital_status, cep, idOccupation)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );

        $st->execute([
            $p->fullName,
            $p->cpf,
            $p->birthDate,
            $p->phone,
            $p->cellphone,
            $p->email,
            $p->address,
            $p->emergencyContact,
            $p->guardianName,
            $p->status,
            $p->notes,
            $p->rg,
            $p->nationality,
            $p->maritalStatus,
            $p->cep,
            $p->occupationId,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(Patient $p): void
    {
        $st = $this->pdo->prepare(
            "UPDATE patient SET
               name_patient      = ?,
               cpf               = ?,
               birth_date        = ?,
               phone             = ?,
               cellphone         = ?,
               email             = ?,
               address           = ?,
               emergency_contact = ?,
               guardian_name     = ?,
               status            = ?,
               notes             = ?,
               rg                = ?,
               nationality       = ?,
               marital_status    = ?,
               cep               = ?,
               idOccupation      = ?
             WHERE id = ?"
        );

        $st->execute([
            $p->fullName,
            $p->cpf,
            $p->birthDate,
            $p->phone,
            $p->cellphone,
            $p->email,
            $p->address,
            $p->emergencyContact,
            $p->guardianName,
            $p->status,
            $p->notes,
            $p->rg,
            $p->nationality,
            $p->maritalStatus,
            $p->cep,
            $p->occupationId,
            $p->id,
        ]);
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM patient WHERE id = ?");
        $st->execute([$id]);
    }

    public function cpfExists(string $cpf, ?int $ignoreId = null): bool
    {
        if ($ignoreId) {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM patient
                 WHERE cpf = ? AND id <> ?"
            );
            $st->execute([$cpf, $ignoreId]);
        } else {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM patient
                 WHERE cpf = ?"
            );
            $st->execute([$cpf]);
        }

        return (bool)$st->fetchColumn();
    }
}
