<?php
namespace App\Repositories;

use App\Models\Consultation;
use PDO;

class ConsultationRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByAppointmentId(int $appointmentId): ?Consultation
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM consultation WHERE appointment_id = ? LIMIT 1"
        );
        $st->execute([$appointmentId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ? Consultation::fromArray($row) : null;
    }

    public function create(Consultation $consultation)
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO consultation (
            appointment_id,
            consultation_date,
            weight_kg,
            height_m,
            bmi,
            activity_level,
            goal,
            dietary_restrictions,
            diseases,
            medications,
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
        $consultation->appointmentId,
        $consultation->consultationDate,
        $consultation->weightKg,
        $consultation->heightM,
        $consultation->bmi,
        $consultation->activityLevel,
        $consultation->goal,
        $consultation->dietaryRestrictions,
        $consultation->diseases,
        $consultation->medications,
        $consultation->notes
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}
