<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UnidadMedidaModel;

class UnidadesMedidaController extends BaseController
{
    protected $unidadModel;

    public function __construct()
    {
        $this->unidadModel = new UnidadMedidaModel();
    }

    public function index()
    {
        return view('catalogos/UnidadesMedidaView', [
            'titulo' => 'Catálogo de Unidades de Medida'
        ]);
    }

    public function listar()
    {
        $unidades = $this->unidadModel->orderBy('nombre', 'ASC')->findAll();
        return $this->response->setJSON($unidades);
    }


    public function activas()
    {
        $unidades = $this->unidadModel
            ->select('nombre')
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();

        return $this->response->setJSON(array_column($unidades, 'nombre'));
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

        $data['nombre'] = strtoupper(trim($data['nombre'] ?? ''));
        $data['activo'] = 1;

        if ($this->unidadModel->insert($data)) {
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 201,
                'message' => 'Unidad de medida registrada correctamente'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->unidadModel->errors()
        ]);
    }

    public function delete($id = null)
    {
        if ($id === null || ! $this->unidadModel->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Unidad de medida no encontrada.'
            ]);
        }

        if ($this->unidadModel->delete($id)) {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Unidad de medida eliminada correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status'  => 400,
            'message' => 'Ocurrió un error al eliminar la unidad de medida.'
        ]);
    }
}
