<?php
namespace App\Repositories;

use App\Models\Occupation;
use PDO;

class OccupationRepository
{
    public function __construct(private PDO $pdo) {}

    public function all(): array
    {
        $st = $this->pdo->query(
            "SELECT id, code, description_occupation
             FROM occupation
             ORDER BY id DESC"
        );
        $rows = $st->fetchAll();
        return array_map(fn(array $r) => Occupation::fromArray($r), $rows);
    }

    public function find(int $id): ?Occupation
    {
        $st = $this->pdo->prepare(
            "SELECT id, code, description_occupation
             FROM occupation
             WHERE id = ?"
        );
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ? Occupation::fromArray($row) : null;
    }

    /**
     * Busca uma profissão pela descrição (sem diferenciar maiúsculas/minúsculas
     * nem espaços nas pontas), para evitar cadastrar duplicada.
     */
    public function findByDescription(string $description): ?Occupation
    {
        $description = trim($description);
        if ($description === '') {
            return null;
        }

        $st = $this->pdo->prepare(
            "SELECT id, code, description_occupation
             FROM occupation
             WHERE LOWER(description_occupation) = LOWER(?)
             LIMIT 1"
        );
        $st->execute([$description]);
        $row = $st->fetch();

        return $row ? Occupation::fromArray($row) : null;
    }

    /**
     * Busca a profissão pela descrição digitada; se não existir, cadastra
     * automaticamente (gerando um código interno único) e retorna a nova.
     * Usado no cadastro de paciente, para permitir digitar livremente.
     */
    public function findOrCreateByDescription(string $description): Occupation
    {
        $description = trim($description);

        $existing = $this->findByDescription($description);
        if ($existing) {
            return $existing;
        }

        $occupation = new Occupation(null, $this->generateAutoCode(), mb_substr($description, 0, 100));
        $occupation->id = $this->create($occupation);

        return $occupation;
    }

    /**
     * Gera um código interno único de 7 caracteres para profissões criadas
     * automaticamente (o campo "code" é obrigatório e único na tabela).
     */
    private function generateAutoCode(): string
    {
        do {
            $code = 'A' . str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->codeExists($code));

        return $code;
    }

    public function create(Occupation $occupation): int
    {
        $st = $this->pdo->prepare(
            "INSERT INTO occupation (code, description_occupation)
             VALUES (:code, :description)"
        );
        $st->execute([
            ':code'        => $occupation->code,
            ':description' => $occupation->description,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(Occupation $occupation): void
    {
        $st = $this->pdo->prepare(
            "UPDATE occupation
             SET code = :code,
                 description_occupation = :description
             WHERE id = :id"
        );
        $st->execute([
            ':code'        => $occupation->code,
            ':description' => $occupation->description,
            ':id'          => $occupation->id,
        ]);
    }

    public function delete(int $id): void
    {
        $st = $this->pdo->prepare(
            "DELETE FROM occupation WHERE id = ?"
        );
        $st->execute([$id]);
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM occupation
                 WHERE code = ? AND id <> ?"
            );
            $st->execute([$code, $ignoreId]);
        } else {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM occupation
                 WHERE code = ?"
            );
            $st->execute([$code]);
        }

        return (bool)$st->fetchColumn();
    }
}
