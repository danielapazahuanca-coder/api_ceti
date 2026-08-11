<?php
namespace App\Controllers;

use App\Repositories\CursoRepository;

class CursoController {
    private $repository;

    public function __construct(CursoRepository $repository) {
        $this->repository = $repository;
    }

    public function handleRequest($method, $uri, $body) {
        // GET /public/cursos
        if ($method === 'GET' && !strpos($uri, 'toggle')) {
            $data = $this->repository->listar();
            return ['status' => 'success', 'data' => $data];
        }

        // POST /public/cursos
        if ($method === 'POST') {
            if (empty($body['id_carrera']) || empty($body['id_nivel']) || empty($body['id_gestion']) || empty($body['paralelo'])) {
                return ['status' => 'error', 'message' => 'Faltan campos mandatorios obligatorios (*).'];
            }
            return $this->repository->crear($body);
        }

        // PUT /public/cursos/toggle/{id}
        if ($method === 'PUT' && strpos($uri, 'toggle') !== false) {
            preg_match('/toggle\/(\d+)/', $uri, $matches);
            $id = $matches[1] ?? null;
            if (!$id) return ['status' => 'error', 'message' => 'ID de curso no especificado.'];
            
            $this->repository->toggleEstado((int)$id, $body['estado_actual'] ?? 1);
            return ['status' => 'success', 'message' => 'Estado modificado de forma correcta.'];
        }

        // PUT /public/cursos/{id}
        if ($method === 'PUT') {
            preg_match('/cursos\/(\d+)/', $uri, $matches);
            $id = $matches[1] ?? null;
            if (!$id) return ['status' => 'error', 'message' => 'ID de curso requerido para la edición.'];
            
            return $this->repository->modificar((int)$id, $body);
        }

        return ['status' => 'error', 'message' => 'Endpoint o método no soportado en Cursos.'];
    }
}