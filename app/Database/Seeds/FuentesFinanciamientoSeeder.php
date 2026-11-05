<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FuentesFinanciamientoSeeder extends Seeder
{
    public function run()
    {
        $fuentes = [
            ['clave' => 'RP', 'nombre' => 'RECURSOS PROPIOS'],
            ['clave' => 'PF', 'nombre' => 'PARTICIPACIONES FEDERALES'],
            ['clave' => 'FORTAMUN', 'nombre' => 'FONDO DE APORTACIONES PARA EL FORTALECIMIENTO DE LOS MUNICIPIOS'],
            ['clave' => 'FAIS', 'nombre' => 'FONDO DE APORTACIONES PARA LA INFRAESTRUCTURA SOCIAL MUNICIPAL Y DE LAS DEMARCACIONES TERRITORIALES'],
        ];

        foreach ($fuentes as $fuente) {
            $existe = $this->db->table('fuentes_financiamiento')
                ->where('clave', $fuente['clave'])
                ->countAllResults();

            if ($existe === 0) {
                $this->db->table('fuentes_financiamiento')->insert([
                    'clave'      => $fuente['clave'],
                    'nombre'     => $fuente['nombre'],
                    'activo'     => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
