<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\ServiceType;
use App\Repositories\ServiceTypeRepository;

class ServiceTypeController
{
    private ServiceTypeRepository $repo;

    public function __construct()
    {
        $db         = new Database();
        $this->repo = new ServiceTypeRepository($db->getConnection());
    }

    public function index(): void
    {
        $this->view('service_types/list', ['types' => $this->repo->all()]);
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
            $name        = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priceRaw    = trim(str_replace(',', '.', $_POST['default_price'] ?? ''));
            $code        = trim($_POST['code'] ?? '');
            $code        = $code !== '' ? strtoupper(preg_replace('/[^A-Za-z0-9_]+/', '_', $code)) : $this->slugify($name);
            $active      = isset($_POST['active']) ? 1 : 0;

            $old = ['code' => $code, 'name' => $name, 'description' => $description, 'default_price' => $priceRaw, 'active' => $active];

            if ($name === '' || $code === '') {
                $this->view('service_types/create', ['error' => 'Preencha nome e código do tipo de serviço.', 'old' => $old]);
                return;
            }

            if ($priceRaw !== '' && !is_numeric($priceRaw)) {
                $this->view('service_types/create', ['error' => 'Valor sugerido deve ser numérico.', 'old' => $old]);
                return;
            }

            if ($this->repo->codeExists($code)) {
                $this->view('service_types/create', ['error' => 'Já existe um tipo de serviço com este código.', 'old' => $old]);
                return;
            }

            $type = new ServiceType(null, $code, $name, $description ?: null, $priceRaw !== '' ? (float)$priceRaw : null, (bool)$active);
            $this->repo->create($type);

            header('Location: ' . BASE_URL . '/?controller=servicetype&action=index&msg=created');
            exit;
        }

        $this->view('service_types/create');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/?controller=servicetype&action=index');
            exit;
        }

        $type = $this->repo->find($id);
        if (!$type) {
            header('Location: ' . BASE_URL . '/?controller=servicetype&action=index&msg=notfound');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priceRaw    = trim(str_replace(',', '.', $_POST['default_price'] ?? ''));
            $code        = trim($_POST['code'] ?? '');
            $code        = $code !== '' ? strtoupper(preg_replace('/[^A-Za-z0-9_]+/', '_', $code)) : $code;
            $active      = isset($_POST['active']) ? 1 : 0;

            if ($name === '' || $code === '') {
                $this->view('service_types/edit', ['error' => 'Preencha nome e código do tipo de serviço.', 'type' => $type]);
                return;
            }

            if ($priceRaw !== '' && !is_numeric($priceRaw)) {
                $this->view('service_types/edit', ['error' => 'Valor sugerido deve ser numérico.', 'type' => $type]);
                return;
            }

            if ($this->repo->codeExists($code, $id)) {
                $this->view('service_types/edit', ['error' => 'Já existe outro tipo de serviço com este código.', 'type' => $type]);
                return;
            }

            $type->code         = $code;
            $type->name         = $name;
            $type->description  = $description ?: null;
            $type->defaultPrice = $priceRaw !== '' ? (float)$priceRaw : null;
            $type->active       = (bool)$active;
            $this->repo->update($type);

            header('Location: ' . BASE_URL . '/?controller=servicetype&action=index&msg=updated');
            exit;
        }

        $this->view('service_types/edit', ['type' => $type]);
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $ok = $this->repo->delete($id);
            header('Location: ' . BASE_URL . '/?controller=servicetype&action=index&msg=' . ($ok ? 'deleted' : 'in_use'));
            exit;
        }
        header('Location: ' . BASE_URL . '/?controller=servicetype&action=index');
        exit;
    }

    private function view(string $path, array $data = []): void
    {
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$path}.php";
    }
}
