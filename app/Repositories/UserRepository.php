<?php
namespace App\Repositories;
use App\Models\User;
use PDO;
class UserRepository {
    public function __construct(private PDO $pdo){}
    public function all(): array {
        $st=$this->pdo->query("SELECT id,name,email,password AS passwordHash,typeUser FROM `user` ORDER BY id DESC");
        return array_map(fn($r)=>new User($r['id'],$r['name'],$r['email'],$r['passwordHash'],$r['typeUser']),$st->fetchAll());
    }
    public function find(int $id): ?User {
        $st=$this->pdo->prepare("SELECT id,name,email,password AS passwordHash,typeUser FROM `user` WHERE id=?");
        $st->execute([$id]); $r=$st->fetch();
        return $r? new User($r['id'],$r['name'],$r['email'],$r['passwordHash'],$r['typeUser']): null;
    }
    public function create(User $u): int {
        $st=$this->pdo->prepare("INSERT INTO `user` (name,email,password,typeUser) VALUES (?,?,?,?)");
        $st->execute([$u->name,$u->email,$u->passwordHash,$u->typeUser]);
        return (int)$this->pdo->lastInsertId();
    }
    public function update(User $u): void {
        $st=$this->pdo->prepare("UPDATE `user` SET name=?, email=?, typeUser=? WHERE id=?");
        $st->execute([$u->name,$u->email,$u->typeUser,$u->id]);
    }
    public function delete(int $id): void {
        $st=$this->pdo->prepare("DELETE FROM `user` WHERE id=?"); $st->execute([$id]);
    }
    public function emailExists(string $email, ?int $ignoreId=null): bool {
        if($ignoreId){
            $st=$this->pdo->prepare("SELECT 1 FROM `user` WHERE email=? AND id<>?"); $st->execute([$email,$ignoreId]);
        } else {
            $st=$this->pdo->prepare("SELECT 1 FROM `user` WHERE email=?"); $st->execute([$email]);
        }
        return (bool)$st->fetchColumn();
    }
    public function findByEmail(string $email): ?User {
        $st = $this->pdo->prepare(
            "SELECT id, name, email, password AS passwordHash, typeUser 
            FROM `user`
            WHERE email = ?"
        );
        $st->execute([$email]);
        $r = $st->fetch();
        return $r ? new User($r['id'], $r['name'], $r['email'], $r['passwordHash'], $r['typeUser']) : null;
    }
}
