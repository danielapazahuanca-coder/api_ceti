<?php
namespace App\Repositories;

use PDO;
use Exception;
use App\Database\Database;

class AsignacionRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function listarMateriasPorCurso(int $idCurso): array {
        $sql = "SELECT m.id_materia, m.sigla, m.nombre AS materia_nombre, n.nombre AS nivel_nombre
                FROM acad_curso c
                INNER JOIN acad_materia m ON m.id_carrera = c.id_carrera AND m.id_nivel = c.id_nivel
                INNER JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                WHERE c.id_curso = :id_curso AND m.estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_curso' => $idCurso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asignarDocente(array $data): array {
        try {
            $sql = "INSERT INTO acad_asignacion_docente (id_docente, id_curso, id_materia) 
                    VALUES (:id_docente, :id_curso, :id_materia)
                    ON DUPLICATE KEY UPDATE id_docente = :id_docente_update";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_docente'        => (int)$data['id_docente'],
                ':id_curso'          => (int)$data['id_curso'],
                ':id_materia'        => (int)$data['id_materia'],
                ':id_docente_update' => (int)$data['id_docente']
            ]);
            return ['status' => 'success', 'message' => 'Docente asignado correctamente a la materia.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error al asignar docente: ' . $e->getMessage()];
        }
    }

    public function listarAsignaciones(): array {
        // Se agregan ad.id_curso y ad.id_materia al SELECT para que el frontend
        // pueda construir el índice [id_curso][id_materia] => asignacion
        $sql = "SELECT ad.id_asignacion,
                    ad.id_curso,
                    ad.id_materia,
                    u.name AS docente_nombre,
                    m.nombre AS materia_nombre,
                    m.sigla,
                    ca.nombre AS carrera_nombre,
                    n.nombre AS nivel_nombre,
                    c.paralelo,
                    g.gestion_varchar,
                    g.id_gestion,
                    ca.sucursal_varchar
                FROM acad_asignacion_docente ad
                INNER JOIN user u          ON ad.id_docente = u.id
                INNER JOIN acad_materia m  ON ad.id_materia = m.id_materia
                INNER JOIN acad_curso c    ON ad.id_curso   = c.id_curso
                INNER JOIN acad_carrera ca ON c.id_carrera  = ca.id_carrera
                INNER JOIN acad_nivel n    ON c.id_nivel    = n.id_nivel
                INNER JOIN gestion g       ON c.id_gestion  = g.id_gestion
                ORDER BY g.gestion_varchar DESC, ca.nombre ASC, n.nombre ASC, c.paralelo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar(int $id): bool {
        $sql = "DELETE FROM acad_asignacion_docente WHERE id_asignacion = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}