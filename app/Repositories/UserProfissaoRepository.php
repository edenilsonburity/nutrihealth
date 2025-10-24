<?php
namespace App\Repositories;
use App\Models\Profissao;
use PDO;
class ProfissaoRepository
{
    public function __construct(private PDO $pdo) {}
    public function all(): array
    {
        $st = $this->pdo->query("SELECT id, codigo, descricao_profissao FROM `cadastro_profissao` ORDER BY id DESC");
        return array_map(fn($r) => new Profissao((int)$r['id'], $r['codigo'], $r['descricao']), $st->fetchAll(PDO::FETCH_ASSOC));
    }

    public function find(int $id): ?Profissao
    {
        $st = $this->pdo->prepare("SELECT id, codigo, descricao FROM `cadastro_profissoes` WHERE id = ?");
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ? new Profissao((int)$r['id'], $r['codigo'], $r['descricao']) : null;
    }

    public function create(Profissao $p): int
    {
        $st = $this->pdo->prepare("INSERT INTO `cadastro_profissoes` (codigo, descricao) VALUES (?,?)");
        $st->execute([$p->codigo, $p->descricao]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(Profissao $p): void
    {
        $st = $this->pdo->prepare("UPDATE `cadastro_profissoes` SET codigo = ?, descricao = ? WHERE id = ?");
        $st->execute([$p->codigo, $p->descricao, $p->id]);
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM `cadastro_profissoes` WHERE id = ?");
        $st->execute([$id]);
    }

    public function codigoExists(string $codigo, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $st = $this->pdo->prepare("SELECT 1 FROM `cadastro_profissoes` WHERE codigo = ? AND id <> ?");
            $st->execute([$codigo, $ignoreId]);
        } else {
            $st = $this->pdo->prepare("SELECT 1 FROM `cadastro_profissoes` WHERE codigo = ?");
            $st->execute([$codigo]);
        }
        return (bool)$st->fetchColumn();
    }
}