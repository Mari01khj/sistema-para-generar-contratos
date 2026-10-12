<?php

namespace App\Controllers;

use App\Models\AreasModel;
use App\Models\BienesModel;
use App\Models\CamposFormularioModel;
use App\Models\ContratosModel;
use App\Models\ContratoValoresModel;
use App\Models\ProveedoresModel;
use App\Models\TipoContrato;

class ContratosController extends BaseController
{
     /** Tasa de IVA usada para el impuesto de todos los contratos.*/
    private const TASA_IVA = 0.16;

    /** columnas reservadas que no se pueden usar como campos dinámicos*/
    private const CAMPOS_RESERVADOS = [
        'folio',
        'tipo_contrato_id',
        'proveedor_id',
        'area_solicitante_id',
        'descripcion_corta',
        'fecha_entrega',
        'lugar_entrega',
        'plazo_pago',
        'estado',
        'usuario_id',
        'subtotal',
        'iva',
        'total',
    ];

    protected $contratoModel;
    protected $valorModel;
    protected $tipoContratoModel;
    protected $campoModel;
    protected $proveedoresModel;
    protected $areasModel;
    protected $bienesModel;

    public function __construct()
    {
        $this->contratoModel     = new ContratosModel();
        $this->valorModel        = new ContratoValoresModel();
        $this->tipoContratoModel = new TipoContrato();
        $this->campoModel        = new CamposFormularioModel();
        $this->proveedoresModel  = new ProveedoresModel();
        $this->areasModel        = new AreasModel();
        $this->bienesModel       = new BienesModel();
    }

    public function index()
    {
        return view('contratos/index', [
            'contratos' => $this->contratoModel->listaConDetalle(),
        ]);
    }

    /**que tipo de contrato se va a crear, para mostrar los campos dinámicos correctos. */
    public function nuevo()
    {
        $tipos = $this->tipoContratoModel->where('activo', 1)->findAll();

        $tipoContratoId  = (int) ($this->request->getGet('tipo_contrato_id') ?? 0);
        $tipoContrato    = null;
        $camposDinamicos = [];
        $opcionesListas  = [];

        if ($tipoContratoId > 0) 
        {
            $tipoContrato = $this->tipoContratoModel->find($tipoContratoId);

            if ($tipoContrato && $tipoContrato['activo']) 
            {
                $camposDinamicos = array_values(array_filter(
                    $this->campoModel->paraTipoContrato($tipoContratoId),
                    fn ($campo) => ! in_array($campo['nombre_campo'], self::CAMPOS_RESERVADOS, true)
                ));

                // si ocupan listas, traer las opciones de cada una para pasarlas a la vista
                foreach ($camposDinamicos as $campo) 
                {
                    $origen = $campo['origen_lista'];
                    if ($campo['tipo_dato'] === 'lista' && $origen && ! isset($opcionesListas[$origen])) 
                    {
                        $opcionesListas[$origen] = $this->opcionesDeCatalogo($origen);
                    }
                }
            } 
            else 
            {
                $tipoContrato = null;
            }
        }

        // Si venimos de un error de validación, trae las filas que el usuario ya había llenado

        $filasBienesPrevias = $this->request->getOldInput('bienes') ?: [[]];

        return view('contratos/crear', [
            'tipos'               => $tipos,
            'tipoContrato'        => $tipoContrato,
            'camposDinamicos'     => $camposDinamicos,
            'opcionesListas'      => $opcionesListas,
            'proveedores'         => $this->proveedoresModel->where('activo', 1)->findAll(),
            'areas'               => $this->areasModel->where('activo', 1)->findAll(),
            'filasBienesPrevias'  => $filasBienesPrevias,
        ]);
    }

