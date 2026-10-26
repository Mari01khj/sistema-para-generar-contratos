<?php

namespace App\Models;

use CodeIgniter\Model;

class BienesServiciosModel extends Model
{
    protected $table            = 'bienes_servicios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'partida_presupuestal',
        'nombre',
        'unidad_medida_id',
        'precio',
        'categoria',
        'activo',
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

    // Validation
    protected $validationRules = [
        'nombre' => 'required',
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

    public function buscar(string $texto, int $limite = 20): array
    {
        return $this->select('bienes_servicios.id, bienes_servicios.nombre, bienes_servicios.precio, '
                . 'bienes_servicios.categoria, unidades_medida.nombre AS unidad_medida')
            ->join('unidades_medida', 'unidades_medida.id = bienes_servicios.unidad_medida_id', 'left')
            ->where('bienes_servicios.activo', 1)
            ->like('bienes_servicios.nombre', $texto)
            ->orderBy('bienes_servicios.nombre', 'ASC')
            ->limit($limite)
            ->find();
    }
}
