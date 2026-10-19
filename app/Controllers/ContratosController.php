<?php

namespace App\Controllers;

use App\Models\AreasModel;
use App\Models\BienesModel;
use App\Models\CamposFormularioModel;
use App\Models\ContratosModel;
use App\Models\ContratoValoresModel;
use App\Models\FuentesFinanciamientoModel;
use App\Models\ProveedoresModel;
use App\Models\TipoContrato;
use App\Libraries\GeneradorPdfContrato;
use RuntimeException;

class ContratosController extends BaseController
{
    /**
     * Tasa de IVA usada para calcular el impuesto de todos los contratos.
     * Vive aquí, en un solo lugar, para que el día que cambie no haya que
     * buscarla en varios archivos.
     */
    private const TASA_IVA = 0.16;

    /**
     * Nombres que NUNCA deben tratarse como "campo dinámico" aunque algún
     * día alguien los capture por accidente en campos_formulario: ya son
     * columnas fijas de "contratos" o pertenecen a la tabla de partidas.
     */
    private const CAMPOS_RESERVADOS = [
        'folio',
        'tipo_contrato_id',
        'fuente_financiamiento_id',
        'proveedor_id',
        'area_solicitante_id',
        'descripcion_corta',
        'fecha_entrega',
        'lugar_entrega',
        'plazo_pago',
        'condiciones_entrega',
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
    protected $fuenteModel;

    public function __construct()
    {
        $this->contratoModel     = new ContratosModel();
        $this->valorModel        = new ContratoValoresModel();
        $this->tipoContratoModel = new TipoContrato();
        $this->campoModel        = new CamposFormularioModel();
        $this->proveedoresModel  = new ProveedoresModel();
        $this->areasModel        = new AreasModel();
        $this->bienesModel       = new BienesModel();
        $this->fuenteModel       = new FuentesFinanciamientoModel();
    }

    /**
     * Listado de contratos (contratos/index). Gracias a useSoftDeletes=true
     * en ContratosModel, listaConDetalle() ya excluye automáticamente los
     * contratos "eliminados" (los que tienen deleted_at con fecha): no hay
     * que filtrarlos aquí a mano.
     */
    public function index()
    {
        return view('contratos/index', [
            'contratos' => $this->contratoModel->listaConDetalle(),
        ]);
    }

    /**
     * Paso 4: pantalla de SOLO LECTURA de un contrato ya guardado. A
     * diferencia de nuevo()/editar(), aquí no se arma ningún formulario:
     * solo se juntan los 3 pedazos en los que vive un contrato (sus datos
     * fijos + nombre de tipo/proveedor/área, sus valores dinámicos con
     * etiqueta ya resuelta, y sus partidas) y se mandan tal cual a la
     * vista para mostrarlos.
     */
    public function ver(int $id)
    {
        // unoConDetalle() ya hace el JOIN con tipos_contrato/proveedores/
        // areas, así que la vista puede mostrar nombres en vez de solo ids.
        $contrato = $this->contratoModel->unoConDetalle($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        return view('contratos/ver', [
            'contrato'   => $contrato,
            // paraContrato() en ambos modelos ya viene ordenado y, en el
            // caso de los valores dinámicos, con la etiqueta del campo
            // incluida (join con campos_formulario), así que la vista no
            // necesita volver a consultar nada para mostrar "Fecha de pago:
            // 2026-11-15" en vez de solo el id del campo y su valor crudo.
            'valores'    => $this->valorModel->paraContrato($id),
            'partidas'   => $this->bienesModel->paraContrato($id),
        ]);
    }

    /**
     * Paso 6: genera el PDF del contrato a partir del machote de Word del
     * tipo de contrato, con los placeholders ya sustituidos. Toda la
     * lógica de "cómo" se arma el PDF vive en GeneradorPdfContrato (ver
     * app/Libraries/GeneradorPdfContrato.php); aquí solo se juntan los
     * datos que esa clase necesita (el contrato con join, el proveedor y
     * el área completos —no solo sus nombres—, los valores dinámicos y las
     * partidas) y se manda el archivo resultante al navegador.
     */
    public function exportarPdf(int $id)
    {
        $contrato = $this->contratoModel->unoConDetalle($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        $tipoContrato = $this->tipoContratoModel->find($contrato['tipo_contrato_id']);
        $proveedor    = $this->proveedoresModel->find($contrato['proveedor_id']);
        $area         = $this->areasModel->find($contrato['area_solicitante_id']);

        if (empty($tipoContrato['plantilla'])) {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Este tipo de contrato no tiene una plantilla de Word asignada. '
                    . 'Sube una en Tipos de contrato → Gestionar plantilla antes de exportar.');
        }

        try {
            $rutaPdf = (new GeneradorPdfContrato())->generar(
                contrato: $contrato,
                proveedor: $proveedor,
                area: $area,
                valoresDinamicos: $this->valorModel->paraContrato($id),
                partidas: $this->bienesModel->paraContrato($id),
                // WRITEPATH ya trae su propia "/" al final (ver app/Config/Paths.php),
                // por eso aquí no se le pone otra antes de concatenar 'plantilla'.
                rutaPlantilla: WRITEPATH . $tipoContrato['plantilla']
            );
        } catch (RuntimeException $e) {
            // Los errores "esperables" (falta la plantilla, no es .docx, no
            // tiene la tabla de partidas) ya vienen con un mensaje pensado
            // para que el usuario entienda qué corregir, así que se le
            // muestra tal cual en vez de un error genérico.
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', $e->getMessage());
        }

        // download() manda el archivo con los encabezados HTTP correctos
        // para que el navegador lo baje (Content-Type, Content-Disposition
        // con el nombre de archivo, etc.). El "true" al final le dice que
        // el segundo parámetro NO es el contenido del archivo sino que lo
        // lea del disco, en la ruta que se le pasó como primer parámetro.
        $nombreDescarga = $contrato['folio'] . '.pdf';

        return $this->response->download($rutaPdf, null)
            ->setFileName($nombreDescarga)
            ->setContentType('application/pdf');
    }

    /**
     * Sin "?tipo_contrato_id=": solo muestra el selector de tipo.
     * Con "?tipo_contrato_id=X" válido: muestra el formulario completo,
     * con los campos fijos, los dinámicos de este tipo y la tabla de
     * partidas (bienes).
     */
    public function nuevo()
    {
        $tipos = $this->tipoContratoModel->where('activo', 1)->findAll();

        $tipoContratoId  = (int) ($this->request->getGet('tipo_contrato_id') ?? 0);
        $tipoContrato    = null;
        $camposDinamicos = [];
        $opcionesListas  = [];

        if ($tipoContratoId > 0) {
            $tipoContrato = $this->tipoContratoModel->find($tipoContratoId);

            // Un tipo de contrato desactivado no debe poder usarse para
            // contratos NUEVOS (pero los contratos viejos que ya lo usan
            // siguen pudiendo editarse: ver editar() más abajo, que no
            // tiene esta restricción a propósito).
            if ($tipoContrato && $tipoContrato['activo']) {
                $camposDinamicos = $this->camposDinamicosDelTipo($tipoContratoId);
                $opcionesListas  = $this->opcionesListasPara($camposDinamicos);
            } else {
                $tipoContrato = null;
            }
        }

        // Si venimos de un error de validación, old('bienes') trae las
        // filas que el usuario ya había llenado; si no, arrancamos con
        // una fila vacía para que la tabla no se vea vacía al abrir.
        $filasBienesPrevias = $this->request->getOldInput('bienes') ?: [[]];

        return view('contratos/crear', [
            'tipos'               => $tipos,
            'tipoContrato'        => $tipoContrato,
            'camposDinamicos'     => $camposDinamicos,
            'opcionesListas'      => $opcionesListas,
            'proveedores'         => $this->proveedoresModel->where('activo', 1)->findAll(),
            'areas'               => $this->areasModel->where('activo', 1)->findAll(),
            'fuentes'             => $this->fuenteModel->where('activo', 1)->findAll(),
            'filasBienesPrevias'  => $filasBienesPrevias,
        ]);
    }

    /**
     * Paso 4: formulario para editar un contrato que ya existe. Reutiliza
     * el mismo partial de formulario que nuevo() (ver
     * app/Views/contratos/_formulario.php), pero con dos diferencias
     * importantes:
     *
     *  1) El tipo de contrato NO se puede cambiar aquí. Cambiar de tipo
     *     cambiaría qué campos dinámicos aplican, y los valores ya
     *     guardados quedarían huérfanos (le pertenecen a los campos del
     *     tipo ANTERIOR). Si de verdad se capturó mal el tipo, lo más
     *     seguro es eliminar este contrato y crear uno nuevo del tipo
     *     correcto, no "convertirlo".
     *  2) Los campos y las partidas vienen precargados con lo que ya
     *     había guardado (o con lo último que el usuario escribió, si
     *     venimos de un error de validación: old() siempre gana sobre lo
     *     que ya había en la base de datos).
     */
    public function editar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        $tipoContrato    = $this->tipoContratoModel->find($contrato['tipo_contrato_id']);
        $fuente          = $contrato['fuente_financiamiento_id'] ? $this->fuenteModel->find($contrato['fuente_financiamiento_id']) : null;
        $camposDinamicos = $this->camposDinamicosDelTipo($contrato['tipo_contrato_id']);
        $opcionesListas  = $this->opcionesListasPara($camposDinamicos);

        // Los valores dinámicos ya guardados se acomodan como
        // [campo_id => valor] para que la vista pueda hacer
        // $valoresCampos[$campo['id']] ?? '' al dibujar cada input.
        $valoresCampos = [];
        foreach ($this->valorModel->paraContrato($id) as $valorGuardado) {
            $valoresCampos[$valorGuardado['campo_id']] = $valorGuardado['valor'];
        }

        // old('campos.X') gana si venimos de un error de validación (el
        // usuario ya había escrito algo distinto); si no, se usa lo que
        // ya estaba guardado.
        foreach ($camposDinamicos as $campo) {
            $valorViejo = old('campos.' . $campo['id']);
            if ($valorViejo !== null) {
                $valoresCampos[$campo['id']] = $valorViejo;
            }
        }

        // Mismo criterio para las partidas: old('bienes') si venimos de
        // un error, si no, las partidas que ya tenía el contrato.
        $filasBienesPrevias = $this->request->getOldInput('bienes')
            ?: $this->bienesModel->paraContrato($id);

        return view('contratos/editar', [
            'contrato'           => $contrato,
            'tipoContrato'       => $tipoContrato,
            'fuente'             => $fuente,
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

        $fuenteFinanciamientoId = (int) $this->request->getPost('fuente_financiamiento_id');
        $fuenteFinanciamiento   = $this->fuenteModel->find($fuenteFinanciamientoId);

        if (! $fuenteFinanciamiento || ! $fuenteFinanciamiento['activo']) {
            return redirect()->to(site_url('contratos/nuevo') . '?tipo_contrato_id=' . $tipoContratoId)
                ->withInput()
                ->with('error', 'Selecciona una fuente de financiamiento válida.');
        }

        $datosFijos = [
            'tipo_contrato_id'         => $tipoContratoId,
            'fuente_financiamiento_id' => $fuenteFinanciamientoId,
            'proveedor_id'             => $this->request->getPost('proveedor_id'),
            'area_solicitante_id'      => $this->request->getPost('area_solicitante_id'),
            'descripcion_corta'        => $this->request->getPost('descripcion_corta'),
            'fecha_entrega'            => $this->request->getPost('fecha_entrega') ?: null,
            'lugar_entrega'            => $this->request->getPost('lugar_entrega'),
            'plazo_pago'               => $this->request->getPost('plazo_pago'),
            'condiciones_entrega'      => $this->request->getPost('condiciones_entrega'),
            'usuario_id'               => session()->get('usuario_id'),
            'estado'                   => 'borrador',
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

        // Los montos NUNCA se toman de lo que mandó el navegador: se
        // recalculan aquí, a partir de las partidas ya validadas, para
        // que nadie pueda alterar un total con las herramientas del
        // navegador o con un formulario armado a mano.
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

        // El folio real (CT-2026-00001) se construye con el ID autoincremental,
        // así que no se puede conocer ANTES de insertar la fila. Pero la
        // columna "folio" es NOT NULL + UNIQUE, así que el primer INSERT
        // necesita algún valor. Se usa un marcador temporal único (nunca
        // puede chocar con un folio real, porque un folio real nunca
        // empieza con "TMP-") y, ya con el ID en la mano, se actualiza al
        // folio definitivo un renglón más abajo.
        $datosFijos['folio'] = 'TMP-' . bin2hex(random_bytes(8));

        // Un solo contrato implica escribir en 3 tablas (contratos,
        // contrato_valores y bienes_detalles). La transacción asegura que
        // o se guardan las tres cosas, o no se guarda ninguna: nunca nos
        // quedamos con un contrato "a medias".
        $db = db_connect();
        $db->transStart();

        $this->contratoModel->insert($datosFijos);
        $contratoId = $this->contratoModel->getInsertID();

        // Ya con la fila insertada (y por lo tanto contada), se arma el
        // folio real y se reemplaza el TMP- de arriba.
        $folio = $this->generarFolio($tipoContrato, $fuenteFinanciamiento);
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

    /**
     * Paso 4: guarda los cambios de editar(). La validación es
     * EXACTAMENTE la misma que create() (mismos métodos privados,
     * mismas reglas) porque un contrato editado tiene que cumplir las
     * mismas condiciones que uno nuevo; lo único que cambia es que aquí
     * ya existe una fila que hay que ACTUALIZAR en vez de crear, y hay
     * que reemplazar sus valores dinámicos y sus partidas en vez de
     * insertarlos por primera vez.
     */
    public function actualizar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        // El tipo de contrato se toma del registro existente, NUNCA de lo
        // que venga en el POST: así, aunque alguien manipule el formulario
        // a mano, es imposible "cambiar" el tipo de un contrato por esta
        // vía (ver el porqué en el comentario de editar()).
        $tipoContratoId = (int) $contrato['tipo_contrato_id'];

        $datosFijos = [
            'proveedor_id'        => $this->request->getPost('proveedor_id'),
            'area_solicitante_id' => $this->request->getPost('area_solicitante_id'),
            'descripcion_corta'   => $this->request->getPost('descripcion_corta'),
            'fecha_entrega'       => $this->request->getPost('fecha_entrega') ?: null,
            'lugar_entrega'       => $this->request->getPost('lugar_entrega'),
            'plazo_pago'          => $this->request->getPost('plazo_pago'),
            'condiciones_entrega' => $this->request->getPost('condiciones_entrega'),
        ];

        // validate() de CodeIgniter corre las $validationRules del modelo
        // sobre el array que se le pase, sin necesidad de insertar ni
        // actualizar nada todavía: sirve igual para validar un INSERT que
        // un UPDATE. Como 'tipo_contrato_id' es requerido por esas reglas
        // pero aquí no viene en $datosFijos (no se edita), se agrega solo
        // para la validación, sin que vaya a sobrescribirse de verdad.
        $datosParaValidar = $datosFijos + [
            'tipo_contrato_id'         => $tipoContratoId,
            'fuente_financiamiento_id' => $contrato['fuente_financiamiento_id'],
        ];

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

        if ($errores) {
            return redirect()->to(site_url('contratos/' . $id . '/editar'))
                ->withInput()
                ->with('error', 'Revisa los campos marcados.')
                ->with('errores_validacion', $errores);
        }

        // Igual que en create(): los montos se recalculan siempre aquí,
        // nunca se confía en nada que haya mandado el navegador.
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

        // Se borran TODOS los valores dinámicos y TODAS las partidas de
        // este contrato y se vuelven a insertar los que vengan del
        // formulario. Es más simple y más seguro que intentar calcular
        // "qué cambió" fila por fila, y como todo esto vive dentro de la
        // misma transacción, si algo falla a la mitad no se pierde nada:
        // transStatus() === false revierte TODO, incluyendo los borrados.
        $this->valorModel->borrarDeContrato($id);
        if ($valoresValidos) {
            $filas = [];
            foreach ($valoresValidos as $campoId => $valor) {
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
        foreach ($partidasValidas as $partida) {
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
     * Paso 4: "eliminar" un contrato. Gracias a useSoftDeletes=true en
     * ContratosModel, delete($id) NO hace un DELETE de SQL: hace un
     * UPDATE que le pone fecha a "deleted_at". El contrato deja de salir
     * en index()/ver() (y el folio queda libre para... en realidad no se
     * reutiliza, porque el folio se arma con el id autoincremental, que
     * nunca se repite), pero la fila y todo su historial (valores,
     * partidas) se quedan intactos en la base de datos.
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
     * Arma el folio real del contrato, ej. "TEM/CAS/FORTAMUN/CP-002/2026":
     *
     *   {prefijo_fijo del tipo}/{clave de la fuente}/{siglas del tipo}-{consecutivo}/{año}
     *
     * El consecutivo se cuenta por SIGLAS DE PROCEDIMIENTO (no por tipo de
     * contrato ni por fuente): si dos tipos distintos comparten las mismas
     * siglas (ej. "aislada" y "con anticipo" son ambas IRNP), cuentan
     * sobre el mismo consecutivo. Se reinicia cada año. Como esto se
     * llama DESPUÉS de insertar el contrato (ver create()), el propio
     * contrato recién insertado ya está incluido en el conteo, así que no
     * hace falta sumarle 1 aparte.
     */
    private function generarFolio(array $tipoContrato, array $fuenteFinanciamiento): string
    {
        $anio   = date('Y');
        $siglas = $tipoContrato['siglas_procedimiento'] ?? '';

        $consecutivo = $this->contratoModel
            ->select('contratos.id')
            ->join('tipos_contrato', 'tipos_contrato.id = contratos.tipo_contrato_id')
            ->where('tipos_contrato.siglas_procedimiento', $siglas)
            ->where('YEAR(contratos.created_at)', $anio)
            ->countAllResults();

        return ($tipoContrato['prefijo_fijo'] ?? '') . '/' . ($fuenteFinanciamiento['clave'] ?? '')
            . '/' . $siglas . '-' . str_pad((string) $consecutivo, 3, '0', STR_PAD_LEFT) . '/' . $anio;
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

    /**
     * Valida las filas de la tabla de "Partidas" (bienes). Una fila
     * completamente vacía se ignora en silencio (es una fila extra que
     * el usuario no llegó a usar); si tiene algo, todos sus campos
     * obligatorios deben venir correctos.
     *
     * Devuelve [filas_validas_con_importe_calculado, errores].
     */
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

    /**
     * Los campos dinámicos (campos_formulario) de un tipo de contrato,
     * ya filtrados para quitar cualquiera que por error tenga el mismo
     * nombre que una columna fija (ver CAMPOS_RESERVADOS). La usan
     * nuevo(), create(), editar() y actualizar(): estar en un solo lugar
     * evita que un día se actualice el filtro en 3 de los 4 métodos y se
     * quede desactualizado en el cuarto.
     */
    private function camposDinamicosDelTipo(int $tipoContratoId): array
    {
        return array_values(array_filter(
            $this->campoModel->paraTipoContrato($tipoContratoId),
            fn ($campo) => ! in_array($campo['nombre_campo'], self::CAMPOS_RESERVADOS, true)
        ));
    }

    /**
     * Para cada campo dinámico de tipo "lista" (por ejemplo, un campo que
     * se llena eligiendo un proveedor o un área de una lista desplegable),
     * trae las opciones de su catálogo de origen. Se agrupa por
     * "origen_lista" para no volver a consultar el mismo catálogo dos
     * veces si dos campos distintos usan, por ejemplo, ambos "proveedores".
     */
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
