<?php
use App\Controllers\UserController;
use App\Repositories\UserRepository;
use App\Services\UserService;

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inicialización de Usuarios
$userRepository = new UserRepository();
$userService = new UserService($userRepository);
$userController = new UserController($userService);

// Inicialización de Activos
$activoRepo = new \App\Repositories\ActivoRepository();
$activoService = new \App\Services\ActivoService($activoRepo);
$activoController = new \App\Controllers\ActivoController($activoService);

// Inicialización de Préstamos (NUEVO)
$prestamoRepo = new \App\Repositories\PrestamoRepository();
$prestamoService = new \App\Services\PrestamoService($prestamoRepo, $activoRepo);
$prestamoController = new \App\Controllers\PrestamoController($prestamoService);

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

$segments = explode('/', trim($path, '/'));

$resource = null;
$id = null;

// Enrutador manual
if (($resourceIndex = array_search('users', $segments)) !== false) {
    $resource = 'users';
    $id = (isset($segments[$resourceIndex + 1]) && is_numeric($segments[$resourceIndex + 1])) ? (int)$segments[$resourceIndex + 1] : null;
} elseif (($resourceIndex = array_search('usuarios', $segments)) !== false) {
    $resource = 'usuarios';
    $id = (isset($segments[$resourceIndex + 1]) && is_numeric($segments[$resourceIndex + 1])) ? (int)$segments[$resourceIndex + 1] : null;
} elseif (($resourceIndex = array_search('activos', $segments)) !== false) {
    $resource = 'activos';
    $id = (isset($segments[$resourceIndex + 1]) && is_numeric($segments[$resourceIndex + 1])) ? (int)$segments[$resourceIndex + 1] : null;
} elseif (($resourceIndex = array_search('prestamos', $segments)) !== false) { // NUEVA RUTA
    $resource = 'prestamos';
    $id = (isset($segments[$resourceIndex + 1]) && is_numeric($segments[$resourceIndex + 1])) ? (int)$segments[$resourceIndex + 1] : null;
}

$requestData = [];
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true) ?? [];
}

$response = null;

try {
    // RECURSO: USERS / USUARIOS
    if ($resource === 'users' || $resource === 'usuarios') {
        switch ($method) {
            case 'GET':
                $response = $id ? $userController->show($id) : ['status' => 'error', 'message' => 'Listado no implementado'];
                break;
            case 'POST':
                $response = $userController->store($requestData);
                break;
            case 'PUT':
                $response = $id ? $userController->update($requestData, $id) : ['status' => 'error', 'message' => 'ID requerido'];
                break;
            case 'DELETE':
                $response = $id ? $userController->delete($id) : ['status' => 'error', 'message' => 'ID requerido'];
                break;
        }
    }

    // RECURSO: ACTIVOS
    elseif ($resource === 'activos') {
        switch ($method) {
            case 'GET':
                $response = $activoController->index();
                break;
            case 'POST':
                $response = $activoController->store($requestData);
                break;
            case 'PUT':
                $response = $id ? $activoController->update($requestData, $id) : ['status' => 'error', 'message' => 'ID requerido'];
                break;
            case 'DELETE':
                $response = $id ? $activoController->destroy($id) : ['status' => 'error', 'message' => 'ID requerido'];
                break;
        }
    }

    // RECURSO: PRESTAMOS (NUEVO)
    elseif ($resource === 'prestamos') {
        switch ($method) {
            case 'GET':
                $response = $id ? $prestamoController->show($id) : $prestamoController->index();
                break;
            case 'POST':
                $response = $prestamoController->store($requestData);
                break;
            case 'PUT': // Usado para devoluciones
                $response = $id ? $prestamoController->return($id) : ['status' => 'error', 'message' => 'ID de préstamo requerido'];
                break;
        }
    }

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
    http_response_code(500);
}

if ($response) {
    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']);
}