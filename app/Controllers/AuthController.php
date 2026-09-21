<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) 
        {
            helper('sesion');

            return redirect()->to(ruta_dashboard());
        }

        return view('auth/login');
    }

    public function authenticate()
    {
        $userModel = new UserModel();
        $correo    = (string) $this->request->getPost('correo');
        $password  = (string) $this->request->getPost('password');

        $usuario = $userModel->obtenerPorCorreo($correo);

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            //id de sesion
            session()->regenerate();

            session()->set([
                'usuario_id' => $usuario['id'],
                'nombre'     => $usuario['nombre'],
                'correo'     => $usuario['correo'],
                'rol_id'     => (int) $usuario['rol_id'],
                'isLoggedIn' => true,
            ]);

            helper('sesion');

            return redirect()->to(ruta_dashboard((int) $usuario['rol_id']));
        }

        return redirect()->back()->withInput()->with('error', 'Credenciales incorrectas o usuario inactivo.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(route_to('loginForm'));
    }
}
