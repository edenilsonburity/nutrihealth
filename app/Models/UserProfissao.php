<?php
namespace App\Models;
class UserProfissao {
    public string $codigo;
    public string $descricao_profissao;
    
    public function __construct(?string $codigo,string $descricao_profissao){
     $this->codigo=$codigo; $this->descricao_profissao=$descricao_profissao;
    }
    public static function fromArray(array $d): self {
        return new self($d['id']??null,$d['name'],$d['email'],$d['password']??($d['passwordHash']??''),$d['typeUser']??'U');
    }
}