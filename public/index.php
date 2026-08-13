<?php
declare(strict_types=1);

session_start();

$basePath = dirname(__DIR__);

/**
 * BASE_URL é calculada automaticamente a partir do caminho real do "index.php"
 * que atendeu a requisição, então funciona sem nenhuma configuração tanto:
 * - localmente no XAMPP, numa subpasta (ex.: /nutrihealth/public)
 * - no Render, servida na raiz do domínio (ex.: "")
 *
 * dirname('/nutrihealth/public/index.php') = '/nutrihealth/public'
 * dirname('/index.php') = '/' -> rtrim vira '' (raiz)
 */
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
define('BASE_URL', rtrim($scriptDir, '/'));

/**
 * Monta uma URL absoluta (com esquema e domínio), necessária para integrações
 * externas que chamam o nosso sistema de fora (ex.: webhook da InfinitePay).
 * Detecta HTTPS mesmo atrás de um proxy reverso (ex.: Render), via
 * X-Forwarded-Proto, que é quem realmente termina o HTTPS nesses ambientes.
 */
function app_absolute_url(string $path = ''): string
{
    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $isHttps = $forwardedProto === 'https'
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? '') == 443;

    $scheme = $isHttps ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . BASE_URL . $path;
}

// Autoload do Composer (biblioteca phpoffice/phpword, usada para gerar o contrato em Word).
// Carregado de forma tolerante: se "composer install" ainda não rodou, o resto do sistema
// continua funcionando normalmente — só a geração do contrato em Word fica desativada.
if (file_exists($basePath . '/vendor/autoload.php')) {
    require $basePath . '/vendor/autoload.php';
}

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
use App\Controllers\PatientController;
use App\Controllers\AppointmentController;
use App\Controllers\AppointmentTypeController;
use App\Controllers\ConsultationController;
use App\Controllers\ReportController;
use App\Controllers\ServiceTypeController;
use App\Controllers\ContractController;

// Controller e action padrão
$controllerName = $_GET['controller'] ?? 'user';
$action         = $_GET['action']     ?? 'index';

// Rotas públicas (sem precisar estar logado)
$publicRoutes = [
    'user'     => ['login', 'logout'], // logout é tecnicamente público, mas só tira sessão
    'contract' => ['infinitepayWebhook'], // a InfinitePay chama essa rota de fora, sem login
];

// Se não for rota pública, exige login
$isPublic = in_array($action, $publicRoutes[$controllerName] ?? [], true);

if (!$isPublic && empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/?controller=user&action=login');
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
    case 'patient':
        $controller = new PatientController();
        break;       
    case 'appointment':
        $controller = new AppointmentController();
        break;         
    case 'appointmenttype':
        $controller = new AppointmentTypeController();
        break;
    case 'report':
        $controller = new ReportController();
        break;
    case 'consultation':
        $controller = new ConsultationController();
        break;
    case 'servicetype':
        $controller = new ServiceTypeController();
        break;
    case 'contract':
        $controller = new ContractController();
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
