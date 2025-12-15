<?php
namespace App\Models;

class Appointment
{
    public ?int $id;
    public int $patientId;
    public int $nutritionistId;
    public string $startDatetime;
    public ?string $endDatetime;
    public string $type;
    public string $status;
    public ?string $notes;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        ?int $id,
        int $patientId,
        int $nutritionistId,
        string $startDatetime,
        ?string $endDatetime = null,
        string $type = 'PRIMEIRA_CONSULTA',
        string $status = 'PENDENTE',
        ?string $notes = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id             = $id;
        $this->patientId      = $patientId;
        $this->nutritionistId = $nutritionistId;
        $this->startDatetime  = $startDatetime;
        $this->endDatetime    = $endDatetime;
        $this->type           = $type;
        $this->status         = $status;
        $this->notes          = $notes;
        $this->createdAt      = $createdAt;
        $this->updatedAt      = $updatedAt;
    }

    /**
     * Cria um Appointment a partir de um array vindo do banco ou de um form.
     */
    public static function fromArray(array $d): self
    {
        return new self(
            $d['id'] ?? null,
            (int)($d['patient_id']      ?? $d['patientId']      ?? 0),
            (int)($d['nutritionist_id'] ?? $d['nutritionistId'] ?? 0),
            $d['start_datetime']        ?? $d['startDatetime']  ?? '',
            $d['end_datetime']          ?? $d['endDatetime']    ?? null,
            $d['type']                  ?? 'PRIMEIRA_CONSULTA',
            $d['status']                ?? 'PENDENTE',
            $d['notes']                 ?? null,
            $d['created_at']            ?? null,
            $d['updated_at']            ?? null
        );
    }

    /**
     * Converte o objeto em array compatível com o banco.
     */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'patient_id'       => $this->patientId,
            'nutritionist_id'  => $this->nutritionistId,
            'start_datetime'   => $this->startDatetime,
            'end_datetime'     => $this->endDatetime,
            'type'             => $this->type,
            'status'           => $this->status,
            'notes'            => $this->notes,
            'created_at'       => $this->createdAt,
            'updated_at'       => $this->updatedAt,
        ];
    }
}
