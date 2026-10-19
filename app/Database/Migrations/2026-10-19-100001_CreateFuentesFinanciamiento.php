<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFuentesFinanciamiento extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            // la parte del folio que va entre el tipo de contrato y las
            // siglas del procedimiento (ej. "FORTAMUN", "FAIS", "RP")
            'clave' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'unique'     => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at datetime default current_timestamp',
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fuentes_financiamiento', true);
    }

    public function down()
    {
        $this->forge->dropTable('fuentes_financiamiento', true);
    }
}
