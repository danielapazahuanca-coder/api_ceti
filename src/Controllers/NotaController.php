<?php
namespace App\Controllers;

use App\Repositories\NotaRepository;
use Exception;

class NotaController {
    private $repo;

    public function __construct(NotaRepository $repo) {
        $this->repo = $repo;
    }

    public function handleRequest(string $method, ?string $subResource, ?int $id, array $data = []): array {
        try {
            // Se extrae la acción buscando consistencia en todas las variables posibles
            $actionReporte = $data['action'] ?? $_GET['action'] ?? $subResource ?? '';

            // 1. Obtener Cursos por docente
            if ($method === 'GET' && $subResource === 'docente' && $id) {
                return ['status' => 'success', 'data' => $this->repo->listarCursosPorDocente($id)];
            }

            // 2. Reporte: Historial Académico de un estudiante individual
            if ($method === 'GET' && ($subResource === 'reporte-estudiante' || $actionReporte === 'reporte-estudiante' || $actionReporte === 'historial')) {
                $id_estudiante = isset($data['id_estudiante']) ? (int)$data['id_estudiante'] : (isset($_GET['id_estudiante']) ? (int)$_GET['id_estudiante'] : 0);
                if ($id_estudiante <= 0) {
                    throw new Exception("El ID de estudiante es obligatorio para generar el historial.");
                }
                return ['status' => 'success', 'data' => $this->repo->obtenerHistorialEstudiante($id_estudiante)];
            }

            // 3. Reporte: Acta de Calificaciones del Curso Completo (Una sola materia)
            if ($method === 'GET' && ($subResource === 'reporte-acta' || $actionReporte === 'reporte-acta' || $actionReporte === 'acta')) {
                $id_curso = isset($data['id_curso']) ? (int)$data['id_curso'] : (isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0);
                $id_materia = isset($data['id_materia']) ? (int)$data['id_materia'] : (isset($_GET['id_materia']) ? (int)$_GET['id_materia'] : 0);
                if ($id_curso <= 0 || $id_materia <= 0) {
                    throw new Exception("Parámetros 'id_curso' e 'id_materia' son obligatorios para el acta.");
                }
                return ['status' => 'success', 'data' => $this->repo->obtenerActaCalificacionesCurso($id_curso, $id_materia)];
            }

            // 4. NUEVO Reporte: Centralizador de Curso (Todas las materias de los estudiantes del curso)
            if ($method === 'GET' && $actionReporte === 'centralizador') {
                $id_curso = isset($data['id_curso']) ? (int)$data['id_curso'] : (isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0);
                if ($id_curso <= 0) {
                    throw new Exception("El parámetro 'id_curso' es obligatorio para el centralizador.");
                }
                return ['status' => 'success', 'data' => $this->repo->obtenerCentralizadorCurso($id_curso)];
            }

            // 5. NUEVO Reporte: Boletines individuales en masa (Notas de todas las materias por separado por alumno)
            if ($method === 'GET' && $actionReporte === 'boletines') {
                $id_curso = isset($data['id_curso']) ? (int)$data['id_curso'] : (isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0);
                if ($id_curso <= 0) {
                    throw new Exception("El parámetro 'id_curso' es obligatorio para las libretas.");
                }
                return ['status' => 'success', 'data' => $this->repo->obtenerBoletinesCurso($id_curso)];
            }

            // 6. Reporte: Listados generales (estudiantes, usuarios, materias)
            if ($method === 'GET' && ($subResource === 'reporte-general' || $actionReporte === 'reporte-general' || $actionReporte === 'general')) {
                $tipo = $data['tipo'] ?? $_GET['tipo'] ?? '';
                $sucursal = $data['sucursal'] ?? $_GET['sucursal'] ?? '';
                if (empty($tipo) || empty($sucursal)) {
                    throw new Exception("Los parámetros 'tipo' y 'sucursal' son obligatorios.");
                }
                $id_gestion_raw = $data['id_gestion'] ?? $_GET['id_gestion'] ?? null;
                $id_gestion = ($id_gestion_raw !== null && $id_gestion_raw !== '') ? (int)$id_gestion_raw : null;
                return ['status' => 'success', 'data' => $this->repo->obtenerListadoGeneral($tipo, $sucursal, $id_gestion)];
            }

            // 7. Listar Estudiantes filtrados por Curso y Materia (Para el registro normal de notas del docente)
            if ($method === 'GET' && $subResource === 'estudiantes') {
                $id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
                $id_materia = isset($_GET['id_materia']) ? (int)$_GET['id_materia'] : 0;

                if ($id_curso === 0 && isset($data['id_curso'])) {
                    $id_curso = (int)$data['id_curso'];
                }
                if ($id_materia === 0 && isset($data['id_materia'])) {
                    $id_materia = (int)$data['id_materia'];
                }

                if ($id_curso <= 0 || $id_materia <= 0) {
                    return [
                        'status' => 'error',
                        'message' => "Faltan parámetros obligatorios. id_curso: {$id_curso}, id_materia: {$id_materia}."
                    ];
                }

                $estudiantes = $this->repo->listarEstudiantesConNotas($id_curso, $id_materia);
                return ['status' => 'success', 'data' => $estudiantes];
            }

            // 8. Guardar o actualizar notas (POST)
            if ($method === 'POST') {
                if (!isset($data['id_materia']) && isset($_GET['id_materia'])) {
                    $data['id_materia'] = $_GET['id_materia'];
                }
                if (!isset($data['id_curso']) && isset($_GET['id_curso'])) {
                    $data['id_curso'] = $_GET['id_curso'];
                }
                if (!isset($data['id_gestion']) && isset($_GET['id_gestion'])) {
                    $data['id_gestion'] = $_GET['id_gestion'];
                }
                if (!isset($data['id_docente']) && isset($_GET['id_docente'])) {
                    $data['id_docente'] = $_GET['id_docente'];
                }

                if (isset($data['notes']) && !isset($data['notas'])) {
                    $data['notas'] = $data['notes'];
                }

                if (isset($data['notas']) && is_array($data['notas'])) {
                    foreach ($data['notas'] as $key => $nota) {
                        if (isset($nota['nota1']) && !isset($nota['b1'])) $data['notas'][$key]['b1'] = $nota['nota1'];
                        if (isset($nota['nota2']) && !isset($nota['b2'])) $data['notas'][$key]['b2'] = $nota['nota2'];
                        if (isset($nota['nota3']) && !isset($nota['b3'])) $data['notas'][$key]['b3'] = $nota['nota3'];
                        if (isset($nota['nota4']) && !isset($nota['b4'])) $data['notas'][$key]['b4'] = $nota['nota4'];
                        
                        if (isset($nota['primer_bimestre']) && !isset($nota['b1'])) $data['notas'][$key]['b1'] = $nota['primer_bimestre'];
                        if (isset($nota['segundo_bimestre']) && !isset($nota['b2'])) $data['notas'][$key]['b2'] = $nota['segundo_bimestre'];
                        if (isset($nota['tercer_bimestre']) && !isset($nota['b3'])) $data['notas'][$key]['b3'] = $nota['tercer_bimestre'];
                        if (isset($nota['cuarto_bimestre']) && !isset($nota['b4'])) $data['notas'][$key]['b4'] = $nota['cuarto_bimestre'];
                    }
                }

                return $this->repo->guardarNotasBasico($data);
            }

            // 9. Eliminar todas las notas de un estudiante (usado antes de borrar la ficha)
            if ($method === 'DELETE' && $subResource === 'estudiante' && $id) {
                return $this->repo->eliminarPorEstudiante($id);
            }

            throw new Exception("Acción no soportada explicitamente. Recurso: ".$subResource." - Acción: ".$actionReporte);
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}