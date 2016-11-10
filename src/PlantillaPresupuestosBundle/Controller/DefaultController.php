<?php

namespace PlantillaPresupuestosBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{   
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $service = $this->get('app_conexion');
        $base    = $this->container->getParameter('application');
        $usuario = $this->get('session')->get($base . '/usuario');

        $seguridadUsuarios = $service->obtenerWsSeguridadUsuarioEmpresaSucursal($usuario);

        return $this->render('PlantillaPresupuestosBundle::index.html.twig', 
            array(
                'seguridadUsuarios' => $seguridadUsuarios,
                )
            );
    }

    /**
     * @Route("/obtener-tipos-curso", name="obtener_tipos_curso")
     */
    public function obtenerTiposCursosAction(Request $request)
    {
        $service      = $this->get('app_conexion');
        $empresa      = $request->query->get('empresa');
        $centrocostos = $request->query->get('centrocostos');

        $tiposcurso   = $service->obtenerWsTiposDeCurso(
            $empresa, 
            $centrocostos
            );

        return new JsonResponse($tiposcurso);
    }

    /**
     * @Route("/obtener-plantillas", name="obtener_plantillas")
     */
    public function obtenerPlantillasAction(Request $request)
    {
        $service      = $this->get('app_conexion');
        $empresa      = $request->query->get('empresa');
        $centrocostos = $request->query->get('centrocostos');
        $tipoCurso    = $request->query->get('tipocurso');

        $plantillas   = $service->obtenerWsPlantillas(
            $empresa, 
            $centrocostos, 
            $tipoCurso
            );

        return new JsonResponse($plantillas);
    }

    /**
     * @Route("/cargar-plantilla", name="cargar_plantilla")
     */
    public function cargarAction(Request $request)
    {
        $empresa       = $request->request->get('empresa');
        $centroCostos  = $request->request->get('centroCostos');
        $tipoCurso     = $request->request->get('tipoCurso');
        $basePlantilla = $request->request->get('basePlantilla');
        
        $service       = $this->get('app_conexion');
        $plantilla     = $service->obtenerWsCargarPlantilla(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $basePlantilla
            );

        return $this->render('PlantillaPresupuestosBundle::plantilla.html.twig', $plantilla);
    }

    /**
     * @Route("/crear-plantilla", name="crear_plantilla")
     */
    public function crearAction(Request $request)
    {
        $empresa       = $request->request->get('empresa');
        $centroCostos  = $request->request->get('centroCostos');
        $tipoCurso     = $request->request->get('tipoCurso');
        $basePlantilla = $request->request->get('basePlantilla');
        $service       = $this->get('app_conexion');

        $arrDatosSoap                  = array();
        $arrItems                      = array();
        $arrDatosSoap['Cod_empresa']   = $empresa;
        $arrDatosSoap['centro_costo']  = $centroCostos;
        $arrDatosSoap['Tipo_curso']    = $tipoCurso;
        
        $arrDatosSoap['bloque']        = 'Ingresos';
        $arrItems[ 'ingresos' ]        = $service->obtenerItemsPorBloque($arrDatosSoap);
        
        $arrDatosSoap['bloque']        = 'Costos fijos';
        $arrItems[ 'costos_fijos' ]    = $service->obtenerItemsPorBloque($arrDatosSoap);
        
        $arrDatosSoap['bloque']        = 'Costos variable';
        $arrItems[ 'costos_variable' ] = $service->obtenerItemsPorBloque($arrDatosSoap);

        $plantilla                     = array(
            'arrIngresos'        => array(),
            'arrCostosFijos'     => array(),
            'arrCostosVariables' => array(),
            'arrItems'           => $arrItems,
            'arrMargenes'        => array(
                array(
                    'Margen' => 0,
                    'Costo'  => 0,
                    )
                ),
            );

        return $this->render('PlantillaPresupuestosBundle::plantilla.html.twig', $plantilla);
    }

    /**
     * @Route("/guardar-plantilla", name="guardar_plantilla")
     */
    public function guardarAction(Request $request)
    {
        $nuevo        = $request->request->get('nuevo');
        $empresa      = $request->request->get('empresa');
        $centroCostos = $request->request->get('centroCostos');
        $tipoCurso    = $request->request->get('tipoCurso');
        $plantilla    = $request->request->get('plantilla');
        
        $elementos    = $request->request->get('elementos');

        $margen                       = 0;
        $margenCostos                 = 0;
        $arrIngresosCrear             = array();
        $arrIngresosActualizar        = array();
        $arrIngresosEliminar          = array();
        $arrCostosFijosCrear          = array();
        $arrCostosFijosActualizar     = array();
        $arrCostosFijosEliminar       = array();
        $arrCostosVariablesCrear      = array();
        $arrCostosVariablesActualizar = array();
        $arrCostosVariablesEliminar   = array();

        foreach ($elementos as $value) {
            $nombre = $value['name'];
            $valor  = $value['value'];

            if ($nombre == 'input-margen') {
                $margen = $valor;
                continue;
            }

            if ($nombre == 'input-margen-costos') {
                $margenCostos = $valor;
                continue;
            }

            // INGRESOS NUEVOS
            $existe = strpos($nombre, 'ingreso_nuevo_');

            if ($existe !== false) {
                // Es el del numero
                $esNumero = strpos($nombre, 'ingreso_nuevo_item');

                if ($esNumero !== false) {
                    $arrIngresosCrear[$valor] = array(
                        'item' => $valor
                        );
                    continue;
                }

                $nombre = str_replace('ingreso_nuevo_', '', $nombre);
                $nombre = str_replace(']', '', $nombre);
                $nombre = explode('[', $nombre);
                $numero = $nombre[1];
                $nombre = $nombre[0];

                $arrIngresosCrear[$numero][$nombre] = $valor;

                continue;
            }

            // INGRESOS ELIMINAR
            $existe = strpos($nombre, 'ingreso_item_eliminado');

            if ($existe !== false) {
                $arrIngresosEliminar[$valor] = $valor;
                continue;
            }

            // INGRESOS ACTUALIZAR
            $existe = strpos($nombre, 'ingreso_');

            if ($existe !== false) {
                // Es el del numero
                $esNumero = strpos($nombre, 'ingreso_item');

                if ($esNumero !== false) {
                    $arrIngresosActualizar[$valor] = array(
                        'item' => $valor
                        );
                    continue;
                }

                $nombre = str_replace('ingreso_', '', $nombre);
                $nombre = str_replace(']', '', $nombre);
                $nombre = explode('[', $nombre);
                $numero = $nombre[1];
                $nombre = $nombre[0];

                $arrIngresosActualizar[$numero][$nombre] = $valor;

                continue;
            }

            // COSTOS FIJOS NUEVOS
            $existe = strpos($nombre, 'costo_fijo_nuevo_');

            if ($existe !== false) {
                // Es el del numero
                $esNumero = strpos($nombre, 'costo_fijo_nuevo_item');

                if ($esNumero !== false) {
                    $arrCostosFijosCrear[$valor] = array(
                        'item' => $valor
                        );
                    continue;
                }

                $nombre = str_replace('costo_fijo_nuevo_', '', $nombre);
                $nombre = str_replace(']', '', $nombre);
                $nombre = explode('[', $nombre);
                $numero = $nombre[1];
                $nombre = $nombre[0];

                $arrCostosFijosCrear[$numero][$nombre] = $valor;

                continue;
            }

            // COSTOS FIJOS ELIMINAR
            $existe = strpos($nombre, 'costo_fijo_item_eliminado');

            if ($existe !== false) {
                $arrCostosFijosEliminar[$valor] = $valor;
                continue;
            }

            // COSTOS FIJOS ACTUALIZAR
            $existe = strpos($nombre, 'costo_fijo_');

            if ($existe !== false) {
                // Es el del numero
                $esNumero = strpos($nombre, 'costo_fijo_item');

                if ($esNumero !== false) {
                    $arrCostosFijosActualizar[$valor] = array(
                        'item' => $valor
                        );
                    continue;
                }

                $nombre = str_replace('costo_fijo_', '', $nombre);
                $nombre = str_replace(']', '', $nombre);
                $nombre = explode('[', $nombre);
                $numero = $nombre[1];
                $nombre = $nombre[0];

                $arrCostosFijosActualizar[$numero][$nombre] = $valor;

                continue;
            }

            // COSTOS VARIABLES NUEVOS
            $existe = strpos($nombre, 'costo_variable_nuevo_');

            if ($existe !== false) {
                // Es el del numero
                $esNumero = strpos($nombre, 'costo_variable_nuevo_item');

                if ($esNumero !== false) {
                    $arrCostosVariablesCrear[$valor] = array(
                        'item' => $valor
                        );
                    continue;
                }

                $nombre = str_replace('costo_variable_nuevo_', '', $nombre);
                $nombre = str_replace(']', '', $nombre);
                $nombre = explode('[', $nombre);
                $numero = $nombre[1];
                $nombre = $nombre[0];

                $arrCostosVariablesCrear[$numero][$nombre] = $valor;

                continue;
            }

            // COSTOS VARIABLES ELIMINAR
            $existe = strpos($nombre, 'costo_variable_item_eliminado');

            if ($existe !== false) {
                $arrCostosVariablesEliminar[$valor] = $valor;
                continue;
            }

            // COSTOS VARIABLES ACTUALIZAR
            $existe = strpos($nombre, 'costo_variable_');

            if ($existe !== false) {
                // Es el del numero
                $esNumero = strpos($nombre, 'costo_variable_item');

                if ($esNumero !== false) {
                    $arrCostosVariablesActualizar[$valor] = array(
                        'item' => $valor
                        );
                    continue;
                }

                $nombre = str_replace('costo_variable_', '', $nombre);
                $nombre = str_replace(']', '', $nombre);
                $nombre = explode('[', $nombre);
                $numero = $nombre[1];
                $nombre = $nombre[0];

                $arrCostosVariablesActualizar[$numero][$nombre] = $valor;

                continue;
            }
        }

        $service       = $this->get('app_conexion');
        $metodoMargen  = 'update';

        if ($nuevo == 'true') {
            $metodoMargen                 = 'insert';
            $arrIngresosCrear             = array_merge($arrIngresosCrear, $arrIngresosActualizar);
            $arrCostosFijosCrear          = array_merge($arrCostosFijosCrear, $arrCostosFijosActualizar);
            $arrCostosVariablesCrear      = array_merge($arrCostosVariablesCrear, $arrCostosVariablesActualizar);
            
            $arrIngresosActualizar        = array();
            $arrCostosFijosActualizar     = array();
            $arrCostosVariablesActualizar = array();
        }

        $respuesta = $service->insertarMargenWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            $margen, 
            $margenCostos,
            $metodoMargen
            );

        $respuesta = $service->insertarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Ingresos', 
            $arrIngresosCrear
            );

        $respuesta = $service->insertarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Costos fijos', 
            $arrCostosFijosCrear
            );

        $respuesta = $service->insertarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Costos variable', 
            $arrCostosVariablesCrear
            );

        $respuesta = $service->actualizarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Ingresos', 
            $arrIngresosActualizar
            );

        $respuesta = $service->actualizarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Costos fijos', 
            $arrCostosFijosActualizar
            );

        $respuesta = $service->actualizarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Costos variable', 
            $arrCostosVariablesActualizar
            );

        $respuesta = $service->eliminarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Ingresos', 
            $arrIngresosEliminar
            );

        $respuesta = $service->eliminarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Costos fijos', 
            $arrCostosFijosEliminar
            );

        $respuesta = $service->eliminarDatoWs(
            $empresa,
            $centroCostos,
            $tipoCurso,
            $plantilla, 
            'Costos variable', 
            $arrCostosVariablesEliminar
            );

        return new Response('OK');
    }

    /**
     * @Route("/pasarela", name="pasarela")
     */
    public function pasarelaAction()
    {
        $usuario = $this->get('request')->query->get('usuario');

        if ($this->get('request')->isMethod('POST')) {
            $usuario = $this->get('request')->request->get('usuario');
        }
        $base = $this->container->getParameter('application');

        $this->get('session')->set($base . '/usuario', $usuario);

        return $this->redirectToRoute('homepage');
    }
}
