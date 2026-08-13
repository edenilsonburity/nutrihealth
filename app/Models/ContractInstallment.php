<?php
namespace App\Models;

class ContractInstallment
{
    public ?int $id;
    public int $contractId;
    public int $installmentNumber;
    public string $dueDate;
    public float $amount;
    public string $status; // PENDENTE, PAGO, CANCELADO
    public ?string $paidAt;
    public ?float $paidAmount;
    public ?string $paymentMethod;
    public ?string $infinitepayChargeId;
    public ?string $infinitepayLink;
    public ?string $infinitepayTransactionNsu;
    public ?string $infinitepayCaptureMethod;
    public ?string $notes;

    public function __construct(
        ?int $id,
        int $contractId,
        int $installmentNumber,
        string $dueDate,
        float $amount,
        string $status = 'PENDENTE',
        ?string $paidAt = null,
        ?float $paidAmount = null,
        ?string $paymentMethod = null,
        ?string $infinitepayChargeId = null,
        ?string $notes = null,
        ?string $infinitepayLink = null,
        ?string $infinitepayTransactionNsu = null,
        ?string $infinitepayCaptureMethod = null
    ) {
        $this->id                  = $id;
        $this->contractId          = $contractId;
        $this->installmentNumber   = $installmentNumber;
        $this->dueDate             = $dueDate;
        $this->amount              = $amount;
        $this->status              = $status;
        $this->paidAt              = $paidAt;
        $this->paidAmount          = $paidAmount;
        $this->paymentMethod       = $paymentMethod;
        $this->infinitepayChargeId = $infinitepayChargeId;
        $this->notes               = $notes;
        $this->infinitepayLink            = $infinitepayLink;
        $this->infinitepayTransactionNsu  = $infinitepayTransactionNsu;
        $this->infinitepayCaptureMethod   = $infinitepayCaptureMethod;
    }

    public static function fromArray(array $d): self
    {
        return new self(
            $d['id'] ?? null,
            (int)($d['contract_id'] ?? 0),
            (int)($d['installment_number'] ?? 0),
            $d['due_date'] ?? '',
            (float)($d['amount'] ?? 0),
            $d['status'] ?? 'PENDENTE',
            $d['paid_at'] ?? null,
            isset($d['paid_amount']) && $d['paid_amount'] !== null ? (float)$d['paid_amount'] : null,
            $d['payment_method'] ?? null,
            $d['infinitepay_charge_id'] ?? null,
            $d['notes'] ?? null,
            $d['infinitepay_link'] ?? null,
            $d['infinitepay_transaction_nsu'] ?? null,
            $d['infinitepay_capture_method'] ?? null
        );
    }

    /**
     * Está vencida e ainda não paga? (calculado na hora, não fica salvo no banco,
     * assim nunca fica desatualizado por falta de um job rodando).
     */
    public function isOverdue(): bool
    {
        return $this->status === 'PENDENTE' && $this->dueDate < date('Y-m-d');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'PAGO'      => 'Pago',
            'CANCELADO' => 'Cancelado',
            default     => 'Pendente',
        };
    }
}
