<?php

if (!function_exists('stream_resolve_include_path')) 
{
	/**
	 * Resolve filename against the include path.
	 *
	 * stream_resolve_include_path was introduced in PHP 5.3.2. This is kinda a PHP_Compat layer for those not using that version.
	 *
	 * @param Integer $length
	 * @return String
	 * @access public
	 */
	function stream_resolve_include_path($filename)
	{
		$paths = PATH_SEPARATOR == ':' ?
			preg_split('#(?<!phar):#', get_include_path()) :
			explode(PATH_SEPARATOR, get_include_path());
		foreach ($paths as $prefix) {
			$file = $prefix . DS . $filename;
			if (file_exists($file)) {
				return $file;
			}
		}
		
		return false;
	}
}

class CAutoloader
{
	protected $_classpaths = array();
	
	public function __construct() 
	{		
	}
		
	static function GetInstance()
	{
		static $instance;		
		if(!is_object($instance))	{
			$instance = new CAutoloader();			
		}
		return $instance;
	}
	
	public function findClass($classname) 
	{
		if (array_key_exists($classname, $this->_classpaths)) 
			return $this->_classpaths[$classname];			
		return $classname.'.php';
	}
	
	public function load($classname) 
	{
		if (class_exists($classname, false)) {
			return false;
		}
		
		$classfile = $this->findClass($classname);
		$fullPath = stream_resolve_include_path($classfile);
		if (!$fullPath) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"no class file '$classfile'");
			return false;
		}
		require_once $fullPath;
	}	
}

?>