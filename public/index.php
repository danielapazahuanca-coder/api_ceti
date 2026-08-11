<?php

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

$db = \App\Database\Database::getInstance();

$activoRepo = new \App\Repositories\ActivoRepository();
$activoService = new \App\Services\ActivoService($activoRepo);
$activoController = new \App\Controllers\ActivoController($activoService);

$prestamoRepo = new \App\Repositories\PrestamoRepository();
$prestamoService = new \App\Services\PrestamoService($prestamoRepo, $activoRepo);
$prestamoController = new \App\Controllers\PrestamoController($prestamoService);

$userRepo = new \App\Repositories\UserRepository($db);
$authService = new \App\Services\AuthService($userRepo);
$authController = new \App\Controllers\AuthController($authService);

$gestionRepository = new \App\Repositories\GestionRepository($db);
$gestionService = new \App\Services\GestionService($gestionRepository);
$gestionController = new \App\Controllers\GestionController($gestionService);

$carreraRepo = new \App\Repositories\CarreraRepository();
$carreraService = new \App\Services\CarreraService($carreraRepo);
$carreraController = new \App\Controllers\CarreraController($carreraService);

$materiaRepo = new \App\Repositories\MateriaRepository();
$materiaService = new \App\Services\MateriaService($materiaRepo);
$materiaController = new \App\Controllers\MateriaController($materiaService);

$cursoRepo = new \App\Repositories\CursoRepository($db);
$cursoController = new \App\Controllers\CursoController($cursoRepo);

$asignacionRepo = new \App\Repositories\AsignacionRepository();
$asignacionController = new \App\Controllers\AsignacionController($asignacionRepo);

$estudianteRepo = new \App\Repositories\EstudianteRepository($db);
$estudianteController = new \App\Controllers\EstudianteController($estudianteRepo);

$inscripcionRepo = new \App\Repositories\InscripcionRepository($db);
$inscripcionController = new \App\Controllers\InscripcionController($inscripcionRepo);

$notaRepo = new \App\Repositories\NotaRepository($db);
$notaController = new \App\Controllers\NotaController($notaRepo);

$academicoRepo = new \App\Repositories\AcademicoRepository();
$academicoController = new \App\Controllers\AcademicoController($academicoRepo);

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

$resource = null;
$subResource = null;
$id = null;

// ================================================================
// ENRUTADOR — 'notas' debe ir ANTES de 'estudiantes'
// ================================================================
if (in_array('activos', $segments)) {
    $resource = 'activos';
    $resourceIndex = array_search('activos', $segments);
    $id = (isset($segments[$resourceIndex + 1]) && is_numeric($segments[$resourceIndex + 1])) ? (int)$segments[$resourceIndex + 1] : null;

} elseif (in_array('prestamos', $segments)) {
    $resource = 'prestamos';
    $resourceIndex = array_search('prestamos', $segments);
    $id = (isset($segments[$resourceIndex + 1]) && is_numeric($segments[$resourceIndex + 1])) ? (int)$segments[$resourceIndex + 1] : null;

} elseif (in_array('auth', $segments)) {
    $resource = 'auth';
    $resourceIndex = array_search('auth', $segments);
    $subResource = isset($segments[$resourceIndex + 1]) ? $segments[$resourceIndex + 1] : null;

} elseif (in_array('gestion', $segments)) {
    $resource = 'gestion';
    $resourceIndex = array_search('gestion', $segments);
    $subResource = isset($segments[$resourceIndex + 1]) ? $segments[$resourceIndex + 1] : null;

} elseif (in_array('carreras', $segments)) {
    $resource = 'carreras';
    $resourceIndex = array_search('carreras', $segments);
    if (isset($segments[$resourceIndex + 1]) && $segments[$resourceIndex + 1] !== '') {
        if (is_numeric($segments[$resourceIndex + 1])) {
            $id = (int)$segments[$resourceIndex + 1];
            $subResource = null;
        } else {
            $id = null;
            $subResource = $segments[$resourceIndex + 1];
        }
    }

} elseif (in_array('materias', $segments)) {
    $resource = 'materias';
    $resourceIndex = array_search('materias', $segments);
    if (isset($segments[$resourceIndex + 1]) && $segments[$resourceIndex + 1] !== '') {
        if (is_numeric($segments[$resourceIndex + 1])) {
            $id = (int)$segments[$resourceIndex + 1];
            $subResource = null;
        } else {
            $id = null;
            $subResource = $segments[$resourceIndex + 1];
        }
    }

} elseif (in_array('cursos', $segments)) {
    $resource = 'cursos';
    $resourceIndex = array_search('cursos', $segments);
    if (isset($segments[$resourceIndex + 1]) && $segments[$resourceIndex + 1] !== '') {
        if (is_numeric($segments[$resourceIndex + 1])) {
            $id = (int)$segments[$resourceIndex + 1];
            $subResource = null;
        } else {
            $id = null;
            $subResource = $segments[$resourceIndex + 1];
        }
    }

} elseif (in_array('asignaciones', $segments)) {
    $resource = 'asignaciones';
    $resourceIndex = array_search('asignaciones', $segments);
    if (isset($segments[$resourceIndex + 1]) && $segments[$resourceIndex + 1] !== '') {
        if (is_numeric($segments[$resourceIndex + 1])) {
            $id = (int)$segments[$resourceIndex + 1];
            $subResource = null;
        } else {
            $id = null;
            $subResource = $segments[$resourceIndex + 1];
        }
    }

} elseif (in_array('inscripciones', $segments)) {
    $resource = 'inscripciones';
    $resourceIndex = array_search('inscripciones', $segments);
    // Soporta /inscripciones/estudiante/{id} para DELETE
    if (
        isset($segments[$resourceIndex + 1]) && $segments[$resourceIndex + 1] === 'estudiante' &&
        isset($segments[$resourceIndex + 2]) && is_numeric($segments[$resourceIndex + 2])
    ) {
        $subResource = 'estudiante';
        $id = (int)$segments[$resourceIndex + 2];
    }

// *** NOTAS ANTES QUE ESTUDIANTES ***
} elseif (in_array('notas', $segments)) {
    $resource = 'notas';
    $resourceIndex = array_search('notas', $segments);
    if (isset($segments[$resourceIndex + 1])) {
        if ($segments[$resourceIndex + 1] === 'docente' && isset($segments[$resourceIndex + 2])) {
            $subResource = 'docente';
            $id = (int)$segments[$resourceIndex + 2];
        } elseif ($segments[$resourceIndex + 1] === 'estudiantes') {
            $subResource = 'estudiantes';
        } elseif ($segments[$resourceIndex + 1] === 'estudiante' && isset($segments[$resourceIndex + 2]) && is_numeric($segments[$resourceIndex + 2])) {
            $subResource = 'estudiante';
            $id = (int)$segments[$resourceIndex + 2];
        }
    }

} elseif (in_array('academico', $segments)) {

    $resource = 'academico';

    $resourceIndex = array_search('academico', $segments);

    $subResource = $segments[$resourceIndex + 1] ?? null;

    if (
        isset($segments[$resourceIndex + 2]) &&
        is_numeric($segments[$resourceIndex + 2])
    ) {
        $id = (int)$segments[$resourceIndex + 2];
    } 
} elseif (in_array('estudiantes', $segments)) {
    $resource = 'estudiantes';
    $resourceIndex = array_search('estudiantes', $segments);
    if (isset($segments[$resourceIndex + 1]) && is_numeric($segments[$resourceIndex + 1])) {
        $id = (int)$segments[$resourceIndex + 1];
    }
}

