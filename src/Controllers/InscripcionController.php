<?php
namespace App\Controllers;

use App\Repositories\InscripcionRepository;
use Exception;

class InscripcionController {
    public function __construct(private InscripcionRepository $repo) {}

    public function handleRequest(string $method, ?int $id = null, array $data = []): array {
        try {
            // Recibe la petición POST desde estudiantes.php para inscribir
            if ($method === 'POST') {
                if (empty($data['id_estudiante']) || empty($data['id_curso'])) {
                    throw new Exception("Error interno: Faltan identificadores de estudiante o curso.");
                }
                return $this->repo->inscribir($data);
            } 
            
            // Recibe la petición GET desde listado_estudiantes_curso.php para mostrar la tabla
            elseif ($method === 'GET') {

                if(isset($data['id_curso'])) {

                    return [
                        'status'=>'success',
                        'data'=>$this->repo->listarPorCurso(
                            (int)$data['id_curso']
                        )
                    ];
                }

                if(isset($data['id_carrera'])) {

                    return [
                        'status'=>'success',
                        'data'=>$this->repo->listarCursosPorCarrera(
                            (int)$data['id_carrera']
                        )
                    ];
                }

                throw new Exception(
                    "Debe indicar un filtro."
                );
            }

            // Recibe la petición DELETE desde estudiantes.php para borrar inscripciones de un estudiante
            elseif ($method === 'DELETE') {
                if (empty($id)) {
                    throw new Exception("Error interno: falta el id del estudiante para eliminar sus inscripciones.");
                }
                return $this->repo->eliminarPorEstudiante($id);
            }

            throw new Exception("Método HTTP no soportado para el módulo de inscripciones.");
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}