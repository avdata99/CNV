<?php
/**
* Generador dinámico de Diagramas Sankey
*
* @package      Hackaton PROGRAM.ar
* @license      http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
* @author       Eugenia Bahit
* @status       Alfa (en desarrollo)
*
*
*
* OBSERVACIONES:
*
*   Nuevos archivos:
*       ./chart.php
*       ./render.php
*
*   Archivos modificados:
*       ./chart.html
*       - Agregado formulario HTML
*       - Reemplzada llamada a archivo js/dataset.js por
*         instrucciones JavaScript en crudo.
*         No se recomienda pasar a archivo ya que parte de las
*         instrucciones son escritas dinámicamente desde PHP
*         a través de chart.php
*
*       ./js/hack.js
*       - Línea 8: se eliminó el valor hardcodeado de height por
*                  la variable altura 
*/

# Constantes requeridas por la librería Template de EuropioEngine
# FIXME: en versiones menores a PHP 5.3 utilizar define()
const USE_PCRE = False;
const STATIC_DIR = "";

# Librería Template
require_once 'render.php';


class DatasetHelper {

    function __construct() {
        $this->json = $this->get_json();
        $this->sources = array();
        $this->targets = array();
        $this->fuerzas = array();
        $this->represores = array();
        $this->empresarios = array();
        $this->empresas = array();
        $this->dataset = array('nodes'=>array(), 'links'=>array());
        $this->ids = array();
        #...
        $this->set_params();
        $this->get_sources_and_targets_from_json();
        $this->get_fuerzas();
        $this->get_represores();
        $this->regularizar_matrices();
    }

    # Obtener el dataset - Retorna un array
    function get_json() {
        $json = file_get_contents("dataset.json");
        $json = json_decode($json);
        settype($json, 'array');
        return $json;
    }

    # Recuperar arámetros
    function set_params() {
        $params = array('fuerza', 'represor', 'empresario', 'empresa');
        foreach($params as $param) {
            $this->$param = isset($_GET[$param]) ? $_GET[$param] : -1;
            settype($this->$param, 'int');
        }
    }

    # Obtener los orígenes y destinos
    function get_sources_and_targets_from_json() {
        list($nodos, $links) = array($this->json['nodes'], $this->json['links']);
        foreach($links as $obj) {
            $this->sources[] = $obj->source;
            $this->targets[] = $obj->target;
        }
    }

    # Obtener el nombre de todas las fuerzas y posición actual en el JSON
    function get_fuerzas() {
        $property = "fuerzas";
        foreach($this->json['nodes'] as $index=>$obj) {
            $esta_en_source = in_array($index, $this->sources);
            $esta_en_target = in_array($index, $this->targets);
            if($esta_en_source && !$esta_en_target) {
                $this->fuerzas[] = $this->set_array($index, $obj->name, 
                    $this->fuerza);
            }
        }
        $this->set_json();
    }

    # Armar un array 
    function set_array($id, $nombre, $parametro) {
        return array(
                    "ID" => $id, 
                    "NOMBRE" => $nombre,
                    "FSELECTED" => ($parametro == $id) ? " selected" : ""
                );
    }

    # Armar el nuevo JSON
    function set_json() {
        global $altura;
        if($this->fuerza > -1) {
            $this->dataset['nodes'][] = $this->json['nodes'][$this->fuerza];
            $altura = 0;
        } else {
            $this->dataset = $this->json;
            $altura = "1500";
        }
    }
    
    # Buscar todos los targets donde source = $this->fuerza
    function get_represores() {
        foreach($this->json['links'] as $index=>$obj) {
            if($obj->source == $this->fuerza) {
                $this->represores[] = $this->set_array($obj->target, 
                    $this->json['nodes'][$obj->target]->name, $this->represor);;
                if($obj->target == $this->represor || $this->represor == -1) {
                    $new_position = array_push($this->dataset['nodes'],
                        $this->json['nodes'][$obj->target]) - 1;
                    $this->dataset['links'][] = array(
                        'source' => 0,
                        'target' => $new_position,
                        'value' => 1
                    );
                    $this->search_target($obj->target, $new_position,
                        'empresarios');
                }
            }
        }
    }

    # Buscar todos los empresarios asociados a un represor 
    # donde source = $param
    function search_target($source, $new_position, $array) {
        $property = substr($array, 0, strlen($array)-1);
        foreach($this->json['links'] as $index=>$obj) {
            if($obj->source == $source) {
                if($obj->target == $this->$property || $this->$property == -1) {
                    if(!array_key_exists("{$obj->target}", $this->ids)) {
                        $newposition = array_push($this->dataset['nodes'],
                            $this->json['nodes'][$obj->target]) - 1;
                        $this->ids["{$obj->target}"] = $newposition;
                        array_push($this->$array, 
                            $this->set_array(
                                $obj->target,
                                $this->json['nodes'][$obj->target]->name, 
                                $this->$property)
                            );
                    } else {
                        $newposition = $this->ids["{$obj->target}"];
                    }

                    $this->dataset['links'][] = array(
                        'source' => $new_position,
                        'target' => $newposition,
                        'value' => 1
                    );

                    $this->search_target($obj->target, $newposition, 'empresas');
                }
            }
        }
    }

    function regularizar_matrices() {
        if($this->represor < 0) $this->empresarios = array();
        if($this->empresario < 0) $this->empresas = array();
    }


    # Calcula la altura aproximada para el gráfico
    # TODO la altura del grafico está estimada casi a "ojo"
    # se recomienda diseñar un algoritmo de cálculo en forma conjunta con 
    # un/a diseñador/a gráfico y/o desarrollador/a front-end
    function calcular_altura() {
        $cantidad = count($this->dataset['links']);
        if($cantidad < 100) {
            $cuanto = 10;
        } elseif($cantidad > 100 && $cantidad < 240) {
            $cuanto = 6;
        } elseif($cantidad > 240 && $cantidad < 400) {
            $cuanto = 3;
        } elseif($cantidad > 400 && $cantidad < 700) {
            $cuanto = 2;
        }
        return "dataset.links.length * $cuanto";  # retorna instrucción javascript
    }

}


$ds = new DatasetHelper();
if($altura == 0) $altura = $ds->calcular_altura();
$dataset = "var dataset = " . json_encode($ds->dataset);
$chart = file_get_contents("chart.html");
$buscar = array("{DATASET}", "{ALTURA}");
$reemplazar = array($dataset, $altura);
$basehtml = str_replace($buscar, $reemplazar, $chart);
$render_fuerzas = Template($basehtml)->render_regex('FUERZAS', $ds->fuerzas);
$render_represores = Template($render_fuerzas)->render_regex('REPRESORES', $ds->represores);
$render_empresarios = Template($render_represores)->render_regex('EMPRESARIOS', $ds->empresarios);
$render_empresas = Template($render_empresarios)->render_regex('EMPRESAS', $ds->empresas);

// Recaptcha
require_once('recaptchalib.php');

$recaptcha_publickey = '6LfC4eUSAAAAAAcVDXbwNKunHRntmRQLA5fsNoJL';
$recaptcha_html = recaptcha_get_html($recaptcha_publickey);
$render_recaptcha = Template($render_empresas)->render(array('RECAPTCHA' => $recaptcha_html));

print $render_recaptcha;
?>
