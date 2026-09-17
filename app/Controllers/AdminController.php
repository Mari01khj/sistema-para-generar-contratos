<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AdminController extends BaseController
{
    public function dashboard()
    {
        $data = [
            'titulo' => 'Panel de Control - Administrador',
            'rol_id' => session()->get('rol_id')
        ];
        return view('admin/dashboard', $data);
    }
}
