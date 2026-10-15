<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratosModel extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    //NO lo borra de la base de datos. En realidad hace UPDATE a contratos y a partir de de este modelo IGNORAN esa fila
    //Así el contrato sigue en la base de bdatos por si se necesita consultar o recuperar, pero desaparece de los listados normales.

    protected $useSoftDeletes   = true;

    // cualquier columna que no esté en $allowedFields será ignorada al hacer insert() o update()
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folio',
        'tipo_contrato_id',
        'proveedor_id',
        'area_solicitante_id',
        'descripcion_corta',
        'usuario_id',
        'estado',
        'fecha_entrega',
        'lugar_entrega',
        'plazo_pago',
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
        'tipo_contrato_id'     => 'required|is_natural_no_zero',
        'proveedor_id'         => 'required|is_natural_no_zero',
        'area_solicitante_id'  => 'required|is_natural_no_zero',
        'descripcion_corta'    => 'required|max_length[255]',
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
     * Devuelve una lista de contratos con detalles.
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

    /**Lo mismo pero para UN solo contrato*/
    public function unoConDetalle(int $id): ?array
    {
        return $this->select('contratos.*, tipos_contrato.nombre as tipo_nombre, proveedores.razon_social, areas.nombre_area')
            ->join('tipos_contrato', 'tipos_contrato.id = contratos.tipo_contrato_id')
            ->join('proveedores', 'proveedores.id = contratos.proveedor_id')
            ->join('areas', 'areas.id = contratos.area_solicitante_id')
            ->where('contratos.id', $id)
            ->first();
    }
}
