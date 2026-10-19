<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFuenteFinanciamientoIdContratos extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->addColumn('contratos', [
            'fuente_financiamiento_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'tipo_contrato_id',
            ],
        ]);

        $this->forge->addForeignKey(
            'fuente_financiamiento_id',
            'fuentes_financiamiento',
            'id',
            'RESTRICT',
            'CASCADE',
            'fk_contratos_fuentes_financiamiento'
        );
        $this->forge->processIndexes('contratos');

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropForeignKey('contratos', 'fk_contratos_fuentes_financiamiento');
        $this->forge->dropColumn('contratos', 'fuente_financiamiento_id');
        $this->db->enableForeignKeyChecks();
    }
}
