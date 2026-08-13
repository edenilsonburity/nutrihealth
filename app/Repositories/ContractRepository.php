<?php
namespace App\Repositories;

use App\Models\Contract;
use PDO;

class ContractRepository
{
    public function __construct(private PDO $pdo) {}

    private const SELECT_WITH_JOINS = "
        SELECT
            c.*,
            p.name_patient   AS patient_name,
            p.cpf            AS patient_cpf,
            p.address        AS patient_address,
            p.rg             AS patient_rg,
            p.nationality    AS patient_nationality,
            p.marital_status AS patient_marital_status,
            p.cep            AS patient_cep,
            occ.description_occupation AS patient_occupation,
            st.name          AS service_type_name
        FROM contract c
        JOIN patient p       ON p.id = c.patient_id
        JOIN service_type st ON st.id = c.service_type_id
        LEFT JOIN occupation occ ON occ.id = p.idOccupation
    ";

    public function all(): array
    {
        $st = $this->pdo->query(self::SELECT_WITH_JOINS . " ORDER BY c.created_at DESC");
        return array_map(fn(array $r) => Contract::fromArray($r), $st->fetchAll());
    }

    public function find(int $id): ?Contract
    {
        $st = $this->pdo->prepare(self::SELECT_WITH_JOINS . " WHERE c.id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ? Contract::fromArray($row) : null;
    }

    public function findAllByPatientId(int $patientId): array
    {
        $st = $this->pdo->prepare(self::SELECT_WITH_JOINS . " WHERE c.patient_id = ? ORDER BY c.created_at DESC");
        $st->execute([$patientId]);
        return array_map(fn(array $r) => Contract::fromArray($r), $st->fetchAll());
    }

    public function create(Contract $c): int
    {
        $st = $this->pdo->prepare(
            "INSERT INTO contract
                (patient_id, service_type_id, total_value, installments, payment_condition,
                 start_date, status, notes)
             VALUES
                (:patient_id, :service_type_id, :total_value, :installments, :payment_condition,
                 :start_date, :status, :notes)"
        );
        $st->execute([
            ':patient_id'        => $c->patientId,
            ':service_type_id'   => $c->serviceTypeId,
            ':total_value'       => $c->totalValue,
            ':installments'      => $c->installments,
            ':payment_condition' => $c->paymentCondition,
            ':start_date'        => $c->startDate,
            ':status'            => $c->status,
            ':notes'             => $c->notes,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(Contract $c): void
    {
        $st = $this->pdo->prepare(
            "UPDATE contract
             SET patient_id = :patient_id, service_type_id = :service_type_id,
                 total_value = :total_value, installments = :installments,
                 payment_condition = :payment_condition, start_date = :start_date,
                 status = :status, notes = :notes
             WHERE id = :id"
        );
        $st->execute([
            ':patient_id'        => $c->patientId,
            ':service_type_id'   => $c->serviceTypeId,
            ':total_value'       => $c->totalValue,
            ':installments'      => $c->installments,
            ':payment_condition' => $c->paymentCondition,
            ':start_date'        => $c->startDate,
            ':status'            => $c->status,
            ':notes'             => $c->notes,
            ':id'                => $c->id,
        ]);
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM contract WHERE id = ?");
        $st->execute([$id]);
    }

    /**
     * Grava o resultado de uma tentativa de geração de cobrança InfinitePay
     * (reservado para a próxima etapa: integração via API para Pix/Cartão).
     */
    public function updateInfinitePaySync(int $id, ?string $link, ?string $orderNsu, ?string $status): void
    {
        $st = $this->pdo->prepare(
            "UPDATE contract
             SET infinitepay_link = ?, infinitepay_order_nsu = ?, infinitepay_status = ?
             WHERE id = ?"
        );
        $st->execute([$link, $orderNsu, $status, $id]);
    }
}
