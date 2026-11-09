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
    private const TASA_IVA = 0.16;

    private const CARPETA_FINALES = 'uploads/finales';

    private const CAMPOS_RESERVADOS = [
        'folio',
        'tipo_contrato_id',
        'fuente_financiamiento_id',
        'proveedor_id',
        'area_solicitante_id',
        'area_tecnica_id',
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

    // Carga los modelos que usa el controlador.
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

    // Lista de contratos.
    public function index()
    {
        return view('contratos/index', [
            'contratos' => $this->contratoModel->listaConDetalle(),
        ]);
    }

    // Detalle de un contrato con sus valores y partidas.
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

    // Genera y descarga el .docx/.xlsx del contrato.
    public function exportarDocumento(int $id)
    {
        $contrato = $this->contratoModel->unoConDetalle($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        $tipoContrato = $this->tipoContratoModel->find($contrato['tipo_contrato_id']);
        $proveedor    = $this->proveedoresModel->find($contrato['proveedor_id']);
        $area         = $this->areasModel->find($contrato['area_solicitante_id']);
        $areaTecnica  = $contrato['area_tecnica_id'] ? $this->areasModel->find($contrato['area_tecnica_id']) : null;

        if (empty($tipoContrato['plantilla'])) {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Este tipo de contrato no tiene una plantilla de Word asignada. '
                    . 'Sube una en Tipos de contrato → Gestionar plantilla antes de exportar.');
        }

        try {
            $rutaDocumento = (new GeneradorPdfContrato())->generar(
                contrato: $contrato,
                proveedor: $proveedor,
                area: $area,
                valoresDinamicos: $this->valorModel->paraContrato($id),
                partidas: $this->bienesModel->paraContrato($id),
                rutaPlantilla: WRITEPATH . $tipoContrato['plantilla'],
                areaTecnica: $areaTecnica ?: null
            );
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', $e->getMessage());
        }

        $extension = strtolower(pathinfo($rutaDocumento, PATHINFO_EXTENSION));
        $tipoMime  = $extension === 'docx'
            ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        $nombreDescarga = str_replace('/', '-', $contrato['folio']) . '.' . $extension;

        return $this->response->download($rutaDocumento, null)
            ->setFileName($nombreDescarga)
            ->setContentType($tipoMime);
    }

    // Formulario de alta.
    public function nuevo()
    {
        $tipos = $this->tipoContratoModel->where('activo', 1)->findAll();

        $tipoContratoId  = (int) ($this->request->getGet('tipo_contrato_id') ?? 0);
        $tipoContrato    = null;
        $camposDinamicos = [];
        $opcionesListas  = [];

        if ($tipoContratoId > 0) {
            $tipoContrato = $this->tipoContratoModel->find($tipoContratoId);

            if ($tipoContrato && $tipoContrato['activo']) {
                $camposDinamicos = $this->camposDinamicosDelTipo($tipoContratoId);
                $opcionesListas  = $this->opcionesListasPara($camposDinamicos);
            } else {
                $tipoContrato = null;
            }
        }

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

    // Formulario de edición precargado.
    public function editar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        if ($contrato['estado'] === 'finalizado') {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Un contrato finalizado ya no se puede editar. Pide a un administrador que lo reabra.');
        }

        $tipoContrato    = $this->tipoContratoModel->find($contrato['tipo_contrato_id']);
        $fuente          = $contrato['fuente_financiamiento_id'] ? $this->fuenteModel->find($contrato['fuente_financiamiento_id']) : null;
        $camposDinamicos = $this->camposDinamicosDelTipo($contrato['tipo_contrato_id']);
        $opcionesListas  = $this->opcionesListasPara($camposDinamicos);

        $valoresCampos = [];
        foreach ($this->valorModel->paraContrato($id) as $valorGuardado) {
            $valoresCampos[$valorGuardado['campo_id']] = $valorGuardado['valor'];
        }

        foreach ($camposDinamicos as $campo) {
            $valorViejo = old('campos.' . $campo['id']);
            if ($valorViejo !== null) {
                $valoresCampos[$campo['id']] = $valorViejo;
            }
        }

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

    // Valida y guarda un contrato nuevo con sus partidas.
    public function create()
    {
        $tipoContratoId = (int) $this->request->getPost('tipo_contrato_id');
        $tipoContrato   = $this->tipoContratoModel->find($tipoContratoId);

        if (! $tipoContrato || ! $tipoContrato['activo']) {
            return redirect()->to(site_url('contratos/nuevo'))
                ->with('error', 'Selecciona un tipo de contrato válido.');
        }

        if (empty($tipoContrato['prefijo_fijo']) || empty($tipoContrato['siglas_procedimiento'])) {
            return redirect()->to(site_url('contratos/nuevo'))
                ->with('error', 'El tipo de contrato "' . $tipoContrato['nombre'] . '" todavía no '
                    . 'tiene configurado el prefijo y las siglas de su folio. Ve a Tipos de '
                    . 'Contrato y llénalos antes de crear un contrato de este tipo.');
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
            'area_tecnica_id'          => $this->request->getPost('area_tecnica_id') ?: null,
            'descripcion_corta'        => $this->request->getPost('descripcion_corta'),
            'nombre_bien'              => $this->request->getPost('nombre_bien'),
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
        if ($filasPartidas && ! $this->bienesModel->skipValidation(true)->insertBatch($filasPartidas)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'No se pudieron guardar las partidas.');
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(site_url('contratos/nuevo') . '?tipo_contrato_id=' . $tipoContratoId)
                ->withInput()
                ->with('error', 'No se pudo guardar el contrato. Intenta de nuevo.');
        }

        return redirect()->to(site_url('contratos'))
            ->with('mensaje', 'Contrato ' . $folio . ' registrado correctamente.');
    }

    // Valida y actualiza un contrato; reemplaza valores y partidas.
    public function actualizar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o fue eliminado.');
        }

        if ($contrato['estado'] === 'finalizado') {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Un contrato finalizado ya no se puede editar. Pide a un administrador que lo reabra.');
        }

        $tipoContratoId = (int) $contrato['tipo_contrato_id'];

        $datosFijos = [
            'proveedor_id'        => $this->request->getPost('proveedor_id'),
            'area_solicitante_id' => $this->request->getPost('area_solicitante_id'),
            'area_tecnica_id'     => $this->request->getPost('area_tecnica_id') ?: null,
            'descripcion_corta'   => $this->request->getPost('descripcion_corta'),
            'nombre_bien'         => $this->request->getPost('nombre_bien'),
            'fecha_entrega'       => $this->request->getPost('fecha_entrega') ?: null,
            'lugar_entrega'       => $this->request->getPost('lugar_entrega'),
            'plazo_pago'          => $this->request->getPost('plazo_pago'),
            'condiciones_entrega' => $this->request->getPost('condiciones_entrega'),
        ];

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
        if ($filasPartidas && ! $this->bienesModel->skipValidation(true)->insertBatch($filasPartidas)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'No se pudieron guardar las partidas.');
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(site_url('contratos/' . $id . '/editar'))
                ->withInput()
                ->with('error', 'No se pudo guardar el contrato. Intenta de nuevo.');
        }

        return redirect()->to(site_url('contratos/' . $id))
            ->with('mensaje', 'Contrato ' . $contrato['folio'] . ' actualizado correctamente.');
    }

    // Borrado lógico del contrato.
    public function eliminar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato) {
            return redirect()->to(site_url('contratos'))
                ->with('error', 'Ese contrato no existe o ya fue eliminado.');
        }

        if ($contrato['estado'] === 'finalizado') {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Un contrato finalizado ya no se puede eliminar. Pide a un administrador que lo reabra.');
        }

        $this->contratoModel->delete($id);

        return redirect()->to(site_url('contratos'))
            ->with('mensaje', 'Contrato ' . $contrato['folio'] . ' eliminado.');
    }

    // Pasa un borrador a "en corrección" (el usuario lo corrige en su equipo).
    public function marcarEnCorreccion(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato || $contrato['estado'] !== 'borrador') {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Solo un contrato en borrador se puede pasar a corrección.');
        }

        $this->contratoModel->update($id, ['estado' => 'en_correccion']);

        return redirect()->to(site_url('contratos/' . $id))
            ->with('mensaje', 'El contrato quedó en corrección.');
    }

    // Sube el archivo final (.pdf/.docx) y marca el contrato como finalizado.
    public function finalizar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (! $contrato || ! in_array($contrato['estado'], ['borrador', 'en_correccion'], true)) {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Este contrato no se puede finalizar en su estado actual.');
        }

        $reglas = [
            'archivo_final' => [
                'label'  => 'Archivo final',
                'rules'  => 'uploaded[archivo_final]|max_size[archivo_final,10240]|ext_in[archivo_final,pdf,docx]'
                    . '|mime_in[archivo_final,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip]',
                'errors' => [
                    'uploaded' => 'Selecciona el archivo final del contrato.',
                    'max_size' => 'El archivo no debe pesar más de 10 MB.',
                    'ext_in'   => 'El archivo debe ser PDF o Word (.docx).',
                    'mime_in'  => 'El archivo debe ser PDF o Word (.docx).',
                ],
            ],
        ];

        if (! $this->validate($reglas)) {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $archivo = $this->request->getFile('archivo_final');
        $nombre  = $archivo->getRandomName();
        $archivo->move(WRITEPATH . self::CARPETA_FINALES, $nombre);

        $anterior = $contrato['archivo_final'] ?? null;
        if ($anterior && is_file(WRITEPATH . $anterior)) {
            unlink(WRITEPATH . $anterior);
        }

        $this->contratoModel->update($id, [
            'estado'             => 'finalizado',
            'archivo_final'      => self::CARPETA_FINALES . '/' . $nombre,
            'fecha_finalizacion' => date('Y-m-d H:i:s'),
            'finalizado_por'     => session()->get('usuario_id'),
        ]);

        return redirect()->to(site_url('contratos/' . $id))
            ->with('mensaje', 'Contrato finalizado.');
    }

    // Descarga el archivo final guardado.
    public function archivoFinal(int $id)
    {
        $contrato = $this->contratoModel->find($id);
        $ruta     = $contrato ? WRITEPATH . ($contrato['archivo_final'] ?? '') : '';

        if (! $contrato || empty($contrato['archivo_final']) || ! is_file($ruta)) {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Este contrato no tiene archivo final.');
        }

        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        return $this->response->download($ruta, null)
            ->setFileName(str_replace('/', '-', $contrato['folio']) . '-final.' . $extension);
    }

    // Solo administrador: regresa un contrato finalizado a "en corrección".
    public function reabrir(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if ((int) session()->get('rol_id') !== 1) {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Solo un administrador puede reabrir un contrato.');
        }

        if (! $contrato || $contrato['estado'] !== 'finalizado') {
            return redirect()->to(site_url('contratos/' . $id))
                ->with('error', 'Este contrato no está finalizado.');
        }

        $this->contratoModel->update($id, ['estado' => 'en_correccion', 'fecha_finalizacion' => null]);

        return redirect()->to(site_url('contratos/' . $id))
            ->with('mensaje', 'Contrato reabierto: quedó en corrección.');
    }

    // Folio: prefijo/fuente/siglas-consecutivo/año (máximo + 1).
    private function generarFolio(array $tipoContrato, array $fuenteFinanciamiento): string
    {
        $anio   = date('Y');
        $siglas = $tipoContrato['siglas_procedimiento'] ?? '';

        $folios = $this->contratoModel
            ->withDeleted()
            ->select('contratos.folio')
            ->join('tipos_contrato', 'tipos_contrato.id = contratos.tipo_contrato_id')
            ->where('tipos_contrato.siglas_procedimiento', $siglas)
            ->like('contratos.folio', '/' . $anio, 'before')
            ->findAll();

        $maximo = 0;
        foreach ($folios as $fila) {
            if (preg_match('/-(\d+)\/' . $anio . '$/', $fila['folio'], $m)) {
                $maximo = max($maximo, (int) $m[1]);
            }
        }
        $consecutivo = $maximo + 1;

        return ($tipoContrato['prefijo_fijo'] ?? '') . '/' . ($fuenteFinanciamiento['clave'] ?? '')
            . '/' . $siglas . '-' . str_pad((string) $consecutivo, 3, '0', STR_PAD_LEFT) . '/' . $anio;
    }

    // Valida los campos propios del tipo de contrato.
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

    // Valida las partidas; devuelve [válidas, errores].
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

    // Campos activos del tipo de contrato.
    private function camposDinamicosDelTipo(int $tipoContratoId): array
    {
        return array_values(array_filter(
            $this->campoModel->paraTipoContrato($tipoContratoId),
            fn ($campo) => ! in_array($campo['nombre_campo'], self::CAMPOS_RESERVADOS, true)
        ));
    }

    // Opciones de los campos tipo lista.
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

    // Opciones de un catálogo (proveedores, áreas, etc.).
    private function opcionesDeCatalogo(string $origen): array
    {
        return match ($origen) {
            'proveedores' => $this->proveedoresModel->where('activo', 1)->findAll(),
            'areas'       => $this->areasModel->where('activo', 1)->findAll(),
            default       => [],
        };
    }

    // Comprueba que el valor exista en el catálogo.
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