<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Para marcar con una "X" en el PDF si el proveedor es persona física o
 * persona moral (el machote ya trae los dos renglones, solo faltaba de
 * dónde sacar el dato).
 */
class AddTipoPersonaProveedores extends Migration
{
    public function up()
    {
        $this->forge->addColumn('proveedores', [
            'tipo_persona' => [
                'type'       => 'ENUM',
                'constraint' => ['fisica', 'moral'],
                'null'       => true,
                'after'      => 'razon_social',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('proveedores', 'tipo_persona');
    }
}