    public function create()
    {
        $tipoContratoId = (int) $this->request->getPost('tipo_contrato_id');
        $tipoContrato   = $this->tipoContratoModel->find($tipoContratoId);

        if (! $tipoContrato || ! $tipoContrato['activo']) 
        {
            return redirect()->to(site_url('contratos/nuevo'))
                ->with('error', 'Selecciona un tipo de contrato válido.');
        }

        $datosFijos = [
            'tipo_contrato_id'    => $tipoContratoId,
            'proveedor_id'        => $this->request->getPost('proveedor_id'),
            'area_solicitante_id' => $this->request->getPost('area_solicitante_id'),
            'descripcion_corta'   => $this->request->getPost('descripcion_corta'),
            'fecha_entrega'       => $this->request->getPost('fecha_entrega') ?: null,
            'lugar_entrega'       => $this->request->getPost('lugar_entrega'),
            'plazo_pago'          => $this->request->getPost('plazo_pago'),
            'usuario_id'          => session()->get('usuario_id'),
            'estado'              => 'activo',
        ];

        $camposDinamicos = array_values(array_filter(
            $this->campoModel->paraTipoContrato($tipoContratoId),
            fn ($campo) => ! in_array($campo['nombre_campo'], self::CAMPOS_RESERVADOS, true)
        ));

        $valoresEnviados = (array) ($this->request->getPost('campos') ?? []);
        [$valoresValidos, $erroresDinamicos] = $this->validarCamposDinamicos($camposDinamicos, $valoresEnviados);

        $filasBienes = (array) ($this->request->getPost('bienes') ?? []);
        [$partidasValidas, $erroresBienes] = $this->validarBienes($filasBienes);

        $errores = array_merge(
            $this->contratoModel->validate($datosFijos) ? [] : $this->contratoModel->errors(),
            $erroresDinamicos,
            $erroresBienes
        );

        if ($errores) 
        {
            return redirect()->to(site_url('contratos/nuevo') . '?tipo_contrato_id=' . $tipoContratoId)
                ->withInput()
                ->with('error', 'Revisa los campos marcados.')
                ->with('errores_validacion', $errores);
        }

        // Los montos se recalculan aquí, a partir de las partidas ya validadas, 
        // para que nadie pueda alterar un total.
    
        $subtotal = 0.0;
        foreach ($partidasValidas as $partida) 
        {
            $subtotal += $partida['importe'];
        }
        $subtotal = round($subtotal, 2);
        $ivaMonto = round($subtotal * self::TASA_IVA, 2);
        $total    = round($subtotal + $ivaMonto, 2);

        $datosFijos['subtotal'] = $subtotal;
        $datosFijos['iva']      = $ivaMonto;
        $datosFijos['total']    = $total;

        // El folio real se construye con el ID autoincremental, así que no se puede conocer ANTES de insertar la fila. 
        // Se usa un marcador temporal único

        $datosFijos['folio'] = 'TMP-' . bin2hex(random_bytes(8));

        // se tienen que llenar las 3 tablas 
        $db = db_connect();
        $db->transStart();

        $this->contratoModel->insert($datosFijos);
        $contratoId = $this->contratoModel->getInsertID();

        $folio = 'CT-' . date('Y') . '-' . str_pad((string) $contratoId, 5, '0', STR_PAD_LEFT);
        $this->contratoModel->update($contratoId, ['folio' => $folio]);

        if ($valoresValidos) 
        {
            $filas = [];
            foreach ($valoresValidos as $campoId => $valor) 
            {
                $filas[] = [
                    'contrato_id' => $contratoId,
                    'campo_id'    => $campoId,
                    'valor'       => $valor,
                ];
            }
            $this->valorModel->insertBatch($filas);
        }

        $filasPartidas = [];
        $numPartida    = 1;
        foreach ($partidasValidas as $partida) 
        {
            $filasPartidas[] = [
                'contrato_id'     => $contratoId,
                'num_partida'     => $numPartida++,
                'descripcion'     => $partida['descripcion'],
                'unidad_medida'   => $partida['unidad_medida'],
                'marca'           => $partida['marca'],
                'cantidad'        => $partida['cantidad'],
                'precio_unitario' => $partida['precio_unitario'],
                'importe'         => $partida['importe'],
            ];
        }

        $this->bienesModel->insertBatch($filasPartidas);
        $db->transComplete();

        if ($db->transStatus() === false) 
        {
            return redirect()->to(site_url('contratos/nuevo') . '?tipo_contrato_id=' . $tipoContratoId)
                ->withInput()
                ->with('error', 'No se pudo guardar el contrato. Intenta de nuevo.');
        }

        return redirect()->to(site_url('contratos'))
            ->with('mensaje', 'Contrato ' . $folio . ' registrado correctamente.');
    }

