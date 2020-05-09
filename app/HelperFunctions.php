<?php
//GLOBAL FUNCTIONS
define('ROOTDIR', dirname(__DIR__, 4));
define('VIEWENGINE', '.php');

function getContext(){
	return stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
}

	function str_contains_all($haystack, array $needles) {
		
		$matches = 0;
		foreach ($needles as $needle) 
		{

			if ($needle && stristr( StripAccents($haystack), StripAccents($needle) )) {
				$matches++;
			}
		}

		return count($needles) == $matches ? true : false ;
	}

	function Contains(string $needle, array $haystack, string $tag = null, bool $exact = false)
        {
            
            $matches = array();
            if(!$exact){
                $queryPattern =  str_replace('+', '|', StripAccents($needle));
                foreach($haystack as $k=>$v) 
                {
			if($tag && str_contains_all($v[$tag], explode('+', $needle)) && preg_match_all("/$queryPattern/i", StripAccents($v[$tag]), $t))
			$matches[$k] =  array_merge($v, ['matches'=> count($t[0])]);
						
						
			if(!$tag && str_contains_all($v, explode('+', $needle)) && preg_match_all("/$queryPattern/i", StripAccents($v), $t)) 
			$matches[$k] =  array_merge($v, ['matches'=> count($t[0])]);
                    
                }
                usort($matches, function($a, $b){
                    return strcmp($b["matches"], $a["matches"]);
                });
            }
            else{
                $queryPattern =  str_replace('+', ' ', StripAccents($needle));
                foreach($haystack as $k=>$v) {
                    if(strtolower(StripAccents($queryPattern)) == strtolower(StripAccents($v[$tag]))) {
                        $matches[$k] =  $v;
                    }
                }
            }

            return $matches;

        }

//usage echo Mask("##.###.###/####-##",$cnpj);
function StringMask( $mask, $str, $depth = 0 ){

	if(!$str) return NULL;

    $str = str_replace(" ","",$str);
	$strCount =  strlen( preg_replace("/[^A-Za-z0-9#!?]/", '', $mask)) - strlen($str);
	
	for ($i=$strCount; $i > 0; $i--) { 
		$str = $depth . $str;
	}

    for( $i=0; $i<strlen($str); $i++){

		$mask[strpos($mask,"#")] = $str[$i];
	}

    return $mask;

}

function headers() {
    $headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}


function restrict($session, $key){
	session_start();
	session_write_close();
	$header = headers();
	$key = ucwords(strtolower($key));

    if (!array_key_exists($key, $header)) die(header('HTTP/1.0 400 REQUEST AUTHORIZATION ONLY WORKS IN APP'));
    if ($_SESSION[$session] !== $header[$key]) die(header('HTTP/1.0 400 REQUEST AUTHORIZATION FAIL'));
}
/**
 * generate a json response view
 * 
 * options Bitmask consistindo de 
 * JSON_HEX_QUOT, 
 * JSON_HEX_TAG, 
 * JSON_HEX_AMP, 
 * JSON_HEX_APOS, 
 * JSON_NUMERIC_CHECK, 
 * JSON_PRETTY_PRINT, 
 * JSON_UNESCAPED_SLASHES, 
 * JSON_FORCE_OBJECT, 
 * JSON_UNESCAPED_UNICODE. 
 * O comportamento destas constantes é descrito na página de constantes https://www.php.net/manual/pt_BR/json.constants.php.
 */
function Json($data, $options = [JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK] ){
	header("Content-type: application/json; charset=utf-8");
	return json_encode($data, ...$options);
}
/**
 * generate html minified structure
 * 
 * set views directory like Views/{Namespace}/{Object}+{Method} name for default,
 * set $buffer to file manual
 */
 function View($buffer = null)
 {
	header('Content-Type: text/html; charset=utf-8');

	if(!$buffer)
		$buffer =  "~\\views\\".Compiler\App\Art::$ControlPath;
	
	$originalPath = str_replace('~', ROOTDIR, $buffer);

	if(!is_file($originalPath.VIEWENGINE )){
		echo "$originalPath não é um caminho válido";
		exit();
	}

    $search = array(
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/' // Remove HTML comments
    );

    $replace = array(
        '>',
        '<',
        '\\1',
        ''
    );

	require_once( str_replace('~', ROOTDIR, $buffer) .VIEWENGINE);

	exit();

}

