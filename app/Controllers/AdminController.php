<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AreasModel;
use App\Models\ProveedoresModel;
use App\Models\TipoContrato;
use App\Models\ContratosModel;

class AdminController extends BaseController
{
    public function dashboard()
    {
        $proveedorModel = new ProveedoresModel();
        $areaModel      = new AreasModel();
        $tipoContrato = new TipoContrato();
        $contratosModel = new ContratosModel();

        $data = [
            'titulo'            => 'Panel de Control - Administrador',
            'rol_id'            => session()->get('rol_id'),
            'total_proveedores' => $proveedorModel->countAllResults(),
            'total_areas'       => $areaModel->countAllResults(),
            'total_tipos'       => $tipoContrato->countAllResults(),
            'total_contratos'   => $contratosModel->where('estado', 'activo')->countAllResults()
        ];
        return view('admin/dashboard', $data);
    }
}
