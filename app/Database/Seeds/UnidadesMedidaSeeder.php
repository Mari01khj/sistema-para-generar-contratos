<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;


class UnidadesMedidaSeeder extends Seeder
{
    public function run()
    {
        $unidades = [
            'BOLSA', 'CAJA', 'EXHIBIDOR', 'HORA', 'JUEGO', 'KILOGRAMO',
            'KIT', 'LITRO', 'METRO', 'METRO CUADRADO', 'MILLAR', 'PAQUETE',
            'PAR', 'PIEZA', 'PÓLIZA DE SEGUROS', 'ROLLO', 'SERVICIO',
            'TONELADA', 'UNIDAD DE SERVICIO',
        ];

        foreach ($unidades as $nombre) {
            $existe = $this->db->table('unidades_medida')
                ->where('nombre', $nombre)
                ->countAllResults();

            if ($existe === 0) {
                $this->db->table('unidades_medida')->insert([
                    'nombre'     => $nombre,
                    'activo'     => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
