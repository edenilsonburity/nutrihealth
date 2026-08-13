<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Consultation;
use App\Models\ConsultationBodyMeasurements;
use App\Repositories\AppointmentRepository;
use App\Repositories\AppointmentTypeRepository;
use App\Repositories\ConsultationRepository;
use App\Repositories\ConsultationBodyMeasurementsRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;

class ConsultationController
{
    private ConsultationRepository $consultationRepo;
    private ConsultationBodyMeasurementsRepository $measureRepo;
    private AppointmentRepository $appointmentRepo;
    private AppointmentTypeRepository $appointmentTypeRepo;
    private PatientRepository $patientRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $db  = new Database();
        $pdo = $db->getConnection();

        $this->consultationRepo = new ConsultationRepository($pdo);
        $this->measureRepo      = new ConsultationBodyMeasurementsRepository($pdo);
        $this->appointmentRepo  = new AppointmentRepository($pdo);
        $this->appointmentTypeRepo = new AppointmentTypeRepository($pdo);
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
            header('Location: ' . BASE_URL . '/?controller=appointment&action=calendar');
            exit;
        }

        $appointment = $this->appointmentRepo->find($appointmentId);
        if (!$appointment) {
            header('Location: ' . BASE_URL . '/?controller=appointment&action=calendar&msg=appointment_not_found');
            exit;
        }

        // GET: abre formulário (não busca consulta aqui para decidir msg=no_consultation)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $patient = $this->patientRepo->find($appointment->patientId);
            $nutritionist = $this->userRepo->find($appointment->nutritionistId);

            $context = $this->buildConsultationContext($patient);

            $old = [];
            // Pré-preenche altura com a última registrada e Objetivo (Queixa) com o da última consulta
            if ($context['lastConsultation'] !== null) {
                if ($context['lastConsultation']->heightM !== null) {
                    $old['height_m'] = str_replace('.', ',', (string)$context['lastConsultation']->heightM);
                }
                if ($context['lastConsultation']->goal !== null) {
                    $old['goal'] = $context['lastConsultation']->goal;
                }
            }

            $this->renderView('consultation/create', [
                'appointment' => $appointment,
                'patient' => $patient,
                'nutritionist' => $nutritionist,
                'old' => $old,
                'errors' => [],
                'patientAge' => $context['patientAge'],
                'isFirstConsultation' => $context['isFirstConsultation'],
                'patientMeta' => $context['patientMeta'],
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
                'meta'                 => trim($_POST['meta'] ?? ''),
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

                // Composição corporal (bioimpedância) — coleta opcional
                'lean_mass_percent'  => str_replace(',', '.', $_POST['lean_mass_percent'] ?? ''),
                'metabolic_age'      => trim($_POST['metabolic_age'] ?? ''),
                'visceral_fat_level' => trim($_POST['visceral_fat_level'] ?? ''),
            ];

            $errors = [];

            // Validações básicas
            if ($data['weight_kg'] !== '' && !is_numeric($data['weight_kg'])) {
                $errors[] = 'Peso deve ser numérico.';
            }
            if ($data['height_m'] !== '' && !is_numeric($data['height_m'])) {
                $errors[] = 'Altura deve ser numérica.';
            }
            if ($data['lean_mass_percent'] !== '' && !is_numeric($data['lean_mass_percent'])) {
                $errors[] = '% de massa magra deve ser numérico.';
            }
            if ($data['metabolic_age'] !== '' && !ctype_digit($data['metabolic_age'])) {
                $errors[] = 'Idade metabólica deve ser um número inteiro.';
            }
            if ($data['visceral_fat_level'] !== '' && !ctype_digit($data['visceral_fat_level'])) {
                $errors[] = 'Gordura visceral deve ser um número inteiro.';
            }

            $weight = $data['weight_kg'] !== '' ? (float)$data['weight_kg'] : null;
            $height = $data['height_m'] !== '' ? (float)$data['height_m'] : null;
            $bmi    = null;

            if ($weight !== null && $height !== null && $height > 0) {
                $bmi = $weight / ($height * $height);
            }

            $context = $this->buildConsultationContext($patient);

            if (!empty($errors)) {
                $this->renderview('consultation/create', [
                    'appointment'  => $appointment,
                    'patient'      => $patient,
                    'nutritionist' => $nutritionist,
                    'errors'       => $errors,
                    'old'          => $data,
                    'patientAge'   => $context['patientAge'],
                    'isFirstConsultation' => $context['isFirstConsultation'],
                    'patientMeta'  => $context['patientMeta'],
                ]);
                return;
            }

            // "Meta" só é definida (e editável) na 1ª consulta do paciente; nas seguintes,
            // é apenas exibida como referência (não duplicamos o valor a cada consulta).
            $metaToSave = $context['isFirstConsultation'] ? ($data['meta'] ?: null) : null;

            $consultation = new Consultation(
                id: null,
                appointmentId: $appointmentId,
                consultationDate: date('Y-m-d H:i:s'),
                weightKg: $weight,
                heightM: $height,
                bmi: $bmi,
                activityLevel: $data['activity_level'] ?: null,
                goal: $data['goal'] ?: null,
                meta: $metaToSave,
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
                bodyFatPercent: $data['body_fat_percent'] !== '' ? (float)$data['body_fat_percent'] : null,
                leanMassPercent: $data['lean_mass_percent'] !== '' ? (float)$data['lean_mass_percent'] : null,
                metabolicAge: $data['metabolic_age'] !== '' ? (int)$data['metabolic_age'] : null,
                visceralFatLevel: $data['visceral_fat_level'] !== '' ? (int)$data['visceral_fat_level'] : null
            );

            $this->measureRepo->create($measure);

            // Opcional: marcar agendamento como CONCLUIDO
            $appointment->status = 'CONCLUIDO';
            $this->appointmentRepo->update($appointment);

            header('Location: ' . BASE_URL . '/?controller=appointment&action=calendar&msg=consultation_created');
            exit;
        }
    }
     
    
    private function renderview(string $path, array $data = []): void
    {
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$path}.php";
    }

    /**
     * Calcula a idade (anos completos) a partir da data de nascimento,
     * na data de referência informada (por padrão, hoje).
     */
    private function calculateAge(?string $birthDate, ?string $atDate = null): ?int
    {
        if (empty($birthDate)) {
            return null;
        }
        try {
            $bd  = new \DateTime($birthDate);
            $ref = $atDate ? new \DateTime($atDate) : new \DateTime('today');
            return $bd->diff($ref)->y;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Monta o contexto usado ao registrar uma nova consulta:
     * - idade atual do paciente
     * - se esta será a 1ª consulta do paciente
     * - a "meta" definida na 1ª consulta (para exibir como referência)
     * - a última consulta registrada (para trazer altura e Objetivo/Queixa anteriores)
     */
    private function buildConsultationContext($patient): array
    {
        $previousConsultations = $patient
            ? $this->consultationRepo->findAllByPatientId((int)$patient->id)
            : [];

        $isFirstConsultation = empty($previousConsultations);
        $firstConsultation   = $previousConsultations[0] ?? null;
        $lastConsultation     = !empty($previousConsultations)
            ? $previousConsultations[count($previousConsultations) - 1]
            : null;

        return [
            'patientAge'          => $patient ? $this->calculateAge($patient->birthDate) : null,
            'isFirstConsultation' => $isFirstConsultation,
            'patientMeta'         => $firstConsultation->meta ?? null,
            'lastConsultation'    => $lastConsultation,
        ];
    }
    
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userType = $_SESSION['user_type'] ?? null;
        $userId   = (int)($_SESSION['user_id'] ?? 0);

        // Se for nutricionista, filtra apenas as consultas dele.
        // Se for admin, mostra todas (nutritionistId = null).
        $nutritionistId = ($userType === 'N' && $userId > 0) ? $userId : null;

        // Filtros via GET
        $dateFrom    = $_GET['date_from'] ?? '';
        $dateTo      = $_GET['date_to'] ?? '';
        $patientName = $_GET['patient_name'] ?? '';
        $type        = $_GET['type'] ?? '';

        $rows = $this->consultationRepo->searchWithDetails(
            $nutritionistId,
            $dateFrom ?: null,
            $dateTo ?: null,
            $patientName ?: null,
            $type ?: null
        );

        $this->renderview('consultation/list', [
            'rows'        => $rows,
            'filters'     => [
                'date_from'    => $dateFrom,
                'date_to'      => $dateTo,
                'patient_name' => $patientName,
                'type'         => $type
            ],
            'isNutri'     => ($userType === 'N'),
            'types'       => $this->appointmentTypeRepo->all(),
        ]);
    }



    public function view(): void
    {
        $appointmentId = (int)($_GET['appointment_id'] ?? 0);
        $from = $_GET['from'] ?? 'agenda';

        if ($appointmentId <= 0) {
            header("Location: /?controller=appointment&action=calendar");
            exit;
        }

        $consultation = $this->consultationRepo->findByAppointmentId($appointmentId);

        if (!$consultation) {
            header("Location: /?controller=appointment&action=calendar&msg=no_consultation");
            exit;
        }

        $measurements = $this->measureRepo->findByConsultationId($consultation->id);
        $appointment  = $this->appointmentRepo->find($appointmentId);
        $patient      = $this->patientRepo->find($appointment->patientId);
        $nutritionist = $this->userRepo->find($appointment->nutritionistId);

        // Idade do paciente na data desta consulta (não a idade "hoje")
        $patientAgeAtConsultation = $patient
            ? $this->calculateAge($patient->birthDate, $consultation->consultationDate)
            : null;

        // A "meta" é definida apenas na 1ª consulta; aqui buscamos ela para mostrar
        // como referência em qualquer consulta posterior também.
        $patientMeta = $consultation->meta;
        if ($patientMeta === null && $patient) {
            $allConsultations = $this->consultationRepo->findAllByPatientId((int)$patient->id);
            $firstConsultation = $allConsultations[0] ?? null;
            $patientMeta = $firstConsultation->meta ?? null;
        }

        // Arquivos de exames anexados (armazenados no servidor)
        $examFiles = $this->listExamFiles((int)$consultation->id);

        $this->renderview("consultation/view", [
            'consultation' => $consultation,
            'measurements' => $measurements,
            'patient'      => $patient,
            'nutritionist' => $nutritionist,
            'appointment'  => $appointment,
            'examFiles'    => $examFiles,
            'from'         => $from,
            'patientAge'   => $patientAgeAtConsultation,
            'patientMeta'  => $patientMeta,
        ]);
    }

    /**
     * Página de evolução do paciente: gráficos de linhas por data da consulta.
     * Permissões:
     *  - Nutricionista (user_type = 'N') vê apenas pacientes/consultas vinculadas a ele.
     *  - Admin vê tudo.
     */
    public function evolution(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $patientId = (int)($_GET['patient_id'] ?? 0);
        $from = $_GET['from'] ?? 'list';

        if ($patientId <= 0) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=index&msg=invalid');
            exit;
        }

        $userType = $_SESSION['user_type'] ?? null;
        $userId   = (int)($_SESSION['user_id'] ?? 0);
        $nutritionistId = ($userType === 'N' && $userId > 0) ? $userId : null;

        $patient = $this->patientRepo->find($patientId);
        if (!$patient) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=index&msg=notfound');
            exit;
        }

        // Idade do paciente (anos completos)
        $patientAge = null;
        if (!empty($patient->birthDate)) {
            try {
                $bd = new \DateTime($patient->birthDate);
                $now = new \DateTime('today');
                $patientAge = $bd->diff($now)->y;
            } catch (\Exception $e) {
                $patientAge = null;
            }
        }

        $rows = $this->consultationRepo->getEvolutionByPatient($patientId, $nutritionistId);

        // Se for nutricionista e não retornou nenhuma linha, pode ser falta de permissão
        // (paciente de outro nutricionista) OU paciente ainda sem consultas.
        // Como o acesso é disparado a partir da listagem, tratamos vazio como "sem dados".

        $labels = [];
        $series = [
            'weight_kg' => [],
            'bmi' => [],
            'body_fat_percent' => [],
            'lean_mass_percent' => [],
            'metabolic_age' => [],
            'visceral_fat_level' => [],

            // Dobras
            'triceps_mm' => [],
            'subscapular_mm' => [],
            'suprailiac_mm' => [],
            'abdominal_mm' => [],
            'thigh_mm' => [],
            'calf_mm' => [],

            // Circunferências
            'waist_circ_cm' => [],
            'hip_circ_cm' => [],
            'arm_circ_cm' => [],
            'thigh_circ_cm' => [],
            'calf_circ_cm' => [],
        ];

        foreach ($rows as $r) {
            $labels[] = date('d/m/Y', strtotime($r['consultation_date']));

            foreach ($series as $k => $_) {
                $v = $r[$k] ?? null;
                $series[$k][] = ($v === null || $v === '') ? null : (float)$v;
            }
        }

        $this->renderview('consultation/evolution', [
            'patient' => $patient,
            'patientAge' => $patientAge,
            'labels'  => $labels,
            'series'  => $series,
            'from'    => $from,
        ]);
    }

    /**
     * Upload de arquivos de exame para a consulta (armazenamento em pasta no servidor).
     */
    public function uploadExam(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $appointmentId = (int)($_GET['appointment_id'] ?? 0);
        $from = $_GET['from'] ?? 'agenda';
        if ($appointmentId <= 0) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=index&msg=invalid');
            exit;
        }

        $appointment = $this->appointmentRepo->find($appointmentId);
        if (!$appointment) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=index&msg=appointment_not_found');
            exit;
        }

        // Permissão: nutricionista só acessa suas próprias consultas; admin pode tudo.
        $userType = $_SESSION['user_type'] ?? null;
        $userId   = (int)($_SESSION['user_id'] ?? 0);
        if ($userType === 'N' && $userId > 0 && $appointment->nutritionistId !== $userId) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=view&appointment_id=' . $appointmentId . '&from=' . urlencode($from) . '&msg=forbidden');
            exit;
        }

        $consultation = $this->consultationRepo->findByAppointmentId($appointmentId);
        if (!$consultation) {
            header('Location: ' . BASE_URL . '/?controller=appointment&action=calendar&msg=no_consultation');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=view&appointment_id=' . $appointmentId . '&from=' . urlencode($from));
            exit;
        }

        if (empty($_FILES['exam_files'])) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=view&appointment_id=' . $appointmentId . '&from=' . urlencode($from) . '&msg=upload_empty');
            exit;
        }

        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $maxSize    = 10 * 1024 * 1024; // 10 MB por arquivo

        $destDir = $this->getExamDir((int)$consultation->id);
        if (!is_dir($destDir) && !mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=view&appointment_id=' . $appointmentId . '&from=' . urlencode($from) . '&msg=upload_dir_error');
            exit;
        }

        $files = $_FILES['exam_files'];
        $total = is_array($files['name']) ? count($files['name']) : 0;
        $saved = 0;

        for ($i = 0; $i < $total; $i++) {
            $name = $files['name'][$i] ?? '';
            $tmp  = $files['tmp_name'][$i] ?? '';
            $err  = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $size = (int)($files['size'][$i] ?? 0);

            if ($err !== UPLOAD_ERR_OK) {
                continue;
            }
            if ($size <= 0 || $size > $maxSize) {
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                continue;
            }

            // Sanitiza nome e evita colisões
            $base = pathinfo($name, PATHINFO_FILENAME);
            $base = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $base);
            $base = trim($base, '_-');
            if ($base === '') {
                $base = 'exame';
            }

            $finalName = date('Ymd_His') . '_' . $base . '.' . $ext;
            $destPath  = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $finalName;

            if (move_uploaded_file($tmp, $destPath)) {
                $saved++;
            }
        }

        $msg = ($saved > 0) ? 'upload_ok' : 'upload_failed';
        header('Location: ' . BASE_URL . '/?controller=consultation&action=view&appointment_id=' . $appointmentId . '&from=' . urlencode($from) . '&msg=' . $msg);
        exit;
    }

    /**
     * Download seguro do exame (arquivo fora de /public).
     */
    public function downloadExam(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $appointmentId = (int)($_GET['appointment_id'] ?? 0);
        $file = (string)($_GET['file'] ?? '');
        if ($appointmentId <= 0 || $file === '') {
            http_response_code(400);
            echo 'Requisição inválida.';
            return;
        }

        $appointment = $this->appointmentRepo->find($appointmentId);
        if (!$appointment) {
            http_response_code(404);
            echo 'Agendamento não encontrado.';
            return;
        }

        // Permissão: nutricionista só acessa seus próprios arquivos; admin pode tudo.
        $userType = $_SESSION['user_type'] ?? null;
        $userId   = (int)($_SESSION['user_id'] ?? 0);
        if ($userType === 'N' && $userId > 0 && $appointment->nutritionistId !== $userId) {
            http_response_code(403);
            echo 'Forbidden.';
            return;
        }

        $consultation = $this->consultationRepo->findByAppointmentId($appointmentId);
        if (!$consultation) {
            http_response_code(404);
            echo 'Consulta não encontrada.';
            return;
        }

        // Evita path traversal
        $safeFile = basename($file);
        $path = $this->getExamDir((int)$consultation->id) . DIRECTORY_SEPARATOR . $safeFile;

        if (!is_file($path)) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            return;
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="' . $safeFile . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    /**
     * Exclusão segura de um exame anexado.
     * Arquivos ficam fora de /public; a exclusão é feita via controller.
     */
    public function deleteExam(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $appointmentId = (int)($_GET['appointment_id'] ?? 0);
        $from = $_GET['from'] ?? 'agenda';
        $file = (string)($_GET['file'] ?? '');

        if ($appointmentId <= 0 || $file === '') {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=index&msg=invalid');
            exit;
        }

        $appointment = $this->appointmentRepo->find($appointmentId);
        if (!$appointment) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=index&msg=appointment_not_found');
            exit;
        }

        // Permissão: nutricionista só pode excluir anexos de suas próprias consultas; admin pode tudo.
        $userType = $_SESSION['user_type'] ?? null;
        $userId   = (int)($_SESSION['user_id'] ?? 0);
        if ($userType === 'N' && $userId > 0 && $appointment->nutritionistId !== $userId) {
            header('Location: ' . BASE_URL . '/?controller=consultation&action=view&appointment_id=' . $appointmentId . '&from=' . urlencode($from) . '&msg=forbidden');
            exit;
        }

        $consultation = $this->consultationRepo->findByAppointmentId($appointmentId);
        if (!$consultation) {
            header('Location: ' . BASE_URL . '/?controller=appointment&action=calendar&msg=no_consultation');
            exit;
        }

        // Evita path traversal
        $safeFile = basename($file);
        $dir = $this->getExamDir((int)$consultation->id);
        $path = $dir . DIRECTORY_SEPARATOR . $safeFile;

        $ok = false;
        if (is_file($path)) {
            $ok = @unlink($path);

            // Se a pasta ficar vazia, remove o diretório da consulta (opcional)
            if ($ok && is_dir($dir)) {
                $remaining = array_values(array_filter(scandir($dir) ?: [], function ($f) {
                    return $f !== '.' && $f !== '..';
                }));
                if (empty($remaining)) {
                    @rmdir($dir);
                }
            }
        }

        $msg = $ok ? 'delete_ok' : 'delete_failed';
        header('Location: ' . BASE_URL . '/?controller=consultation&action=view&appointment_id=' . $appointmentId . '&from=' . urlencode($from) . '&msg=' . $msg);
        exit;
    }

    private function getExamBaseDir(): string
    {
        // Fora do /public (mais seguro)
        $base = dirname(__DIR__, 2);
        return $base . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'exams';
    }

    private function getExamDir(int $consultationId): string
    {
        return $this->getExamBaseDir() . DIRECTORY_SEPARATOR . $consultationId;
    }

    private function listExamFiles(int $consultationId): array
    {
        $dir = $this->getExamDir($consultationId);
        if (!is_dir($dir)) {
            return [];
        }

        $items = array_values(array_filter(scandir($dir) ?: [], function ($f) {
            return $f !== '.' && $f !== '..';
        }));

        $out = [];
        foreach ($items as $f) {
            $path = $dir . DIRECTORY_SEPARATOR . $f;
            if (!is_file($path)) {
                continue;
            }
            $out[] = [
                'name' => $f,
                'size' => filesize($path),
                'mtime' => filemtime($path),
            ];
        }

        // Mais recentes primeiro
        usort($out, fn($a, $b) => ($b['mtime'] <=> $a['mtime']));
        return $out;
    }

}
