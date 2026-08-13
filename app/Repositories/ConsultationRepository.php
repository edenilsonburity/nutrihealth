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
            meta,
            dietary_restrictions,
            diseases,
            medications,
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
        $consultation->appointmentId,
        $consultation->consultationDate,
        $consultation->weightKg,
        $consultation->heightM,
        $consultation->bmi,
        $consultation->activityLevel,
        $consultation->goal,
        $consultation->meta,
        $consultation->dietaryRestrictions,
        $consultation->diseases,
        $consultation->medications,
        $consultation->notes
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Retorna todas as consultas de um paciente (ordenadas por data, mais antiga primeiro).
     * Usado para: detectar se é a 1ª consulta do paciente, trazer a "meta" definida
     * na 1ª consulta, e trazer o "Objetivo (Queixa)" e a altura da última consulta.
     */
    public function findAllByPatientId(int $patientId): array
    {
        $sql = "
            SELECT c.*
            FROM consultation c
            JOIN appointment a ON a.id = c.appointment_id
            WHERE a.patient_id = ?
            ORDER BY c.consultation_date ASC, c.id ASC
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([$patientId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($r) => Consultation::fromArray($r), $rows);
    }

    public function searchWithDetails(
        ?int    $nutritionistId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $patientName,
        ?string $type
    ): array
    {
        $sql = "
            SELECT
                c.id              AS consultation_id,
                c.consultation_date,
                a.id              AS appointment_id,
                a.start_datetime,
                a.type,
                at.name           AS type_name,
                a.status,
                p.id              AS patient_id,
                p.name_patient    AS patient_name,
                p.cellphone       AS patient_cellphone,
                u.name            AS nutritionist_name
            FROM consultation c
            JOIN appointment a ON a.id = c.appointment_id
            JOIN patient p     ON p.id = a.patient_id
            JOIN `user` u      ON u.id = a.nutritionist_id
            LEFT JOIN appointment_type at ON at.code = a.type
            WHERE 1=1
        ";

        $params = [];

        // Filtra por nutricionista logado (quando aplicável)
        if ($nutritionistId !== null && $nutritionistId > 0) {
            $sql .= " AND a.nutritionist_id = :nutr";
            $params['nutr'] = $nutritionistId;
        }

        // Filtra por nome do paciente (LIKE)
        if ($patientName !== null && trim($patientName) !== '') {
            $sql .= " AND p.name_patient LIKE :pname";
            $params['pname'] = '%' . trim($patientName) . '%';
        }

        // Filtra por tipo de consulta (appointment.type)
        if ($type !== null && $type !== '') {
            $sql .= " AND a.type = :type";
            $params['type'] = $type;
        }

        // Filtro por datas (intervalo)
        // Aqui uso DATE(c.consultation_date) — a “data da consulta registrada”
        if ($dateFrom !== null && $dateFrom !== '') {
            $sql .= " AND DATE(c.consultation_date) >= :dfrom";
            $params['dfrom'] = $dateFrom;
        }

        if ($dateTo !== null && $dateTo !== '') {
            $sql .= " AND DATE(c.consultation_date) <= :dto";
            $params['dto'] = $dateTo;
        }

        $sql .= " ORDER BY p.name_patient, c.consultation_date DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a série histórica de evolução (peso/IMC/%gordura/dobras/circunferências)
     * de um paciente, ordenada por data da consulta.
     *
     * Se $nutritionistId for informado, restringe aos agendamentos desse nutricionista.
     */
    public function getEvolutionByPatient(int $patientId, ?int $nutritionistId = null): array
    {
        $sql = "
            SELECT
                c.consultation_date,
                c.weight_kg,
                c.bmi,
                bm.body_fat_percent,
                bm.lean_mass_percent,
                bm.metabolic_age,
                bm.visceral_fat_level,

                bm.triceps_mm,
                bm.subscapular_mm,
                bm.suprailiac_mm,
                bm.abdominal_mm,
                bm.thigh_mm,
                bm.calf_mm,

                bm.waist_circ_cm,
                bm.hip_circ_cm,
                bm.arm_circ_cm,
                bm.thigh_circ_cm,
                bm.calf_circ_cm
            FROM consultation c
            JOIN appointment a ON a.id = c.appointment_id
            LEFT JOIN consultation_body_measurements bm ON bm.consultation_id = c.id
            WHERE a.patient_id = :pid
        ";

        $params = ['pid' => $patientId];

      //  if ($nutritionistId !== null && $nutritionistId > 0) {
        //    $sql .= " AND a.nutritionist_id = :nid";
          //  $params['nid'] = $nutritionistId;
        //}

        $sql .= " ORDER BY c.consultation_date ASC";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
