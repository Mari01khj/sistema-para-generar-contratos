<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * La descripción corta (para tablas/listados) y la larga (cláusula del objeto
 * del contrato) las tienen TODOS los tipos de contrato, así que van como
 * columnas fijas en "contratos" y no como campos dinámicos.
 */
class AddDescripcionesToContratos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('contratos', [
            'descripcion_corta' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'folio',
            ],
            'descripcion_larga' => [
                'type'    => 'TEXT',
                'null'    => true,
                'after'   => 'descripcion_corta',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('contratos', ['descripcion_corta', 'descripcion_larga']);
    }
}
