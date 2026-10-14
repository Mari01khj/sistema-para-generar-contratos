<?php

namespace App\Models;

use CodeIgniter\Model;

class CamposFormularioModel extends Model
{
    protected $table            = 'campos_formulario';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tipo_contrato_id',
        'etiqueta',
        'nombre_campo',
        'tipo_dato',
        'origen_lista',
        'obligatorio',
        'orden',
        'activo',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**  lista de tipos de dato válidos.*/
    public const TIPOS_DATO = [
        'texto'       => 'Texto corto',
        'texto_largo' => 'Texto largo (párrafo)',
        'numero'      => 'Número',
        'fecha'       => 'Fecha',
        'lista'       => 'Lista desplegable',
    ];

    /** Catálogos de los que puede salir una lista desplegable.*/
    public const CATALOGOS_LISTA = [
        'proveedores' => 'Proveedores',
        'areas'       => 'Áreas',
    ];

    protected $validationRules = [
        'tipo_contrato_id' => 'required|is_natural_no_zero',
        'etiqueta'         => 'required|max_length[150]|min_length[2]',
        'nombre_campo'     => 'required|max_length[100]|regex_match[/^[a-z][a-z0-9_]*$/]',
        'tipo_dato'        => 'required|in_list[texto,texto_largo,numero,fecha,lista]',
        'origen_lista'     => 'permit_empty|in_list[proveedores,areas]',
        'obligatorio'      => 'permit_empty|in_list[0,1]',
        'orden'            => 'permit_empty|is_natural',
    ];

    protected $validationMessages = 
    [
        'nombre_campo' => [
            'regex_match' => 'El nombre técnico solo puede tener minúsculas, números y guion bajo, y debe empezar con una letra (ej. fecha_entrega).',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    public function existeNombreCampo(int $tipoContratoId, string $nombreCampo, ?int $excluirId = null): bool
    {
        $builder = $this->withDeleted()
            ->where('tipo_contrato_id', $tipoContratoId)
            ->where('nombre_campo', $nombreCampo);

        if ($excluirId !== null) {
            $builder->where('id !=', $excluirId);
        }

        return $builder->countAllResults() > 0;
    }

    /**Deshace un borrado lógico.*/
    public function reactivar(int $id): bool
    {
        return $this->builder()
            ->where('id', $id)
            ->update(['deleted_at' => null]);
    }

    /**Campos activos de un tipo de contrato en orden */
    public function paraTipoContrato(int $tipoContratoId): array
    {
        return $this->where('tipo_contrato_id', $tipoContratoId)
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
