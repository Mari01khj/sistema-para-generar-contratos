<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AreasModel;

class AreasController extends BaseController
{
    protected $areaModel;

    public function __construct()
    {
        $this->areaModel = new AreasModel();
    }

    public function index()
    {
        return view('catalogos/AreasView', [
            'titulo' => 'Catálogo de Áreas'
        ]);
    }

    public function listar()
    {
        $areas = $this->areaModel->findAll();
        return $this->response->setJSON($areas);
    }

    //metodo crear
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

        if ($this->areaModel->insert($data)) 
        {
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 201,
                'message' => 'Área registrada correctamente'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->areaModel->errors()
        ]);
    }
    // método actualizar
    public function update($id = null)
    {
        if ($id === null || !$this->areaModel->find($id)) 
        {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Área no encontrada.'
            ]);
        }

        $data = $this->request->getJSON(true);

        if (empty($data)) 
        {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 400,
                'message' => 'No se enviaron datos para actualizar.'
            ]);
        }

        if ($this->areaModel->update($id, $data)) 
        {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Área actualizada correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->areaModel->errors()
        ]);
    }

    // método eliminar
    public function delete($id = null)
    {
        if ($id === null || !$this->areaModel->find($id)) 
        {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Área no encontrada.'
            ]);
        }

        if ($this->areaModel->delete($id)) 
        {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Área eliminada correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status'  => 400,
            'message' => 'Ocurrió un error al eliminar el área.'
        ]);
    }
}

