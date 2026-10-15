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
    /**Tasa de IVA usada para calcular el impuesto de todos los contratos.*/
    private const TASA_IVA = 0.16;

    /** campos fijos de la tabla contratos que no se pueden usar como campos dinámicos.*/
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

    /**
     * Listado de contratos que incluye los eliminados
     */
    public function index()
    {
        return view('contratos/index', [
            'contratos' => $this->contratoModel->listaConDetalle(),
        ]);
    }

    /** pantalla de solo lectura */
    public function ver(int $id)
    {
        $contrato = $this->contratoModel->unoConDetalle($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        return view('contratos/ver', [
            'contrato'   => $contrato,
            'valores'    => $this->valorModel->paraContrato($id),
            'partidas'   => $this->bienesModel->paraContrato($id),
        ]);
    }

    public function nuevo()
    {
        $tipos = $this->tipoContratoModel->where('activo', 1)->findAll();

        $tipoContratoId  = (int) ($this->request->getGet('tipo_contrato_id') ?? 0);
        $tipoContrato    = null;
        $camposDinamicos = [];
        $opcionesListas  = [];

        if ($tipoContratoId > 0) {
            $tipoContrato = $this->tipoContratoModel->find($tipoContratoId);

            // Si el tipo de contrato no existe o está inactivo, se ignora 
            if ($tipoContrato && $tipoContrato['activo']) {
                $camposDinamicos = $this->camposDinamicosDelTipo($tipoContratoId);
                $opcionesListas  = $this->opcionesListasPara($camposDinamicos);
            }
            else 
            {
                $tipoContrato = null;
            }
        }

        // Si venimos de un error de validación trae las filas que el usuario ya había escrito
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

    /**
     * para editar un contrato que ya existe se reutiliza el mismo partial pero con dos diferencias
     *  1) El tipo de contrato NO se puede cambiar aquí. 
     *  2) Los campos y las partidas vienen precargados con lo que ya había guardado 
     */
    public function editar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        $tipoContrato    = $this->tipoContratoModel->find($contrato['tipo_contrato_id']);
        $camposDinamicos = $this->camposDinamicosDelTipo($contrato['tipo_contrato_id']);
        $opcionesListas  = $this->opcionesListasPara($camposDinamicos);

        $valoresCampos = [];
        foreach ($this->valorModel->paraContrato($id) as $valorGuardado) {
            $valoresCampos[$valorGuardado['campo_id']] = $valorGuardado['valor'];
        }

      
        foreach ($camposDinamicos as $campo) {
            $valorViejo = old('campos.' . $campo['id']);

            if ($valorViejo !== null) 
            {
                $valoresCampos[$campo['id']] = $valorViejo;
            }
        }

        // lo mismo sucede para las partidas
        $filasBienesPrevias = $this->request->getOldInput('bienes')
            ?: $this->bienesModel->paraContrato($id);

        return view('contratos/editar', [
            'contrato'           => $contrato,
            'tipoContrato'       => $tipoContrato,
            'camposDinamicos'    => $camposDinamicos,
            'opcionesListas'     => $opcionesListas,
            'valoresCampos'      => $valoresCampos,
            'proveedores'        => $this->proveedoresModel->where('activo', 1)->findAll(),
            'areas'              => $this->areasModel->where('activo', 1)->findAll(),
            'filasBienesPrevias' => $filasBienesPrevias,
        ]);
    }

    public function create()
    {
        $tipoContratoId = (int) $this->request->getPost('tipo_contrato_id');
        $tipoContrato   = $this->tipoContratoModel->find($tipoContratoId);

        if (! $tipoContrato || ! $tipoContrato['activo']) {
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
            'estado'              => 'borrador',
        ];

        $camposDinamicos = $this->camposDinamicosDelTipo($tipoContratoId);

        $valoresEnviados = (array) ($this->request->getPost('campos') ?? []);
        [$valoresValidos, $erroresDinamicos] = $this->validarCamposDinamicos($camposDinamicos, $valoresEnviados);

        $filasBienes = (array) ($this->request->getPost('bienes') ?? []);
        [$partidasValidas, $erroresBienes] = $this->validarBienes($filasBienes);

        $errores = array_merge(
            $this->contratoModel->validate($datosFijos) ? [] : $this->contratoModel->errors(),
            $erroresDinamicos,
            $erroresBienes
        );

        if ($errores) {
            return redirect()->to(site_url('contratos/nuevo') . '?tipo_contrato_id=' . $tipoContratoId)
                ->withInput()
                ->with('error', 'Revisa los campos marcados.')
                ->with('errores_validacion', $errores);
        }

        // Los montos NUNCA se recalculan aquí, se calculan a partir de las partidas ya validadas
        $subtotal = 0.0;
        foreach ($partidasValidas as $partida) {
            $subtotal += $partida['importe'];
        }
        $subtotal = round($subtotal, 2);
        $ivaMonto = round($subtotal * self::TASA_IVA, 2);
        $total    = round($subtotal + $ivaMonto, 2);

        $datosFijos['subtotal'] = $subtotal;
        $datosFijos['iva']      = $ivaMonto;
        $datosFijos['total']    = $total;

        
        $datosFijos['folio'] = 'TMP-' . bin2hex(random_bytes(8));

        $db = db_connect();
        $db->transStart();

        $this->contratoModel->insert($datosFijos);
        $contratoId = $this->contratoModel->getInsertID();

        $folio = 'CT-' . date('Y') . '-' . str_pad((string) $contratoId, 5, '0', STR_PAD_LEFT);
        $this->contratoModel->update($contratoId, ['folio' => $folio]);

        if ($valoresValidos) {
            $filas = [];
            foreach ($valoresValidos as $campoId => $valor) {
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
        foreach ($partidasValidas as $partida) {
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

        if ($db->transStatus() === false) {
            return redirect()->to(site_url('contratos/nuevo') . '?tipo_contrato_id=' . $tipoContratoId)
                ->withInput()
                ->with('error', 'No se pudo guardar el contrato. Intenta de nuevo.');
        }

        return redirect()->to(site_url('contratos'))
            ->with('mensaje', 'Contrato ' . $folio . ' registrado correctamente.');
    }

    // metodo de actualizar
    public function actualizar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        $tipoContratoId = (int) $contrato['tipo_contrato_id'];

        $datosFijos = [
            'proveedor_id'        => $this->request->getPost('proveedor_id'),
            'area_solicitante_id' => $this->request->getPost('area_solicitante_id'),
            'descripcion_corta'   => $this->request->getPost('descripcion_corta'),
            'fecha_entrega'       => $this->request->getPost('fecha_entrega') ?: null,
            'lugar_entrega'       => $this->request->getPost('lugar_entrega'),
            'plazo_pago'          => $this->request->getPost('plazo_pago'),
        ];

        $datosParaValidar = $datosFijos + ['tipo_contrato_id' => $tipoContratoId];

        $camposDinamicos = $this->camposDinamicosDelTipo($tipoContratoId);
        $valoresEnviados = (array) ($this->request->getPost('campos') ?? []);
        [$valoresValidos, $erroresDinamicos] = $this->validarCamposDinamicos($camposDinamicos, $valoresEnviados);

        $filasBienes = (array) ($this->request->getPost('bienes') ?? []);
        [$partidasValidas, $erroresBienes] = $this->validarBienes($filasBienes);

        $errores = array_merge(
            $this->contratoModel->validate($datosParaValidar) ? [] : $this->contratoModel->errors(),
            $erroresDinamicos,
            $erroresBienes
        );

        if ($errores) 
        {
            return redirect()->to(site_url('contratos/' . $id . '/editar'))
                ->withInput()
                ->with('error', 'Revisa los campos marcados.')
                ->with('errores_validacion', $errores);
        }

        // los montos se recalculan siempre aquí
        $subtotal = 0.0;
        foreach ($partidasValidas as $partida) {
            $subtotal += $partida['importe'];
        }
        $subtotal = round($subtotal, 2);
        $ivaMonto = round($subtotal * self::TASA_IVA, 2);
        $total    = round($subtotal + $ivaMonto, 2);

        $datosFijos['subtotal'] = $subtotal;
        $datosFijos['iva']      = $ivaMonto;
        $datosFijos['total']    = $total;

        $db = db_connect();
        $db->transStart();

        $this->contratoModel->update($id, $datosFijos);

        // Se borran valores dinamicos y partidas para actualizarlas
        $this->valorModel->borrarDeContrato($id);
        if ($valoresValidos) 
        {
            $filas = [];
            foreach ($valoresValidos as $campoId => $valor) 
            {
                $filas[] = [
                    'contrato_id' => $id,
                    'campo_id'    => $campoId,
                    'valor'       => $valor,
                ];
            }
            
            $this->valorModel->insertBatch($filas);
        }

        $this->bienesModel->borrarDeContrato($id);
        $filasPartidas = [];
        $numPartida    = 1;
        foreach ($partidasValidas as $partida) 
        {
            $filasPartidas[] = [
                'contrato_id'     => $id,
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

        if ($db->transStatus() === false) {
            return redirect()->to(site_url('contratos/' . $id . '/editar'))
                ->withInput()
                ->with('error', 'No se pudo guardar el contrato. Intenta de nuevo.');
        }

        return redirect()->to(site_url('contratos/' . $id))
            ->with('mensaje', 'Contrato ' . $contrato['folio'] . ' actualizado correctamente.');
    }

    /**
     *no elimina por el soft delete
     */
    public function eliminar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o ya fue eliminado.');
        }

        $this->contratoModel->delete($id);

        return redirect()->to(site_url('contratos'))
            ->with('mensaje', 'Contrato ' . $contrato['folio'] . ' eliminado.');
    }

    /**
     * Valida cada valor dinámico según el tipo_dato de su campo.
     */
    private function validarCamposDinamicos(array $camposDinamicos, array $valoresEnviados): array
    {
        $valores = [];
        $errores = [];

        foreach ($camposDinamicos as $campo) {
            $valor = trim((string) ($valoresEnviados[$campo['id']] ?? ''));

            if ($valor === '') {
                if ($campo['obligatorio']) {
                    $errores['campos_' . $campo['id']] = 'El campo "' . $campo['etiqueta'] . '" es obligatorio.';
                }
                continue; // opcional y vacío: no se guarda fila para este campo
            }

            $valido = match ($campo['tipo_dato']) {
                'fecha'  => (bool) \DateTime::createFromFormat('Y-m-d', $valor),
                'numero' => is_numeric($valor),
                'lista'  => $this->opcionValidaEnCatalogo($campo['origen_lista'], $valor),
                default  => true,
            };

            if (! $valido) {
                $errores['campos_' . $campo['id']] = 'El campo "' . $campo['etiqueta'] . '" no tiene un valor válido.';
                continue;
            }

            $valores[$campo['id']] = $valor;
        }

        return [$valores, $errores];
    }

    /** las partidas tienen que estar bien llendas */
    private function validarBienes(array $filas): array
    {
        $validas = [];
        $errores = [];

        foreach ($filas as $i => $fila) {
            $descripcion   = trim((string) ($fila['descripcion'] ?? ''));
            $unidadMedida  = trim((string) ($fila['unidad_medida'] ?? ''));
            $marca         = trim((string) ($fila['marca'] ?? ''));
            $cantidad      = trim((string) ($fila['cantidad'] ?? ''));
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

        if (! $validas && ! $errores) {
            $errores['bienes_general'] = 'Agrega al menos una partida (bien o servicio) al contrato.';
        }

        return [$validas, $errores];
    }

    private function camposDinamicosDelTipo(int $tipoContratoId): array
    {
        return array_values(array_filter(
            $this->campoModel->paraTipoContrato($tipoContratoId),
            fn ($campo) => ! in_array($campo['nombre_campo'], self::CAMPOS_RESERVADOS, true)
        ));
    }

    private function opcionesListasPara(array $camposDinamicos): array
    {
        $opcionesListas = [];

        foreach ($camposDinamicos as $campo) {
            $origen = $campo['origen_lista'];
            if ($campo['tipo_dato'] === 'lista' && $origen && ! isset($opcionesListas[$origen])) {
                $opcionesListas[$origen] = $this->opcionesDeCatalogo($origen);
            }
        }

        return $opcionesListas;
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
        if (! $origen || ! ctype_digit($valorId)) {
            return false;
        }

        return match ($origen) {
            'proveedores' => (bool) $this->proveedoresModel->where('activo', 1)->find((int) $valorId),
            'areas'       => (bool) $this->areasModel->where('activo', 1)->find((int) $valorId),
            default       => false,
        };
    }
}
