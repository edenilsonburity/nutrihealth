<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Patient;
use App\Repositories\OccupationRepository;
use App\Repositories\PatientRepository;

class PatientController
{
    private PatientRepository $repo;
    private OccupationRepository $occRepo;

    public function __construct()
    {
        $db         = new Database();
        $pdo        = $db->getConnection();
        $this->repo    = new PatientRepository($pdo);
        $this->occRepo = new OccupationRepository($pdo);
    }

    public function index(): void
    {
        $patients = $this->repo->all();
        $this->view('patients/list', ['patients' => $patients]);
    }

    public function create(): void
    {
        $occupations = $this->occRepo->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name_patient'      => trim($_POST['name_patient']         ?? ''),
                'cpf'               => preg_replace('/\D+/', '', $_POST['cpf'] ?? ''),
                'birth_date'        => (trim($_POST['birth_date'] ?? '') !== '') ? $_POST['birth_date'] : null,
                'phone'             => trim($_POST['phone']        ?? ''),
                'cellphone'         => trim($_POST['cellphone']    ?? ''),
                'email'             => trim($_POST['email']        ?? ''),
                'address'           => trim($_POST['address']      ?? ''),
                'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
                'guardian_name'     => trim($_POST['guardian_name']    ?? ''),
                'rg'                => trim($_POST['rg']              ?? ''),
                'nationality'       => trim($_POST['nationality']     ?? ''),
                'marital_status'    => trim($_POST['marital_status']  ?? ''),
                'cep'               => trim($_POST['cep']             ?? ''),
                'occupation_text'   => trim($_POST['occupation']       ?? ''),
                'status'            => $_POST['status'] ?? 'A',
                'notes'             => trim($_POST['notes'] ?? ''),
            ];

            $errors = [];

            if ($data['name_patient'] === '') {
                $errors[] = 'Nome do paciente é obrigatório.';
            }

            if ($data['cpf'] === '') {
                $errors[] = 'CPF é obrigatório.';
            } elseif (!$this->isValidCpf($data['cpf'])) {
                $errors[] = 'CPF inválido.';
            }

            if ($this->repo->cpfExists($data['cpf'])) {
                $errors[] = 'Já existe um paciente cadastrado com este CPF.';
            }

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'E-mail inválido.';
            }

            if ($data['cellphone'] === '') {
                $errors[] = 'Celular  é obrigatório.';
            }            

            if ($data['occupation_text'] === '') {
                $errors[] = 'Profissão é obrigatória.';
            }

            if ($errors) {
                $this->view('patients/create', [
                    'error' => implode(' ', $errors),
                    'old'   => $data,
                    'occupations' => $occupations,
                ]);
                return;
            }

            // Busca a profissão pelo texto digitado; cria automaticamente se ainda não existir
            $occupation = $this->occRepo->findOrCreateByDescription($data['occupation_text']);
            $data['idOccupation'] = $occupation->id;

            $patient = Patient::fromArray($data);
            $this->repo->create($patient);

            header('Location: ' . BASE_URL . '/?controller=patient&action=index&msg=created');
            exit;
        }

        $this->view('patients/create', ['occupations' => $occupations]);
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $occupations = $this->occRepo->all();

        $patient = $this->repo->find($id);
        if (!$patient) {
            header('Location: ' . BASE_URL . '/?controller=patient&action=index&msg=notfound');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id'                => $id,
                'name_patient'      => trim($_POST['name_patient']         ?? ''),
                'cpf'               => preg_replace('/\D+/', '', $_POST['cpf'] ?? ''),
                'birth_date'        => (trim($_POST['birth_date'] ?? '') !== '') ? $_POST['birth_date'] : null,
                'phone'             => trim($_POST['phone']        ?? ''),
                'cellphone'         => trim($_POST['cellphone']    ?? ''),
                'email'             => trim($_POST['email']        ?? ''),
                'address'           => trim($_POST['address']      ?? ''),
                'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
                'guardian_name'     => trim($_POST['guardian_name']    ?? ''),
                'rg'                => trim($_POST['rg']              ?? ''),
                'nationality'       => trim($_POST['nationality']     ?? ''),
                'marital_status'    => trim($_POST['marital_status']  ?? ''),
                'cep'               => trim($_POST['cep']             ?? ''),
                'occupation_text'   => trim($_POST['occupation']       ?? ''),
                'status'            => $_POST['status'] ?? 'A',
                'notes'             => trim($_POST['notes'] ?? ''),
            ];

            $errors = [];

            if ($data['name_patient'] === '') {
                $errors[] = 'Nome completo é obrigatório.';
            }

            if ($data['cpf'] === '' ) {
                $errors[] = 'CPF é obrigatório e deve conter 11 dígitos (apenas números).';
            } elseif (!$this->isValidCpf($data['cpf'])) {
                $errors[] = 'CPF inválido.';                
            }

            if ($this->repo->cpfExists($data['cpf'], $id)) {
                $errors[] = 'Já existe outro paciente com este CPF.';
            }

            if ($data['cellphone'] === '') {
                            $errors[] = 'Celular  é obrigatório.';
            }

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'E-mail inválido.';
            }

            if ($data['occupation_text'] === '') {
                $errors[] = 'Profissão é obrigatória.';
            }

            if ($errors) {
                $this->view('patients/edit', [
                    'error'   => implode(' ', $errors),
                    'patient' => $patient,
                    'old'     => $data,
                    'occupations' => $occupations,
                ]);
                return;
            }

            // Busca a profissão pelo texto digitado; cria automaticamente se ainda não existir
            $occupation = $this->occRepo->findOrCreateByDescription($data['occupation_text']);
            $data['idOccupation'] = $occupation->id;

            $patient = Patient::fromArray($data);
            $this->repo->update($patient);

            header('Location: ' . BASE_URL . '/?controller=patient&action=index&msg=updated');
            exit;
        }

        $this->view('patients/edit', ['patient' => $patient, 'occupations' => $occupations]);
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->repo->delete($id);
        }

        header('Location: ' . BASE_URL . '/?controller=patient&action=index&msg=deleted');
        exit;
    }

    private function view(string $path, array $data = []): void
    {
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$path}.php";
    }

    /**
    * Validação de CPF (formato brasileiro)
    */
    private function isValidCpf(string $cpf): bool
    {
        // mantém apenas números
        $cpf = preg_replace('/\D+/', '', $cpf ?? '');

        // tamanho inválido
        if (strlen($cpf) !== 11) {
            return false;
        }

        // rejeita sequências do tipo 00000000000, 11111111111, etc.
        if (preg_match('/^(\\d)\\1{10}$/', $cpf)) {
            return false;
        }

        // cálculo dos dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int)$cpf[$i] * (($t + 1) - $i);
            }

            $digit = ($sum * 10) % 11;
            if ($digit === 10) {
                $digit = 0;
            }

            if ((int)$cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

}