$requestData = [];
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true) ?? [];
}

$response = null;

try {
    if ($resource === 'activos') {
        switch ($method) {
            case 'GET':   $response = $activoController->index(); break;
            case 'POST':  $response = $activoController->store($requestData); break;
            case 'PUT':   $response = $id ? $activoController->update($requestData, $id) : ['status' => 'error', 'message' => 'ID requerido']; break;
            case 'DELETE':$response = $id ? $activoController->destroy($id) : ['status' => 'error', 'message' => 'ID requerido']; break;
        }

    } elseif ($resource === 'prestamos') {
        switch ($method) {
            case 'GET':  $response = $id ? $prestamoController->show($id) : $prestamoController->index(); break;
            case 'POST': $response = $prestamoController->store($requestData); break;
            case 'PUT':  $response = $id ? $prestamoController->return($id) : ['status' => 'error', 'message' => 'ID de préstamo requerido']; break;
        }

    } elseif ($resource === 'auth') {
        if ($subResource === 'login' && $method === 'POST') {
            $response = $authController->login($requestData);
        } elseif ($subResource === 'users' && $method === 'GET') {
            $response = $authController->indexUsers();
        } elseif ($subResource === 'users' && $method === 'POST') {
            $response = $authController->createUser($requestData);
        } elseif ($subResource === 'users' && $method === 'PUT') {
            $userId = (isset($segments[$resourceIndex + 2]) && is_numeric($segments[$resourceIndex + 2])) ? (int)$segments[$resourceIndex + 2] : null;
            $response = $userId ? $authController->updateForm($requestData, $userId) : ['status' => 'error', 'message' => 'ID de usuario requerido'];
        } elseif ($subResource === 'users' && $method === 'DELETE') {
            $userId = (isset($segments[$resourceIndex + 2]) && is_numeric($segments[$resourceIndex + 2])) ? (int)$segments[$resourceIndex + 2] : null;
            $response = $userId ? $authController->destroyUser($userId) : ['status' => 'error', 'message' => 'ID de usuario requerido'];
        } else {
            $response = ['status' => 'error', 'message' => 'Método o sub-recurso de autenticación no permitido'];
        }

    } elseif ($resource === 'gestion') {
        if ($method === 'GET' && empty($subResource)) {
            $response = $gestionController->index();
        } elseif ($method === 'POST' && empty($subResource)) {
            $response = $gestionController->create($requestData);
        } elseif ($method === 'PUT' && $subResource === 'activate') {
            $gestionId = (isset($segments[$resourceIndex + 2]) && is_numeric($segments[$resourceIndex + 2])) ? (int)$segments[$resourceIndex + 2] : null;
            $response = $gestionId ? $gestionController->activate($gestionId, $requestData) : ['status' => 'error', 'message' => 'ID de gestión requerido'];
        } else {
            $response = ['status' => 'error', 'message' => 'Ruta de gestión no soportada.'];
        }

    } elseif ($resource === 'carreras') {
        switch ($method) {
            case 'GET':  $response = $carreraController->index(); break;
            case 'POST': $response = $carreraController->store($requestData); break;
            case 'PUT':
                if ($subResource === 'toggle') {
                    $carreraId = (isset($segments[$resourceIndex + 2]) && is_numeric($segments[$resourceIndex + 2])) ? (int)$segments[$resourceIndex + 2] : null;
                    $response = $carreraId ? $carreraController->toggle($carreraId, $requestData) : ['status' => 'error', 'message' => 'ID requerido para toggle'];
                } else {
                    $response = $id ? $carreraController->update($requestData, $id) : ['status' => 'error', 'message' => 'ID requerido para actualizar'];
                }
                break;
            default: $response = ['status' => 'error', 'message' => 'Método HTTP no soportado para carreras.']; break;
        }

    } elseif ($resource === 'materias') {
        switch ($method) {
            case 'GET':  $response = $materiaController->handleRequest('GET', $requestUri, []); break;
            case 'POST': $response = $materiaController->handleRequest('POST', $requestUri, $requestData); break;
            case 'PUT':
                if ($subResource === 'toggle') {
                    $materiaId = (isset($segments[$resourceIndex + 2]) && is_numeric($segments[$resourceIndex + 2])) ? (int)$segments[$resourceIndex + 2] : null;
                    $response = $materiaId ? $materiaController->handleRequest('PUT', "/toggle/$materiaId", $requestData) : ['status' => 'error', 'message' => 'ID requerido para toggle'];
                } else {
                    $response = $id ? $materiaController->handleRequest('PUT', "/materias/$id", $requestData) : ['status' => 'error', 'message' => 'ID requerido para actualizar'];
                }
                break;
            default: $response = ['status' => 'error', 'message' => 'Método no soportado para materias.']; break;
        }

    } elseif ($resource === 'cursos') {
        switch ($method) {
            case 'GET':  $response = $cursoController->handleRequest('GET', $requestUri, []); break;
            case 'POST': $response = $cursoController->handleRequest('POST', $requestUri, $requestData); break;
            case 'PUT':
                if ($subResource === 'toggle') {
                    $cursoId = (isset($segments[$resourceIndex + 2]) && is_numeric($segments[$resourceIndex + 2])) ? (int)$segments[$resourceIndex + 2] : null;
                    $response = $cursoId ? $cursoController->handleRequest('PUT', "/toggle/$cursoId", $requestData) : ['status' => 'error', 'message' => 'ID requerido para toggle'];
                } else {
                    $response = $id ? $cursoController->handleRequest('PUT', "/cursos/$id", $requestData) : ['status' => 'error', 'message' => 'ID requerido para actualizar'];
                }
                break;
            default: $response = ['status' => 'error', 'message' => 'Método no soportado para cursos.']; break;
        }

    } elseif ($resource === 'asignaciones') {
        switch ($method) {
            case 'GET':    $response = $asignacionController->handleRequest('GET', $requestUri, []); break;
            case 'POST':   $response = $asignacionController->handleRequest('POST', $requestUri, $requestData); break;
            case 'DELETE': $response = $asignacionController->handleRequest('DELETE', $requestUri, []); break;
            default: $response = ['status' => 'error', 'message' => 'Método no soportado para asignaciones.']; break;
        }

    } elseif ($resource === 'inscripciones') {
        switch ($method) {
            case 'POST': $response = $inscripcionController->handleRequest('POST', null, $requestData); break;
            case 'GET':  $response = $inscripcionController->handleRequest('GET', null, $_GET); break;
            case 'DELETE':
                if ($subResource === 'estudiante' && $id) {
                    $response = $inscripcionController->handleRequest('DELETE', $id, []);
                } else {
                    $response = ['status' => 'error', 'message' => 'Se requiere el id del estudiante para eliminar sus inscripciones.'];
                }
                break;
        }

    } elseif ($resource === 'notas') {
        $params = array_merge($_GET, $requestData);
        $response = $notaController->handleRequest($method, $subResource, $id, $params);

    } elseif ($resource === 'academico') {

        $response = $academicoController->handleRequest(
            $method,
            $subResource,
            $id,
            $_GET
        );

    } elseif ($resource === 'estudiantes') {
        $response = $estudianteController->handleRequest($method, $id, $_GET);
    }

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
    http_response_code(500);
}

if ($response !== null) {
    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']);
}