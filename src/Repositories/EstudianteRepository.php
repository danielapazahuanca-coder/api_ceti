<?php
namespace App\Repositories;

use PDO;

class EstudianteRepository {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll($sucursal) {
        $sql = "SELECT * FROM acad_estudiante WHERE sucursal_varchar = :sucursal ORDER BY apellidos, nombres";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sucursal' => $sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM acad_estudiante WHERE id_estudiante = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO acad_estudiante (ci, expedido, nombres, apellidos, telefono, foto_ruta, sucursal_varchar) 
                VALUES (:ci, :expedido, :nombres, :apellidos, :telefono, :foto_ruta, :sucursal_varchar)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ci' => $data['ci'],
            ':expedido' => $data['expedido'],
            ':nombres' => $data['nombres'],
            ':apellidos' => $data['apellidos'],
            ':telefono' => $data['telefono'] ?? null,
            ':foto_ruta' => $data['foto_ruta'] ?? 'uploads/fotos/default.png',
            ':sucursal_varchar' => $data['sucursal_varchar']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE acad_estudiante SET 
                ci = :ci, expedido = :expedido, nombres = :nombres, 
                apellidos = :apellidos, telefono = :telefono, foto_ruta = :foto_ruta, estado = :estado
                WHERE id_estudiante = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':ci' => $data['ci'],
            ':expedido' => $data['expedido'],
            ':nombres' => $data['nombres'],
            ':apellidos' => $data['apellidos'],
            ':telefono' => $data['telefono'] ?? null,
            ':foto_ruta' => $data['foto_ruta'],
            ':estado' => (int)$data['estado']
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM acad_estudiante WHERE id_estudiante = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getDisponiblesParaInscripcion($sucursal, $id_curso) {
        $sql = "SELECT e.* 
                FROM acad_estudiante e 
                WHERE e.sucursal_varchar = :sucursal 
                AND e.id_estudiante NOT IN (
                    SELECT i.id_estudiante 
                    FROM acad_inscripcion i
                    INNER JOIN acad_curso c ON i.id_curso = c.id_curso
                    WHERE c.estado = 1
                )
                ORDER BY e.apellidos, e.nombres";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sucursal' => $sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllConCurso($sucursal) {
        $id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : (isset($_GET['id_curso_filtro']) ? (int)$_GET['id_curso_filtro'] : 0);

        $sql = "SELECT e.*, c.paralelo, n.nombre AS nombre_nivel, ca.nombre AS nombre_carrera,
                    c.id_gestion
                FROM acad_estudiante e 
                LEFT JOIN acad_inscripcion i ON e.id_estudiante = i.id_estudiante
                LEFT JOIN acad_curso c ON i.id_curso = c.id_curso
                LEFT JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                LEFT JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                WHERE e.sucursal_varchar = :sucursal";

        if ($id_curso > 0) {
            $sql .= " AND i.id_curso = :id_curso";
        }

        $sql .= " ORDER BY e.apellidos, e.nombres";
    
        $stmt = $this->db->prepare($sql);
        $params = [':sucursal' => $sucursal];
        if ($id_curso > 0) {
            $params[':id_curso'] = $id_curso;
        }

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public function getAllConEstadoInscripcion($sucursal, $id_gestion = null) {
        // La inscripción SIEMPRE es LEFT JOIN: un estudiante sin ninguna
        // inscripción (recién creado) debe aparecer igual, solo que sin curso.
        // El filtro de gestión va en el ON del curso, no condiciona la existencia
        // del estudiante en el resultado.
        $joinCurso = $id_gestion !== null
            ? "LEFT JOIN acad_curso c ON i.id_curso = c.id_curso AND c.id_gestion = :id_gestion"
            : "LEFT JOIN acad_curso c ON i.id_curso = c.id_curso";

        $sql = "SELECT e.*, c.paralelo, n.nombre AS nombre_nivel, ca.nombre AS nombre_carrera,
                    c.id_gestion
                FROM acad_estudiante e 
                LEFT JOIN acad_inscripcion i ON e.id_estudiante = i.id_estudiante
                $joinCurso
                LEFT JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                LEFT JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                WHERE e.sucursal_varchar = :sucursal
                ORDER BY e.apellidos, e.nombres";

        $params = [':sucursal' => $sucursal];
        if ($id_gestion !== null) {
            $params[':id_gestion'] = $id_gestion;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca estudiantes por nombre, apellido o C.I. dentro de una sucursal específica.
     * Incluye su inscripción actual (carrera, paralelo) si tiene, igual que getAllConEstadoInscripcion.
     * Si se pasa id_gestion, solo trae la inscripción de esa gestión (no mezcla gestiones pasadas).
     */
public function buscar($sucursal, $termino, $id_gestion = null) {
        $like = '%' . trim($termino) . '%';

        $joinCurso = $id_gestion !== null
            ? "LEFT JOIN acad_curso c ON i.id_curso = c.id_curso AND c.id_gestion = :id_gestion"
            : "LEFT JOIN acad_curso c ON i.id_curso = c.id_curso";

        $sql = "SELECT e.*, c.paralelo, n.nombre AS nombre_nivel, ca.nombre AS nombre_carrera,
                    c.id_gestion
                FROM acad_estudiante e 
                LEFT JOIN acad_inscripcion i ON e.id_estudiante = i.id_estudiante
                $joinCurso
                LEFT JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                LEFT JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                WHERE e.sucursal_varchar = :sucursal
                  AND (
                        e.nombres   LIKE :like1
                     OR e.apellidos LIKE :like2
                     OR e.ci        LIKE :like3
                     OR CONCAT(e.apellidos, ' ', e.nombres) LIKE :like4
                     OR CONCAT(e.nombres, ' ', e.apellidos) LIKE :like5
                  )
                ORDER BY e.apellidos, e.nombres";

        $params = [
            ':sucursal' => $sucursal,
            ':like1' => $like,
            ':like2' => $like,
            ':like3' => $like,
            ':like4' => $like,
            ':like5' => $like,
        ];
        if ($id_gestion !== null) {
            $params[':id_gestion'] = $id_gestion;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}