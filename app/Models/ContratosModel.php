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
