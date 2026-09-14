<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class UserController extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    public function index()
    {
        $users = $this->model->findAll();
        return $this->respond($users);
    }
//FUNCION PARA CREAR USUARIOS
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
                'message' => 'Usuario registrado correctamente',
                'id'      => $this->model->getInsertID()
            ]);
        }
        
        return $this->failValidationErrors($this->model->errors());
    }
}

