<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AreaTecnicaYFinalizacion extends Migration
{
    private const ESTADOS_NUEVOS   = ['borrador', 'en_correccion', 'finalizado', 'cancelado'];
    private const ESTADOS_ANTERIOR = ['borrador', 'finalizado', 'cancelado'];

    public function up()
    {
        $this->forge->addColumn('contratos', [
            'area_tecnica_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'area_solicitante_id',
            ],
            'archivo_final' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'fecha_finalizacion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'finalizado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
        ]);

        $this->forge->modifyColumn('contratos', [
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => self::ESTADOS_NUEVOS,
                'default'    => 'borrador',
            ],
        ]);
    }

    public function down()
    {
        $this->db->table('contratos')->where('estado', 'en_correccion')->update(['estado' => 'borrador']);

        $this->forge->modifyColumn('contratos', [
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => self::ESTADOS_ANTERIOR,
                'default'    => 'borrador',
            ],
        ]);

        $this->forge->dropColumn('contratos', ['area_tecnica_id', 'archivo_final', 'fecha_finalizacion', 'finalizado_por']);
    }
}
