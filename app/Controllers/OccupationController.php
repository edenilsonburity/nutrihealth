<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Occupation;
use App\Repositories\OccupationRepository;

class OccupationController
{
    private OccupationRepository $repo;

    public function __construct()
    {
        $db         = new Database();
        $this->repo = new OccupationRepository($db->getConnection());
    }

    public function index(): void
    {
        $occupations = $this->repo->all();
        $this->view('occupation/list', ['occupations' => $occupations]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code        = strtoupper(trim($_POST['code'] ?? ''));
            $description = trim($_POST['description'] ?? '');

            $old = [
                'code'        => $code,
                'description' => $description,
            ];

            if ($code === '' || $description === '') {
                $this->view('occupation/create', [
                    'error' => 'Preencha todos os campos.',
                    'old'   => $old,
                ]);
                return;
            }

            if ($this->repo->codeExists($code)) {
                $this->view('occupation/create', [
                    'error' => 'Este código de profissão já está cadastrado.',
                    'old'   => $old,
                ]);
                return;
            }

            $occupation = new Occupation(null, $code, $description);
            $this->repo->create($occupation);

            header('Location: /nutrihealth/public/?controller=occupation&action=index&msg=created');
            exit;
        }

        $this->view('occupation/create');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /nutrihealth/public/?controller=occupation&action=index');
            exit;
        }

        $occupation = $this->repo->find($id);
        if (!$occupation) {
            header('Location: /nutrihealth/public/?controller=occupation&action=index&msg=notfound');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code        = strtoupper(trim($_POST['code'] ?? ''));
            $description = trim($_POST['description'] ?? '');

            if ($code === '' || $description === '') {
                $this->view('occupation/edit', [
                    'error'      => 'Preencha todos os campos.',
                    'occupation' => $occupation,
                ]);
                return;
            }

            if ($this->repo->codeExists($code, $id)) {
                $this->view('occupation/edit', [
                    'error'      => 'Já existe outra profissão com este código.',
                    'occupation' => $occupation,
                ]);
                return;
            }

            $occupation->code        = $code;
            $occupation->description = $description;
            $this->repo->update($occupation);

            header('Location: /nutrihealth/public/?controller=occupation&action=index&msg=updated');
            exit;
        }

        $this->view('occupation/edit', ['occupation' => $occupation]);
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->repo->delete($id);
        }

        header('Location: /nutrihealth/public/?controller=occupation&action=index&msg=deleted');
        exit;
    }

    private function view(string $path, array $data = []): void
    {
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$path}.php";
    }
}
