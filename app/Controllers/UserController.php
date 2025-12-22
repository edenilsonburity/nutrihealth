<?php
namespace App\Controllers;
use App\Config\Database;
use App\Models\User;
use App\Repositories\UserRepository;
class UserController {
    private UserRepository $repo;
    public function __construct(){ $db=new Database(); $this->repo=new UserRepository($db->getConnection()); }
    public function index(): void {
        $users=$this->repo->all(); $this->view('users/list',['users'=>$users]);
    }
    public function create(): void {
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $password=(string)($_POST['password']??''); $typeUser=$_POST['typeUser']??'U';
            if($this->repo->emailExists($email)){ $this->view('users/create',['error'=>'E-mail já cadastrado.','old'=>compact('name','email','typeUser')]); return; }
            $hash=password_hash($password,PASSWORD_DEFAULT);
            $user=new User(null,$name,$email,$hash,$typeUser);
            $this->repo->create($user);
            header('Location: /nutrihealth/public/?controller=user&action=index&msg=created'); exit;
        }
        $this->view('users/create');
    }
    public function edit(): void {
        $id=(int)($_GET['id']??0); $user=$this->repo->find($id);
        if(!$user){ http_response_code(404); echo "Usuário não encontrado."; return; }
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $typeUser=$_POST['typeUser']??'U';
            if($this->repo->emailExists($email,$id)){ $this->view('users/edit',['error'=>'E-mail já cadastrado em outro usuário.','user'=>$user]); return; }
            $user->name=$name; $user->email=$email; $user->typeUser=$typeUser;
            $this->repo->update($user);
            header('Location: /nutrihealth/public/?controller=user&action=index&msg=updated'); exit;
        }
        $this->view('users/edit',['user'=>$user]);
    }
    public function delete(): void {
        $id=(int)($_GET['id']??0); if($id>0){ $this->repo->delete($id); }
        header('Location: /nutrihealth/public/?action=index&msg=deleted'); exit;
    }
    private function view(string $path,array $data=[]): void { extract($data); $base=dirname(__DIR__,2); include $base."/views/{$path}.php"; }
    public function login(): void
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email    = trim($_POST['email'] ?? '');
                $password = (string)($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $this->view('auth/login', ['error' => 'Por favor informe o e-mail e senha.']);
                return;
            }

            $user = $this->repo->findByEmail($email);
            if (!$user || !password_verify($password, $user->passwordHash)) {
                $this->view('auth/login', ['error' => 'E-mail ou senha inválido.']);
                return;
            }

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            session_regenerate_id(true);
            $_SESSION['user_id']   = $user->id;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['user_type'] = $user->typeUser;

            header('Location: /nutrihealth/public/?controller=report&action=index');            
            exit;
        }

        $this->view('auth/login');
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        header('Location: /nutrihealth/public/?controller=user&action=login');
        exit;
    }

    public function changePassword(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: /nutrihealth/public/?controller=user&action=index&msg=notfound');
            exit;
        }

        // Permissões:
        // - o usuário pode alterar a própria senha
        // - admin pode alterar a senha de qualquer usuário
        $loggedId   = (int)($_SESSION['user_id'] ?? 0);
        $userType   = $_SESSION['user_type'] ?? null; // ex.: 'A' admin, 'N' nutri, etc.
        $isAdmin    = ($userType === 'A');
        $isSelf     = ($loggedId === $id);

        if (!$isSelf && !$isAdmin) {
            header('Location: /nutrihealth/public/?controller=user&action=index&msg=forbidden');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = (string)($_POST['current_password'] ?? '');
            $newPassword     = (string)($_POST['new_password'] ?? '');
            $confirmPassword = (string)($_POST['confirm_password'] ?? '');

            $errors = [];

            // regras de senha (ajuste como quiser)
            if (strlen($newPassword) < 8) {
                $errors[] = 'A nova senha deve ter no mínimo 8 caracteres.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Confirmação de senha não confere.';
            }

            // Se for o próprio usuário (não admin reset), exige senha atual
            if ($isSelf && !$isAdmin) {
                $hash = $this->repo->getPasswordHashById($id);
                if (!$hash || !password_verify($currentPassword, $hash)) {
                    $errors[] = 'Senha atual inválida.';
                }
            }

            if ($errors) {
                $this->view('users/change_password', [
                    'error'  => implode(' ', $errors),
                    'userId' => $id,
                    'isSelf' => $isSelf,
                    'isAdmin'=> $isAdmin,
                ]);
                return;
            }

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->repo->updatePassword($id, $newHash);

            header('Location: /nutrihealth/public/?controller=user&action=index&msg=updated');
            exit;
        }

        $this->view('users/change_password', [
            'userId' => $id,
            'isSelf' => $isSelf,
            'isAdmin'=> $isAdmin,
        ]);
    }


}
