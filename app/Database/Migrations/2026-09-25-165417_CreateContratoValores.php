<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratoValores extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'campo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'valor' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('campo_id');
        $this->forge->addUniqueKey(['contrato_id', 'campo_id']);

        $this->forge->addForeignKey('contrato_id', 'contratos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('campo_id', 'campos_formulario', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('contrato_valores', true);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('contrato_valores', true);
        $this->db->enableForeignKeyChecks();
    }
}