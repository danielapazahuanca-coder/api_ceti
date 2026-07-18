<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Curso;
use PDO;
use Exception;

class CursoRepository {
    private PDO $db;

    public function __construct($db = null) {
        $this->db = $db ?? Database::getInstance();
    }



    public function listar(): array {
        $sql = "SELECT c.*,
               ca.nombre as carrera_nombre,
               ca.sucursal_varchar as sucursal_varchar,
               n.nombre as nivel_nombre,
               g.gestion_varchar,
               g.estado_bt
        FROM acad_curso c
        INNER JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
        INNER JOIN acad_nivel n ON c.id_nivel = n.id_nivel
        INNER JOIN gestion g ON c.id_gestion = g.id_gestion
        ORDER BY g.gestion_varchar DESC,
                 c.id_nivel ASC,
                 c.paralelo ASC";

        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function crear(array $data): array {
        try {
            $this->db->beginTransaction();

            // 1. Validar que no exista el curso duplicado en la misma gestión, carrera, nivel y paralelo
            $sqlCheck = "SELECT COUNT(*) FROM acad_curso 
                         WHERE id_carrera = :id_carrera AND id_nivel = :id_nivel 
                         AND id_gestion = :id_gestion AND paralelo = :paralelo";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([
                ':id_carrera' => (int)$data['id_carrera'],
                ':id_nivel'   => (int)$data['id_nivel'],
                ':id_gestion' => (int)$data['id_gestion'],
                ':paralelo'   => strtoupper(trim($data['paralelo']))
            ]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("El paralelo ya se encuentra registrado para esta gestión académica.");
            }

            // 2. Guardamos el nuevo Curso/Aula física (Ej: 1A de Sistemas Informáticos)
            $sql = "INSERT INTO acad_curso (id_carrera, id_nivel, id_gestion, paralelo, estado) 
                    VALUES (:id_carrera, :id_nivel, :id_gestion, :paralelo, 1)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_carrera' => (int)$data['id_carrera'],
                ':id_nivel'   => (int)$data['id_nivel'],
                ':id_gestion' => (int)$data['id_gestion'],
                ':paralelo'   => strtoupper(trim($data['paralelo']))
            ]);

            // Obtener el ID del curso recién creado por si se necesita más adelante
            $idCursoCreado = $this->db->lastInsertId();

            // 3. Verificamos cuántas materias oficiales del plan de estudios se le heredarán implícitamente
            // Busca todas las materias registradas para ESA carrera y ESE año (nivel) específico
            $sqlM = "SELECT COUNT(*) FROM acad_materia WHERE id_carrera = :id_carrera AND id_nivel = :id_nivel AND estado = 1";
            $stmtM = $this->db->prepare($sqlM);
            $stmtM->execute([
                ':id_carrera' => (int)$data['id_carrera'],
                ':id_nivel'   => (int)$data['id_nivel']
            ]);
            $cantMaterias = $stmtM->fetchColumn();

            $this->db->commit();

            // Mensaje optimizado para que la Secretaria vea en pantalla que el paralelo ya tiene su malla cargada
            return [
                'status' => 'success',
                'message' => 'Curso creado con éxito. Al ser creado, este paralelo adopta automáticamente las ' . $cantMaterias . ' materias configuradas para su año académico.'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    public function modificar(int $id, array $data): array {
        try {
            $sql = "UPDATE acad_curso SET 
                    id_carrera = :id_carrera, 
                    id_nivel = :id_nivel, 
                    id_gestion = :id_gestion, 
                    paralelo = :paralelo, 
                    estado = :estado 
                    WHERE id_curso = :id";
            $stmt = $this->db->prepare($sql);
            $res = $stmt->execute([
                ':id_carrera' => (int)$data['id_carrera'],
                ':id_nivel'   => (int)$data['id_nivel'],
                ':id_gestion' => (int)$data['id_gestion'],
                ':paralelo'   => strtoupper(trim($data['paralelo'])),
                ':estado'     => (int)$data['estado'],
                ':id'         => $id
            ]);
            return ['status' => 'success', 'message' => 'Curso modified exitosamente.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error al modificar: ' . $e->getMessage()];
        }
    }

    public function toggleEstado(int $id, int $estadoActual): void {
        $nuevoEstado = $estadoActual === 1 ? 0 : 1;
        $sql = "UPDATE acad_curso SET estado = :estado WHERE id_curso = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
    }
}