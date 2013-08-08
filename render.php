<?php
/**
* Clase que permite realizar sustituciones estáticas y dinámicas (iterativas)
*
* @package      EuropioEngine
* @subpackage   core.helpers
* @license      http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
* @author       Eugenia Bahit <ebahit@member.fsf.org>
* @link         http://www.europio.org
* @contributor  Jimmy Daniel Barranco
*/

 
class Template {
 
    public function __construct($str='') {
        $this->str = $str;
        $this->filename = STATIC_DIR . "html/template.html";
    }
 
    public function render($dict=array()) {
        settype($dict, 'array');
        $this->set_dict($dict);
        return str_replace(array_keys($this->dict), array_values($this->dict),
            $this->str);
    }
 
    function get_regex($key, $remove_keys=True) {
        $regex = "/<!--$key-->(.|\n){1,}<!--$key-->/";
        preg_match($regex, $this->str, $matches);
        $no_keys = str_replace("<!--$key-->", "", $matches[0]);
        return ($remove_keys) ? $no_keys : $matches[0];
    }
 
    function get_substr($key, $remove_keys=True) {
        $needle = "<!--$key-->";
        $first = strpos($this->str, $needle);
        $last = strrpos($this->str, $needle);
        $long = ($last - $first) + strlen($needle);
        $str = substr($this->str, $first, $long);
        $no_keys = str_replace($needle, "", $str);
        return ($remove_keys) ? $no_keys : $str;
    }
   
    function render_regex($key='REGEX', $stack=array(), $use_pcre=USE_PCRE) {
        $originalstr = $this->str;
        $func = ($use_pcre) ? "get_regex" : "get_substr";
        $match = $this->$func($key, False);
        $this->str = $this->$func($key);
        $render = '';
        foreach($stack as $dict) $render .= $this->render($dict);
        return str_replace($match, $render, $originalstr);
    }

    function render_substr($key='REGEX', $stack=array()) {
        return $this->render_regex($key, $stack, False);
    }
 
    protected function set_dict($dict=array()) {
        $this->sanitize($dict);
        $keys = array_keys($dict);
        $values = array_values($dict);
        foreach($keys as &$key) {
            $key = "{{$key}}";
        }
        $this->dict = array_combine($keys, $values);
    }
   
    private function sanitize(&$dict) {
        foreach($dict as $key=>&$value) {
            if(is_array($value) or is_object($value)) {
                $value = print_r($value, True);
                if(strlen($value) > 100) {
                    $value = substr($value, 0, 100) . chr(10) . "(...)";
                    $value = nl2br($value);
                }
            }
        }
    }
   
    public function show($contenido='') {
        $tmpl = file_get_contents($this->filename);
        $dict = array("TITLE"=>$this->str, "CONTENIDO"=>$contenido);
        return Template($tmpl)->render($dict);
    }
}
 
 
# Función agregada para compatibilidad con PHP 5.3
function Template($str='') {
    return new Template($str);
}
 
 
# Alias para estilo por procedimientos
function template_render($str='', $dict=array()) {
    return Template($str)->render($dict);
}
 
function template_render_regex($str='', $key='REGEX', $dict=array()) {
    return Template($str)->render_regex($key, $dict);
}
?>
