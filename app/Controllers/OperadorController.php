<?php
namespace App\Controllers;

use App\Models\UserModel; 

class OperadorController extends BaseController
{
    public function misDatos()
    {
        $userModel = new UserModel();
        $usuario = $userModel->find(session()->get('usuario_id'));

        return view('operador/MisDatosView', [
            'titulo' => 'Mis Datos',
            'usuario' => $usuario
        ]);
    }

    public function actualizarDatos()
    {
        $userModel = new UserModel();
        $id = session()->get('usuario_id');
        
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'correo' => $this->request->getPost('correo'),
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) 
        {
            $data['password_hash'] = $password;
        }

        if ($userModel->update($id, $data)) 
        {
            session()->set('nombre', $data['nombre']);
            return redirect()->back()->with('success', 'Tus datos han sido actualizados.');
        }

        return redirect()->back()->with('error', 'Ocurrió un error al actualizar tus datos.');
    }
}
