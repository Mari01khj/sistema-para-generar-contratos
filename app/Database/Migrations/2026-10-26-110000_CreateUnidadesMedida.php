<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Catálogo de unidades de medida (Pieza, Caja, Servicio, etc.) que se
 * elige de una lista al capturar un bien/servicio, en vez de escribirla
 * libre cada vez — así "PIEZA"/"PZA"/"PZ" no quedan como 3 unidades
 * distintas por error de captura.
 */
class CreateUnidadesMedida extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'unique'     => true,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('unidades_medida', true);
    }

    public function down()
    {
        $this->forge->dropTable('unidades_medida', true);
    }
}
