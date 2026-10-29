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
     * ya existe */
    /**
     * Guarda solo el prefijo fijo y las siglas del procedimiento de un
     * tipo que ya existe  */
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

    // Edita solo el nombre; prefijo/siglas y plantilla ya tienen su propio
    // formulario (actualizarFolio/actualizarPlantilla).
    public function update(int $id)
    {
        $tipoContrato = $this->tipoContratoModel->find($id);

        if (! $tipoContrato) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'Ese tipo de contrato no existe.');
        }

        $nombre = trim((string) $this->request->getPost('nombre'));

        if ($nombre === '') {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'El nombre no puede quedar vacío.');
        }

        $this->tipoContratoModel->update($id, ['nombre' => $nombre]);

        return redirect()->to(route_to('tiposContratoGestion'))
            ->with('mensaje', 'Tipo de contrato actualizado correctamente.');
    }

    // Activo/inactivo: para los tipos que ya no se usan pero no se pueden
    // borrar porque ya tienen contratos o campos asociados (ver delete()).
    public function cambiarEstado(int $id)
    {
        $tipoContrato = $this->tipoContratoModel->find($id);

        if (! $tipoContrato) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'Ese tipo de contrato no existe.');
        }

        $this->tipoContratoModel->update($id, ['activo' => $tipoContrato['activo'] ? 0 : 1]);

        return redirect()->to(route_to('tiposContratoGestion'))
            ->with('mensaje', 'Estado de "' . $tipoContrato['nombre'] . '" actualizado.');
    }

    public function delete(int $id)
    {
        $tipoContrato = $this->tipoContratoModel->find($id);

        if (! $tipoContrato) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'Ese tipo de contrato no existe.');
        }

        // tipos_contrato tiene llaves foráneas RESTRICT desde
        // campos_formulario y contratos: si ya tiene campos o contratos
        // asociados, la base de datos rechaza el borrado. En ese caso se
        // avisa y se sugiere desactivarlo en vez de eliminarlo.
        try {
            $this->tipoContratoModel->delete($id);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'No se puede eliminar "' . $tipoContrato['nombre'] . '": ya tiene campos o contratos asociados. Desactívalo en su lugar.');
        }

        if (! empty($tipoContrato['plantilla']) && is_file(WRITEPATH . $tipoContrato['plantilla'])) {
            unlink(WRITEPATH . $tipoContrato['plantilla']);
        }

        return redirect()->to(route_to('tiposContratoGestion'))
            ->with('mensaje', 'Tipo de contrato "' . $tipoContrato['nombre'] . '" eliminado.');
    }
}