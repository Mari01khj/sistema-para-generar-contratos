<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FuentesFinanciamientoSeeder extends Seeder
{
    public function run()
    {
        $fuentes = [
            ['clave' => 'RP', 'nombre' => 'Recursos Propios'],
            ['clave' => 'PF', 'nombre' => 'Participaciones Federales'],
            ['clave' => 'FORTAMUN', 'nombre' => 'Fondo de Aportaciones para el Fortalecimiento de los Municipios'],
            ['clave' => 'FAIS', 'nombre' => 'Fondo de Aportaciones para la Infraestructura Social Municipal y de las Demarcaciones Territoriales'],
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
