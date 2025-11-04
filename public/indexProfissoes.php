<?php
// Início do arquivo PHP

// Front Controller (compatível PHP 7.4+)
// comentário informativo: este arquivo é o ponto de entrada da aplicação

declare(strict_types=1);
// Diz ao PHP para usar checagem rígida de tipos — se uma função diz que recebe int e você passar string, o PHP reclama.

$basePath = dirname(__DIR__);
// Pega o diretório pai do diretório atual e guarda em $basePath.
// Se este arquivo está em public/, $basePath será o caminho até a pasta do projeto.

spl_autoload_register(function ($class) use ($basePath) {
// Registra uma função que será chamada automaticamente quando uma classe for usada mas ainda não foi carregada.
// Ou seja: quando você fizer new App\Algo, o PHP tenta carregar o arquivo da classe para você.
    $prefix = 'App\\'; $baseDir = $basePath . '/app/';
    // Define que as classes do projeto começam com o namespace "App\" e que os arquivos ficam em /app/ do projeto.

    if (strpos($class, $prefix) === 0) {
    // Verifica se o nome da classe começa com "App\". Se não começar, esta função ignora.
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        // Remove o "App\" do começo do nome da classe e troca as barras de namespace "\" por "/" para formar caminho de arquivo.
        $file = $baseDir . $relative . '.php';
        // Monta o caminho completo do arquivo que deveria conter a classe, por exemplo: /caminho/do/projeto/app/Controllers/ProfissaoController.php
        if (file_exists($file)) { require $file; }
        // Se o arquivo existir, inclui ele (carrega o código da classe). Se não existir, nada acontece aqui.
    }
});

use App\Controllers\ProfissaoController;
// Diz que vamos usar a classe ProfissaoController que está no namespace App\Controllers.
// Isso permite escrever apenas ProfissaoController() mais abaixo sem o caminho completo.

$action = $_GET['action'] ?? 'indexProfissoes';
// Lê a query string 'action' da URL (ex: ?action=editProfissoes).
// Se não existir, usa 'indexProfissoes' como padrão.
// Ou seja: define qual ação o usuário quer executar.

$controller = new ProfissaoController();
// Cria uma nova instância (objeto) do controlador de profissão.
// Esse objeto tem métodos que vão tratar cada ação (index, create, edit, delete).

switch ($action) {
    case 'indexProfissoes':  $controller->indexProfissoes(); break;
    // Se $action for 'indexProfissoes', chama o método indexProfissoes() do controlador.
    // Normalmente esse método lista as profissões.

    case 'createProfissoes': $controller->createProfissoes(); break;
    // Se $action for 'createProfissoes', chama createProfissoes() — geralmente mostra ou processa o formulário de criação.

    case 'editProfissoes':   $controller->editProfissoes(); break;
    // Se $action for 'editProfissoes', chama editProfissoes() — geralmente mostra ou processa o formulário de edição.

    case 'deleteProfissoes': $controller->deleteProfissoes(); break;
    // Se $action for 'deleteProfissoes', chama deleteProfissoes() — geralmente remove um registro.

    default: http_response_code(404); echo "Rota não encontrada.";
    // Se $action não bater com nenhum case acima, retorna erro 404 e mostra "Rota não encontrada."
    // Ou seja: a URL pediu algo que o sistema não sabe fazer.
}
