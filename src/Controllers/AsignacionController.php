<?php
namespace App\Controllers;

use App\Repositories\AsignacionRepository;

class AsignacionController {
    private $repository;

    public function __construct(AsignacionRepository $repository) {
        $this->repository = $repository;
    }

    public function handleRequest($method, $uri, $body) {
        // GET /public/asignaciones
        if ($method === 'GET' && !strpos($uri, 'curso')) {
            $data = $this->repository->listarAsignaciones();
            return ['status' => 'success', 'data' => $data];
        }

        // GET /public/asignaciones/curso/{id} -> Carga las materias automáticas de un curso
        if ($method === 'GET' && strpos($uri, 'curso') !== false) {
            preg_match('/curso\/(\d+)/', $uri, $matches);
            $idCurso = $matches[1] ?? null;
            if (!$idCurso) return ['status' => 'error', 'message' => 'ID de curso requerido.'];
            
            $data = $this->repository->listarMateriasPorCurso((int)$idCurso);
            return ['status' => 'success', 'data' => $data];
        }

        // POST /public/asignaciones -> Guarda una asignación
        if ($method === 'POST') {
            if (empty($body['id_docente']) || empty($body['id_curso']) || empty($body['id_materia'])) {
                return ['status' => 'error', 'message' => 'Faltan datos obligatorios para la asignación.'];
            }
            return $this->repository->asignarDocente($body);
        }

        // DELETE /public/asignaciones/{id} -> Quita a un docente de una materia
        if ($method === 'DELETE') {
            preg_match('/asignaciones\/(\d+)/', $uri, $matches);
            $id = $matches[1] ?? null;
            if (!$id) return ['status' => 'error', 'message' => 'ID de asignación requerido.'];

            $res = $this->repository->eliminar((int)$id);
            return $res ? ['status' => 'success', 'message' => 'Asignación eliminada.'] : ['status' => 'error', 'message' => 'No se pudo eliminar.'];
        }

        return ['status' => 'error', 'message' => 'Endpoint no soportado en Asignaciones.'];
    }
}