<?php
namespace App\Models;

class ConsultationBodyMeasurements
{
    public ?int $id;
    public int $consultationId;
    public ?float $tricepsMm;
    public ?float $subscapularMm;
    public ?float $suprailiacMm;
    public ?float $abdominalMm;
    public ?float $thighMm;
    public ?float $calfMm;
    public ?float $waistCircCm;
    public ?float $hipCircCm;
    public ?float $armCircCm;
    public ?float $thighCircCm;
    public ?float $calfCircCm;
    public ?float $bodyFatPercent;
    public ?string $createdAt;

    public function __construct(
        ?int $id,
        int $consultationId,
        ?float $tricepsMm = null,
        ?float $subscapularMm = null,
        ?float $suprailiacMm = null,
        ?float $abdominalMm = null,
        ?float $thighMm = null,
        ?float $calfMm = null,
        ?float $waistCircCm = null,
        ?float $hipCircCm = null,
        ?float $armCircCm = null,
        ?float $thighCircCm = null,
        ?float $calfCircCm = null,
        ?float $bodyFatPercent = null,
        ?string $createdAt = null
    ) {
        $this->id              = $id;
        $this->consultationId  = $consultationId;
        $this->tricepsMm       = $tricepsMm;
        $this->subscapularMm   = $subscapularMm;
        $this->suprailiacMm    = $suprailiacMm;
        $this->abdominalMm     = $abdominalMm;
        $this->thighMm         = $thighMm;
        $this->calfMm          = $calfMm;
        $this->waistCircCm     = $waistCircCm;
        $this->hipCircCm       = $hipCircCm;
        $this->armCircCm       = $armCircCm;
        $this->thighCircCm     = $thighCircCm;
        $this->calfCircCm      = $calfCircCm;
        $this->bodyFatPercent  = $bodyFatPercent;
        $this->createdAt       = $createdAt;
    }

    public static function fromArray(array $d): self
    {
        return new self(
            $d['id'] ?? null,
            (int)($d['consultation_id'] ?? $d['consultationId']),
            isset($d['triceps_mm']) ? (float)$d['triceps_mm'] : null,
            isset($d['subscapular_mm']) ? (float)$d['subscapular_mm'] : null,
            isset($d['suprailiac_mm']) ? (float)$d['suprailiac_mm'] : null,
            isset($d['abdominal_mm']) ? (float)$d['abdominal_mm'] : null,
            isset($d['thigh_mm']) ? (float)$d['thigh_mm'] : null,
            isset($d['calf_mm']) ? (float)$d['calf_mm'] : null,
            isset($d['waist_circ_cm']) ? (float)$d['waist_circ_cm'] : null,
            isset($d['hip_circ_cm']) ? (float)$d['hip_circ_cm'] : null,
            isset($d['arm_circ_cm']) ? (float)$d['arm_circ_cm'] : null,
            isset($d['thigh_circ_cm']) ? (float)$d['thigh_circ_cm'] : null,
            isset($d['calf_circ_cm']) ? (float)$d['calf_circ_cm'] : null,
            isset($d['body_fat_percent']) ? (float)$d['body_fat_percent'] : null,
            $d['created_at'] ?? null
        );
    }
}
