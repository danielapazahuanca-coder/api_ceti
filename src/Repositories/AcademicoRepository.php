<?php
namespace App\Repositories;

use App\Database\Database;
use PDO;

class AcademicoRepository {

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /*
    |-------------------------------------------------
    | CURSOS CON CARRERA - NIVEL - GESTION
    |-------------------------------------------------
    */
    public function listarCursos(): array
    {
        $sql = "SELECT
                    c.id_curso,
                    ca.nombre AS carrera,
                    n.nombre AS nivel,
                    c.paralelo,
                    g.gestion_varchar AS gestion,
                    c.estado
                FROM acad_curso c
                INNER JOIN acad_carrera ca
                    ON ca.id_carrera = c.id_carrera
                INNER JOIN acad_nivel n
                    ON n.id_nivel = c.id_nivel
                INNER JOIN gestion g
                    ON g.id_gestion = c.id_gestion
                ORDER BY
                    g.gestion_varchar DESC,
                    ca.nombre,
                    n.id_nivel,
                    c.paralelo";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |-------------------------------------------------
    | DETALLE COMPLETO DEL CURSO
    |-------------------------------------------------
    */
    public function obtenerCurso(int $idCurso): array
    {
        $resultado = [];

        $sqlCurso = "SELECT
                        c.id_curso,
                        ca.nombre AS carrera,
                        n.nombre AS nivel,
                        c.paralelo,
                        g.gestion_varchar AS gestion
                    FROM acad_curso c
                    INNER JOIN acad_carrera ca
                        ON ca.id_carrera=c.id_carrera
                    INNER JOIN acad_nivel n
                        ON n.id_nivel=c.id_nivel
                    INNER JOIN gestion g
                        ON g.id_gestion=c.id_gestion
                    WHERE c.id_curso=:id";

        $stmt = $this->db->prepare($sqlCurso);
        $stmt->execute([':id'=>$idCurso]);

        $resultado['curso'] = $stmt->fetch(PDO::FETCH_ASSOC);

        $sqlMaterias = "
            SELECT
                m.id_materia,
                m.sigla,
                m.nombre AS materia,
                COALESCE(u.name,'SIN DOCENTE') AS docente
            FROM acad_materia m
            INNER JOIN acad_curso c
                ON c.id_carrera = m.id_carrera
                AND c.id_nivel = m.id_nivel

            LEFT JOIN acad_asignacion_docente ad
                ON ad.id_curso = c.id_curso
                AND ad.id_materia = m.id_materia

            LEFT JOIN user u
                ON u.id = ad.id_docente

            WHERE c.id_curso = :id
            ORDER BY m.nombre";

        $stmt = $this->db->prepare($sqlMaterias);
        $stmt->execute([':id'=>$idCurso]);

        $resultado['materias'] =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlEstudiantes = "
            SELECT
                e.id_estudiante,
                e.ci,
                e.nombres,
                e.apellidos,
                e.foto_ruta
            FROM acad_inscripcion i
            INNER JOIN acad_estudiante e
                ON e.id_estudiante=i.id_estudiante
            WHERE i.id_curso=:id
            ORDER BY e.apellidos,e.nombres";

        $stmt = $this->db->prepare($sqlEstudiantes);
        $stmt->execute([':id'=>$idCurso]);

        $resultado['estudiantes'] =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

    /*
    |-------------------------------------------------
    | FICHA COMPLETA DEL ESTUDIANTE
    |-------------------------------------------------
    */
    public function obtenerFichaEstudiante(int $idEstudiante): array
    {
        $sql = "
            SELECT
                e.id_estudiante,
                e.ci,
                e.expedido,
                e.nombres,
                e.apellidos,
                e.telefono,
                e.foto_ruta,

                ca.nombre AS carrera,
                n.nombre AS nivel,
                c.paralelo,
                g.gestion_varchar AS gestion

            FROM acad_estudiante e

            LEFT JOIN acad_inscripcion i
                ON i.id_estudiante=e.id_estudiante

            LEFT JOIN acad_curso c
                ON c.id_curso=i.id_curso

            LEFT JOIN acad_carrera ca
                ON ca.id_carrera=c.id_carrera

            LEFT JOIN acad_nivel n
                ON n.id_nivel=c.id_nivel

            LEFT JOIN gestion g
                ON g.id_gestion=c.id_gestion

            WHERE e.id_estudiante=:id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id'=>$idEstudiante]);

        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        $sqlNotas = "
            SELECT
                m.sigla,
                m.nombre AS materia,
                u.name AS docente,

                nt.primer_bimestre,
                nt.segundo_bimestre,
                nt.tercer_bimestre,
                nt.cuarto_bimestre,
                nt.promedio_final

            FROM acad_nota nt

            INNER JOIN acad_materia m
                ON m.id_materia=nt.id_materia

            LEFT JOIN user u
                ON u.id=nt.id_docente

            WHERE nt.id_estudiante=:id

            ORDER BY m.nombre";

        $stmt = $this->db->prepare($sqlNotas);
        $stmt->execute([':id'=>$idEstudiante]);

        return [
            'estudiante'=>$info,
            'notas'=>$stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
    /*
    |-------------------------------------------------
    | BÚSQUEDA PÚBLICA POR NOMBRE, APELLIDOS Y CI
    |-------------------------------------------------
    */
    public function buscarPorNombreCompleto(string $nombre, string $apellido1, string $apellido2, string $ci): ?array
    {
        $sql = "SELECT id_estudiante
                FROM acad_estudiante
                WHERE LOWER(nombres)   LIKE :nombre
                  AND LOWER(apellidos) LIKE :apellido1
                  AND LOWER(apellidos) LIKE :apellido2
                  AND ci = :ci
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'    => '%' . mb_strtolower(trim($nombre), 'UTF-8') . '%',
            ':apellido1' => '%' . mb_strtolower(trim($apellido1), 'UTF-8') . '%',
            ':apellido2' => '%' . mb_strtolower(trim($apellido2), 'UTF-8') . '%',
            ':ci'        => trim($ci),
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Reutilizamos la misma ficha completa que ya usa ficha_estudiante.php
        return $this->obtenerFichaEstudiante((int)$row['id_estudiante']);
    }

}