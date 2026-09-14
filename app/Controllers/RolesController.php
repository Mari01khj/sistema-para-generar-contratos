<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RolesModel;

class RolesController extends ResourceController
{
    protected $modelName = 'App\Models\RolesModel';
    protected $format    = 'json';

    public function index()
    {
        $roles = $this->model->findAll();
        return $this->respond($roles);
    }

    //FUNCION PARA CREAR ROLES
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) {
            return $this->fail('No hay datos.', 400);
        }

        if ($this->model->insert($data)) {
            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Rol registrado correctamente',
                'id'      => $this->model->getInsertID()
            ]);
        }

        return $this->failValidationErrors($this->model->errors());
    }
}