    /** Valida cada valor dinámico según el tipo_dato de su campo */
    private function validarCamposDinamicos(array $camposDinamicos, array $valoresEnviados): array
    {
        $valores = [];
        $errores = [];

        foreach ($camposDinamicos as $campo) 
        {
            $valor = trim((string) ($valoresEnviados[$campo['id']] ?? ''));

            if ($valor === '') 
            {
                if ($campo['obligatorio']) 
                {
                    $errores['campos_' . $campo['id']] = 'El campo "' . $campo['etiqueta'] . '" es obligatorio.';
                }
                continue; 
            }

            $valido = match ($campo['tipo_dato']) 
            {
                'fecha'  => (bool) \DateTime::createFromFormat('Y-m-d', $valor),
                'numero' => is_numeric($valor),
                'lista'  => $this->opcionValidaEnCatalogo($campo['origen_lista'], $valor),
                default  => true,
            };

            if (! $valido) 
            {
                $errores['campos_' . $campo['id']] = 'El campo "' . $campo['etiqueta'] . '" no tiene un valor válido.';
                continue;
            }

            $valores[$campo['id']] = $valor;
        }

        return [$valores, $errores];
    }

    /**
     * Valida las filas de la tabla de bienes, si tiene algo, todos sus campos obligatorios deben venir correctos.
     */
    private function validarBienes(array $filas): array
    {
        $validas = [];
        $errores = [];

        foreach ($filas as $i => $fila) {
            $descripcion    = trim((string) ($fila['descripcion'] ?? ''));
            $unidadMedida   = trim((string) ($fila['unidad_medida'] ?? ''));
            $marca          = trim((string) ($fila['marca'] ?? ''));
            $cantidad       = trim((string) ($fila['cantidad'] ?? ''));
            $precioUnitario = trim((string) ($fila['precio_unitario'] ?? ''));

            // Fila completamente vacía: se ignora, no es un error.
            if ($descripcion === '' && $unidadMedida === '' && $cantidad === '' && $precioUnitario === '') {
                continue;
            }

            $filaValida = true;

            if ($descripcion === '') {
                $errores['bienes_' . $i . '_descripcion'] = 'Partida ' . ($i + 1) . ': la descripción es obligatoria.';
                $filaValida = false;
            }

            if ($unidadMedida === '') {
                $errores['bienes_' . $i . '_unidad_medida'] = 'Partida ' . ($i + 1) . ': la unidad de medida es obligatoria.';
                $filaValida = false;
            }

            if ($cantidad === '' || ! is_numeric($cantidad) || (float) $cantidad <= 0) {
                $errores['bienes_' . $i . '_cantidad'] = 'Partida ' . ($i + 1) . ': la cantidad debe ser un número mayor a cero.';
                $filaValida = false;
            }

            if ($precioUnitario === '' || ! is_numeric($precioUnitario) || (float) $precioUnitario < 0) {
                $errores['bienes_' . $i . '_precio_unitario'] = 'Partida ' . ($i + 1) . ': el precio unitario debe ser un número válido.';
                $filaValida = false;
            }

            if (! $filaValida) {
                continue;
            }

            $validas[] = [
                'descripcion'     => $descripcion,
                'unidad_medida'   => $unidadMedida,
                'marca'           => $marca !== '' ? $marca : null,
                'cantidad'        => (float) $cantidad,
                'precio_unitario' => (float) $precioUnitario,
                'importe'         => round((float) $cantidad * (float) $precioUnitario, 2),
            ];
        }

        if (! $validas && ! $errores) 
        {
            $errores['bienes_general'] = 'Agrega al menos una partida al contrato.';
        }

        return [$validas, $errores];
    }

    /** Para cuando se agregue algún catálogo nuevo de donde salgan listas. */
    private function opcionesDeCatalogo(string $origen): array
    {
        return match ($origen) {
            'proveedores' => $this->proveedoresModel->where('activo', 1)->findAll(),
            'areas'       => $this->areasModel->where('activo', 1)->findAll(),
            default       => [],
        };
    }

    private function opcionValidaEnCatalogo(?string $origen, string $valorId): bool
    {
        if (! $origen || ! ctype_digit($valorId)) 
        {
            return false;
        }

        return match ($origen) {
            'proveedores' => (bool) $this->proveedoresModel->where('activo', 1)->find((int) $valorId),
            'areas'       => (bool) $this->areasModel->where('activo', 1)->find((int) $valorId),
            default       => false,
        };
    }
}
