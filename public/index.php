<?php
declare(strict_types=1);

session_start();

$basePath = dirname(__DIR__);

spl_autoload_register(function ($class) use ($basePath) {
    $prefix  = 'App\\';
    $baseDir = $basePath . '/app/';

    if (strpos($class, $prefix) === 0) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file     = $baseDir . $relative . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

use App\Controllers\UserController;
use App\Controllers\OccupationController;

// Controller e action padrão
$controllerName = $_GET['controller'] ?? 'user';
$action         = $_GET['action']     ?? 'index';

// Rotas públicas (sem precisar estar logado)
$publicRoutes = [
    'user' => ['login', 'logout'], // logout é tecnicamente público, mas só tira sessão
];

// Se não for rota pública, exige login
$isPublic = in_array($action, $publicRoutes[$controllerName] ?? [], true);

if (!$isPublic && empty($_SESSION['user_id'])) {
    header('Location: /nutrihealth/public/?controller=user&action=login');
    exit;
}

// Instancia o controller certo
switch ($controllerName) {
    case 'user':
        $controller = new UserController();
        break;
    case 'occupation':
        $controller = new OccupationController();
        break;
    default:
        http_response_code(404);
        echo 'Controller não encontrada';
        exit;
}

// Chama o método solicitado
if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo 'Rota não encontrada.';
    exit;
}

$controller->{$action}();
