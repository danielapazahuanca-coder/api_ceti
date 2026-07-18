<?php
namespace App\Controllers;

use App\Repositories\AcademicoRepository;

class AcademicoController {

    private $repo;

    public function __construct(AcademicoRepository $repo)
    {
        $this->repo = $repo;
    }

    public function handleRequest(
        string $method,
        ?string $subResource,
        ?int $id,
        array $params = []
    ): array {

        if($method !== 'GET'){
            return [
                'status'=>'error',
                'message'=>'Método no permitido'
            ];
        }

        if($subResource === 'curso' && $id){
            return [
                'status'=>'success',
                'data'=>$this->repo->obtenerCurso($id)
            ];
        }

        if($subResource === 'estudiante' && $id){
            return [
                'status'=>'success',
                'data'=>$this->repo->obtenerFichaEstudiante($id)
            ];
        }

        // ---- NUEVO: búsqueda pública por nombre, apellidos y CI ----
        if($subResource === 'buscar'){
            $nombre    = trim($params['nombre'] ?? '');
            $apellido1 = trim($params['apellido1'] ?? '');
            $apellido2 = trim($params['apellido2'] ?? '');
            $ci        = trim($params['ci'] ?? '');

            if ($nombre === '' || $apellido1 === '' || $apellido2 === '' || $ci === '') {
                return [
                    'status'=>'error',
                    'message'=>'Debes completar tu primer nombre, tus dos apellidos y tu CI'
                ];
            }

            $data = $this->repo->buscarPorNombreCompleto($nombre, $apellido1, $apellido2, $ci);

            if (!$data || !$data['estudiante']) {
                return [
                    'status'=>'error',
                    'message'=>'No se encontró ningún estudiante con esos datos'
                ];
            }

            return ['status'=>'success','data'=>$data];
        }

        return [
            'status'=>'success',
            'data'=>$this->repo->listarCursos()
        ];
    }
}