<?php
namespace App\Repositories;

use App\Models\Contract;
use App\Models\ContractInstallment;
use PDO;

class ContractInstallmentRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByContractId(int $contractId): array
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM contract_installment
             WHERE contract_id = ?
             ORDER BY installment_number ASC"
        );
        $st->execute([$contractId]);
        return array_map(fn(array $r) => ContractInstallment::fromArray($r), $st->fetchAll());
    }

    public function find(int $id): ?ContractInstallment
    {
        $st = $this->pdo->prepare("SELECT * FROM contract_installment WHERE id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ? ContractInstallment::fromArray($row) : null;
    }

    public function countByContractId(int $contractId): int
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM contract_installment WHERE contract_id = ?");
        $st->execute([$contractId]);
        return (int)$st->fetchColumn();
    }

    /**
     * Gera as parcelas de um contrato (cadência mensal a partir da data de início),
     * só se ainda não existir nenhuma — chamado automaticamente ao criar o contrato,
     * e de forma preventiva ao abrir a tela de parcelas/impressão (cobre contratos
     * antigos, criados antes desta funcionalidade existir).
     */
    public function ensureGenerated(Contract $contract): void
    {
        if ($this->countByContractId((int)$contract->id) > 0) {
            return;
        }

        $n = max(1, (int)$contract->installments);
        $valorParcela = round($contract->totalValue / $n, 2);
        $inicio = $contract->startDate ? strtotime($contract->startDate) : time();

        $st = $this->pdo->prepare(
            "INSERT INTO contract_installment
                (contract_id, installment_number, due_date, amount, status)
             VALUES (?, ?, ?, ?, 'PENDENTE')"
        );

        for ($i = 1; $i <= $n; $i++) {
            $dueDate = date('Y-m-d', strtotime("+" . ($i - 1) . " months", $inicio));
            $valor = ($i === $n) ? round($contract->totalValue - $valorParcela * ($n - 1), 2) : $valorParcela;
            $st->execute([$contract->id, $i, $dueDate, $valor]);
        }
    }

    public function hasPaidInstallments(int $contractId): bool
    {
        $st = $this->pdo->prepare(
            "SELECT 1 FROM contract_installment WHERE contract_id = ? AND status = 'PAGO' LIMIT 1"
        );
        $st->execute([$contractId]);
        return (bool)$st->fetchColumn();
    }

    /**
     * Apaga e recria as parcelas do zero. Só deve ser chamado quando se tem certeza
     * de que nenhuma parcela já foi paga (ver hasPaidInstallments) — do contrário,
     * o histórico de pagamento seria perdido.
     */
    public function regenerate(Contract $contract): void
    {
        $st = $this->pdo->prepare("DELETE FROM contract_installment WHERE contract_id = ?");
        $st->execute([$contract->id]);
        $this->ensureGenerated($contract);
    }

    /**
     * Grava o link de cobrança gerado e o order_nsu usado, antes do pagamento acontecer.
     */
    public function saveInfinitePayCharge(int $id, string $orderNsu, string $link): void
    {
        $st = $this->pdo->prepare(
            "UPDATE contract_installment
             SET infinitepay_charge_id = ?, infinitepay_link = ?
             WHERE id = ?"
        );
        $st->execute([$orderNsu, $link, $id]);
    }

    public function findByInfinitePayOrderNsu(string $orderNsu): ?ContractInstallment
    {
        $st = $this->pdo->prepare("SELECT * FROM contract_installment WHERE infinitepay_charge_id = ? LIMIT 1");
        $st->execute([$orderNsu]);
        $row = $st->fetch();
        return $row ? ContractInstallment::fromArray($row) : null;
    }

    /**
     * Confirma o pagamento de uma parcela a partir dos dados recebidos no webhook
     * (ou da consulta manual de fallback) da InfinitePay.
     */
    public function confirmInfinitePayPayment(int $id, float $paidAmount, string $captureMethod, ?string $transactionNsu): void
    {
        $method = match ($captureMethod) {
            'pix'          => 'Pix',
            'credit_card'  => 'Cartão',
            default        => $captureMethod,
        };

        $st = $this->pdo->prepare(
            "UPDATE contract_installment
             SET status = 'PAGO', paid_at = NOW(), paid_amount = ?, payment_method = ?,
                 infinitepay_transaction_nsu = ?, infinitepay_capture_method = ?
             WHERE id = ?"
        );
        $st->execute([$paidAmount, $method, $transactionNsu, $captureMethod, $id]);
    }

    public function markAsPaid(int $id, string $paidAt, float $paidAmount, ?string $paymentMethod, ?string $notes): void
    {
        $st = $this->pdo->prepare(
            "UPDATE contract_installment
             SET status = 'PAGO', paid_at = ?, paid_amount = ?, payment_method = ?, notes = ?
             WHERE id = ?"
        );
        $st->execute([$paidAt, $paidAmount, $paymentMethod, $notes, $id]);
    }

    public function markAsPending(int $id): void
    {
        $st = $this->pdo->prepare(
            "UPDATE contract_installment
             SET status = 'PENDENTE', paid_at = NULL, paid_amount = NULL, payment_method = NULL
             WHERE id = ?"
        );
        $st->execute([$id]);
    }

    /**
     * Grava o ID da cobrança InfinitePay vinculada a esta parcela
     * (reservado para a integração de geração automática de cobrança).
     */
    /**
     * Grava o ID da cobrança InfinitePay vinculada a esta parcela
     * (mantido por compatibilidade; use saveInfinitePayCharge() para o fluxo novo).
     */
    public function updateInfinitePayCharge(int $id, ?string $chargeId): void
    {
        $st = $this->pdo->prepare("UPDATE contract_installment SET infinitepay_charge_id = ? WHERE id = ?");
        $st->execute([$chargeId, $id]);
    }

    /**
     * Resumo financeiro de um contrato: total, total pago, total pendente, parcelas atrasadas.
     */
    public function summaryByContractId(int $contractId): array
    {
        $installments = $this->findByContractId($contractId);

        $totalPago = 0.0;
        $totalPendente = 0.0;
        $atrasadas = 0;

        foreach ($installments as $i) {
            if ($i->status === 'PAGO') {
                $totalPago += $i->paidAmount ?? $i->amount;
            } elseif ($i->status === 'PENDENTE') {
                $totalPendente += $i->amount;
                if ($i->isOverdue()) {
                    $atrasadas++;
                }
            }
        }

        return [
            'totalPago'     => $totalPago,
            'totalPendente' => $totalPendente,
            'atrasadas'     => $atrasadas,
            'installments'  => $installments,
        ];
    }
}
