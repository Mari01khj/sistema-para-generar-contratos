<?php

namespace App\Controllers;

use App\Models\AreasModel;
use App\Models\CamposFormularioModel;
use App\Models\ContratosModel;
use App\Models\ContratoValoresModel;
use App\Models\ProveedoresModel;
use App\Models\TipoContrato;

class ContratosController extends BaseController
{
    private const CAMPOS_RESERVADOS = [
        'folio', 
        'tipo_contrato_id', 
        'proveedor_id', 
        'area_solicitante_id',
        'descripcion_corta', 
        'descripcion_larga', 
        'fecha_entrega',
        'lugar_entrega', 
        'plazo_pago', 
        'estado', 
        'usuario_id',
    ];

    protected $contratoModel;
    protected $valorModel;
    protected $tipoContratoModel;
    protected $campoModel;
    protected $proveedoresModel;
    protected $areasModel;

    public function __construct()
    {
        $this->contratoModel     = new ContratosModel();
        $this->valorModel        = new ContratoValoresModel();
        $this->tipoContratoModel = new TipoContrato();
        $this->campoModel        = new CamposFormularioModel();
        $this->proveedoresModel  = new ProveedoresModel();
        $this->areasModel        = new AreasModel();
    }
    
    public function index()
    {
        return view('contratos/index', [
            'contratos' => $this->contratoModel->listaConDetalle(),
        ]);
    }

    /**
     * Sin "?tipo_contrato_id=": solo muestra el selector de tipo.
     * Con "?tipo_contrato_id=X" válido: muestra el formulario completo, con los campos fijos y los dinámicos de este
     */
     
    public function nuevo()
    {
        $tipos = $this->tipoContratoModel->where('activo', 1)->findAll();

        $tipoContratoId = (int) ($this->request->getGet('tipo_contrato_id') ?? 0);
        $tipoContrato   = null;
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

                //se cargan los catalogos para campos dinamicos 
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

        return view('contratos/crear', [
            'tipos'           => $tipos,
            'tipoContrato'    => $tipoContrato,
            'camposDinamicos' => $camposDinamicos,
            'opcionesListas'  => $opcionesListas,
            'proveedores'     => $this->proveedoresModel->where('activo', 1)->findAll(),
            'areas'           => $this->areasModel->where('activo', 1)->findAll(),
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
            'descripcion_larga'   => $this->request->getPost('descripcion_larga'),
            'fecha_entrega'       => $this->request->getPost('fecha_entrega') ?: null,
            'lugar_entrega'       => $this->request->getPost('lugar_entrega'),
            'plazo_pago'          => $this->request->getPost('plazo_pago'),
            'usuario_id'          => session()->get('usuario_id'),
            'estado'              => 'borrador',
        ];

        $camposDinamicos = array_values(array_filter(
            $this->campoModel->paraTipoContrato($tipoContratoId),
            fn ($campo) => ! in_array($campo['nombre_campo'], self::CAMPOS_RESERVADOS, true)
        ));

        $valoresEnviados = (array) ($this->request->getPost('campos') ?? []);
        [$valoresValidos, $erroresDinamicos] = $this->validarCamposDinamicos($camposDinamicos, $valoresEnviados);

        if (! $this->contratoModel->validate($datosFijos) || $erroresDinamicos) 
        {
            $errores = array_merge($this->contratoModel->errors() ?? [], $erroresDinamicos);

            return redirect()->to(site_url('contratos/nuevo') . '?tipo_contrato_id=' . $tipoContratoId)
                ->withInput()
                ->with('error', 'Revisa los campos marcados.')
                ->with('errores_validacion', $errores);
        }

        //guardar el contrato con valores fijos y dinámicos en una transacción para que todo se guarde 
        $db = db_connect();
        $db->transStart();

        $this->contratoModel->insert($datosFijos);
        $contratoId = $this->contratoModel->getInsertID();

        //se crea el folio mediante el id 
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

    /**
     * Valida cada valor dinámico según el tipo_dato de su campo
     */
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
                continue; // opcional y vacío: no se guarda fila para este camp
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

    /** para agregar algún catalogo nuevo*/
    private function opcionesDeCatalogo(string $origen): array
    {
        return match ($origen)
        {
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
