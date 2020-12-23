<?php
//GLOBAL FUNCTIONS
define('ROOTDIR', dirname(__DIR__, 4));
define('VIEWENGINE', '.php');

function getContext(){
	return stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
}

function kill(int $status, string $message){
	http_response_code($status);
	echo Json(['message' => $message]);
	die();
}


function HttpPost($url, $data = [])
{

	$json = json_encode($data);
	$curl = curl_init($url);

	curl_setopt_array($curl, [
		CURLOPT_POST => true,
		CURLOPT_HEADER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POSTFIELDS => $json,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($json)
		),
	]);

	$response = curl_exec($curl);
	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	curl_close($curl);

	return [
		'status' => $httpcode,
		'data' => json_decode($response)
	];
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
                    if(strtolower(StripAccents($queryPattern)) == strtolower( $tag ? StripAccents($v[$tag]) : StripAccents($v)  )) {
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
	
	if($strCount < 0) return $str;
	
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
		return file_get_contents( 'php://input' )
	   		? json_decode( file_get_contents( 'php://input' ), $isObject )
	   		: $_POST;
	}

	function FromBody($key){
		$fromBody =  file_get_contents( 'php://input' );
		if($fromBody)
		return array_key_exists($key, json_decode( $fromBody, true ))   
				? json_decode( $fromBody, true )[$key]
				: die(http_response_code(422));
		else 
		return array_key_exists($key, $_POST) 
			   		? $_POST[$key]
					: die(http_response_code(422));
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

/**
 * Receives format 
 * dd/mm/yyyyThh:mm:ss 
 * dd/mm/yyyy hh:mm:ss 

 * return Y-m-d H:i:s or null if invalid date
 */
function DateTime2(string $date, string $format = 'Y-m-d H:i:s'){

        if(strtotime($date)){
            $date = str_replace('/', '-', $date);
            return date($format, strtotime($date));
        }


	$hour = '00';

	if(strstr($date, ' ') || strstr($date, 'T'))
	[$date, $hour] = preg_split('/[\s|T]/', $date);

	$hour = StringMask('##:##:##', str_replace(':', '', $hour));

	$date = implode('-', array_reverse(preg_split("/(-|\/)/", $date)));
	$date = Date($format, strtotime("$date $hour"));

	return (bool) strtotime($date) ? $date : null;

}

    function Monetizeme($money){
        // ['1,10 USD', 1.10],
        // ['1 000 000.00', 1000000.0],
        // ['$1 000 000.21', 1000000.21],
        // ['£1.10', 1.10],
        // ['$123 456 789', 123456789.0],
        // ['$123,456,789.12', 123456789.12],
        // ['$123 456 789,12', 123456789.12],
        // ['1.10', 1.1],
        // [',,,,.10', .1],
        // ['1.000', 1000.0],
        // ['1,000', 1000.0]

        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);
    
        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;
    
        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);
    
        return (float) str_replace(',', '.', $removedThousandSeparator);
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
