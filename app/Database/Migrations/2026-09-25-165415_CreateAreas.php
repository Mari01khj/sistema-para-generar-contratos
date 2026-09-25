<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAreas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'nombre_area' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'titular_area' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'cargo_titular' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->createTable('areas', true);
    }

    public function down()
    {
        $this->forge->dropTable('areas', true);
    }
}
