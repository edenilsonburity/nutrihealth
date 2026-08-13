<?php
namespace App\Repositories;

use App\Models\ConsultationBodyMeasurements;
use PDO;

class ConsultationBodyMeasurementsRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(ConsultationBodyMeasurements $m): int
    {
        $st = $this->pdo->prepare(
            "INSERT INTO consultation_body_measurements
             (consultation_id, triceps_mm, subscapular_mm, suprailiac_mm,
              abdominal_mm, thigh_mm, calf_mm, waist_circ_cm, hip_circ_cm,
              arm_circ_cm, thigh_circ_cm, calf_circ_cm, body_fat_percent,
              lean_mass_percent, metabolic_age, visceral_fat_level)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );

        $st->execute([
            $m->consultationId,
            $m->tricepsMm,
            $m->subscapularMm,
            $m->suprailiacMm,
            $m->abdominalMm,
            $m->thighMm,
            $m->calfMm,
            $m->waistCircCm,
            $m->hipCircCm,
            $m->armCircCm,
            $m->thighCircCm,
            $m->calfCircCm,
            $m->bodyFatPercent,
            $m->leanMassPercent,
            $m->metabolicAge,
            $m->visceralFatLevel,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Buscar medidas corporais associadas à consulta
     */
    public function findByConsultationId(int $consultationId): ?ConsultationBodyMeasurements
    {
        $st = $this->pdo->prepare("
            SELECT *
            FROM consultation_body_measurements
            WHERE consultation_id = ?
            LIMIT 1
        ");

        $st->execute([$consultationId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ? ConsultationBodyMeasurements::fromArray($row) : null;
    }

     /**
     * Registrar ou atualizar medidas corporais
     * (este item é opcional, mas provavelmente você usará)
     */
    public function save(ConsultationBodyMeasurements $m): int
    {
        if ($m->id) {
            $sql = "
                UPDATE consultation_body_measurements
                SET triceps_mm = ?, subscapular_mm = ?, suprailiac_mm = ?, abdominal_mm = ?,
                    thigh_mm = ?, calf_mm = ?, waist_circ_cm = ?, hip_circ_cm = ?,
                    arm_circ_cm = ?, thigh_circ_cm = ?, calf_circ_cm = ?, body_fat_percent = ?,
                    lean_mass_percent = ?, metabolic_age = ?, visceral_fat_level = ?
                WHERE id = ?
            ";
            $params = [
                $m->tricepsMm, $m->subscapularMm, $m->suprailiacMm, $m->abdominalMm,
                $m->thighMm, $m->calfMm, $m->waistCircCm, $m->hipCircCm,
                $m->armCircCm, $m->thighCircCm, $m->calfCircCm, $m->bodyFatPercent,
                $m->leanMassPercent, $m->metabolicAge, $m->visceralFatLevel,
                $m->id
            ];
        } else {
            $sql = "
                INSERT INTO consultation_body_measurements 
                    (consultation_id, triceps_mm, subscapular_mm, suprailiac_mm, abdominal_mm,
                     thigh_mm, calf_mm, waist_circ_cm, hip_circ_cm, arm_circ_cm, thigh_circ_cm,
                     calf_circ_cm, body_fat_percent, lean_mass_percent, metabolic_age, visceral_fat_level)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $params = [
                $m->consultationId, $m->tricepsMm, $m->subscapularMm, $m->suprailiacMm, $m->abdominalMm,
                $m->thighMm, $m->calfMm, $m->waistCircCm, $m->hipCircCm, $m->armCircCm,
                $m->thighCircCm, $m->calfCircCm, $m->bodyFatPercent,
                $m->leanMassPercent, $m->metabolicAge, $m->visceralFatLevel
            ];
        }

        $st = $this->pdo->prepare($sql);
        $st->execute($params);

        return $m->id ? $m->id : (int)$this->pdo->lastInsertId();
    }
}
