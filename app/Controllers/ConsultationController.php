<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Consultation;
use App\Models\ConsultationBodyMeasurements;
use App\Repositories\AppointmentRepository;
use App\Repositories\ConsultationRepository;
use App\Repositories\ConsultationBodyMeasurementsRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;

class ConsultationController
{
    private ConsultationRepository $consultationRepo;
    private ConsultationBodyMeasurementsRepository $measureRepo;
    private AppointmentRepository $appointmentRepo;
    private PatientRepository $patientRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $db  = new Database();
        $pdo = $db->getConnection();

        $this->consultationRepo = new ConsultationRepository($pdo);
        $this->measureRepo      = new ConsultationBodyMeasurementsRepository($pdo);
        $this->appointmentRepo  = new AppointmentRepository($pdo);
        $this->patientRepo      = new PatientRepository($pdo);
        $this->userRepo         = new UserRepository($pdo);
    }

    /**
     * Formulário para registrar consulta a partir de um agendamento.
     * GET  -> mostra formulário
     * POST -> grava consulta + medidas e marca agendamento como CONCLUIDO
     */
    public function create(): void
    {
        $appointmentId = (int)($_GET['appointment_id'] ?? 0);
        if ($appointmentId <= 0) {
            header('Location: /nutrihealth/public/?controller=appointment&action=calendar');
            exit;
        }

        $appointment = $this->appointmentRepo->find($appointmentId);
        if (!$appointment) {
            header('Location: /nutrihealth/public/?controller=appointment&action=calendar&msg=appointment_not_found');
            exit;
        }

        // GET: abre formulário (não busca consulta aqui para decidir msg=no_consultation)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $patient = $this->patientRepo->find($appointment->patientId);
            $nutritionist = $this->userRepo->find($appointment->nutritionistId);

            $this->renderView('consultation/create', [
                'appointment' => $appointment,
                'patient' => $patient,
                'nutritionist' => $nutritionist,
                'old' => [],
                'errors' => [],
            ]);
            return;
        }

        $patient      = $this->patientRepo->find($appointment->patientId);
        $nutritionist = $this->userRepo->find($appointment->nutritionistId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'weight_kg'            => str_replace(',', '.', $_POST['weight_kg'] ?? ''),
                'height_m'             => str_replace(',', '.', $_POST['height_m'] ?? ''),
                'activity_level'       => $_POST['activity_level'] ?? null,
                'goal'                 => trim($_POST['goal'] ?? ''),
                'dietary_restrictions' => trim($_POST['dietary_restrictions'] ?? ''),
                'diseases'             => trim($_POST['diseases'] ?? ''),
                'medications'          => trim($_POST['medications'] ?? ''),
                'notes'                => trim($_POST['notes'] ?? ''),

                // Dobras
                'triceps_mm'     => str_replace(',', '.', $_POST['triceps_mm'] ?? ''),
                'subscapular_mm' => str_replace(',', '.', $_POST['subscapular_mm'] ?? ''),
                'suprailiac_mm'  => str_replace(',', '.', $_POST['suprailiac_mm'] ?? ''),
                'abdominal_mm'   => str_replace(',', '.', $_POST['abdominal_mm'] ?? ''),
                'thigh_mm'       => str_replace(',', '.', $_POST['thigh_mm'] ?? ''),
                'calf_mm'        => str_replace(',', '.', $_POST['calf_mm'] ?? ''),

                // Circunferências
                'waist_circ_cm'  => str_replace(',', '.', $_POST['waist_circ_cm'] ?? ''),
                'hip_circ_cm'    => str_replace(',', '.', $_POST['hip_circ_cm'] ?? ''),
                'arm_circ_cm'    => str_replace(',', '.', $_POST['arm_circ_cm'] ?? ''),
                'thigh_circ_cm'  => str_replace(',', '.', $_POST['thigh_circ_cm'] ?? ''),
                'calf_circ_cm'   => str_replace(',', '.', $_POST['calf_circ_cm'] ?? ''),
                'body_fat_percent' => str_replace(',', '.', $_POST['body_fat_percent'] ?? ''),
            ];

            $errors = [];

            // Validações básicas
            if ($data['weight_kg'] !== '' && !is_numeric($data['weight_kg'])) {
                $errors[] = 'Peso deve ser numérico.';
            }
            if ($data['height_m'] !== '' && !is_numeric($data['height_m'])) {
                $errors[] = 'Altura deve ser numérica.';
            }

            $weight = $data['weight_kg'] !== '' ? (float)$data['weight_kg'] : null;
            $height = $data['height_m'] !== '' ? (float)$data['height_m'] : null;
            $bmi    = null;

            if ($weight !== null && $height !== null && $height > 0) {
                $bmi = $weight / ($height * $height);
            }

            if (!empty($errors)) {
                $this->renderview('consultation/create', [
                    'appointment'  => $appointment,
                    'patient'      => $patient,
                    'nutritionist' => $nutritionist,
                    'errors'       => $errors,
                    'old'          => $data,
                ]);
                return;
            }

            $consultation = new Consultation(
                id: null,
                appointmentId: $appointmentId,
                consultationDate: date('Y-m-d H:i:s'),
                weightKg: $weight,
                heightM: $height,
                bmi: $bmi,
                activityLevel: $data['activity_level'] ?: null,
                goal: $data['goal'] ?: null,
                dietaryRestrictions: $data['dietary_restrictions'] ?: null,
                diseases: $data['diseases'] ?: null,
                medications: $data['medications'] ?: null,
                notes: $data['notes'] ?: null
            );

            $consultationId = $this->consultationRepo->create($consultation);

            $measure = new ConsultationBodyMeasurements(
                id: null,
                consultationId: $consultationId,
                tricepsMm: $data['triceps_mm'] !== '' ? (float)$data['triceps_mm'] : null,
                subscapularMm: $data['subscapular_mm'] !== '' ? (float)$data['subscapular_mm'] : null,
                suprailiacMm: $data['suprailiac_mm'] !== '' ? (float)$data['suprailiac_mm'] : null,
                abdominalMm: $data['abdominal_mm'] !== '' ? (float)$data['abdominal_mm'] : null,
                thighMm: $data['thigh_mm'] !== '' ? (float)$data['thigh_mm'] : null,
                calfMm: $data['calf_mm'] !== '' ? (float)$data['calf_mm'] : null,
                waistCircCm: $data['waist_circ_cm'] !== '' ? (float)$data['waist_circ_cm'] : null,
                hipCircCm: $data['hip_circ_cm'] !== '' ? (float)$data['hip_circ_cm'] : null,
                armCircCm: $data['arm_circ_cm'] !== '' ? (float)$data['arm_circ_cm'] : null,
                thighCircCm: $data['thigh_circ_cm'] !== '' ? (float)$data['thigh_circ_cm'] : null,
                calfCircCm: $data['calf_circ_cm'] !== '' ? (float)$data['calf_circ_cm'] : null,
                bodyFatPercent: $data['body_fat_percent'] !== '' ? (float)$data['body_fat_percent'] : null
            );

            $this->measureRepo->create($measure);

            // Opcional: marcar agendamento como CONCLUIDO
            $appointment->status = 'CONCLUIDO';
            $this->appointmentRepo->update($appointment);

            header('Location: /nutrihealth/public/?controller=appointment&action=calendar&msg=consultation_created');
            exit;
        }

        $this->view('consultation/create', [
            'appointment'  => $appointment,
            'patient'      => $patient,
            'nutritionist' => $nutritionist,
            'old'          => [],
            'errors'       => [],
        ]);
    }
     
    
    private function renderview(string $path, array $data = []): void
    {
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$path}.php";
    }
    
    public function view(): void
    {
        $appointmentId = (int)($_GET['appointment_id'] ?? 0);

        if ($appointmentId <= 0) {
            header("Location: /nutrihealth/public/?controller=appointment&action=calendar");
            exit;
        }

        $consultation = $this->consultationRepo->findByAppointmentId($appointmentId);

        if (!$consultation) {
            header("Location: /nutrihealth/public/?controller=appointment&action=calendar&msg=no_consultation");
            exit;
        }

        $measurements = $this->measureRepo->findByConsultationId($consultation->id);
        $appointment  = $this->appointmentRepo->find($appointmentId);
        $patient      = $this->patientRepo->find($appointment->patientId);
        $nutritionist = $this->userRepo->find($appointment->nutritionistId);

        $this->renderview("consultation/view", [
            'consultation' => $consultation,
            'measurements' => $measurements,
            'patient'      => $patient,
            'nutritionist' => $nutritionist,
            'appointment'  => $appointment
        ]);
    }

}
