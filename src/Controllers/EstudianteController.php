<?php
namespace App\Controllers;

use App\Repositories\EstudianteRepository;

class EstudianteController {
    private $repository;

    public function __construct(EstudianteRepository $repository) {
        $this->repository = $repository;
    }

    public function handleRequest($method, $id, $queryParams) {
        $sucursal = $queryParams['sucursal'] ?? 'LP'; 

        switch ($method) {
            case 'GET':
                // SI LLEGA EL FILTRO, LLAMA AL MÉTODO DE FILTRADO E IGNORA EL RESTO
                if (!empty($queryParams['id_curso_filtro'])) {
                    $data = $this->repository->getDisponiblesParaInscripcion($sucursal, (int)$queryParams['id_curso_filtro']);
                    return ["status" => "success", "data" => $data];
                }

                // Comportamiento normal si se busca un estudiante específico por ID
                if ($id) {
                    $data = $this->repository->getById($id);
                    return $data ? ["status" => "success", "data" => $data] : ["status" => "error", "message" => "Estudiante no encontrado."];
                }

                $id_gestion = !empty($queryParams['id_gestion']) ? (int)$queryParams['id_gestion'] : null;

                // SI LLEGA EL PARÁMETRO 'buscar', SE DELEGA AL MÉTODO DE BÚSQUEDA
                if (!empty($queryParams['buscar'])) {
                    $data = $this->repository->buscar($sucursal, $queryParams['buscar'], $id_gestion);
                    return ["status" => "success", "data" => $data];
                }

                $data = $this->repository->getAllConEstadoInscripcion($sucursal, $id_gestion);

                return ["status" => "success", "data" => $data];

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['ci']) || empty($input['nombres']) || empty($input['apellidos']) || empty($input['sucursal_varchar'])) {
                    http_response_code(400);
                    return ["status" => "error", "message" => "Datos incompletos."];
                }
                try {
                    if ($this->repository->create($input)) {
                        return ["status" => "success", "message" => "Estudiante registrado con éxito."];
                    }
                    throw new \Exception("Error al insertar.");
                } catch (\Exception $e) {
                    http_response_code(500);
                    return ["status" => "error", "message" => "Error: El CI ya existe o hay un problem con la base de datos."];
                }

            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true);
                if (!$id || empty($input['ci']) || empty($input['nombres']) || empty($input['apellidos'])) {
                    http_response_code(400);
                    return ["status" => "error", "message" => "Datos insuficientes."];
                }
                return $this->repository->update($id, $input) ? 
                    ["status" => "success", "message" => "Estudiante actualizado."] : 
                    ["status" => "error", "message" => "No se pudo actualizar."];

            case 'DELETE':
                if (!$id) {
                    http_response_code(400);
                    return ["status" => "error", "message" => "ID requerido."];
                }
                return $this->repository->delete($id) ? 
                    ["status" => "success", "message" => "Estudiante eliminado."] : 
                    ["status" => "error", "message" => "Error al eliminar."];

            default:
                http_response_code(405);
                return ["status" => "error", "message" => "Método no permitido."];
        }
    }
}