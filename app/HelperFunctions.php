<?php
//GLOBAL FUNCTIONS
define('ROOTDIR', dirname(__DIR__, 4));
define('VIEWENGINE', '.php');

function getContext(){
	return stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
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
function Json($data, $options = [JSON_NUMERIC_CHECK] ){
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
