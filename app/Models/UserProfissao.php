<?php
namespace App\Models;
class UserProfissao {

    public ?int $id;
    public string $codigo;
    public string $descricao_profissao;
    
    public function __construct(?int $id,string $codigo,string $descricao_profissao){
     $this->codigo=$codigo; $this->descricao_profissao=$descricao_profissao;
    }
    public static function fromArray(array $d): self {
        return new self($d['id']??null,$d['codigo'],$d['descricao_profissao']);
    }
}