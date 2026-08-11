<?php
namespace App\Repositories;

use PDO;
use Exception;
use App\Database\Database;

class InscripcionRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // 1. Matricular a un estudiante en un curso
    public function inscribir(array $data): array {
        try {
            $sql = "INSERT INTO acad_inscripcion (id_estudiante, id_curso) 
                    VALUES (:id_estudiante, :id_curso)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_estudiante' => (int)$data['id_estudiante'],
                ':id_curso'      => (int)$data['id_curso']
            ]);
            
            return ['status' => 'success', 'message' => 'Estudiante inscrito correctamente.'];
        } catch (Exception $e) {
            // Manejo de error si se intenta inscribir al mismo estudiante en el mismo curso 2 veces (Error 1062 - Duplicado)
            if ($e->getCode() == 23000) {
                return ['status' => 'error', 'message' => 'El estudiante ya se encuentra matriculado en este paralelo.'];
            }
            return ['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    public function listarPorCurso(int $id_curso): array {
        $sql = "
            SELECT
                e.id_estudiante,
                e.ci,
                e.expedido,
                e.apellidos,
                e.nombres,
                e.telefono,
                e.foto_ruta,
                c.id_curso,
                c.id_nivel,
                c.paralelo,
                ca.id_carrera,
                ca.nombre AS carrera,
                n.nombre AS nivel_nombre
            FROM acad_inscripcion i
            INNER JOIN acad_estudiante e
                ON e.id_estudiante = i.id_estudiante
            INNER JOIN acad_curso c
                ON c.id_curso = i.id_curso
            INNER JOIN acad_carrera ca
                ON ca.id_carrera = c.id_carrera
            INNER JOIN acad_nivel n
                ON n.id_nivel = c.id_nivel
            WHERE i.id_curso = :id_curso
            AND e.estado = 1
            ORDER BY e.apellidos, e.nombres
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_curso' => $id_curso
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCursosPorCarrera(int $idCarrera): array {
        $sql = "
            SELECT
                c.id_curso,
                c.id_nivel,
                c.paralelo,
                c.estado
            FROM acad_curso c
            WHERE c.id_carrera = :id_carrera
            AND c.estado = 1
            ORDER BY c.id_nivel, c.paralelo
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_carrera' => $idCarrera
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Elimina todas las inscripciones de un estudiante (usado antes de borrar la ficha)
    public function eliminarPorEstudiante(int $idEstudiante): array {
        try {
            $sql = "DELETE FROM acad_inscripcion WHERE id_estudiante = :id_estudiante";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_estudiante' => $idEstudiante]);

            return ['status' => 'success', 'message' => 'Inscripciones del estudiante eliminadas correctamente.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error al eliminar inscripciones: ' . $e->getMessage()];
        }
    }

    public function moverEstudiante(int $idEstudiante, int $idCurso): array {
        $sql = "
            UPDATE acad_inscripcion
            SET id_curso = :curso
            WHERE id_estudiante = :estudiante
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':curso' => $idCurso,
            ':estudiante' => $idEstudiante
        ]);

        return [
            'status' => 'success',
            'message' => 'Curso actualizado.'
        ];
    }
}