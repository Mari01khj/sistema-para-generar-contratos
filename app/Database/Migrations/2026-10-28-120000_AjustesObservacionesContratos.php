<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AjustesObservacionesContratos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('proveedores', [
            'folio_ine' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'representante_legal',
            ],
        ]);

        $this->forge->addColumn('contratos', [
            'nombre_bien' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'descripcion_corta',
            ],
        ]);

        $this->forge->modifyColumn('contratos', [
            'fecha_entrega' => [
                'name' => 'fecha_entrega',
                'type' => 'TEXT',
                'null' => true,
            ],
            'lugar_entrega' => [
                'name' => 'lugar_entrega',
                'type' => 'TEXT',
                'null' => true,
            ],
            'plazo_pago' => [
                'name' => 'plazo_pago',
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('proveedores', 'folio_ine');
        $this->forge->dropColumn('contratos', 'nombre_bien');

        $this->forge->modifyColumn('contratos', [
            'fecha_entrega' => [
                'name'       => 'fecha_entrega',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'lugar_entrega' => [
                'name'       => 'lugar_entrega',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'plazo_pago' => [
                'name'       => 'plazo_pago',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ]);
    }
}
