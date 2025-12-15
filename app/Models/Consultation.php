<?php
namespace App\Models;

class Consultation
{
    public ?int $id;
    public int $appointmentId;
    public string $consultationDate; // 'Y-m-d H:i:s'
    public ?float $weightKg;
    public ?float $heightM;
    public ?float $bmi;
    public ?string $activityLevel;
    public ?string $goal;
    public ?string $dietaryRestrictions;
    public ?string $diseases;
    public ?string $medications;
    public ?string $notes;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        ?int $id,
        int $appointmentId,
        string $consultationDate,
        ?float $weightKg = null,
        ?float $heightM = null,
        ?float $bmi = null,
        ?string $activityLevel = null,
        ?string $goal = null,
        ?string $dietaryRestrictions = null,
        ?string $diseases = null,
        ?string $medications = null,
        ?string $notes = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id                  = $id;
        $this->appointmentId       = $appointmentId;
        $this->consultationDate    = $consultationDate;
        $this->weightKg            = $weightKg;
        $this->heightM             = $heightM;
        $this->bmi                 = $bmi;
        $this->activityLevel       = $activityLevel;
        $this->goal                = $goal;
        $this->dietaryRestrictions = $dietaryRestrictions;
        $this->diseases            = $diseases;
        $this->medications         = $medications;
        $this->notes               = $notes;
        $this->createdAt           = $createdAt;
        $this->updatedAt           = $updatedAt;
    }

    public static function fromArray(array $d): self
    {
        return new self(
            $d['id'] ?? null,
            (int)($d['appointment_id'] ?? $d['appointmentId']),
            $d['consultation_date'] ?? $d['consultationDate'],
            isset($d['weight_kg']) ? (float)$d['weight_kg'] : null,
            isset($d['height_m']) ? (float)$d['height_m'] : null,
            isset($d['bmi']) ? (float)$d['bmi'] : null,
            $d['activity_level'] ?? null,
            $d['goal'] ?? null,
            $d['dietary_restrictions'] ?? null,
            $d['diseases'] ?? null,
            $d['medications'] ?? null,
            $d['notes'] ?? null,
            $d['created_at'] ?? null,
            $d['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'appointment_id'       => $this->appointmentId,
            'consultation_date'    => $this->consultationDate,
            'weight_kg'            => $this->weightKg,
            'height_m'             => $this->heightM,
            'bmi'                  => $this->bmi,
            'activity_level'       => $this->activityLevel,
            'goal'                 => $this->goal,
            'dietary_restrictions' => $this->dietaryRestrictions,
            'diseases'             => $this->diseases,
            'medications'          => $this->medications,
            'notes'                => $this->notes,
            'created_at'           => $this->createdAt,
            'updated_at'           => $this->updatedAt,
        ];
    }
}
