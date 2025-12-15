<?php
namespace App\Repositories;

use App\Models\Appointment;
use PDO;

class AppointmentRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Retorna todos os agendamentos (ordenados pela data/hora).
     */
    public function all(): array
    {
        $st = $this->pdo->query(
            "SELECT id, patient_id, nutritionist_id, start_datetime, end_datetime,
                    type, status, notes, created_at, updated_at
             FROM appointment
             ORDER BY start_datetime DESC"
        );

        $rows = $st->fetchAll();

        return array_map(fn ($r) => Appointment::fromArray($r), $rows);
    }

    /**
     * Busca um agendamento específico.
     */
    public function find(int $id): ?Appointment
    {
        $st = $this->pdo->prepare(
            "SELECT id, patient_id, nutritionist_id, start_datetime, end_datetime,
                    type, status, notes, created_at, updated_at
             FROM appointment
             WHERE id = ?"
        );
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ? Appointment::fromArray($row) : null;
    }

    /**
     * Cria um novo agendamento.
     * Retorna o ID gerado.
     */
    public function create(Appointment $a): int
    {
        $st = $this->pdo->prepare(
            "INSERT INTO appointment
             (patient_id, nutritionist_id, start_datetime, end_datetime, type, status, notes)
             VALUES (?,?,?,?,?,?,?)"
        );

        $st->execute([
            $a->patientId,
            $a->nutritionistId,
            $a->startDatetime,
            $a->endDatetime,
            $a->type,
            $a->status,
            $a->notes,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Atualiza um agendamento existente.
     */
    public function update(Appointment $a): void
    {
        $st = $this->pdo->prepare(
            "UPDATE appointment
             SET patient_id      = ?,
                 nutritionist_id = ?,
                 start_datetime  = ?,
                 end_datetime    = ?,
                 type            = ?,
                 status          = ?,
                 notes           = ?
             WHERE id = ?"
        );

        $st->execute([
            $a->patientId,
            $a->nutritionistId,
            $a->startDatetime,
            $a->endDatetime,
            $a->type,
            $a->status,
            $a->notes,
            $a->id,
        ]);
    }

    /**
     * Remove um agendamento.
     */
    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM appointment WHERE id = ?");
        $st->execute([$id]);
    }

    /**
     * Retorna agendamentos de um dia específico (para a visão de calendário).
     * 
     * Opcionalmente pode filtrar por nutricionista.
     * 
     * Formato de $date: 'YYYY-MM-DD'
     */
    public function findByDateWithDetails(string $date, ?int $nutritionistId = null): array
    {
        $sql = "
            SELECT 
                a.id,
                a.patient_id,
                a.nutritionist_id,
                a.start_datetime,
                a.end_datetime,
                a.type,
                a.status,
                a.notes,
                p.name_patient AS patient_name,
                u.name         AS nutritionist_name
            FROM appointment a
            JOIN patient p ON p.id = a.patient_id
            JOIN `user` u ON u.id = a.nutritionist_id
            WHERE DATE(a.start_datetime) = :day
        ";

        $params = ['day' => $date];

        if ($nutritionistId !== null && $nutritionistId > 0) {
            $sql .= " AND a.nutritionist_id = :nutr";
            $params['nutr'] = $nutritionistId;
        }

        $sql .= " ORDER BY a.start_datetime ASC";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);

        // Aqui retornamos arrays associativos para facilitar na view do calendário,
        // mas se preferir você pode mapear só o a.* para Appointment::fromArray().
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasConsultation(int $appointmentId): bool
    {
        $st = $this->pdo->prepare("SELECT id FROM consultation WHERE appointment_id = ? LIMIT 1");
        $st->execute([$appointmentId]);
        return (bool)$st->fetchColumn();
    }
}
