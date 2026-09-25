<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCamposFormulario extends Migration
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
            'tipo_contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'etiqueta' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'nombre_campo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tipo_dato' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'origen_lista' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'obligatorio' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'orden' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        
        $this->forge->addUniqueKey(['tipo_contrato_id', 'nombre_campo']);
        
        $this->forge->addForeignKey('tipo_contrato_id', 'tipos_contrato', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('campos_formulario', true);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('campos_formulario', true);
        $this->db->enableForeignKeyChecks();
    }
}