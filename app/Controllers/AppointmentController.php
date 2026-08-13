<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use App\Repositories\AppointmentTypeRepository;
use App\Repositories\ConsultationRepository;
use App\Repositories\ConsultationBodyMeasurementsRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;
use PDO;

class AppointmentController
{
    private PDO $pdo;
    private AppointmentRepository $appointmentRepo;
    private AppointmentTypeRepository $appointmentTypeRepo;
    private PatientRepository $patientRepo;
    private UserRepository $userRepo;
    private ConsultationRepository $consultationRepo;
    private ConsultationBodyMeasurementsRepository $measureRepo;

    public function __construct()
    {
        $db                 = new Database();
        $pdo                = $db->getConnection();
        $this->appointmentRepo  = new AppointmentRepository($pdo);
        $this->appointmentTypeRepo = new AppointmentTypeRepository($pdo);
        $this->patientRepo      = new PatientRepository($pdo);
        $this->userRepo         = new UserRepository($pdo);
        $this->consultationRepo = new ConsultationRepository($pdo);
        $this->measureRepo      = new ConsultationBodyMeasurementsRepository($pdo);
    }

    /**
     * Ponto de entrada da Agenda.
     * Aqui podemos reutilizar a mesma lógica da visão de calendário diário.
     */
    public function index(): void
    {
        $this->calendar();
    }

    /**
     * Visão de calendário diário.
     * Espera GET ?date=YYYY-MM-DD (opcional) e nutritionist_id (opcional).
     */
    public function calendar(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $nutritionistId = isset($_GET['nutritionist_id']) ? (int)$_GET['nutritionist_id'] : null;

        // Carrega os agendamentos do dia
        $appointments = $this->appointmentRepo->findByDateWithDetails($date, $nutritionistId);

        foreach ($appointments as &$a) {
            $a['has_consultation'] = $this->consultationRepo->findByAppointmentId($a['id']) ? true : false;
        }

        $patients      = $this->patientRepo->all();
        $allUsers      = $this->userRepo->all();
        $nutritionists = array_filter($allUsers, fn($u) => $u->typeUser === 'N');

        // Envia para a view
        $this->view('appointments/calendar', [
            'date'          => $date,
            'appointments'  => $appointments,
            'patients'      => $patients,
            'nutritionists' => $nutritionists,
            'selectedNutritionist' => $nutritionistId,
            'typeLabels'    => $this->appointmentTypeRepo->allAsMap(),
        ]);
    }

    /**
     * Criação de um novo agendamento.
     */
    public function create(): void
    {
        $patients      = $this->patientRepo->all();
        $allUsers      = $this->userRepo->all();
        $nutritionists = array_filter($allUsers, fn($u) => $u->typeUser === 'N');
        $appointmentTypes = $this->appointmentTypeRepo->allActive();
        $defaultType = $appointmentTypes[0]->code ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'patient_id'      => (int)($_POST['patient_id']      ?? 0),
                'nutritionist_id' => (int)($_POST['nutritionist_id'] ?? 0),
                'date'            => $_POST['date'] ?? '',
                'time'            => $_POST['time'] ?? '',
                'type'            => $_POST['type'] ?? $defaultType,
                'status'          => $_POST['status'] ?? 'PENDENTE',
                'notes'           => trim($_POST['notes'] ?? ''),
            ];

            $errors = [];

            if ($data['patient_id'] <= 0) {
                $errors[] = 'Paciente é obrigatório.';
            }

            if ($data['nutritionist_id'] <= 0) {
                $errors[] = 'Nutricionista é obrigatório.';
            }

            if ($data['date'] === '') {
                $errors[] = 'Data da consulta é obrigatória.';
            }

            if ($data['time'] === '') {
                $errors[] = 'Horário da consulta é obrigatório.';
            }

            if (!empty($errors)) {
                $this->view('appointments/create', [
                    'errors'        => $errors,
                    'patients'      => $patients,
                    'nutritionists' => $nutritionists,
                    'appointmentTypes' => $appointmentTypes,
                    'old'           => $data,
                ]);
                return;
            }

