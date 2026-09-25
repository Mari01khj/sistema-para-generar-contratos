<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProveedores extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'razon_social' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'rfc' => [
                'type'       => 'VARCHAR',
                'constraint' => 13,
            ],
            'representante_legal' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'domicilio_fiscal' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'actividad_economica' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'direccion' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
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
        $this->forge->createTable('proveedores', true);
    }

    public function down()
    {
        $this->forge->dropTable('proveedores', true);
    }
}