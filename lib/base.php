<?php


/**
 * This is a comment.
 *
 * @file
 *
 * @brief 
 
 * base file
 *
 */

define('RMAGIC', 1 );

define('RPATH_LIB', dirname(__FILE__) );
define('RPATH_ROOT', dirname(RPATH_LIB) );
if (!defined('DS'))
	define( 'DS', DIRECTORY_SEPARATOR );
require_once( RPATH_LIB.DS.'defines.php');
require_once( RPATH_LIB.DS.'errors.php');
require_once( RPATH_LIB.DS.'factory.php');
require_once (RPATH_LIB.DS.'autoloader.php');
require_once (RPATH_LIB.DS.'common.php');
require_once (RPATH_LIB.DS.'filetype.php');

define('PHP_CHARSET', 'UTF-8');
define('OS_CHARSET', substr(PHP_OS, 0, 3) == 'WIN' ? 'GB2312' : 'UTF-8');


class RC
{
	public static function initPaths() 
	{
		$paths = RPATH_CLASS. PATH_SEPARATOR .
			RPATH_CLASS.DS."apps". PATH_SEPARATOR .
			RPATH_CLASS.DS."components".PATH_SEPARATOR .
			RPATH_CLASS.DS."modules".PATH_SEPARATOR .
			RPATH_CLASS.DS."models".PATH_SEPARATOR .
			RPATH_CLASS.DS."configs".PATH_SEPARATOR .
			get_include_path();
			
		set_include_path($paths);		
	}
	
	////////////////////////// Error/Exception/Shutdown register handler ///////////////////////////
	protected static function show_debug_info($exception)
	{
		$errstr = $exception->getMessage();
		
		$errfile = $exception->getFile();
		$errline = $exception->getLine();
		$errno = $exception->getCode();
		$errtrace = $exception->getTraceAsString();
		$trace = $exception->getTrace();
		
		// these are our templates
		$traceline = "#%s %s(%s): %s(%s)";		
		// alter your trace as you please, here		
		foreach ($trace as $key => $stackPoint) {
			// I'm converting arguments to their type
			// (prevents passwords from ever getting logged as anything other than 'string')
			$trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
		}
		
		// build your tracelines
		$result = array();
		foreach ($trace as $key => $stackPoint) {
			$result[] = sprintf(
					$traceline,
					$key,
					$stackPoint['file'],
					$stackPoint['line'],
					$stackPoint['function'],
					implode(', ', $stackPoint['args'])
					);
		}
		// trace always ends with {main}
		$result[] = '#' . ++$key . ' {main}';
		
		// write tracelines into main template
		$tracemsg = implode("\n", $result);
		
		rlog(RC_LOG_DEBUG, $errfile, $errline, $errno, $errstr, $errtrace, $tracemsg);
		
	}
	
	public static function show_exception_callback($exception)
	{
		
		self::show_debug_info($exception);
	}
	
	
	public static function show_error_callback($errno, $errstr, $errfile, $errline)
	{
		self::show_exception_callback(new CException($errno, $errstr, $errfile, $errline));
	}
	
	
	
	public static function show_shutdown_callback()
	{
		if (!is_null($error = error_get_last())) {
			self::show_exception_callback(new CException(
						$error['type'], $error['message'], $error['file'], $error['line']
						));
		}
	}
	
		
	
	public static function init() 
	{
		// register autoloader
		$loader = CAutoloader::GetInstance();
		spl_autoload_register(array($loader, 'load'));

		self::initPaths();				
		// Don't display errors and log them
		error_reporting(E_ALL^E_STRICT);
		error_reporting(E_ALL^E_NOTICE);		
		@ini_set('display_errors', 1);
		@ini_set('log_errors', 1);
		
		set_error_handler(array(__CLASS__, 'show_error_callback'), E_ERROR);
        set_exception_handler(array(__CLASS__, 'show_exception_callback'));
		register_shutdown_function(array(__CLASS__, 'show_shutdown_callback'));
		

		date_default_timezone_set('UTC');
		
		@set_time_limit(3600);
		@ini_set('max_execution_time', 3600);
		@ini_set('max_input_time', 3600);
		@ini_set('memory_limit', '512M');
		@ini_set('session.cookie_path', '/');
		
		//try to set the maximum filesize to 10G
		//php环境一般不给如此配置，城要修改php.ini才生效
		@ini_set('upload_max_filesize', '10G');
		@ini_set('post_max_size', '10G');
		@ini_set('file_uploads', '50');		
	}
	
	private static function handleLogin($request) 
	{
		return false;
	}
	
	protected static function handleAuthHeaders()
	{
		return false;		
	}
	
	/* 检查是否安装 */
	protected static function isInstalled()
	{
		$configuration = RPATH_CONFIG.DS."installed";
		if (file_exists($configuration)) 
			return true;
		else
			return false;		
	}
	
	
	protected static function render($mainapp, $options=array())
	{
		if (!self::isInstalled()) {
			$app = Factory::GetApplication('installation');
			if (!$app) {
				exit("error!");
			}	
			return $app->run();
		}		
		
		$mapp = Factory::GetApplication($mainapp, $options);	
		if (!$mapp) {
			exit("NOT Found APP '$mainapp' error!");
		}	
		return $mapp->run($options);
	}
	
	public static function run($mainapp='system', $options=array()) 
	{
		RC::render($mainapp, $options);	
	}	
}

RC::init();	
