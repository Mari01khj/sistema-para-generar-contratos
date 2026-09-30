<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratosModel extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folio',
        'tipo_contrato_id',
        'proveedor_id',
        'area_solicitante_id',
        'descripcion_corta',
        'descripcion_larga',
        'usuario_id',
        'estado',
        'fecha_entrega',
        'lugar_entrega',
        'plazo_pago',
        'conddiciones_entrega'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';

    /**
     * "folio" no está aquí a propósito: lo genera el sistema después de
     * insertar (ver ContratosController::create), nunca lo escribe el
     * operador. "usuario_id" tampoco: lo pone el controlador a partir de
     * la sesión, no del formulario (para que nadie pueda crear un
     * contrato a nombre de otro usuario).
     */
    protected $validationRules      = [
        'tipo_contrato_id'     => 'required|is_natural_no_zero',
        'proveedor_id'         => 'required|is_natural_no_zero',
        'area_solicitante_id'  => 'required|is_natural_no_zero',
        'descripcion_corta'    => 'required|max_length[255]',
        'descripcion_larga'    => 'required',
        'fecha_entrega'        => 'permit_empty|valid_date[Y-m-d]',
        'lugar_entrega'        => 'permit_empty|max_length[255]',
        'plazo_pago'           => 'permit_empty|max_length[255]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Lista de contratos para la pantalla principal, con el nombre del
     * tipo, proveedor y área ya resueltos (evita N+1 consultas en la vista).
     */
    public function listaConDetalle(): array
    {
        return $this->select('contratos.*, tipos_contrato.nombre as tipo_nombre, proveedores.razon_social, areas.nombre_area')
            ->join('tipos_contrato', 'tipos_contrato.id = contratos.tipo_contrato_id')
            ->join('proveedores', 'proveedores.id = contratos.proveedor_id')
            ->join('areas', 'areas.id = contratos.area_solicitante_id')
            ->orderBy('contratos.id', 'DESC')
            ->findAll();
    }
}
