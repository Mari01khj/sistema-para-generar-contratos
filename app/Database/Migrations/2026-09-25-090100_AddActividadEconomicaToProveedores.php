<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * "actividad_economica" es un dato del PROVEEDOR (no cambia contrato a contrato),
 * por eso es una columna fija aquí y no un campo dinámico en campos_formulario.
 * Se captura una vez al dar de alta al proveedor y de ahí se reutiliza siempre
 * que ese proveedor aparezca en un contrato.
 */
class AddActividadEconomicaToProveedores extends Migration
{
    public function up()
    {
        $this->forge->addColumn('proveedores', [
            'actividad_economica' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'domicilio_fiscal',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('proveedores', 'actividad_economica');
    }
}
