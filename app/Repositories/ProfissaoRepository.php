<?php
namespace App\Repositories; // Grupo/etiqueta que organiza este código dentro do projeto

use App\Models\Profissao; // Diz que vamos usar a "receita" (classe) Profissao de outra pasta
use PDO; // Diz que vamos usar a classe PDO (para conversar com o banco de dados)

class ProfissaoRepository // Começa a definição de uma "caixa de ferramentas" para lidar com profissões no banco
{
    public function __construct(private PDO $pdo) {} 
    // Função executada quando criamos este objeto.
    // Recebe $pdo (conexão com o banco) e guarda dentro do objeto para usar depois.

    public function all(): array
    {
        $st = $this->pdo->query("SELECT id, codigo, descricao_profissao FROM `cadastro_profissao` ORDER BY id DESC");
        // Pede ao banco todos os registros da tabela (colunas id, codigo, descricao_profissao), ordenando por id decrescente.
        return array_map(fn($r) => new Profissao((int)$r['id'], $r['codigo'], $r['descricao']), $st->fetchAll());
        // Transforma cada linha retornada em um objeto Profissao e devolve todas em um array.
    }

    public function find(int $id): ?Profissao
    {
        $st = $this->pdo->prepare("SELECT id, codigo, descricao FROM `cadastro_profissoes` WHERE id = ?");
        // Prepara uma pergunta ao banco: "me traz a profissão com este id?"
        $st->execute([$id]); // Executa a pergunta substituindo ? pelo $id fornecido
        $r = $st->fetch(); // Pega a primeira linha da resposta 
        return $r ? new Profissao((int)$r['id'], $r['codigo'], $r['descricao']) : null;
        // Se encontrou, cria e retorna um objeto Profissao com os dados; se não, retorna null (nada)
    }

    public function createProfissoes(Profissao $p): int
    {
        $st = $this->pdo->prepare("INSERT INTO `cadastro_profissoes` (codigo, descricao) VALUES (?,?)");
        // Prepara uma instrução para inserir um novo registro na tabela
        $st->execute([$p->codigo, $p->descricao]); // Executa a inserção com os valores do objeto $p
        return (int)$this->pdo->lastInsertId(); // Retorna o id do novo registro criado no banco
    }

    public function updateProfissoes(Profissao $p): void
    {
        $st = $this->pdo->prepare("UPDATE `cadastro_profissoes` SET codigo = ?, descricao = ? WHERE id = ?");
        // Prepara a instrução para atualizar os campos de um registro específico
        $st->execute([$p->codigo, $p->descricao, $p->id]); // Executa a atualização usando os valores do objeto
    }

    public function deleteProfissoes(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM `cadastro_profissoes` WHERE id = ?");
        // Prepara uma instrução para apagar um registro pelo id
        $st->execute([$id]); // Executa a exclusão
    }

    public function codigoExists(string $codigo, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $st = $this->pdo->prepare("SELECT 1 FROM `cadastro_profissoes` WHERE codigo = ? AND id <> ?");
            $st->execute([$codigo, $ignoreId]);
            // Verifica se existe outro registro com o mesmo código, ignorando um id específico (útil ao editar)
        } else {
            $st = $this->pdo->prepare("SELECT 1 FROM `cadastro_profissoes` WHERE codigo = ?");
            $st->execute([$codigo]);
            // Verifica se existe qualquer registro com esse código (útil ao criar)
        }
        return (bool)$st->fetchColumn(); 
        // Se encontrar algo, fetchColumn retorna um valor que convertemos para true; senão retorna false.
    }
}