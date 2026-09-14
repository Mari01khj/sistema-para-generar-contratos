<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AreasModel;

class AreasController extends ResourceController
{
    protected $modelName = 'App\Models\AreasModel';
    protected $format    = 'json';

    public function index()
    {
        $areas = $this->model->findAll();
        return $this->respond($areas);
    }
//FUNCION PARA CREAR AREAS
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
                'message' => 'Área registrada correctamente',
                'id'      => $this->model->getInsertID()
            ]);
        }
        
        return $this->failValidationErrors($this->model->errors());
    }
}