<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class OperadorController extends BaseController
{
    public function dashboardOperador()
    {
        $data = [
            'titulo' => 'Panel de Control - Operador',
            'rol_id' => session()->get('rol_id')
        ];
        return view('operador/dashboardOperador', $data);
    }
}
