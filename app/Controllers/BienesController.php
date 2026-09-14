<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BienesModel;

class BienesController extends ResourceController
{
    protected $modelName = 'App\Models\BienesModel';
    protected $format    = 'json';

    public function index()
    {
        $bienes = $this->model->findAll();
        return $this->respond($bienes);
    }
//FUNCION PARA CREAR BIENES
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
                'message' => 'registro correcto',
                'id'      => $this->model->getInsertID()
            ]);
        }

        return $this->failValidationErrors($this->model->errors());
    }
}