function Post($isObject = true){
	return json_decode(file_get_contents('php://input'), $isObject);
}

		/**
	* Verifica se uma string é UTF-8
	* @param string $string A string que será verificada
	* @return boolean
	*/
	function is_utf8( $string ){
		return preg_match( '%^(?:
			 [\x09\x0A\x0D\x20-\x7E]
			| [\xC2-\xDF][\x80-\xBF]
			| \xE0[\xA0-\xBF][\x80-\xBF]
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}
			| \xED[\x80-\x9F][\x80-\xBF]
			| \xF0[\x90-\xBF][\x80-\xBF]{2}
			| [\xF1-\xF3][\x80-\xBF]{3}
			| \xF4[\x80-\x8F][\x80-\xBF]{2}
			)*$%xs',
			$string
		);
	}
	/**
	* Remove os acentos de uma string
	* @param string $string
	* @return string
	*/
	function StripAccents( $string ){
		return preg_replace(
			array(
			//Maiúsculos
			'/\xc3[\x80-\x85]/', '/\xc3\x87/', '/\xc3[\x88-\x8b]/', '/\xc3[\x8c-\x8f]/', '/\xc3([\x92-\x96]|\x98)/', '/\xc3[\x99-\x9c]/',
			//Minúsculos
			'/\xc3[\xa0-\xa5]/', '/\xc3\xa7/', '/\xc3[\xa8-\xab]/', '/\xc3[\xac-\xaf]/', '/\xc3([\xb2-\xb6]|\xb8)/', '/\xc3[\xb9-\xbc]/', ),
		str_split( 'ACEIOUaceiou' , 1 ),
		is_utf8( $string ) ? $string : utf8_encode( $string )
	    );
    	}


function ArrayString(array $string)
{
	$key = key($string);
	return array($key => strval($string[$key]));
}
function ArrayToString(array $string)
{
	$key = key($string);
	return strval($string[$key]);
}
function string(array $string)
{
	$key = key($string);
	return strval($string[$key]);
}

function clearstring(array $string)
{
	$key = key($string);
	$replace = array('/','-','.',',');  

	return  strval( str_replace($replace, '', $string[$key]) );
}

function int(array $string)
{
	$key = key($string);
	return intval($string[$key]);
}
function first($v){
	return reset($v);	
}
function firstOrFalse($v , $key = 0){
	if(is_array($v))
	return (array_key_exists($key, $v)) ? $v[$key] : false;	
	
	if(is_object($v))
	return (property_exists($key, $v)) ? $v->{$key} : false;	
		
	return false;

}
function last($v){
	return (is_array($v) || is_object($v))? end($v) : false;	
}

function DateTime(string $date, bool $throwException = false){
	$date = str_replace('-', '', $date);
	$date = str_replace('/', '-', $date);
	$time = strtotime($date);
	if ($time)
	  $new_date = date('Y-m-d', $time);
	  else {
		if($throwException === true){
			header('http/1.0 400 invalid variable');			
			die("$date is invalid date");
		}
		else
		$new_date = NULL;
	}

	return $new_date;
}

function ArrayDateTime(array $date, bool $throwException = false)
{

	$key = key($date);

	$date = str_replace('-', '',  $date[$key]);
	$date = str_replace('/', '-', $date);
	$time = strtotime($date);
	if ($time)
	  $new_date = date('Y-m-d', $time);
	else {
		if($throwException === true){
			header('http/1.0 400 invalid variable');
			die("$date as $key is invalid date");
		}
		else
		$new_date = NULL;
	}

	return array($key => $new_date);
}
function ArrayToDateTime(array $date, bool $throwException = false)
{

	$key = key($date);

	$date = str_replace('-', '',  $date[$key]);
	$date = str_replace('/', '-', $date);
	$time = strtotime($date);
	if ($time)
	  $new_date = date('Y-m-d', $time);
	  else {
		if($throwException === true){
			header('http/1.0 400 invalid variable');
			die("$date as $key is invalid date");
		}
		else
		$new_date = NULL;
	}

	return $new_date;
}
