<?php
namespace App\Repositories; // Diz que este arquivo pertence ao grupo App\Repositories — ajuda a organizar o projeto

use App\Models\Profissao; // Diz que vamos usar a "receita" Profissao (classe que representa uma profissão)
use PDO; // Diz que vamos usar a classe PDO para conversar com o banco de dados

class ProfissaoRepository // Começa uma "caixa de ferramentas" para ler/escrever profissões no banco
{
    public function __construct(private PDO $pdo) {} 
    // Função que roda quando criamos essa caixa de ferramentas.
    // Ela recebe a conexão com o banco ($pdo) e guarda para usar nos outros métodos.

    public function all(): array
    {
        $st = $this->pdo->query("SELECT id, codigo, descricao_profissao FROM `cadastro_profissao` ORDER BY id DESC");
        // Pergunta ao banco: me traga todas as profissões (colunas id, codigo, descricao_profissao), ordenadas do mais novo para o mais antigo.

        return array_map(fn($r) => new Profissao((int)$r['id'], $r['codigo'], $r['descricao']), $st->fetchAll(PDO::FETCH_ASSOC));
        // Para cada linha que o banco retornou, cria um objeto Profissao com esses dados e devolve tudo em um array.
        // fetchAll(PDO::FETCH_ASSOC) pega todas as linhas como arrays associativos (coluna => valor).
    }

    public function find(int $id): ?Profissao
    {
        $st = $this->pdo->prepare("SELECT id, codigo, descricao FROM `cadastro_profissoes` WHERE id = ?");
        // Prepara uma pergunta ao banco: me traga a profissão com este id.

        $st->execute([$id]); // Executa a pergunta substituindo o ? pelo id que veio na chamada.
        $r = $st->fetch(PDO::FETCH_ASSOC); // Pega a primeira linha da resposta como um array (ou false se não existir).

        return $r ? new Profissao((int)$r['id'], $r['codigo'], $r['descricao']) : null;
        // Se encontrou a linha, cria um objeto Profissao com os dados; se não encontrou, retorna null (nada).
    }

    public function create(Profissao $p): int
    {
        $st = $this->pdo->prepare("INSERT INTO `cadastro_profissoes` (codigo, descricao) VALUES (?,?)");
        // Prepara uma instrução para inserir uma nova profissão na tabela.

        $st->execute([$p->codigo, $p->descricao]); // Executa a inserção usando os valores vindos do objeto $p.
        return (int)$this->pdo->lastInsertId(); // Retorna o id que o banco criou para esse novo registro.
    }

    public function update(Profissao $p): void
    {
        $st = $this->pdo->prepare("UPDATE `cadastro_profissoes` SET codigo = ?, descricao = ? WHERE id = ?");
        // Prepara uma instrução para atualizar os dados de uma profissão já existente (identificada pelo id).

        $st->execute([$p->codigo, $p->descricao, $p->id]); // Executa a atualização com os valores do objeto $p.
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM `cadastro_profissoes` WHERE id = ?");
        // Prepara uma instrução para apagar a profissão com o id fornecido.

        $st->execute([$id]); // Executa a exclusão no banco.
    }

    public function codigoExists(string $codigo, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $st = $this->pdo->prepare("SELECT 1 FROM `cadastro_profissoes` WHERE codigo = ? AND id <> ?");
            $st->execute([$codigo, $ignoreId]);
            // Aqui checamos se existe outra profissão com o mesmo código, mas ignoramos um id específico.
            // Isso é útil quando estamos editando: queremos saber se outro registro (diferente do que estamos editando) já usa o código.
        } else {
            $st = $this->pdo->prepare("SELECT 1 FROM `cadastro_profissoes` WHERE codigo = ?");
            $st->execute([$codigo]);
            // Aqui checamos se existe qualquer profissão com esse código (útil ao criar um novo registro).
        }

        return (bool)$st->fetchColumn();
        // fetchColumn traz o primeiro valor da primeira linha encontrada.
        // Se encontrou algo, convertemos para true (existe); se não, false (não existe).
    }
}