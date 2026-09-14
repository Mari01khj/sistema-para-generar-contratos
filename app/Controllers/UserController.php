<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $usuarios = $this->userModel
                         ->select('id, rol_id, nombre, correo, activo, created_at')
                         ->findAll();

        return $this->response->setJSON($usuarios);
    }

//METODO PARA CREAR UN NUEVO USUARIO
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data)) 
        {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 400,
                'message' => 'No se recibieron datos.'
            ]);
        }

        if (!isset($data['activo'])) 
        {
            $data['activo'] = 1;
        }

        if ($this->userModel->insert($data)) 
        {
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 201,
                'message' => 'Usuario registrado correctamente',
                'id'      => $this->userModel->getInsertID()
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->userModel->errors()
        ]);
    }
}