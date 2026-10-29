<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BienesServiciosModel;
use App\Models\UnidadMedidaModel;

// buscar() alimenta el autocompletar de la captura de partidas (ver _formulario.php).
class BienesServiciosController extends BaseController
{
    protected $catalogoModel;
    protected $unidadModel;

    public function __construct()
    {
        $this->catalogoModel = new BienesServiciosModel();
        $this->unidadModel   = new UnidadMedidaModel();
    }

    public function index()
    {
        $categorias = $this->catalogoModel
            ->distinct()
            ->select('categoria')
            ->where('categoria IS NOT NULL')
            ->orderBy('categoria', 'ASC')
            ->findAll();

        $unidades = $this->unidadModel->orderBy('nombre', 'ASC')->findAll();

        return view('catalogos/BienesServiciosView', [
            'titulo'     => 'Catálogo de Bienes y Servicios',
            'categorias' => array_column($categorias, 'categoria'),
            'unidades'   => $unidades,
        ]);
    }

    /**
     * Para la tabla de la pantalla de consulta: por página, con filtro de
     * texto y/o categoría. Con 3,039 filas, mandarlas todas de un jalón al
     * navegador (como sí hacen los demás catálogos, mucho más chicos) lo
     * haría sentir lento, por eso aquí SÍ se pagina en el servidor.
     */
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

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (empty($data)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 400,
                'message' => 'No se enviaron datos.'
            ]);
        }

        $data['nombre']   = trim($data['nombre'] ?? '');
        $data['categoria'] = trim($data['categoria'] ?? '') ?: null;
        $data['activo']   = 1;

        if ($this->catalogoModel->insert($data)) {
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 201,
                'message' => 'Bien/servicio registrado correctamente.',
                'id'      => $this->catalogoModel->getInsertID(),
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->catalogoModel->errors(),
        ]);
    }

    public function update($id = null)
    {
        if ($id === null || ! $this->catalogoModel->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Bien/servicio no encontrado.'
            ]);
        }

        $data = $this->request->getJSON(true);

        if (empty($data)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 400,
                'message' => 'No se enviaron datos para actualizar.'
            ]);
        }

        $data['nombre']    = trim($data['nombre'] ?? '');
        $data['categoria'] = trim($data['categoria'] ?? '') ?: null;

        if ($this->catalogoModel->update($id, $data)) {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Bien/servicio actualizado correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->catalogoModel->errors(),
        ]);
    }

    // Desactivar/activar, no borrar: así deja de aparecer en el
    // autocompletar de partidas pero no se pierde el histórico.
    public function cambiarEstado($id = null)
    {
        $registro = $id !== null ? $this->catalogoModel->find($id) : null;

        if (! $registro) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Bien/servicio no encontrado.'
            ]);
        }

        $nuevoEstado = $registro['activo'] ? 0 : 1;
        $this->catalogoModel->update($id, ['activo' => $nuevoEstado]);

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'activo' => $nuevoEstado,
            'message' => $nuevoEstado ? 'Bien/servicio activado.' : 'Bien/servicio desactivado.',
        ]);
    }

    public function delete($id = null)
    {
        if ($id === null || ! $this->catalogoModel->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Bien/servicio no encontrado.'
            ]);
        }

        if ($this->catalogoModel->delete($id)) {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Bien/servicio eliminado correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status'  => 400,
            'message' => 'Ocurrió un error al eliminar el bien/servicio.'
        ]);
    }
}