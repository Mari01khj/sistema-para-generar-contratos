<?php

namespace App\Controllers;

use App\Models\CamposFormularioModel;
use App\Models\TipoContrato;

class CamposFormularioController extends BaseController
{
    protected $campoModel;
    protected $tipoContratoModel;

    public function __construct()
    {
        $this->campoModel        = new CamposFormularioModel();
        $this->tipoContratoModel = new TipoContrato();
    }

    /**
     * Pantalla: campos de UN tipo de contrato en particular.
     * $tipoContratoId viene de la URL: tipos-contrato/{id}/campos
     */
    public function gestion(int $tipoContratoId)
    {
        $tipoContrato = $this->tipoContratoModel->find($tipoContratoId);

        if (! $tipoContrato) {
            return redirect()->to(route_to('tiposContratoGestion'))
                ->with('error', 'Ese tipo de contrato no existe.');
        }

        // withDeleted(): a diferencia de paraTipoContrato() (que usa el
        // formulario dinámico), aquí el admin SÍ necesita ver los campos
        // eliminados, para poder reactivarlos si fue un error.
        $campos = $this->campoModel
            ->withDeleted()
            ->where('tipo_contrato_id', $tipoContratoId)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('catalogos/camposFormularioView', [
            'tipoContrato'   => $tipoContrato,
            'campos'         => $campos,
            'tiposDato'      => CamposFormularioModel::TIPOS_DATO,
            'catalogosLista' => CamposFormularioModel::CATALOGOS_LISTA,
            // Sugerencia de orden para el siguiente campo nuevo.
            'siguienteOrden' => count($campos) + 1,
        ]);
    }

    public function create(int $tipoContratoId)
    {
        $nombreCampo = (string) $this->request->getPost('nombre_campo');

        if ($this->campoModel->existeNombreCampo($tipoContratoId, $nombreCampo)) {
            return redirect()->to(route_to('camposFormularioGestion', $tipoContratoId))->withInput()
                ->with('error', 'Ya existe un campo con el nombre técnico "' . $nombreCampo . '" en este tipo de contrato. Si lo habías eliminado, reactívalo en vez de crear uno nuevo.');
        }

        $data = [
            'tipo_contrato_id' => $tipoContratoId,
            'etiqueta'         => $this->request->getPost('etiqueta'),
            'nombre_campo'     => $nombreCampo,
            'tipo_dato'        => $this->request->getPost('tipo_dato'),
            // Si el tipo de dato no es "lista", origen_lista no aplica.
            'origen_lista'     => $this->request->getPost('tipo_dato') === 'lista'
                ? $this->request->getPost('origen_lista')
                : null,
            'obligatorio'      => $this->request->getPost('obligatorio') ? 1 : 0,
            'orden'            => $this->request->getPost('orden') ?: 0,
            'activo'           => 1,
        ];

        if (! $this->campoModel->insert($data)) {
            return redirect()->to(route_to('camposFormularioGestion', $tipoContratoId))->withInput()
                ->with('error', 'No se pudo crear el campo.')
                ->with('errores_validacion', $this->campoModel->errors());
        }

        return redirect()->to(route_to('camposFormularioGestion', $tipoContratoId))
            ->with('mensaje', 'Campo "' . $data['etiqueta'] . '" agregado correctamente.');
    }

    public function update(int $id)
    {
        $campo = $this->campoModel->find($id);

        if (! $campo) {
            return redirect()->back()->with('error', 'Ese campo no existe.');
        }

        $nombreCampo = (string) $this->request->getPost('nombre_campo');

        if ($this->campoModel->existeNombreCampo($campo['tipo_contrato_id'], $nombreCampo, $id)) {
            return redirect()->to(route_to('camposFormularioGestion', $campo['tipo_contrato_id']))->withInput()
                ->with('error', 'Ya existe otro campo con el nombre técnico "' . $nombreCampo . '" en este tipo de contrato.');
        }

        $data = [
            'etiqueta'     => $this->request->getPost('etiqueta'),
            'nombre_campo' => $nombreCampo,
            'tipo_dato'    => $this->request->getPost('tipo_dato'),
            'origen_lista' => $this->request->getPost('tipo_dato') === 'lista'
                ? $this->request->getPost('origen_lista')
                : null,
            'obligatorio'  => $this->request->getPost('obligatorio') ? 1 : 0,
            'orden'        => $this->request->getPost('orden') ?: 0,
        ];

        if (! $this->campoModel->update($id, $data)) {
            return redirect()->to(route_to('camposFormularioGestion', $campo['tipo_contrato_id']))->withInput()
                ->with('error', 'No se pudo actualizar el campo.')
                ->with('errores_validacion', $this->campoModel->errors());
        }

        return redirect()->to(route_to('camposFormularioGestion', $campo['tipo_contrato_id']))
            ->with('mensaje', 'Campo actualizado correctamente.');
    }

    /**
     * Borrado LÓGICO (ver el porqué en la migración de campos_formulario):
     * el campo deja de mostrarse en contratos nuevos, pero los contratos
     * que ya lo usaron conservan su valor guardado.
     */
    public function delete(int $id)
    {
        $campo = $this->campoModel->find($id);

        if (! $campo) {
            return redirect()->back()->with('error', 'Ese campo no existe.');
        }

        $this->campoModel->delete($id);

        return redirect()->to(route_to('camposFormularioGestion', $campo['tipo_contrato_id']))
            ->with('mensaje', 'Campo "' . $campo['etiqueta'] . '" eliminado. Los contratos que ya lo usaron no se ven afectados.');
    }

    /**
     * Reactivar un campo que se había "eliminado".
     */
    public function activar(int $id)
    {
        $campo = $this->campoModel->withDeleted()->find($id);

        if (! $campo) {
            return redirect()->back()->with('error', 'Ese campo no existe.');
        }

        $this->campoModel->reactivar($id);

        return redirect()->to(route_to('camposFormularioGestion', $campo['tipo_contrato_id']))
            ->with('mensaje', 'Campo "' . $campo['etiqueta'] . '" reactivado.');
    }
}

