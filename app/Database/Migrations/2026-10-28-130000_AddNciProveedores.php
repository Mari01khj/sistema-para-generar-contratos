<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;


class AddNciProveedores extends Migration
{
    public function up()
    {
        $this->forge->addColumn('proveedores', [
            'nci' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'folio_ine',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('proveedores', 'nci');
    }
}
