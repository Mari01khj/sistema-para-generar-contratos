<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AreasModel;
use App\Models\ProveedoresModel;
use App\Models\TipoContrato;
use App\Models\ContratosModel;
use App\Models\FuentesFinanciamientoModel;
use App\Models\UserModel;
use App\Models\BienesServiciosModel;

class AdminController extends BaseController
{
    public function dashboard()
    {
        $proveedorModel = new ProveedoresModel();
        $areaModel      = new AreasModel();
        $tipoContrato = new TipoContrato();
        $contratosModel = new ContratosModel();
        $fuenteModel    = new FuentesFinanciamientoModel();
        $usuarioModel   = new UserModel();
        $bienModel      = new BienesServiciosModel();

        $data = [
            'titulo'            => 'Panel de Control - Administrador',
            'rol_id'            => session()->get('rol_id'),
            'total_proveedores' => $proveedorModel->countAllResults(),
            'total_areas'       => $areaModel->countAllResults(),
            'total_tipos'       => $tipoContrato->countAllResults(),
            'total_contratos'   => $contratosModel->whereIn('estado', ['borrador', 'en_correccion'])->countAllResults(),
            'total_fuentes'     => $fuenteModel->countAllResults(),
            'total_usuarios'    => $usuarioModel->countAllResults(),
            'total_bienes'      => $bienModel->countAllResults()
        ];
        return view('admin/dashboard', $data);
    }
}
