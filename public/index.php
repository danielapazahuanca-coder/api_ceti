<?php
//require_once __DIR__ . '/../vendor/autoload.php';
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

$userRepository = new UserRepository();
$userService = new UserService($userRepository);
$userController = new UserController($userService);

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

$id = null;
if (count($segments) > 1 && is_numeric(end($segments))) {
    $id = (int) end($segments);
}

$requestData = [];
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true) ?? [];
}

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                // GET /users/1 - Obtener usuario específico
                $response = $userController->show($id);
            } else {
                // GET /users - Listar todos (opcional)
                $response = ['status' => 'error', 'message' => 'Método no implementado'];
            }
            break;

        case 'POST':
            // POST /users - Crear usuario
            $response = $userController->store($requestData);
            break;

        case 'PUT':
            if ($id) {
                // PUT /users/1 - Actualizar usuario
                $response = $userController->update($requestData, $id);
            } else {
                $response = ['status' => 'error', 'message' => 'ID requerido'];
            }
            break;

        case 'DELETE':
            if ($id) {
                // DELETE /users/1 - Eliminar usuario
                $response = $userController->delete($id);
            } else {
                $response = ['status' => 'error', 'message' => 'ID requerido'];
            }
            break;

        default:
            $response = ['status' => 'error', 'message' => 'Método no permitido'];
            http_response_code(405);
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
    http_response_code(500);
}

echo json_encode($response, JSON_PRETTY_PRINT);