<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProveedorModel;

class ProveedoresController extends ResourceController
{
    protected $modelName = 'App\Models\ProveedoresModel';
    protected $format    = 'json';

    public function index()
    {
        $proveedores = $this->model->findAll();
        return $this->respond($proveedores);
    }

    //FUNCION PARA CREAR PROVEEDORES
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) {
            return $this->fail('No hay datos.', 400);
        }

        if (!isset($data['activo'])) {
            $data['activo'] = 1;
        }

        if ($this->model->insert($data)) {
            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Proveedor registrado correctamente',
                'id'      => $this->model->getInsertID()
            ]);
        }

        return $this->failValidationErrors($this->model->errors());
    }
}
