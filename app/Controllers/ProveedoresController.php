<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\ProveedoresModel; 
class ProveedoresController extends BaseController
{
    protected $proveedorModel;

    public function __construct()
    {
        $this->proveedorModel = new ProveedoresModel();
    }

    public function index()
    {
          return view('catalogos/ProveedoresView', [
            'titulo' => 'Catálogo de Proveedores'
        ]);
    }
    public function listar()
    {
        $proveedores = $this->proveedorModel->findAll();
        return $this->response->setJSON($proveedores);
    }

    // METODO PARA CREAR UN NUEVO PROVEEDOR
    public function create()
    {
        $data = $this->request->getJSON(true);
        
        if (empty($data)) 
        {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 400,
                'message' => 'No se enviaron datos.'
            ]);
        }

        if (!isset($data['activo'])) 
        {
            $data['activo'] = 1;
        }

        if ($this->proveedorModel->insert($data)) 
        {
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 201,
                'message' => 'Proveedor registrado correctamente',
                'id'      => $this->proveedorModel->getInsertID()
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->proveedorModel->errors()
        ]);
    }

     public function update($id = null)
    {
        if ($id === null || !$this->proveedorModel->find($id)) 
        {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Proveedor no encontrado.'
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

        if ($this->proveedoresModel->update($id, $data)) 
        {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Proveedor actualizado correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->proveedoresModel->errors()
        ]);
    }

    // método eliminar
    public function delete($id = null)
    {
        if ($id === null || !$this->proveedorModel->find($id)) 
        {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Proveedor no encontrado.'
            ]);
        }

        if ($this->proveedorModel->delete($id)) 
        {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Proveedor eliminado correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status'  => 400,
            'message' => 'Ocurrió un error al eliminar el proveedor.'
        ]);
    }
}