<?php
namespace App\Repositories;

use App\Database\Database;
use PDO;

class NotaRepository {
    private $db;
 
    public function __construct($db = null){
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Elimina todas las notas de un estudiante (usado antes de borrar la ficha)
     */
    public function eliminarPorEstudiante(int $idEstudiante): array {
        try {
            $sql = "DELETE FROM acad_nota WHERE id_estudiante = :id_estudiante";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_estudiante' => $idEstudiante]);

            return ['status' => 'success', 'message' => 'Notas del estudiante eliminadas correctamente.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error al eliminar notas: ' . $e->getMessage()];
        }
    }

    /**
     * Lista los cursos y materias asignados a un docente específico
     */
    public function listarCursosPorDocente(int $id_docente): array {
        $sql = "SELECT 
                    a.id_asignacion,
                    a.id_curso,
                    a.id_materia,
                    c.paralelo,
                    c.id_nivel,
                    c.id_gestion,
                    m.nombre AS materia_nombre,
                    m.sigla AS sigla,
                    ca.nombre AS carrera_nombre,
                    n.nombre AS nivel_nombre,
                    g.gestion_varchar
                FROM acad_asignacion_docente a
                INNER JOIN acad_curso c ON a.id_curso = c.id_curso
                INNER JOIN acad_materia m ON a.id_materia = m.id_materia
                INNER JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                INNER JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                INNER JOIN gestion g ON c.id_gestion = g.id_gestion
                INNER JOIN user u ON a.id_docente = u.id
                WHERE a.id_docente = :id_docente 
                AND c.estado = 1
                AND g.estado_bt = 1
                AND g.sucursal_varchar = u.sucursal_varchar";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_docente' => $id_docente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trae los alumnos inscritos con sus respectivas notas mapeadas correctamente
     */
    public function listarEstudiantesConNotas(int $id_curso, int $id_materia): array {
        $sql = "SELECT 
                    e.id_estudiante,
                    e.ci,
                    e.nombres,
                    e.apellidos,
                    e.telefono,
                    e.foto_ruta,
                    e.estado,
                    c.paralelo,
                    c.id_gestion,
                    ca.nombre AS nombre_carrera,
                    n.nombre AS nombre_nivel,
                    CAST(IFNULL(nt.primer_bimestre, 0) AS UNSIGNED) AS primer_bimestre,
                    CAST(IFNULL(nt.segundo_bimestre, 0) AS UNSIGNED) AS segundo_bimestre,
                    CAST(IFNULL(nt.tercer_bimestre, 0) AS UNSIGNED) AS tercer_bimestre,
                    CAST(IFNULL(nt.cuarto_bimestre, 0) AS UNSIGNED) AS cuarto_bimestre,
                    CAST(IFNULL(nt.promedio_final, 0) AS UNSIGNED) AS promedio_final
                FROM acad_inscripcion i
                INNER JOIN acad_estudiante e ON i.id_estudiante = e.id_estudiante
                INNER JOIN acad_curso c ON i.id_curso = c.id_curso
                INNER JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                INNER JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                LEFT JOIN acad_nota nt ON e.id_estudiante = nt.id_estudiante 
                    AND nt.id_materia = :id_materia 
                    AND nt.id_curso = :id_curso
                    AND nt.id_gestion = c.id_gestion
                WHERE i.id_curso = :id_curso_filtro
                ORDER BY e.apellidos ASC, e.nombres ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_materia'      => $id_materia,
            ':id_curso'        => $id_curso,
            ':id_curso_filtro' => $id_curso
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda o actualiza masivamente las calificaciones de los estudiantes
     */
    public function guardarNotasBasico(array $data): array {
        if (empty($data)) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true) ?? [];
        }

        $id_materia = (int)($data['id_materia'] ?? 0);
        $id_curso = (int)($data['id_curso'] ?? 0);
        $id_gestion = (int)($data['id_gestion'] ?? 0);
        $id_docente = (int)($data['id_docente'] ?? 0);
        
        $notas = $data['notas'] ?? ($data['notes'] ?? []);

        if ($id_materia === 0 || $id_curso === 0 || $id_gestion === 0 || $id_docente === 0) {
            return ['status' => 'error', 'message' => "Parámetros insuficientes. Mat:{$id_materia}, Cur:{$id_curso}, Ges:{$id_gestion}, Doc:{$id_docente}"];
        }

        if (empty($notas)) {
            return ['status' => 'error', 'message' => 'No se recibieron calificaciones para procesar.'];
        }

        $this->db->beginTransaction();
        try {
            $sqlUpsert = "INSERT INTO acad_nota 
                            (id_estudiante, id_materia, id_curso, id_gestion, id_docente, primer_bimestre, segundo_bimestre, tercer_bimestre, cuarto_bimestre, promedio_final)
                          VALUES 
                            (:id_est, :id_mat, :id_cur, :id_ges, :id_doc, :b1, :b2, :b3, :b4, :prom)
                          ON DUPLICATE KEY UPDATE 
                            id_docente = VALUES(id_docente),
                            primer_bimestre = VALUES(primer_bimestre),
                            segundo_bimestre = VALUES(segundo_bimestre),
                            tercer_bimestre = VALUES(tercer_bimestre),
                            cuarto_bimestre = VALUES(cuarto_bimestre),
                            promedio_final = VALUES(promedio_final)";
            
            $stmt = $this->db->prepare($sqlUpsert);

            foreach ($notas as $n) {
                $id_estudiante = (int)$n['id_estudiante'];
                
                $b1 = (int)($n['b1'] ?? ($n['primer_bimestre'] ?? 0));
                $b2 = (int)($n['b2'] ?? ($n['segundo_bimestre'] ?? 0));
                $b3 = (int)($n['b3'] ?? ($n['tercer_bimestre'] ?? 0));
                $b4 = (int)($n['b4'] ?? ($n['cuarto_bimestre'] ?? 0));
                
                $promedio = (int)round(($b1 + $b2 + $b3 + $b4) / 4);

                $stmt->execute([
                    ':id_est' => $id_estudiante,
                    ':id_mat' => $id_materia,
                    ':id_cur' => $id_curso,
                    ':id_ges' => $id_gestion,
                    ':id_doc' => $id_docente,
                    ':b1'     => $b1,
                    ':b2'     => $b2,
                    ':b3'     => $b3,
                    ':b4'     => $b4,
                    ':prom'   => $promedio
                ]);
            }
            
            $this->db->commit();
            return ['status' => 'success', 'message' => 'Notas guardadas con éxito de forma estricta.'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => 'Error en base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * HISTORIAL ACADÉMICO INDIVIDUAL DE UN ESTUDIANTE
     */
    public function obtenerHistorialEstudiante(int $id_estudiante): array {
        $sql = "SELECT 
                    m.sigla,
                    m.nombre AS materia,
                    n.nombre AS año_academico,
                    g.gestion_varchar AS gestion,
                    nt.primer_bimestre AS b1,
                    nt.segundo_bimestre AS b2,
                    nt.tercer_bimestre AS b3,
                    nt.cuarto_bimestre AS b4,
                    nt.promedio_final AS total,
                    IF(nt.promedio_final >= 61, 'APROBADO', 'REPROBADO') AS condicion,
                    u.name AS docente
                FROM acad_nota nt
                INNER JOIN acad_materia m ON nt.id_materia = m.id_materia
                INNER JOIN acad_nivel n ON m.id_nivel = n.id_nivel
                INNER JOIN gestion g ON nt.id_gestion = g.id_gestion
                INNER JOIN user u ON nt.id_docente = u.id
                WHERE nt.id_estudiante = :id_estudiante
                ORDER BY g.gestion_varchar DESC, n.id_nivel ASC, m.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $id_estudiante]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ACTA CENTRALIZADA DE CALIFICACIONES POR CURSO Y MATERIA
     */
    public function obtenerActaCalificacionesCurso(int $id_curso, int $id_materia): array {
        $sql = "SELECT 
                    c.id_curso,
                    ca.nombre AS carrera,
                    n.nombre AS año_academico,
                    c.paralelo,
                    g.gestion_varchar AS gestion,
                    m.nombre AS materia,
                    m.sigla,
                    u.name AS docente_responsable,
                    e.ci,
                    CONCAT(e.apellidos, ' ', e.nombres) AS estudiante,
                    IFNULL(nt.primer_bimestre, 0) AS b1,
                    IFNULL(nt.segundo_bimestre, 0) AS b2,
                    IFNULL(nt.tercer_bimestre, 0) AS b3,
                    IFNULL(nt.cuarto_bimestre, 0) AS b4,
                    IFNULL(nt.promedio_final, 0) AS final
                FROM acad_curso c
                INNER JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                INNER JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                INNER JOIN gestion g ON c.id_gestion = g.id_gestion
                INNER JOIN acad_materia m ON m.id_carrera = ca.id_carrera AND m.id_nivel = n.id_nivel
                LEFT JOIN acad_asignacion_docente ad ON ad.id_curso = c.id_curso AND ad.id_materia = m.id_materia
                LEFT JOIN user u ON ad.id_docente = u.id
                INNER JOIN acad_inscripcion i ON i.id_curso = c.id_curso
                INNER JOIN acad_estudiante e ON i.id_estudiante = e.id_estudiante
                LEFT JOIN acad_nota nt ON nt.id_estudiante = e.id_estudiante 
                    AND nt.id_materia = m.id_materia 
                    AND nt.id_curso = c.id_curso
                WHERE c.id_curso = :id_curso AND m.id_materia = :id_materia
                ORDER BY estudiante ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_curso'   => $id_curso,
            ':id_materia' => $id_materia
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * REPORTES GENERALES DE LISTADOS
     */
    public function obtenerListadoGeneral(string $tipo, string $sucursal, ?int $id_gestion = null): array {
        $params = [':sucursal' => $sucursal];
        switch ($tipo) {
            case 'estudiantes':
                // Se incluye la carrera/curso vigente del alumno (vía inscripción) para
                // poder agrupar el listado por Carrera → Curso en el reporte.
                // LEFT JOIN: un estudiante sin inscripción igual debe aparecer (sin curso).
                $joinCurso = $id_gestion !== null
                    ? "LEFT JOIN acad_curso c ON i.id_curso = c.id_curso AND c.id_gestion = :id_gestion"
                    : "LEFT JOIN acad_curso c ON i.id_curso = c.id_curso";

                $sql = "SELECT e.ci, e.apellidos, e.nombres, e.telefono,
                            IF(e.estado=1,'Activo','Inactivo') as estado,
                            ca.id_carrera, ca.nombre AS nombre_carrera,
                            c.id_curso, n.id_nivel, n.nombre AS nombre_nivel, c.paralelo
                        FROM acad_estudiante e
                        LEFT JOIN acad_inscripcion i ON e.id_estudiante = i.id_estudiante
                        $joinCurso
                        LEFT JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                        LEFT JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                        WHERE e.sucursal_varchar = :sucursal
                        ORDER BY (ca.nombre IS NULL), ca.nombre ASC, n.id_nivel ASC, c.paralelo ASC, e.apellidos ASC, e.nombres ASC";
                if ($id_gestion !== null) {
                    $params[':id_gestion'] = $id_gestion;
                }
                break;
            case 'usuarios':
                $sql = "SELECT u.username, u.name, u.emailid, r.nombre_rol, u.sucursal_varchar 
                        FROM user u 
                        LEFT JOIN roles r ON u.role_id = r.id 
                        WHERE u.sucursal_varchar = :sucursal ORDER BY u.name ASC";
                break;
            case 'materias':
                $sql = "SELECT m.sigla, m.nombre, c.nombre as carrera, n.nombre as año 
                        FROM acad_materia m 
                        INNER JOIN acad_carrera c ON m.id_carrera = c.id_carrera 
                        INNER JOIN acad_nivel n ON m.id_nivel = n.id_nivel 
                        WHERE c.sucursal_varchar = :sucursal ORDER BY c.nombre, n.id_nivel, m.nombre";
                break;
            default:
                return [];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * CENTRALIZADOR: Obtiene todas las materias y notas de todos los estudiantes de un curso
     */
    public function obtenerCentralizadorCurso(int $id_curso): array {
        $sql = "SELECT 
                    c.id_curso,
                    ca.nombre AS carrera,
                    n.nombre AS año_academico,
                    c.paralelo,
                    g.gestion_varchar AS gestion,
                    e.id_estudiante,
                    e.ci,
                    CONCAT(e.apellidos, ' ', e.nombres) AS estudiante,
                    m.id_materia,
                    m.nombre AS materia,
                    m.sigla,
                    IFNULL(nt.promedio_final, 0) AS nota_final
                FROM acad_curso c
                INNER JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                INNER JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                INNER JOIN gestion g ON c.id_gestion = g.id_gestion
                INNER JOIN acad_materia m ON m.id_carrera = ca.id_carrera AND m.id_nivel = n.id_nivel
                INNER JOIN acad_inscripcion i ON i.id_curso = c.id_curso
                INNER JOIN acad_estudiante e ON i.id_estudiante = e.id_estudiante
                LEFT JOIN acad_nota nt ON nt.id_estudiante = e.id_estudiante 
                    AND nt.id_materia = m.id_materia 
                    AND nt.id_curso = c.id_curso
                WHERE c.id_curso = :id_curso
                ORDER BY estudiante ASC, m.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_curso' => $id_curso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * BOLETINES EN MASA: Obtiene el desglose completo por bimestre de todas las materias de los alumnos del curso
     */
    public function obtenerBoletinesCurso(int $id_curso): array {
        $sql = "SELECT 
                    c.id_curso,
                    ca.nombre AS carrera,
                    n.nombre AS año_academico,
                    c.paralelo,
                    g.gestion_varchar AS gestion,
                    e.id_estudiante,
                    e.ci,
                    CONCAT(e.apellidos, ' ', e.nombres) AS estudiante,
                    m.sigla,
                    m.nombre AS materia,
                    IFNULL(nt.primer_bimestre, 0) AS b1,
                    IFNULL(nt.segundo_bimestre, 0) AS b2,
                    IFNULL(nt.tercer_bimestre, 0) AS b3,
                    IFNULL(nt.cuarto_bimestre, 0) AS b4,
                    IFNULL(nt.promedio_final, 0) AS final,
                    IF(nt.promedio_final >= 61, 'APROBADO', 'REPROBADO') AS condicion,
                    IFNULL(u.name, 'Sin Asignar') AS docente
                FROM acad_curso c
                INNER JOIN acad_carrera ca ON c.id_carrera = ca.id_carrera
                INNER JOIN acad_nivel n ON c.id_nivel = n.id_nivel
                INNER JOIN gestion g ON c.id_gestion = g.id_gestion
                INNER JOIN acad_materia m ON m.id_carrera = ca.id_carrera AND m.id_nivel = n.id_nivel
                INNER JOIN acad_inscripcion i ON i.id_curso = c.id_curso
                INNER JOIN acad_estudiante e ON i.id_estudiante = e.id_estudiante
                LEFT JOIN acad_nota nt ON nt.id_estudiante = e.id_estudiante 
                    AND nt.id_materia = m.id_materia 
                    AND nt.id_curso = c.id_curso
                LEFT JOIN acad_asignacion_docente ad ON ad.id_curso = c.id_curso AND ad.id_materia = m.id_materia
                LEFT JOIN user u ON ad.id_docente = u.id
                WHERE c.id_curso = :id_curso
                ORDER BY estudiante ASC, m.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_curso' => $id_curso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}