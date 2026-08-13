<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\AppointmentType;
use App\Repositories\AppointmentTypeRepository;

class AppointmentTypeController
{
    private AppointmentTypeRepository $repo;

    public function __construct()
    {
        $db         = new Database();
        $this->repo = new AppointmentTypeRepository($db->getConnection());
    }

    public function index(): void
    {
        $types = $this->repo->all();
        $this->view('appointment_types/list', ['types' => $types]);
    }

    private function slugify(string $name): string
    {
        $slug = strtoupper(trim($name));
        $slug = preg_replace('/[^A-Z0-9]+/u', '_', $slug) ?? '';
        return trim($slug, '_');
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $code = $code !== '' ? strtoupper(preg_replace('/[^A-Za-z0-9_]+/', '_', $code)) : $this->slugify($name);
            $active = isset($_POST['active']) ? 1 : 0;

            $old = ['code' => $code, 'name' => $name, 'active' => $active];

            if ($name === '' || $code === '') {
                $this->view('appointment_types/create', [
                    'error' => 'Preencha nome e código do tipo de consulta.',
                    'old'   => $old,
                ]);
                return;
            }

            if ($this->repo->codeExists($code)) {
                $this->view('appointment_types/create', [
                    'error' => 'Já existe um tipo de consulta com este código.',
                    'old'   => $old,
                ]);
                return;
            }

            $type = new AppointmentType(null, $code, $name, (bool)$active);
            $this->repo->create($type);

            header('Location: ' . BASE_URL . '/?controller=appointmenttype&action=index&msg=created');
            exit;
        }

        $this->view('appointment_types/create');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/?controller=appointmenttype&action=index');
            exit;
        }

        $type = $this->repo->find($id);
        if (!$type) {
            header('Location: ' . BASE_URL . '/?controller=appointmenttype&action=index&msg=notfound');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $code = $code !== '' ? strtoupper(preg_replace('/[^A-Za-z0-9_]+/', '_', $code)) : $code;
            $active = isset($_POST['active']) ? 1 : 0;

            if ($name === '' || $code === '') {
                $this->view('appointment_types/edit', [
                    'error' => 'Preencha nome e código do tipo de consulta.',
                    'type'  => $type,
                ]);
                return;
            }

            if ($this->repo->codeExists($code, $id)) {
                $this->view('appointment_types/edit', [
                    'error' => 'Já existe outro tipo de consulta com este código.',
                    'type'  => $type,
                ]);
                return;
            }

            $type->code   = $code;
            $type->name   = $name;
            $type->active = (bool)$active;
            $this->repo->update($type);

            header('Location: ' . BASE_URL . '/?controller=appointmenttype&action=index&msg=updated');
            exit;
        }

        $this->view('appointment_types/edit', ['type' => $type]);
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $ok = $this->repo->delete($id);
            $msg = $ok ? 'deleted' : 'in_use';
            header('Location: ' . BASE_URL . '/?controller=appointmenttype&action=index&msg=' . $msg);
            exit;
        }

        header('Location: ' . BASE_URL . '/?controller=appointmenttype&action=index');
        exit;
    }

    private function view(string $path, array $data = []): void
    {
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$path}.php";
    }
}
