<?php
namespace App\Repositories;

use PDO;

class ReportRepository
{
    public function __construct(private PDO $pdo) {}

    public function getSummary(): array
    {
        $total = (int)$this->pdo->query("SELECT COUNT(*) FROM appointment")->fetchColumn();

        $counts = $this->pdo->query("
            SELECT status, COUNT(*) AS total
            FROM appointment
            GROUP BY status
        ")->fetchAll(PDO::FETCH_KEY_PAIR);

        $pend = (int)($counts['PENDENTE'] ?? 0);
        $conf = (int)($counts['CONFIRMADO'] ?? 0);
        $canc = (int)($counts['CANCELADO'] ?? 0);

        $monthTotal = (int)$this->pdo->query("
            SELECT COUNT(*)
            FROM appointment
            WHERE YEAR(start_datetime)=YEAR(CURDATE())
              AND MONTH(start_datetime)=MONTH(CURDATE())
        ")->fetchColumn();

        $next7 = (int)$this->pdo->query("
            SELECT COUNT(*)
            FROM appointment
            WHERE start_datetime >= NOW()
              AND start_datetime < DATE_ADD(NOW(), INTERVAL 7 DAY)
        ")->fetchColumn();

        $confirmRate = $total > 0 ? (int)round(($conf / $total) * 100) : 0;
        $cancelRate  = $total > 0 ? (int)round(($canc / $total) * 100) : 0;

        return [
            'total'        => $total,
            'pending'      => $pend,
            'confirmed'    => $conf,
            'canceled'     => $canc,
            'confirm_rate' => $confirmRate,
            'cancel_rate'  => $cancelRate,
            'month_total'  => $monthTotal,
            'next7_total'  => $next7,
        ];
    }

    public function getCountsByType(): array
    {
        return $this->pdo->query("
            SELECT type, COUNT(*) AS total
            FROM appointment
            GROUP BY type
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingAppointments(int $days = 7): array
    {
        $st = $this->pdo->prepare("
            SELECT a.id,
                   p.name_patient AS patient_name,
                   a.start_datetime,
                   a.type,
                   a.status
            FROM appointment a
            JOIN patient p ON p.id = a.patient_id
            WHERE a.start_datetime >= NOW()
              AND a.start_datetime < DATE_ADD(NOW(), INTERVAL ? DAY)
            ORDER BY a.start_datetime ASC
            LIMIT 20
        ");
        $st->execute([$days]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentAppointments(int $limit = 10): array
    {
        $st = $this->pdo->prepare("
            SELECT a.id,
                   p.name_patient AS patient_name,
                   a.start_datetime,
                   a.type,
                   a.status
            FROM appointment a
            JOIN patient p ON p.id = a.patient_id
            ORDER BY a.id DESC
            LIMIT ?
        ");
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAppointmentsForCsv(): array
    {
        return $this->pdo->query("
            SELECT a.id,
                   p.name_patient AS patient_name,
                   u.name AS nutritionist_name,
                   a.start_datetime,
                   a.end_datetime,
                   a.type,
                   a.status
            FROM appointment a
            JOIN patient p ON p.id = a.patient_id
            JOIN `user` u ON u.id = a.nutritionist_id
            ORDER BY a.start_datetime DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'PRIMEIRA_CONSULTA' => 'Primeira Consulta',
            'RETORNO' => 'Retorno',
            'AVALIACAO_CORPORAL' => 'Avaliação Corporal',
            'ORIENTACAO_NUTRICIONAL' => 'Orientação Nutricional',
            default => $type
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'PENDENTE' => 'Pendente',
            'CONFIRMADO' => 'Confirmado',
            'CONCLUIDO' => 'Concluído',
            'CANCELADO' => 'Cancelado',
            default => $status
        };
    }
}
