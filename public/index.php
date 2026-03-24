<?php
// Autoloader para cargar las clases automáticamente
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// Importamos los Controladores, Repositorios y Servicios
use App\Controllers\UserController;
use App\Repositories\Interfaces\UserRepository;
use App\Services\UserService;

use App\Controllers\ActivoController;
use App\Repositories\Interfaces\ActivoRepository;
use App\Services\ActivoService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- INSTANCIAS DE USUARIOS ---
$userRepository = new UserRepository();
$userService = new UserService($userRepository);
$userController = new UserController($userService);

// --- INSTANCIAS DE ACTIVOS ---
$activoRepository = new ActivoRepository();
$activoService = new ActivoService($activoRepository);
$activoController = new ActivoController($activoService);

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// Detectar qué recurso se está pidiendo (users o activos)
// Ejemplo: si la URL es /api_ceti/public/index.php/activos -> el recurso es 'activos'
$resource = $segments[count($segments) - 1];
$id = null;

if (is_numeric($resource)) {
    $id = (int) $resource;
    $resource = $segments[count($segments) - 2];
}

$requestData = [];
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true) ?? [];
}

try {
    // RUTAS PARA USUARIOS
    if ($resource === 'users') {
        switch ($method) {
            case 'GET':
                $response = $id ? $userController->show($id) : $userController->index();
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
    // RUTAS PARA ACTIVOS (MUEBLES)
    elseif ($resource === 'activos') {
        switch ($method) {
            case 'GET':
                $response = $id ? $activoController->show($id) : $activoController->index();
                break;
            case 'POST':
                $response = $activoController->store($requestData);
                break;
            case 'PUT':
                $response = $id ? $activoController->update($requestData, $id) : ['status' => 'error', 'message' => 'ID requerido'];
                break;
            case 'DELETE':
                $response = $id ? $activoController->delete($id) : ['status' => 'error', 'message' => 'ID requerido'];
                break;
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Recurso no encontrado: ' . $resource];
        http_response_code(404);
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
    http_response_code(500);
}

echo json_encode($response, JSON_PRETTY_PRINT);