<?php
namespace App\Controllers; // Diz que esta classe faz parte do grupo "App\Controllers" (organização do código)
use App\Config\Database; // Pede a classe que cria a conexão com o banco de dados
use App\Models\UserProfissao; // Pede a "receita" do objeto que representa uma profissão de usuário
use App\Repositories\UserProfissaoRepository; // Pede a parte que fala com o banco para salvar/ler profissões
class UserProfissaoController { // Começa a declaração da "classe" que controla as ações relacionadas a profissões
    private UserProfissaoRepository $repo; // Variável que vai guardar quem fala com o banco

    public function __construct(){ $db=new Database(); $this->repo=new UserProfissaoRepository($db->getConnection()); } 
    // Quando eu crio este controlador:
    // - crio uma conexão com o banco ($db)
    // - crio um repositório ($this->repo) que usa essa conexão para ler/escrever profissões

    public function index(): void { // Ação "index": mostrar a lista de profissões
        $profissoes=$this->repo->all(); // Pede ao repositório todas as profissões do banco
        $this->view('profissoes/list',['profissoes'=>$profissoes]); // Mostra a página/lista com os dados retornados
    }

    public function create(): void {  // Ação para criar uma nova profissão
        if($_SERVER['REQUEST_METHOD']==='POST'){ // Verifica se o formulário foi enviado (método POST)
            $codigo=trim($_POST['codigo']??''); $descricao_profissao=trim($_POST['descricao_profissao']??''); 
            // Pega os valores enviados pelo formulário e remove espaços em branco extras

            if($this->repo->codigoExists($codigo)){ 
                $this->view('profissoes/create',['error'=>'Código já cadastrado.','old'=>compact('codigo','descricao_profissao')]); 
                return; 
            } 
            // Se já existe outra profissão com o mesmo código, mostra o formulário novamente com erro

            $userProfissao=new UserProfissao($codigo,$descricao_profissao); // Cria um objeto com os dados informados
            $this->repo->create($userProfissao); // Pede ao repositório para salvar esse objeto no banco
            header('Location: /nutrihealth/public/?action=index&msg=success'); exit; 
            // Redireciona o usuário para a lista com uma mensagem de sucesso
        }
        $this->view('profissoes/create'); // Se não for envio de formulário, apenas mostra o formulário vazio
    }

    public function edit(): void { // Ação para editar uma profissão existente
        $id=(int)($_GET['id']??0); $userProfissao=$this->repo->find($id);
        // Pega o id da URL e busca a profissão correspondente no banco

        if(!$userProfissao){ http_response_code(404); echo "Usuário não encontrado."; return; }
        // Se não encontrou a profissão, retorna erro 404 e uma mensagem

        if($_SERVER['REQUEST_METHOD']==='POST'){ // Se o formulário de edição foi enviado
            $codigo=trim($_POST['codigo']??''); $descricao_profissao=trim($_POST['descricao_profissao']??'');
            // Pega e limpa os novos valores

            if($this->repo->codigoExists($codigo,$id)){ 
                $this->view('profissoes/edit',['error'=>'Código já cadastrado em outro usuário.','userProfissao'=>$userProfissao]); 
                return; 
            } 
            // Verifica se o código informado já pertence a outra profissão (evita duplicidade)

            $userProfissao->codigo=$codigo; $userProfissao->descricao_profissao=$descricao_profissao;
            // Atualiza o objeto em memória com os novos valores

            $this->repo->update($userProfissao); // Salva as mudanças no banco
            header('Location: /nutrihealth/public/?action=index&msg=success'); exit;
            // Redireciona de volta para a lista com mensagem de sucesso
        }
        $this->view('profissoes/edit',['userProfissao'=>$userProfissao]); 
        // Se não for POST, mostra o formulário de edição preenchido com os dados atuais
    }

    public function delete(): void { // Ação para excluir uma profissão
        $id=(int)($_GET['id']??0); if($id>0){ $this->repo->delete($id); } 
        // Pega o id da URL e pede ao repositório para apagar do banco (se id válido)

        header('Location: /nutrihealth/public/?action=index&msg=deleted'); exit;
        // Redireciona para a lista com mensagem de que foi excluído
    }

    private function view(string $path,array $data=[]): void { extract($data); $base=dirname(__DIR__,2); include $base."/views/{$path}.php"; } 
    // Função auxiliar que:
    // - transforma o array de dados em variáveis (extract)
    // - determina a pasta base do projeto
    // - inclui (carrega) o arquivo de visualização (a página HTML/PHP) para mostrar ao usuário
}