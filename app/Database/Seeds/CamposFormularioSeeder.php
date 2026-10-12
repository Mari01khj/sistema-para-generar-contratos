<?php

namespace App\Database\Seeds;

use App\Models\CamposFormularioModel;
use App\Models\TipoContrato;
use CodeIgniter\Database\Seeder;

/** este seeder solo ahorra la primera captura. */
class CamposFormularioSeeder extends Seeder
{
    public function run()
    {
        $tipoContratoModel  = new TipoContrato();
        $campoModel         = new CamposFormularioModel();

        $definiciones = [
            [
                'alias' => ['contrato abierto'],
                'campos' => [
                    ['etiqueta' => 'Folio INE',              'nombre_campo' => 'folio_ine',          'tipo_dato' => 'texto',  'obligatorio' => 1, 'orden' => 10],
                    ['etiqueta' => 'Monto mínimo',           'nombre_campo' => 'monto_minimo',       'tipo_dato' => 'numero', 'obligatorio' => 1, 'orden' => 20],
                    ['etiqueta' => 'Importe del 10%',        'nombre_campo' => 'diez_importe',       'tipo_dato' => 'numero', 'obligatorio' => 1, 'orden' => 30],
                    ['etiqueta' => 'Importe del 5%',         'nombre_campo' => 'cinco_importe',      'tipo_dato' => 'numero', 'obligatorio' => 1, 'orden' => 40],
                ],
            ],
            [
                'alias' => ['contrato con anticipo'],
                'campos' => [
                    ['etiqueta' => 'Cantidad del anticipo',  'nombre_campo' => 'cantidad_anticipo',  'tipo_dato' => 'numero', 'obligatorio' => 1, 'orden' => 10],
                    ['etiqueta' => 'Importe del 10%',        'nombre_campo' => 'diez_importe',       'tipo_dato' => 'numero', 'obligatorio' => 1, 'orden' => 20],
                ],
            ],
            [
                'alias' => ['contrato pedido'],
                'campos' => [
                    ['etiqueta' => 'Fecha de pago',          'nombre_campo' => 'fecha_pago',         'tipo_dato' => 'fecha',       'obligatorio' => 1, 'orden' => 10],
                    ['etiqueta' => 'Condiciones de entrega',  'nombre_campo' => 'condiciones_entrega', 'tipo_dato' => 'texto_largo', 'obligatorio' => 0, 'orden' => 20],
                ],
            ],
            [
                'alias' => ['contrato de 1 sola exhibicion', 'contrato de una sola exhibicion', 'contrato de 1 sola exhibición', 'contrato de una sola exhibición'],
                'campos' => [
                    ['etiqueta' => 'Importe del 10%',        'nombre_campo' => 'diez_importe',       'tipo_dato' => 'numero', 'obligatorio' => 1, 'orden' => 10],
                    ['etiqueta' => 'Importe del 5%',         'nombre_campo' => 'cinco_importe',      'tipo_dato' => 'numero', 'obligatorio' => 1, 'orden' => 20],
                ],
            ],
        ];

        $todosLosTipos = $tipoContratoModel->findAll();

        foreach ($definiciones as $definicion) 
        {
            $tipoContrato = $this->buscarTipoPorAlias($todosLosTipos, $definicion['alias']);

            if (! $tipoContrato) {
                echo '  [omitido] No encontré un tipo de contrato llamado "' . $definicion['alias'][0]
                    . '" (ni sus variantes). Revisa el nombre exacto en "Tipos de contrato" y, si es distinto, '
                    . 'agrégalo a la lista de alias en este seeder.' . PHP_EOL;
                continue;
            }

            foreach ($definicion['campos'] as $campo) 
            {
                if ($campoModel->existeNombreCampo($tipoContrato['id'], $campo['nombre_campo'])) {
                    echo '  [ya existe] ' . $tipoContrato['nombre'] . ' -> ' . $campo['nombre_campo'] . PHP_EOL;
                    continue;
                }

                $campoModel->insert([
                    'tipo_contrato_id' => $tipoContrato['id'],
                    'etiqueta'         => $campo['etiqueta'],
                    'nombre_campo'     => $campo['nombre_campo'],
                    'tipo_dato'        => $campo['tipo_dato'],
                    'origen_lista'     => null,
                    'obligatorio'      => $campo['obligatorio'],
                    'orden'            => $campo['orden'],
                    'activo'           => 1,
                ]);

                echo '  [creado] ' . $tipoContrato['nombre'] . ' -> ' . $campo['nombre_campo'] . PHP_EOL;
            }
        }
    }


    private function buscarTipoPorAlias(array $tipos, array $alias): ?array
    {
        foreach ($tipos as $tipo) {
            $nombreNormalizado = $this->normalizar($tipo['nombre']);

            foreach ($alias as $candidato) {
                if ($nombreNormalizado === $this->normalizar($candidato)) {
                    return $tipo;
                }
            }
        }

        return null;
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        ]);

        return $texto;
    }
}
