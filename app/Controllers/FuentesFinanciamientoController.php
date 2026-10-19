<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FuentesFinanciamientoModel;

class FuentesFinanciamientoController extends BaseController
{
    protected $fuenteModel;

    public function __construct()
    {
        $this->fuenteModel = new FuentesFinanciamientoModel();
    }

    public function index()
    {
        return view('catalogos/FuentesFinanciamientoView', [
            'titulo' => 'Catálogo de Fuentes de Financiamiento'
        ]);
    }

    public function listar()
    {
        $fuentes = $this->fuenteModel->findAll();
        return $this->response->setJSON($fuentes);
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

        $data['activo'] = 1;

        if ($this->fuenteModel->insert($data)) {
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 201,
                'message' => 'Fuente de financiamiento registrada correctamente'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->fuenteModel->errors()
        ]);
    }

    public function update($id = null)
    {
        if ($id === null || !$this->fuenteModel->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Fuente de financiamiento no encontrada.'
            ]);
        }

        $data = $this->request->getJSON(true);

        if (empty($data)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 400,
                'message' => 'No se enviaron datos para actualizar.'
            ]);
        }

        if ($this->fuenteModel->update($id, $data)) {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Fuente de financiamiento actualizada correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->fuenteModel->errors()
        ]);
    }

    public function delete($id = null)
    {
        if ($id === null || !$this->fuenteModel->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Fuente de financiamiento no encontrada.'
            ]);
        }

        if ($this->fuenteModel->delete($id)) {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Fuente de financiamiento eliminada correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status'  => 400,
            'message' => 'Ocurrió un error al eliminar la fuente de financiamiento.'
        ]);
    }
}
