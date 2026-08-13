<?php
namespace App\Models;

class Contract
{
    public ?int $id;
    public int $patientId;
    public int $serviceTypeId;
    public float $totalValue;
    public int $installments;
    public string $paymentCondition; // PIX, CARTAO, BOLETO
    public string $startDate;
    public string $status; // ATIVO, CONCLUIDO, CANCELADO
    public ?string $notes;
    public ?string $infinitepayLink;
    public ?string $infinitepayOrderNsu;
    public ?string $infinitepayStatus;
    public ?string $createdAt;
    public ?string $updatedAt;

    // Campos trazidos via JOIN, só leitura (para telas de listagem/impressão)
    public ?string $patientName = null;
    public ?string $patientCpf = null;
    public ?string $patientAddress = null;
    public ?string $patientRg = null;
    public ?string $patientNationality = null;
    public ?string $patientMaritalStatus = null;
    public ?string $patientCep = null;
    public ?string $patientOccupation = null;
    public ?string $serviceTypeName = null;

    public function __construct(
        ?int $id,
        int $patientId,
        int $serviceTypeId,
        float $totalValue,
        int $installments = 1,
        string $paymentCondition = 'PIX',
        string $startDate = '',
        string $status = 'ATIVO',
        ?string $notes = null,
        ?string $infinitepayLink = null,
        ?string $infinitepayOrderNsu = null,
        ?string $infinitepayStatus = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id                   = $id;
        $this->patientId            = $patientId;
        $this->serviceTypeId        = $serviceTypeId;
        $this->totalValue           = $totalValue;
        $this->installments         = $installments;
        $this->paymentCondition     = $paymentCondition;
        $this->startDate            = $startDate;
        $this->status               = $status;
        $this->notes                = $notes;
        $this->infinitepayLink      = $infinitepayLink;
        $this->infinitepayOrderNsu  = $infinitepayOrderNsu;
        $this->infinitepayStatus    = $infinitepayStatus;
        $this->createdAt            = $createdAt;
        $this->updatedAt            = $updatedAt;
    }

    public static function fromArray(array $d): self
    {
        $c = new self(
            $d['id'] ?? null,
            (int)($d['patient_id'] ?? 0),
            (int)($d['service_type_id'] ?? 0),
            (float)($d['total_value'] ?? 0),
            (int)($d['installments'] ?? 1),
            $d['payment_condition'] ?? 'PIX',
            $d['start_date'] ?? '',
            $d['status'] ?? 'ATIVO',
            $d['notes'] ?? null,
            $d['infinitepay_link'] ?? null,
            $d['infinitepay_order_nsu'] ?? null,
            $d['infinitepay_status'] ?? null,
            $d['created_at'] ?? null,
            $d['updated_at'] ?? null
        );

        $c->patientName      = $d['patient_name'] ?? null;
        $c->patientCpf       = $d['patient_cpf'] ?? null;
        $c->patientAddress   = $d['patient_address'] ?? null;
        $c->patientRg              = $d['patient_rg'] ?? null;
        $c->patientNationality     = $d['patient_nationality'] ?? null;
        $c->patientMaritalStatus   = $d['patient_marital_status'] ?? null;
        $c->patientCep             = $d['patient_cep'] ?? null;
        $c->patientOccupation      = $d['patient_occupation'] ?? null;
        $c->serviceTypeName  = $d['service_type_name'] ?? null;

        return $c;
    }

    /**
     * Valor de cada parcela (o total dividido igualmente; a última parcela
     * absorve a diferença de centavos de arredondamento).
     */
    public function installmentValue(): float
    {
        if ($this->installments <= 1) {
            return $this->totalValue;
        }
        return round($this->totalValue / $this->installments, 2);
    }

    public static function paymentConditionLabel(string $code): string
    {
        return match ($code) {
            'PIX'    => 'Pix',
            'CARTAO' => 'Cartão',
            'BOLETO' => 'Boleto',
            default  => $code,
        };
    }

    public static function statusLabel(string $code): string
    {
        return match ($code) {
            'ATIVO'     => 'Ativo',
            'CONCLUIDO' => 'Concluído',
            'CANCELADO' => 'Cancelado',
            default     => $code,
        };
    }
}
