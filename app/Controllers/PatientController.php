<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Patient;
use App\Repositories\PatientRepository;

class PatientController
{
    private PatientRepository $repo;

    public function __construct()
    {
        $db         = new Database();
        $this->repo = new PatientRepository($db->getConnection());
    }

    public function index(): void
    {
        $patients = $this->repo->all();
        $this->view('patients/list', ['patients' => $patients]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name_patient'      => trim($_POST['name_patient']         ?? ''),
                'cpf'               => preg_replace('/\D+/', '', $_POST['cpf'] ?? ''),
                'birth_date'        => $_POST['birth_date']        ?? null,
                'phone'             => trim($_POST['phone']        ?? ''),
                'cellphone'         => trim($_POST['cellphone']    ?? ''),
                'email'             => trim($_POST['email']        ?? ''),
                'address'           => trim($_POST['address']      ?? ''),
                'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
                'guardian_name'     => trim($_POST['guardian_name']    ?? ''),
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

            if ($errors) {
                $this->view('patients/create', [
                    'error' => implode(' ', $errors),
                    'old'   => $data,
                ]);
                return;
            }

            $patient = Patient::fromArray($data);
            $this->repo->create($patient);

            header('Location: /nutrihealth/public/?controller=patient&action=index&msg=created');
            exit;
        }

        $this->view('patients/create');
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $patient = $this->repo->find($id);
        if (!$patient) {
            header('Location: /nutrihealth/public/?controller=patient&action=index&msg=notfound');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id'                => $id,
                'name_patient'      => trim($_POST['name_patient']         ?? ''),
                'cpf'               => preg_replace('/\D+/', '', $_POST['cpf'] ?? ''),
                'birth_date'        => $_POST['birth_date']        ?? null,
                'phone'             => trim($_POST['phone']        ?? ''),
                'cellphone'         => trim($_POST['cellphone']    ?? ''),
                'email'             => trim($_POST['email']        ?? ''),
                'address'           => trim($_POST['address']      ?? ''),
                'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
                'guardian_name'     => trim($_POST['guardian_name']    ?? ''),
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

            if ($errors) {
                $this->view('patients/edit', [
                    'error'   => implode(' ', $errors),
                    'patient' => $patient,
                    'old'     => $data,
                ]);
                return;
            }

            $patient = Patient::fromArray($data);
            $this->repo->update($patient);

            header('Location: /nutrihealth/public/?controller=patient&action=index&msg=updated');
            exit;
        }

        $this->view('patients/edit', ['patient' => $patient]);
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->repo->delete($id);
        }

        header('Location: /nutrihealth/public/?controller=patient&action=index&msg=deleted');
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