            $startDatetime = $data['date'] . ' ' . $data['time'] . ':00';

            $appointment = new Appointment(
                id: null,
                patientId: $data['patient_id'],
                nutritionistId: $data['nutritionist_id'],
                startDatetime: $startDatetime,
                endDatetime: null,
                type: $data['type'],
                status: $data['status'],
                notes: $data['notes']
            );

            $this->appointmentRepo->create($appointment);

            header('Location: ' . BASE_URL . '/?controller=appointment&action=index&msg=created');
            exit;
        }

        $this->view('appointments/create', [
            'patients'      => $patients,
            'nutritionists' => $nutritionists,
            'appointmentTypes' => $appointmentTypes,
        ]);
    }

    /**
     * Edição de agendamento existente.
     */
    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/?controller=appointment&action=index');
            exit;
        }

        $appointment = $this->appointmentRepo->find($id);
        if (!$appointment) {
            header('Location: ' . BASE_URL . '/?controller=appointment&action=index&msg=notfound');
            exit;
        }

        $patients      = $this->patientRepo->all();
        $allUsers      = $this->userRepo->all();
        $nutritionists = array_filter($allUsers, fn($u) => $u->typeUser === 'N');
        $appointmentTypes = $this->appointmentTypeRepo->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'patient_id'      => (int)($_POST['patient_id']      ?? 0),
                'nutritionist_id' => (int)($_POST['nutritionist_id'] ?? 0),
                'date'            => $_POST['date'] ?? '',
                'time'            => $_POST['time'] ?? '',
                'type'            => $_POST['type'] ?? $appointment->type,
                'status'          => $_POST['status'] ?? 'PENDENTE',
                'notes'           => trim($_POST['notes'] ?? ''),
            ];

            $errors = [];

            if ($data['patient_id'] <= 0) {
                $errors[] = 'Paciente é obrigatório.';
            }

            if ($data['nutritionist_id'] <= 0) {
                $errors[] = 'Nutricionista é obrigatório.';
            }

            if ($data['date'] === '') {
                $errors[] = 'Data da consulta é obrigatória.';
            }

            if ($data['time'] === '') {
                $errors[] = 'Horário da consulta é obrigatório.';
            }

            if (!empty($errors)) {
                $this->view('appointments/edit', [
                    'errors'        => $errors,
                    'patients'      => $patients,
                    'nutritionists' => $nutritionists,
                    'appointmentTypes' => $appointmentTypes,
                    'appointment'   => $appointment,
                    'old'           => $data,
                ]);
                return;
            }

            $startDatetime = $data['date'] . ' ' . $data['time'] . ':00';

            $appointment->patientId      = $data['patient_id'];
            $appointment->nutritionistId = $data['nutritionist_id'];
            $appointment->startDatetime  = $startDatetime;
            $appointment->type           = $data['type'];
            $appointment->status         = $data['status'];
            $appointment->notes          = $data['notes'];

            $this->appointmentRepo->update($appointment);

            header('Location: ' . BASE_URL . '/?controller=appointment&action=index&msg=updated');
            exit;
        }

        // Quebra data/hora para preencher o form
        $dt  = new \DateTime($appointment->startDatetime);
        $old = [
            'patient_id'      => $appointment->patientId,
            'nutritionist_id' => $appointment->nutritionistId,
            'date'            => $dt->format('Y-m-d'),
            'time'            => $dt->format('H:i'),
            'type'            => $appointment->type,
            'status'          => $appointment->status,
            'notes'           => $appointment->notes,
        ];

        $this->view('appointments/edit', [
            'patients'      => $patients,
            'nutritionists' => $nutritionists,
            'appointmentTypes' => $appointmentTypes,
            'appointment'   => $appointment,
            'old'           => $old,
        ]);
    }

    /**
     * Exclui um agendamento.
     */
    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->appointmentRepo->delete($id);
        }

        header('Location: ' . BASE_URL . '/?controller=appointment&action=index&msg=deleted');
        exit;
    }

    /**
     * Helper para carregar views (mesmo padrão dos outros controllers).
     */
    private function view(string $path, array $data = []): void
    {
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$path}.php";
    }
}
