<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratos extends Migration
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
            'folio' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'descripcion_corta' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'descripcion_larga' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tipo_contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'proveedor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'area_solicitante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['borrador', 'finalizado', 'cancelado'],
                'default'    => 'borrador',
            ],
            'fecha_entrega' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'lugar_entrega' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'plazo_pago' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'condiciones_entrega' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tipo_contrato_id', 'tipos_contrato', 'id', 'RESTRICT', 'CASCADE', 'fk_contratos_tipos');
        $this->forge->addForeignKey('proveedor_id', 'proveedores', 'id', 'RESTRICT', 'CASCADE', 'fk_contratos_proveedores');
        $this->forge->addForeignKey('area_solicitante_id', 'areas', 'id', 'RESTRICT', 'CASCADE', 'fk_contratos_areas');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'RESTRICT', 'CASCADE', 'fk_contratos_usuarios');
        $this->forge->createTable('contratos', true);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->forge->dropTable('contratos', true);
    }
}