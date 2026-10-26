<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BienesServiciosModel;


class BienesServiciosController extends BaseController
{
    protected $catalogoModel;

    public function __construct()
    {
        $this->catalogoModel = new BienesServiciosModel();
    }

    public function index()
    {
        $categorias = $this->catalogoModel
            ->distinct()
            ->select('categoria')
            ->where('categoria IS NOT NULL')
            ->orderBy('categoria', 'ASC')
            ->findAll();

        return view('catalogos/BienesServiciosView', [
            'titulo'     => 'Catálogo de Bienes y Servicios',
            'categorias' => array_column($categorias, 'categoria'),
        ]);
    }

    
    public function listar()
    {
        $texto      = trim((string) $this->request->getGet('q'));
        $categoria  = trim((string) $this->request->getGet('categoria'));
        $pagina     = max(1, (int) $this->request->getGet('pagina'));
        $porPagina  = 50;

        $consulta = $this->catalogoModel
            ->select('bienes_servicios.id, bienes_servicios.partida_presupuestal, bienes_servicios.nombre, '
                . 'bienes_servicios.precio, bienes_servicios.categoria, bienes_servicios.activo, '
                . 'unidades_medida.nombre AS unidad_medida')
            ->join('unidades_medida', 'unidades_medida.id = bienes_servicios.unidad_medida_id', 'left');

        if ($texto !== '') {
            $consulta->like('bienes_servicios.nombre', $texto);
        }
        if ($categoria !== '') {
            $consulta->where('bienes_servicios.categoria', $categoria);
        }

        $total = $consulta->countAllResults(false);
        $filas = $consulta
            ->orderBy('bienes_servicios.nombre', 'ASC')
            ->limit($porPagina, ($pagina - 1) * $porPagina)
            ->find();

        return $this->response->setJSON([
            'data'        => $filas,
            'total'       => $total,
            'pagina'      => $pagina,
            'totalPaginas' => (int) ceil($total / $porPagina),
        ]);
    }

    /**
     * Autocompletar al capturar una partida de un contrato: unos cuantos
     * resultados (no todo el catálogo) que coincidan con lo que el
     * operador ya escribió.
     */
    public function buscar()
    {
        $texto = trim((string) $this->request->getGet('q'));

        if (mb_strlen($texto) < 3) {
            return $this->response->setJSON([]);
        }

        return $this->response->setJSON($this->catalogoModel->buscar($texto));
    }
}
