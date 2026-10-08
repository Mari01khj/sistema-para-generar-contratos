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
        return view('catalogos/UsuariosView', [
            'titulo' => 'Catálogo de Usuarios'
        ]);
    }

    public function listar()
    {
        $usuarios = $this->userModel->findAll();
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
//METODO PARA ACTUALIZAR 
public function update($id = null)
    {
        if ($id === null || !$this->userModel->find($id)) 
        {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Usuario no encontrado.'
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

        if ($this->userModel->update($id, $data)) 
        {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Usuario actualizado correctamente.'
            ]);


        return $this->response->setStatusCode(400)->setJSON([
            'status' => 400,
            'errors' => $this->userModel->errors()
        ]);
    }
}

    //METODO PARA ELIMINAR UN USUARIO
public function delete($id = null)
{
        if ($id === null || !$this->userModel->find($id)) 
        {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Usuario no encontrado.'
            ]);
        }

        if ($this->userModel->delete($id)) 
        {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 200,
                'message' => 'Usuario eliminado correctamente.'
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status'  => 400,
            'message' => 'Ocurrió un error al eliminar el usuario.'
        ]);
    }
}