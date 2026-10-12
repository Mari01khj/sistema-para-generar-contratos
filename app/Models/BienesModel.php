<?php

namespace App\Models;

use CodeIgniter\Model;

class BienesModel extends Model
{
    protected $table            = 'bienes_detalles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'num_partida',
        'contrato_id',
        'descripcion',
        'unidad_medida',
        'marca',
        'cantidad',
        'precio_unitario',
        'importe',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'descripcion'     => 'required|max_length[500]',
        'unidad_medida'   => 'required|max_length[50]',
        'marca'           => 'permit_empty|max_length[60]',
        'cantidad'        => 'required|decimal',
        'precio_unitario' => 'required|decimal',
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
     * Las partidas (bienes) de un contrato, en el orden en que se
     * capturaron, para mostrarlas en el detalle/PDF del contrato.
     */
    public function paraContrato(int $contratoId): array
    {
        return $this->where('contrato_id', $contratoId)
            ->orderBy('num_partida', 'ASC')
            ->findAll();
    }
}
