<?php
// Front Controller (compatível PHP 7.4+)
declare(strict_types=1);
$basePath = dirname(__DIR__);
spl_autoload_register(function ($class) use ($basePath) {
    $prefix = 'App\\'; $baseDir = $basePath . '/app/';
    if (strpos($class, $prefix) === 0) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = $baseDir . $relative . '.php';
        if (file_exists($file)) { require $file; }
    }
});
use App\Controllers\UserProfissaoController;
$action = $_GET['action'] ?? 'indexProfissoes';
$controller = new UserProfissaoController();
switch ($action) {
    case 'indexProfissoes':  $controller->indexProfissoes(); break;
    case 'create': $controller->create(); break;
    case 'edit':   $controller->edit(); break;
    case 'delete': $controller->delete(); break;
    default: http_response_code(404); echo "Rota não encontrada.";
}
