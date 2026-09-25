<?php

namespace App\Controllers;

use App\Models\CamposFormularioModel;
use App\Models\TipoContrato;

class TipoContratoController extends BaseController
{
    protected $tipoContratoModel;

    public function __construct()
    {
        $this->tipoContratoModel = new TipoContrato();
    }

    /**
     * Pantalla: lista de tipos de contrato + formulario para crear uno.
     * Desde aquí el admin entra a "Gestionar campos" de cada tipo.
     */
    public function gestion()
    {
        $campoModel = new CamposFormularioModel();
        $tipos      = $this->tipoContratoModel->orderBy('id', 'DESC')->findAll();

        // Para mostrar "3 campos" junto a cada tipo, sin una consulta por fila.
        $conteoCampos = [];
        foreach ($campoModel->where('activo', 1)->findAll() as $campo) {
            $tid = $campo['tipo_contrato_id'];
            $conteoCampos[$tid] = ($conteoCampos[$tid] ?? 0) + 1;
        }

        return view('catalogos/tiposContratosView', [
            'tipos'        => $tipos,
            'conteoCampos' => $conteoCampos,
        ]);
    }

    public function index()
    {
        $tipos = $this->tipoContratoModel->findAll();
        return $this->response->setJSON($tipos);
    }

    public function create()
    {
        $data = [
            'nombre'             => $this->request->getPost('nombre'),
            'requiere_conceptos' => $this->request->getPost('requiere_conceptos') ?? 0,
            'lleva_precios'      => $this->request->getPost('lleva_precios') ?? 0,
            'datos_entrega'      => $this->request->getPost('datos_entrega') ?? 0,
            'activo'             => $this->request->getPost('activo') ?? 1,
        ];

        $archivo = $this->request->getFile('plantilla');

        if ($archivo && $archivo->isValid() && ! $archivo->hasMoved()) {
            $extension  = strtolower($archivo->getClientExtension());
            $permitidas = ['docx', 'doc', 'xlsx', 'xls'];

            if (! in_array($extension, $permitidas, true)) {
                return redirect()->back()->withInput()
                    ->with('error', 'Formato no permitido. Solo se aceptan archivos Word (.docx) o Excel (.xlsx).');
            }

            $nuevoNombre = uniqid('plantilla_', true) . '.' . $extension;
            $archivo->move(WRITEPATH . 'uploads/plantillas', $nuevoNombre);

            $data['plantilla'] = 'uploads/plantillas/' . $nuevoNombre;
        } else {
            return redirect()->back()->withInput()
                ->with('error', 'Es obligatorio adjuntar un archivo de plantilla válido.');
        }

        if (! $this->tipoContratoModel->insert($data)) {
            return redirect()->back()->withInput()
                ->with('error', 'No se pudo registrar el tipo de contrato.')
                ->with('errores_validacion', $this->tipoContratoModel->errors());
        }

        return redirect()->to(route_to('tiposContratoGestion'))
            ->with('mensaje', 'Tipo de contrato "' . $data['nombre'] . '" registrado correctamente.');
    }
}
