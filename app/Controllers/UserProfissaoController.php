<?php
namespace App\Controllers;
use App\Config\Database;
use App\Models\UserProfissao;
use App\Repositories\UserProfissaoRepository;
class UserProfissaoController {
    private UserProfissaoRepository $repo;
    public function __construct(){ $db=new Database(); $this->repo=new UserProfissaoRepository($db->getConnection()); }
    public function index(): void {
        $profissoes=$this->repo->all(); $this->view('profissoes/list',['profissoes'=>$profissoes]);
    }
    public function create(): void { 
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $codigo=trim($_POST['codigo']??''); $descricao_profissao=trim($_POST['descricao_profissao']??''); 
            if($this->repo->codigoExists($codigo)){ $this->view('profissoes/create',['error'=>'Código já cadastrado.','old'=>compact('codigo','descricao_profissao')]); return; }
            $userProfissao=new UserProfissao($codigo,$descricao_profissao);
            $this->repo->create($userProfissao);
            header('Location: /nutrihealth/public/?action=index&msg=success'); exit;
        }
        $this->view('profissoes/create');
    }
    public function edit(): void {
        $id=(int)($_GET['id']??0); $userProfissao=$this->repo->find($id);
        if(!$userProfissao){ http_response_code(404); echo "Usuário não encontrado."; return; }
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $codigo=trim($_POST['codigo']??''); $descricao_profissao=trim($_POST['descricao_profissao']??'');
            if($this->repo->codigoExists($codigo,$id)){ $this->view('profissoes/edit',['error'=>'Código já cadastrado em outro usuário.','userProfissao'=>$userProfissao]); return; }
            $userProfissao->codigo=$codigo; $userProfissao->descricao_profissao=$descricao_profissao;
            $this->repo->update($userProfissao);
            header('Location: /nutrihealth/public/?action=index&msg=success'); exit;
        }
        $this->view('profissoes/edit',['userProfissao'=>$userProfissao]);
    }
    public function delete(): void {
        $id=(int)($_GET['id']??0); if($id>0){ $this->repo->delete($id); }
        header('Location: /nutrihealth/public/?action=index&msg=deleted'); exit;
    }
    private function view(string $path,array $data=[]): void { extract($data); $base=dirname(__DIR__,2); include $base."/views/{$path}.php"; }
}