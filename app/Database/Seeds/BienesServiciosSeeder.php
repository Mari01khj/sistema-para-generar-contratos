<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BienesServiciosSeeder extends Seeder
{
    public function run()
    {
        $this->call(UnidadesMedidaSeeder::class);

        $idsPorUnidad = [];
        foreach ($this->db->table('unidades_medida')->get()->getResultArray() as $unidad) {
            $idsPorUnidad[$unidad['nombre']] = (int) $unidad['id'];
        }

        $rutaCsv = __DIR__ . '/data/bienes_servicios.csv';
        $archivo = fopen($rutaCsv, 'r');
        if ($archivo === false) {
            echo "No se encontró $rutaCsv\n";
            return;
        }

        $this->db->table('bienes_servicios')->truncate();

        $encabezados = fgetcsv($archivo); 
        $lote        = [];
        $ahora       = date('Y-m-d H:i:s');

        while (($fila = fgetcsv($archivo)) !== false) {
            [$partida, $nombre, $unidad, $precio, $categoria, $activo] = $fila;

            $lote[] = [
                'partida_presupuestal' => $partida !== '' ? $partida : null,
                'nombre'               => $nombre,
                'unidad_medida_id'     => $idsPorUnidad[$unidad] ?? null,
                'precio'               => $precio !== '' ? $precio : null,
                'categoria'            => $categoria !== '' ? $categoria : null,
                'activo'               => (int) $activo,
                'created_at'           => $ahora,
            ];

            // insertBatch() en lotes de 500: mandar las 3,039 de un jalón
            // en un solo INSERT es innecesariamente pesado para el motor.
            if (count($lote) >= 500) {
                $this->db->table('bienes_servicios')->insertBatch($lote);
                $lote = [];
            }
        }

        if (! empty($lote)) {
            $this->db->table('bienes_servicios')->insertBatch($lote);
        }

        fclose($archivo);
    }
}
