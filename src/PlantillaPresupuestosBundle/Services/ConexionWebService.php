<?php

namespace PlantillaPresupuestosBundle\Services;

use Psr\Log\LoggerInterface;

class ConexionWebService
{
    private $logger;

    /**
     * @var string
     */
    private $urlWebService = '';

    /**
     * @var nusoap_client | null
     */
    private $nuSopClient = null;

    public function __construct(LoggerInterface $logger, $urlWebService)
    {
        $this->logger        = $logger;
        $this->urlWebService = $urlWebService;
    }
    
    public function obtenerWsSeguridadUsuarioEmpresaSucursal($usuario)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap            =  array();
        $arrDatosSoap['usuario'] = $usuario;

        $respuesta = $nuSopClient->call('SEGURIDAD_USUARIO_EMPRESA_SUCURSAL', 
            $arrDatosSoap
            , 'http://tempuri.org'
            )
        ;

        if (!is_array($respuesta)) {
            return false;
        }

        if (!array_key_exists('SEGURIDAD_USUARIO_EMPRESA_SUCURSALResult', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['SEGURIDAD_USUARIO_EMPRESA_SUCURSALResult'];

        if (!array_key_exists('diffgram', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['diffgram'];

        if (!is_array($respuesta)) {
            return [];
        }

        $respuesta = $respuesta['NewDataSet']['Table'];

        $arrEmpresas   = [];
        $arrSucursales = [];

        foreach ($respuesta as $value) {
            $arrEmpresas[$value[ 'Cod_empresa' ]] = $value[ 'Empresa' ];

            $arrSucursal =[];

            $arrSucursal['codigo']  = $value[ 'CoD_sucursal' ];
            $arrSucursal['nombre']  = $value[ 'Sucursal' ];
            $arrSucursal['empresa'] = $value[ 'Cod_empresa' ];

            $arrSucursales[] = $arrSucursal;
        }

        return array(
            'arrEmpresas'   => $arrEmpresas,
            'arrSucursales' => $arrSucursales,
            );
    }

    public function obtenerWsTiposDeCurso($empresa, $centroCostos)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap                 = array();
        $arrDatosSoap['proceso']      = 'descripcion_orden';
        $arrDatosSoap['Cod_empresa']  = $empresa;
        $arrDatosSoap['centro_costo'] = $centroCostos;
        $arrDatosSoap['Fecha']        = (new \DateTime())->format('d/m/Y');

        $respuesta = $nuSopClient->call('SP_Item_control_presupuestario', 
            $arrDatosSoap
            , 'http://tempuri.org'
            )
        ;

        if (!is_array($respuesta)) {
            return false;
        }

        if (!array_key_exists('SP_Item_control_presupuestarioResult', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['SP_Item_control_presupuestarioResult'];

        if (!array_key_exists('diffgram', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['diffgram'];

        if (!is_array($respuesta)) {
            return [];
        }

        $respuesta = $respuesta['NewDataSet']['Table'];

        $arrTiposCurso = [];

        foreach ($respuesta as $value) {
            $arrTiposCurso[] = $value[ 'descripcion_orden_trabajo' ];
        }

        return $arrTiposCurso;
    }

    public function obtenerWsPlantillas($empresa, $centroCostos, $tipoCurso)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap                 = array();
        $arrDatosSoap['proceso']      = 'Nombre_plnatilla';
        $arrDatosSoap['Cod_empresa']  = $empresa;
        $arrDatosSoap['Tipo_curso']   = $tipoCurso;
        $arrDatosSoap['centro_costo'] = $centroCostos;
        $arrDatosSoap['Fecha']        = (new \DateTime())->format('d/m/Y');

        $respuesta = $nuSopClient->call('SP_Item_control_presupuestario', 
            $arrDatosSoap
            , 'http://tempuri.org'
            )
        ;

        if (!is_array($respuesta)) {
            return false;
        }

        if (!array_key_exists('SP_Item_control_presupuestarioResult', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['SP_Item_control_presupuestarioResult'];

        if (!array_key_exists('diffgram', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['diffgram'];

        if (!is_array($respuesta)) {
            return [];
        }

        $respuesta = $respuesta['NewDataSet']['Table'];

        if (array_key_exists('nombre_plantilla', $respuesta)) {
            $respuestaAux[] = $respuesta;

            $respuesta      = $respuestaAux;
        }

        $arrTiposCurso = [];

        foreach ($respuesta as $value) {
            if (array_key_exists('nombre_plantilla', $value)) {
                $arrTiposCurso[] = $value[ 'nombre_plantilla' ];
            }
        }

        return $arrTiposCurso;
    }

    public function obtenerWsCargarPlantilla($empresa, $centroCostos, $tipoCurso, $basePlantilla)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap                     = array();
        $arrDatosSoap['Cod_empresa']      = $empresa;
        $arrDatosSoap['centro_costo']     = $centroCostos;
        $arrDatosSoap['Tipo_curso']       = $tipoCurso;
        $arrDatosSoap['proceso']          = 'select';
        $arrDatosSoap['Fecha']            = (new \DateTime())->format('d/m/Y');
        $arrDatosSoap['Nombre_plantilla'] = $basePlantilla;

        $respuesta = $nuSopClient->call('SP_Item_control_presupuestario', 
            $arrDatosSoap
            , 'http://tempuri.org'
            )
        ;

        if (!is_array($respuesta)) {
            return false;
        }

        if (!array_key_exists('SP_Item_control_presupuestarioResult', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['SP_Item_control_presupuestarioResult'];

        if (!array_key_exists('diffgram', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['diffgram'];

        if (!is_array($respuesta)) {
            return [];
        }

        $respuesta = $respuesta['NewDataSet']['Table'];

        $arrIngresos        = [];
        $arrCostosFijos     = [];
        $arrCostosVariables = [];
        $arrMargenes        = [];

        if (array_key_exists('bloque', $respuesta)) {
            $respuesta = array($respuesta);
        }

        foreach ($respuesta as $value) {
            if ($value['bloque'] == 'Ingresos') {
                $arrIngresos[] = $value;
                continue;
            }

            if ($value['bloque'] == 'Costos fijos') {
                $arrCostosFijos[] = $value;
                continue;
            }

            if ($value['bloque'] == 'Costos variable') {
                $arrCostosVariables[] = $value;
                continue;
            }

            if ($value['bloque'] == 'margen') {
                $arrMargenes[] = $value;
                continue;
            }
        }

        if ( COUNT($arrMargenes) === 0 ) {
            $arrMargenes = array(
                array(
                    'Margen' => 0,
                    'Costo'  => 0,
                    )
                );
        }

        $arrItems                      = [];
        
        $arrDatosSoap['bloque']        = 'Ingresos';
        $arrItems[ 'ingresos' ]        = $this->obtenerItemsPorBloque($arrDatosSoap);
        
        $arrDatosSoap['bloque']        = 'Costos fijos';
        $arrItems[ 'costos_fijos' ]    = $this->obtenerItemsPorBloque($arrDatosSoap);
        
        $arrDatosSoap['bloque']        = 'Costos variable';
        $arrItems[ 'costos_variable' ] = $this->obtenerItemsPorBloque($arrDatosSoap);

        return array(
            'arrIngresos'        => $arrIngresos,
            'arrCostosFijos'     => $arrCostosFijos,
            'arrCostosVariables' => $arrCostosVariables,
            'arrMargenes'        => $arrMargenes,
            'arrItems'           => $arrItems,
            );
    }

    public function insertarMargenWs($empresa, $centroCostos, $tipoCurso, $plantilla, $margen, $margenCostos, $metodoMargen)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap                     = array();
        $arrDatosSoap['Cod_empresa']      = $empresa;
        $arrDatosSoap['centro_costo']     = $centroCostos;
        $arrDatosSoap['Tipo_curso']       = $tipoCurso;
        $arrDatosSoap['proceso']          = $metodoMargen;
        $arrDatosSoap['Fecha']            = (new \DateTime())->format('d/m/Y');
        $arrDatosSoap['Nombre_plantilla'] = $plantilla;
        $arrDatosSoap['bloque']           = 'margen';
        $arrDatosSoap['margen']           = str_replace('.', '', $margen);;
        $arrDatosSoap['costo']            = str_replace('.', '', $margenCostos);;

        $respuesta = $nuSopClient->call('SP_Item_control_presupuestario', 
            $arrDatosSoap
            , 'http://tempuri.org'
            )
        ;

        $respuesta = $respuesta['SP_Item_control_presupuestarioResult'];
        $respuesta = $respuesta['diffgram'];
        $respuesta = $respuesta['NewDataSet'];
        $respuesta = $respuesta['Table'];
        $respuesta = $respuesta['Estatus'];

        $this->logger->info($respuesta);
    }

    public function insertarDatoWs($empresa, $centroCostos, $tipoCurso, $plantilla, $bloque, $valores)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap                     = array();
        $arrDatosSoap['Cod_empresa']      = $empresa;
        $arrDatosSoap['centro_costo']     = $centroCostos;
        $arrDatosSoap['Tipo_curso']       = $tipoCurso;
        $arrDatosSoap['proceso']          = 'insert';
        $arrDatosSoap['Fecha']            = (new \DateTime())->format('d/m/Y');
        $arrDatosSoap['Nombre_plantilla'] = $plantilla;
        $arrDatosSoap['bloque']           = $bloque;

        foreach ($valores as $value) {
            $arrDatosSoap['item']        = $value[ 'item' ];
            $arrDatosSoap['descripcion'] = $value[ 'descripcion' ];
            $arrDatosSoap['c1']          = str_replace('.', '', $value[ 'c1' ]);
            $arrDatosSoap['t1']          = str_replace('.', '', $value[ 't1' ]);
            $arrDatosSoap['c2']          = str_replace('.', '', $value[ 'c2' ]);
            $arrDatosSoap['t2']          = str_replace('.', '', $value[ 't2' ]);

            $respuesta = $nuSopClient->call('SP_Item_control_presupuestario', 
                $arrDatosSoap
                , 'http://tempuri.org'
                )
            ;

            $respuesta = $respuesta['SP_Item_control_presupuestarioResult'];
            $respuesta = $respuesta['diffgram'];
            $respuesta = $respuesta['NewDataSet'];
            $respuesta = $respuesta['Table'];
            $respuesta = $respuesta['Estatus'];

            $this->logger->info($respuesta);
        }
    }

    public function actualizarDatoWs($empresa, $centroCostos, $tipoCurso, $plantilla, $bloque, $valores)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap                     = array();
        $arrDatosSoap['Cod_empresa']      = $empresa;
        $arrDatosSoap['centro_costo']     = $centroCostos;
        $arrDatosSoap['Tipo_curso']       = $tipoCurso;
        $arrDatosSoap['proceso']          = 'update';
        $arrDatosSoap['Fecha']            = (new \DateTime())->format('d/m/Y');
        $arrDatosSoap['Nombre_plantilla'] = $plantilla;
        $arrDatosSoap['bloque']           = $bloque;

        foreach ($valores as $value) {
            $arrDatosSoap['item']        = $value[ 'item' ];
            $arrDatosSoap['descripcion'] = $value[ 'descripcion' ];
            $arrDatosSoap['c1']          = str_replace('.', '', $value[ 'c1' ]);
            $arrDatosSoap['t1']          = str_replace('.', '', $value[ 't1' ]);
            $arrDatosSoap['c2']          = str_replace('.', '', $value[ 'c2' ]);
            $arrDatosSoap['t2']          = str_replace('.', '', $value[ 't2' ]);

            $respuesta = $nuSopClient->call('SP_Item_control_presupuestario', 
                $arrDatosSoap
                , 'http://tempuri.org'
                )
            ;

            $respuesta = $respuesta['SP_Item_control_presupuestarioResult'];
            $respuesta = $respuesta['diffgram'];
            $respuesta = $respuesta['NewDataSet'];
            $respuesta = $respuesta['Table'];
            $respuesta = $respuesta['Estatus'];

            if ($respuesta == 'El dato no se ha actualizado correctamente') {
                // echo('<pre>');var_dump($arrDatosSoap);exit();
            }

            $this->logger->info($respuesta);
        }
    }

    public function eliminarDatoWs($empresa, $centroCostos, $tipoCurso, $plantilla, $bloque, $valores)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap                     = array();
        $arrDatosSoap['Cod_empresa']      = $empresa;
        $arrDatosSoap['centro_costo']     = $centroCostos;
        $arrDatosSoap['Tipo_curso']       = $tipoCurso;
        $arrDatosSoap['proceso']          = 'delete';
        $arrDatosSoap['Fecha']            = (new \DateTime())->format('d/m/Y');
        $arrDatosSoap['Nombre_plantilla'] = $plantilla;
        $arrDatosSoap['bloque']           = $bloque;

        foreach ($valores as $value) {
            $arrDatosSoap['item']        = $value;

            $respuesta = $nuSopClient->call('SP_Item_control_presupuestario', 
                $arrDatosSoap
                , 'http://tempuri.org'
                )
            ;

            $respuesta = $respuesta['SP_Item_control_presupuestarioResult'];
            $respuesta = $respuesta['diffgram'];
            $respuesta = $respuesta['NewDataSet'];
            $respuesta = $respuesta['Table'];
            $respuesta = $respuesta['Estatus'];

            $this->logger->info($respuesta);
        }
    }

    private function obtenerConexionNuSoap()
    {
        if ($this->nuSopClient === null) {
            $this->nuSopClient = new \nusoap_client($this->urlWebService, true);
            $this->nuSopClient->soap_defencoding = 'UTF-8';
            $this->nuSopClient->decode_utf8 = false;
            $this->logger->info('Nueva Inicialización Cliente SOAP');
        }
        
        $this->logger->info('Nueva Conexión SOAP');

        if ($this->nuSopClient->getError()) {
            return false;
        }

        return $this->nuSopClient;
    }

    public function obtenerItemsPorBloque($opciones)
    {
        $nuSopClient = $this->obtenerConexionNuSoap();

        $arrDatosSoap                 = array();
        $arrDatosSoap['Cod_empresa']  = $opciones['Cod_empresa'];
        $arrDatosSoap['centro_costo'] = $opciones['centro_costo'];
        $arrDatosSoap['Tipo_curso']   = $opciones['Tipo_curso'];
        $arrDatosSoap['proceso']      = 'item';
        $arrDatosSoap['bloque']       = $opciones['bloque'];

        $respuesta = $nuSopClient->call('SP_Item_control_presupuestario', 
            $arrDatosSoap
            , 'http://tempuri.org'
            )
        ;

        if (!is_array($respuesta)) {
            return false;
        }

        if (!array_key_exists('SP_Item_control_presupuestarioResult', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['SP_Item_control_presupuestarioResult'];

        if (!array_key_exists('diffgram', $respuesta)) {
            return false;
        }

        $respuesta = $respuesta['diffgram'];

        if (!is_array($respuesta)) {
            return [];
        }

        $respuesta = $respuesta['NewDataSet']['Table'];
        $arrReturn = [];

        if (array_key_exists('Cod_Item', $respuesta)) {
            $respuesta = array($respuesta);
        }

        foreach ($respuesta as $value) {
            $arrReturn[ $value['Cod_Item'] ] = $value[ 'Descripcion' ];
        }

        return $arrReturn;
    }
}
