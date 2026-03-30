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

// instancias user activos
$userRepository = new UserRepository();
$userService = new UserService($userRepository);
$userController = new UserController($userService);

$activoRepo = new \App\Repositories\ActivoRepository();
$activoService = new \App\Services\ActivoService($activoRepo);
$activoController = new \App\Controllers\ActivoController($activoService);

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// Buscar id
$id = null;
if (count($segments) > 1 && is_numeric(end($segments))) {
    $id = (int) end($segments);
}

// leer datos JSON
$requestData = [];
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true) ?? [];
}

$response = null; 

try {
    if (in_array('users', $segments) || in_array('usuarios', $segments)) {
        switch ($method) {
            case 'GET':
                $response = $id ? $userController->show($id) : ['status' => 'error', 'message' => 'Listado no implementado'];
                break;
            case 'POST':
                $response = $userController->store($requestData);
                break;
            case 'DELETE':
                $response = $id ? $userController->delete($id) : ['status' => 'error', 'message' => 'ID requerido'];
                break;
        }
    } 

    elseif (in_array('activos', $segments)) {
        switch ($method) {
            case 'GET':
                $response = $activoController->index();
                break;
            case 'POST':
                $response = $activoController->store($requestData);
                break;
            // AGREGAMOS EL CASO PUT QUE FALTABA
            case 'PUT':
                $response = $id ? $activoController->update($requestData, $id) : ['status' => 'error', 'message' => 'ID requerido'];
                break;
            case 'DELETE':
                $response = $id ? $activoController->destroy($id) : ['status' => 'error', 'message' => 'ID requerido'];
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