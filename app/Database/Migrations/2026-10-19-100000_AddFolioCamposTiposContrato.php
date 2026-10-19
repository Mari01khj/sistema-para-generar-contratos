<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * El folio real de un contrato (ej. TEM/CAS/FORTAMUN/CP-002/2026) usa dos
 * pedazos que son fijos por tipo de contrato:
 *
 *  - prefijo_fijo: la parte que nunca cambia para ese tipo (ej. "TEM/CAS"
 *    para Contrato Pedido, "MTM/CAYS" para los demás).
 *  - siglas_procedimiento: las siglas del procedimiento (CP, IRNP, LPNP...).
 *    El consecutivo del folio se cuenta por estas siglas (ver
 *    ContratosController::generarFolio()), no por tipo de contrato: dos
 *    tipos distintos pueden compartir las mismas siglas.
 */
class AddFolioCamposTiposContrato extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tipos_contrato', [
            'prefijo_fijo' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'nombre',
            ],
            'siglas_procedimiento' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'prefijo_fijo',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tipos_contrato', ['prefijo_fijo', 'siglas_procedimiento']);
    }
}
