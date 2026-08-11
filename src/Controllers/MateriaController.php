<?php
namespace App\Controllers;

use App\Services\MateriaService;
use Exception;

class MateriaController {
    private MateriaService $service;

    public function __construct(MateriaService $service) {
        $this->service = $service;
    }

    public function handleRequest($method, $uri, $body) {
        if ($method === 'GET') {
            try {
                $data = $this->service->listarMaterias();
                return ['status' => 'success', 'data' => $data];
            } catch (Exception $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        if ($method === 'POST') {
            try {
                if (empty($body['nombre']) || empty($body['sigla']) || empty($body['id_carrera']) || empty($body['id_nivel'])) {
                    return ['status' => 'error', 'message' => 'Todos los campos son obligatorios para crear una materia.'];
                }
                $res = $this->service->registrarMateria($body);
                return ['status' => 'success', 'message' => $res['message']];
            } catch (Exception $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        if ($method === 'PUT') {
            try {
                // Verificar si es un cambio de estado (toggle)
                if (str_contains($uri, '/toggle/')) {
                    // Extraer ID de la URI original enviada por el index (/toggle/{id})
                    $parts = explode('/', trim($uri, '/'));
                    $id_materia = (int)end($parts);
                    
                    if (!$id_materia) {
                        return ['status' => 'error', 'message' => 'ID de materia no válido para toggle.'];
                    }
                    
                    $res = $this->service->cambiarEstadoMateria($id_materia, $body);
                    return ['status' => 'success', 'message' => $res['message']];
                } 
                
                // Si es actualización normal (/materias/{id})
                $parts = explode('/', trim($uri, '/'));
                $id_materia = (int)end($parts);
                if (!$id_materia) {
                    return ['status' => 'error', 'message' => 'ID de materia no especificado para actualizar.'];
                }
                
                $res = $this->service->actualizarMateria($id_materia, $body);
                return ['status' => 'success', 'message' => $res['message']];

            } catch (Exception $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return ['status' => 'error', 'message' => 'Método no soportado en Materias.'];
    }
}