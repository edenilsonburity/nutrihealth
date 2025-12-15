<?php
namespace App\Controllers;

use App\Config\Database;
use App\Repositories\ReportRepository;

class ReportController
{
    private \PDO $pdo;
    private ReportRepository $repo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->repo = new ReportRepository($this->pdo);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$view}.php";
    }

    public function index(): void
    {
        $summary  = $this->repo->getSummary();
        $byType   = $this->repo->getCountsByType();
        $upcoming = $this->repo->getUpcomingAppointments(7);
        $recent   = $this->repo->getRecentAppointments(10);

        $this->render('reports/dashboard', [
            'summary'  => $summary,
            'byType'   => $byType,
            'upcoming' => $upcoming,
            'recent'   => $recent,
        ]);
    }

    public function exportCsv(): void
    {
        $rows = $this->repo->getAppointmentsForCsv();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_agendamentos.csv"');

        $out = fopen('php://output', 'w');

        fputcsv($out, ['ID', 'Paciente', 'Nutricionista', 'Início', 'Fim', 'Tipo', 'Status']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['patient_name'],
                $r['nutritionist_name'],
                date('d/m/Y H:i', strtotime($r['start_datetime'])),
                $r['end_datetime'] ? date('d/m/Y H:i', strtotime($r['end_datetime'])) : '',
                ReportRepository::typeLabel($r['type']),
                ReportRepository::statusLabel($r['status']),
            ]);
        }

        fclose($out);
        exit;
    }
}
