<?php
namespace App\Controllers; // Diz que esta classe faz parte do grupo "App\Controllers" (organização do código)
use App\Config\Database; // Pede a classe que cria a conexão com o banco de dados
use App\Models\Profissao; // Pede a "receita" do objeto que representa uma profissão de usuário
use App\Repositories\ProfissaoRepository; // Pede a parte que fala com o banco para salvar/ler profissões
class ProfissaoController { // Começa a declaração da "classe" que controla as ações relacionadas a profissões
    private ProfissaoRepository $repo; // Variável que vai guardar quem fala com o banco

    public function __construct(){ $db=new Database(); $this->repo=new ProfissaoRepository($db->getConnection()); } 
    // Quando eu crio este controlador:
    // - crio uma conexão com o banco ($db)
    // - crio um repositório ($this->repo) que usa essa conexão para ler/escrever profissões

    public function indexProfissoes(): void { // Ação "index": mostrar a lista de profissões
        $profissoes=$this->repo->all(); // Pede ao repositório todas as profissões do banco
        $this->view('profissoes/listProfissoes',['profissoes'=>$profissoes]); // Mostra a página/lista com os dados retornados
    }

    public function createProfissoes(): void {  // Ação para criar uma nova profissão
        if($_SERVER['REQUEST_METHOD']==='POST'){ // Verifica se o formulário foi enviado (método POST)
            $codigo=trim($_POST['codigo']??''); $descricao_profissao=trim($_POST['descricao_profissao']??''); 
            // Pega os valores enviados pelo formulário e remove espaços em branco extras

            if($this->repo->codigoExists($codigo)){ 
                $this->view('profissoes/createProfissoes',['error'=>'Código já cadastrado.','old'=>compact('codigo','descricao_profissao')]); 
                return; 
            } 
            // Se já existe outra profissão com o mesmo código, mostra o formulário novamente com erro

            $profissao=new Profissao(null,$codigo,$descricao_profissao); // Cria um objeto com os dados informados
            $this->repo->createProfissoes($profissao); // Pede ao repositório para salvar esse objeto no banco
            header('Location: /nutrihealth/public/indexProfissoes.php?action=indexProfissoes&msg=success'); exit; 
            // Redireciona o usuário para a lista com uma mensagem de sucesso
        }
        $this->view('profissoes/createProfissoes'); // Se não for envio de formulário, apenas mostra o formulário vazio
    }

    public function editProfissoes(): void { // Ação para editar uma profissão existente
        $id=(int)($_GET['id']??0); $profissao=$this->repo->find($id);
        // Pega o id da URL e busca a profissão correspondente no banco

        if(!$profissao){ http_response_code(404); echo "Usuário não encontrado."; return; }
        // Se não encontrou a profissão, retorna erro 404 e uma mensagem

        if($_SERVER['REQUEST_METHOD']==='POST'){ // Se o formulário de edição foi enviado
            $codigo=trim($_POST['codigo']??''); $descricao_profissao=trim($_POST['descricao_profissao']??'');
            // Pega e limpa os novos valores

            if($this->repo->codigoExists($codigo,$id)){ 
                $this->view('profissoes/editProfissoes',['error'=>'Código já cadastrado em outro usuário.','profissao'=>$profissao]); 
                return; 
            } 
            // Verifica se o código informado já pertence a outra profissão (evita duplicidade)

            $profissao->codigo=$codigo; $profissao->descricao_profissao=$descricao_profissao;
            // Atualiza o objeto em memória com os novos valores

            $this->repo->updateProfissoes($profissao); // Salva as mudanças no banco
             header('Location: /nutrihealth/public/indexProfissoes.php?action=indexProfissoes&msg=success'); exit;
            // Redireciona de volta para a lista com mensagem de sucesso
        }
        $this->view('profissoes/editProfissoes',['profissao'=>$profissao]); 
        // Se não for POST, mostra o formulário de edição preenchido com os dados atuais
    }

    public function deleteProfissoes(): void { // Ação para excluir uma profissão
        $id=(int)($_GET['id']??0); if($id>0){ $this->repo->deleteProfissoes($id); } 
        // Pega o id da URL e pede ao repositório para apagar do banco (se id válido)

         header('Location: /nutrihealth/public/indexProfissoes.php?action=indexProfissoes&msg=deleted'); exit;
        // Redireciona para a lista com mensagem de que foi excluído
    }

    private function view(string $path,array $data=[]): void { extract($data); $base=dirname(__DIR__,2); include $base."/views/{$path}.php"; } 
    // Função auxiliar que:
    // - transforma o array de dados em variáveis (extract)
    // - determina a pasta base do projeto
    // - inclui (carrega) o arquivo de visualização (a página HTML/PHP) para mostrar ao usuário
}