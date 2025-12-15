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
}
