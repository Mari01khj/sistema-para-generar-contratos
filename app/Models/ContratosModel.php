<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratosModel extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $protectFields    = true;
    protected $allowedFields    = [
        'folio',
        'tipo_contrato_id',
        'fuente_financiamiento_id',
        'proveedor_id',
        'area_solicitante_id',
        'descripcion_corta',
        'nombre_bien',
        'usuario_id',
        'estado',
        'fecha_entrega',
        'lugar_entrega',
        'plazo_pago',
        'condiciones_entrega',
        'subtotal',
        'iva',
        'total',
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
    protected $deletedField  = 'deleted_at';


    protected $validationRules      = [
        'tipo_contrato_id'          => 'required|is_natural_no_zero',
        'fuente_financiamiento_id'  => 'permit_empty|is_natural_no_zero',
        'proveedor_id'              => 'required|is_natural_no_zero',
        'area_solicitante_id'       => 'required|is_natural_no_zero',
        'descripcion_corta'         => 'required|max_length[255]',
        'fecha_entrega'             => 'permit_empty',
        'lugar_entrega'             => 'permit_empty',
        'plazo_pago'                => 'permit_empty',
        'condiciones_entrega'       => 'permit_empty',
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


    public function listaConDetalle(): array
    {
        return $this->select('contratos.*, tipos_contrato.nombre as tipo_nombre, proveedores.razon_social, areas.nombre_area, fuentes_financiamiento.nombre as fuente_nombre')
            ->join('tipos_contrato', 'tipos_contrato.id = contratos.tipo_contrato_id')
            ->join('proveedores', 'proveedores.id = contratos.proveedor_id')
            ->join('areas', 'areas.id = contratos.area_solicitante_id')
            ->join('fuentes_financiamiento', 'fuentes_financiamiento.id = contratos.fuente_financiamiento_id', 'left')
            ->orderBy('contratos.id', 'DESC')
            ->findAll();
    }

    /**
     * Lo mismo que listaConDetalle pero con un solo contrato
     */
    public function unoConDetalle(int $id): ?array
    {
        return $this->select('contratos.*, tipos_contrato.nombre as tipo_nombre, tipos_contrato.siglas_procedimiento, proveedores.razon_social, areas.nombre_area, fuentes_financiamiento.nombre as fuente_nombre')
            ->join('tipos_contrato', 'tipos_contrato.id = contratos.tipo_contrato_id')
            ->join('proveedores', 'proveedores.id = contratos.proveedor_id')
            ->join('areas', 'areas.id = contratos.area_solicitante_id')
            ->join('fuentes_financiamiento', 'fuentes_financiamiento.id = contratos.fuente_financiamiento_id', 'left')
            ->where('contratos.id', $id)
            ->first();
    }
}
