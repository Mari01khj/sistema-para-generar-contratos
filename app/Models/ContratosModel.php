<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratosModel extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Paso 4: "eliminar" un contrato NO lo borra de la base de datos.
    // Con useSoftDeletes=true, Model::delete($id) en realidad hace
    // UPDATE contratos SET deleted_at = NOW() WHERE id = $id, y a partir
    // de ahí findAll()/find()/where() de este modelo IGNORAN esa fila
    // automáticamente (como si no existiera), a menos que se use
    // withDeleted() explícitamente. Así el contrato sigue en la base de
    // datos por si se necesita consultar o recuperar, pero desaparece de
    // los listados normales.
    protected $useSoftDeletes   = true;

    // protectFields=true es la razón por la que $allowedFields existe:
    // cualquier llave del array que se le pase a insert()/update() que NO
    // esté en esta lista se descarta en silencio (sin error, sin aviso).
    // Es una medida de seguridad (nadie puede inyectar una columna que no
    // debería tocar desde un formulario), pero también es la fuente de
    // varios bugs "silenciosos" que hemos ido encontrando: si agregas una
    // columna nueva a la tabla y se te olvida agregarla aquí, esa columna
    // nunca se va a llenar y no vas a ver ningún error explicándolo.
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
    // No hay columna "updated_at" en la tabla contratos, así que se deja
    // vacío: CodeIgniter no intenta escribir en una columna que no existe.
    protected $updatedField  = '';
    // Este es el nombre de columna que usan useSoftDeletes=true y
    // withDeleted()/onlyDeleted() de arriba para saber dónde escribir y
    // dónde filtrar. Tiene que coincidir con el nombre real de la columna
    // (la que agrega la migración AddDeletedAtToContratos).
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
     * Para el listado (contratos/index): trae TODOS los contratos (los no
     * eliminados; eso ya lo filtra useSoftDeletes solo) con el nombre del
     * tipo, del proveedor y del área ya resueltos, para no tener que hacer
     * una consulta aparte por cada fila solo para mostrar un nombre.
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

    /**
     * Lo mismo que listaConDetalle(), pero para UN solo contrato (lo usan
     * ver() y editar() en el controlador). Regresa null si el id no
     * existe o si el contrato ya fue "eliminado" (soft delete).
     */
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
