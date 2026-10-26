<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Catálogo maestro de bienes y servicios (lo que se puede comprar), para
 * elegirlo al capturar una partida de un contrato en vez de escribir todo
 * a mano cada vez. Una sola tabla para bienes Y servicios juntos (el
 * origen de los datos, un Excel con 35 hojas por partida presupuestal, no
 * separa limpio uno de otro: p.ej. la hoja "FUMIGACIÓN" es puro servicio
 * y "PAPELERÍA" puro bien, pero varias hojas mezclan ambos); "categoria"
 * guarda el nombre de esa hoja para poder filtrar.
 */
class CreateBienesServicios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            // clasificador presupuestal (ej. "5411"); varias filas del
            // catálogo real no lo traen, por eso es opcional.
            'partida_presupuestal' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'nombre' => [
                'type' => 'TEXT',
            ],
            'unidad_medida_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'precio' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            // el nombre de la hoja de origen en el Excel (ej. "PAPELERÍA",
            // "FUMIGACIÓN"), para poder filtrar por categoría.
            'categoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('categoria');
        $this->forge->addForeignKey('unidad_medida_id', 'unidades_medida', 'id', 'SET NULL', 'CASCADE', 'fk_bienesservicios_unidad');
        $this->forge->createTable('bienes_servicios', true);
    }

    public function down()
    {
        $this->forge->dropTable('bienes_servicios', true);
    }
}
