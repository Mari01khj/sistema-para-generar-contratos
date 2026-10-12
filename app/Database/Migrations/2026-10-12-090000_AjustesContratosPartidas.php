<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 *subtotal/iva/total se agregan aquí como columnas fijas en contratos.
 */
class AjustesContratosPartidas extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('contratos', 'descripcion_larga');

        $this->forge->modifyColumn('bienes_detalles', [
            'cantidad' => [
                'name'       => 'cantidad',
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
        ]);

        $this->forge->addColumn('contratos', [
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'condiciones_entrega',
            ],
            'iva' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'subtotal',
            ],
            'total' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'iva',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('contratos', ['subtotal', 'iva', 'total']);

        $this->forge->addColumn('contratos', [
            'descripcion_larga' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'descripcion_corta',
            ],
        ]);

        $this->forge->modifyColumn('bienes_detalles', [
            'cantidad' => [
                'name'       => 'cantidad',
                'type'       => 'INT',
                'constraint' => 11,
            ],
        ]);
    }
}
