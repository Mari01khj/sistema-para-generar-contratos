<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) 
        {
            return (session()->get('rol_id') == 1) 
                ? redirect()->to(base_url('admin/dashboard')) 
                : redirect()->to(base_url('operador/dashboardOperador'));
        }

        return view('auth/login');
    }

    public function authenticate()
    {
        $usuarioModel = new UsuarioModel();
        $correo   = $this->request->getPost('correo');
        $password = (string)$this->request->getPost('password');

        $usuario = $usuarioModel->obtenerPorCorreo($correo);

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
          
            $sessionData = [
                'usuario_id'  => $usuario['id'],
                'nombre'      => $usuario['nombre'],
                'correo'      => $usuario['correo'],
                'rol_id'      => (int)$usuario['rol_id'],
                'isLoggedIn'  => true,
            ];
            session()->set($sessionData);

            if ($usuario['rol_id'] == 1) 
            {
                return redirect()->to(base_url('admin/dashboard'));
            } 
            else 
            {
                return redirect()->to(base_url('operador/dashboardOperador'));
            }
        }

        return redirect()->back()->withInput()->with('error', 'Credenciales incorrectas o usuario inactivo.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}
