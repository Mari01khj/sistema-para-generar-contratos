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

    
    public function gestion()
    {
        $campoModel = new CamposFormularioModel();
        $tipos      = $this->tipoContratoModel->orderBy('id', 'DESC')->findAll();

        $conteoCampos = [];
        foreach ($campoModel->where('activo', 1)->findAll() as $campo) 
        {
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
            'nombre'               => $this->request->getPost('nombre'),
            'prefijo_fijo'         => $this->request->getPost('prefijo_fijo'),
            'siglas_procedimiento' => strtoupper((string) $this->request->getPost('siglas_procedimiento')),
            'requiere_conceptos'   => $this->request->getPost('requiere_conceptos') ?? 0,
            'lleva_precios'        => $this->request->getPost('lleva_precios') ?? 0,
            'datos_entrega'        => $this->request->getPost('datos_entrega') ?? 0,
            'activo'               => $this->request->getPost('activo') ?? 1,
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

    /**
     * Reemplaza SOLO el archivo de la plantilla de un tipo de contrato que
     * ya existe (por ejemplo, para subir una versión corregida con los
     * placeholders del Paso 6). A propósito NO borra ni vuelve a crear la
     * fila de tipos_contrato: así sus campos dinámicos (campos_formulario)
     * y todos los contratos que ya se hicieron con ese tipo siguen
     * exactamente igual, intactos. Es la alternativa a "borrar y crear de
     * nuevo", que truena por las llaves foráneas en cuanto ese tipo ya
     * tiene al menos un contrato guardado (aunque ya esté eliminado).
     */
    /**
     * Guarda solo el prefijo fijo y las siglas del procedimiento de un
     * tipo que ya existe (las dos piezas del folio real que son fijas por
     * tipo, ver ContratosController::generarFolio()). Se edita aparte de
     * create() porque los tipos que ya existían antes de este campo
     * necesitan poder llenarlo sin tener que volver a subir su plantilla.
     */
    public function actualizarFolio(int $id)
    {
        $tipoContrato = $this->tipoContratoModel->find($id);

        if (! $tipoContrato) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'Ese tipo de contrato no existe.');
        }

        $this->tipoContratoModel->update($id, [
            'prefijo_fijo'         => $this->request->getPost('prefijo_fijo'),
            'siglas_procedimiento' => strtoupper((string) $this->request->getPost('siglas_procedimiento')),
        ]);

        return redirect()->to(route_to('tiposContratoGestion'))
            ->with('mensaje', 'Folio de "' . $tipoContrato['nombre'] . '" actualizado correctamente.');
    }

    public function actualizarPlantilla(int $id)
    {
        $tipoContrato = $this->tipoContratoModel->find($id);

        if (! $tipoContrato) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'Ese tipo de contrato no existe.');
        }

        $archivo = $this->request->getFile('plantilla');

        if (! $archivo || ! $archivo->isValid() || $archivo->hasMoved()) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'Selecciona un archivo válido para la plantilla.');
        }

        $extension  = strtolower($archivo->getClientExtension());
        $permitidas = ['docx', 'doc', 'xlsx', 'xls'];

        if (! in_array($extension, $permitidas, true)) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'Formato no permitido. Solo se aceptan archivos Word (.docx) o Excel (.xlsx).');
        }

        // Guarda la ruta del archivo VIEJO antes de pisarla, para borrarlo
        // del disco después de que el nuevo ya se haya guardado bien (si
        // algo fallara a medio camino, es mejor quedarse con un archivo
        // viejo de sobra que quedarse sin ninguno).
        $rutaAnterior = $tipoContrato['plantilla'] ? WRITEPATH . $tipoContrato['plantilla'] : null;

        $nuevoNombre = uniqid('plantilla_', true) . '.' . $extension;
        $archivo->move(WRITEPATH . 'uploads/plantillas', $nuevoNombre);

        $this->tipoContratoModel->update($id, [
            'plantilla' => 'uploads/plantillas/' . $nuevoNombre,
        ]);

        if ($rutaAnterior && is_file($rutaAnterior)) {
            unlink($rutaAnterior);
        }

        return redirect()->to(route_to('tiposContratoGestion'))
            ->with('mensaje', 'Plantilla de "' . $tipoContrato['nombre'] . '" actualizada correctamente.');
    }
}
