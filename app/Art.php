<?php 
namespace Compiler\App;

interface IArt 
{
	public function UseMvc() : void;
	public function GetPath() : array;
	public function DefaultRoute(string $namespace, string $ontrue, bool $bool = true, string $onfalse = '', string $defaultfn = 'Index') : void;

}

require_once('HelperFunctions.php');
/**
 * MVC Route Configuration to call composer object by url params.
 * all classes must start with capital letters
 */
class Art implements IArt
{
	public $Path;
	public static $ControlPath;

    function __construct()
    {
        $this->Path = explode('/', array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : (array_key_exists('ORIG_PATH_INFO', $_SERVER) ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['REQUEST_URI']));
	}
	
	public function UseMvc() : void
	{
		
		$_PATH = $this->GetPath();
		#separate into path and params
		$_ARGS = array_slice($_PATH, 3);
		#preserve full post to use in functions
		$_POST = Post();
		#add the same POSTS on argments for function
		if($_POST)
		foreach($_POST as $key => $value) array_push($_ARGS,[$key => $value]);		
		# generate {namespace}\\{class} 
		$namespace = implode('\\', array_slice($_PATH,0,2)) . 'Controller';
		# generate {method}
		$callmethod =  implode('\\', array_slice($_PATH,2,1));
		#the route now can be callable class->method, only accept Controller class in name
		if( method_exists($namespace,$callmethod) )
			$this->execute($namespace,$callmethod, $_ARGS);
	}
	/**
	 * returns an array that can be used as 
	 * [ 0 => namespace, 1 => class, 2 => method, ...params ]
	 */
	public function GetPath() : array
	{
		#initiaize array to return
		$temp = [];
		#convert to small case, first char to upper, accept w,-,d,
		foreach($this->Path as $key => $p)
		if($p) 
			if($key < 4)
			# turns dash to upercase first leetter, example: update-single => UpdateSingle
				$temp[] = preg_replace("/[^A-Za-z0-9!]/",'', implode(array_map('ucfirst', explode("-", $p))));
			 else
			# clear the rest to use as args
				$temp[] =  preg_replace("/[^A-Za-z0-9-._ !]/",'', urldecode($p) );

		return $temp;
		
	}

	/**
	 * execute a function receiving {namespace}\\{class}, {methodName} with optional arguments as array
	 */
	public function execute(string $namespace, string $callmethod , array $_ARGS = []) : void
	{
		self::$ControlPath = str_replace('Controller', '', $namespace.$callmethod);
		 print_r(call_user_func_array( array( new $namespace, $callmethod), $_ARGS ));
		#finally ends
		die();
	}

	/**
	 * add a default route on load app
	 */
	public function DefaultRoute(string $namespace, string $ontrue, bool $bool = true, string $onfalse = '', string $defaultfn = 'Index') : void
	{

		if($this->GetPath()[0] == $namespace && method_exists($ontrue, $defaultfn) ){
			if ($bool)
			$this->execute($ontrue, $defaultfn);
			else
			$this->execute($onfalse, $defaultfn);
		}

	}
}