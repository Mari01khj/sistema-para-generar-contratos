<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Una fila por cada campo dinámico lleno de cada contrato.
 * No usa soft delete: cuando se borra un contrato, sus valores
 * no tienen razón de existir por separado (ver ContratosController).
 */
class ContratoValoresModel extends Model
{
    protected $table            = 'contrato_valores';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'contrato_id',
        'campo_id',
        'valor',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Todos los valores dinámicos de un contrato, ya con la etiqueta
     * y el tipo de dato de su campo (join con campos_formulario).
     * Útil para mostrar el contrato completo (paso 4) o exportarlo (paso 6).
     */
    public function paraContrato(int $contratoId): array
    {
        return $this->select('contrato_valores.*, campos_formulario.etiqueta, campos_formulario.nombre_campo, campos_formulario.tipo_dato')
            ->join('campos_formulario', 'campos_formulario.id = contrato_valores.campo_id')
            ->where('contrato_valores.contrato_id', $contratoId)
            ->orderBy('campos_formulario.orden', 'ASC')
            ->findAll();
    }
}
