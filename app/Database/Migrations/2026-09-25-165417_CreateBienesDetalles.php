<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBienesDetalles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'num_partida' => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'descripcion' => [
                'type' => 'TEXT',
            ],
            'cantidad' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'unidad_medida' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'marca' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'precio_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'importe' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'iva' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'total' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('contrato_id', 'contratos', 'id', 'CASCADE', 'CASCADE', 'fk_bienes_contratos');
        $this->forge->createTable('bienes_detalles', true);
    }

    public function down()
    {
        $this->forge->dropTable('bienes_detalles', true);
    }
}