<?php
namespace App\Models; // Diz que esta classe pertence ao grupo "App\Models" (organiza o código, como uma pasta)

class UserProfissao { // Início da definição da classe/objeto "UserProfissao" — representa uma profissão de usuário

    public ?int $id; // Guarda o identificador (id). ?int = pode ser número inteiro ou null (quando ainda não foi salvo)
    public string $codigo; // Guarda um código da profissão (texto)
    public string $descricao_profissao; // Guarda a descrição/nome da profissão (texto)
    
    public function __construct(?int $id,string $codigo,string $descricao_profissao){
     // Função chamada automaticamente ao criar um novo objeto.
     // Recebe: $id (pode ser null), $codigo e $descricao_profissao.
     $this->codigo=$codigo; $this->descricao_profissao=$descricao_profissao;
     // Armazena os valores recebidos nas "caixinhas" (propriedades) do objeto.
     // Observação: aqui o $id recebido NÃO está sendo atribuído a $this->id.
    }

    public static function fromArray(array $d): self {
        // Método estático que cria um objeto a partir de um array associativo.
        // Útil quando os dados vêm do banco ou de um formulário.
        return new self($d['id']??null,$d['codigo'],$d['descricao_profissao']);
        // Cria e retorna um novo UserProfissao usando os valores do array.
        // $d['id']??null => usa $d['id'] se existir, caso contrário usa null.
    }

}