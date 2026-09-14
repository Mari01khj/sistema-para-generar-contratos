<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ContratosModel;

class ContratosController extends ResourceController
{
    protected $modelName = 'App\Models\ContratosModel';
    protected $format    = 'json';

    public function index()
    {
        $contratos = $this->model->findAll();
        return $this->respond($contratos);
    }

    //FUNCION PARA CREAR CONTRATOS
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) {
            return $this->fail('No hay datos.', 400);
        }

        if ($this->model->insert($data)) {
            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Contrato registrado correctamente',
                'id'      => $this->model->getInsertID()
            ]);
        }

        return $this->failValidationErrors($this->model->errors());
    }
}
