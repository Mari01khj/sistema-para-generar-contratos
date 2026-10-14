<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToContratos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('contratos', [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'total',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('contratos', 'deleted_at');
    }
}
