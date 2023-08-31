<?php

//error_reporting(E_ALL^E_STRICT);
//error_reporting(E_ALL^E_NOTICE);	
define('RPATH_BASE', dirname(__FILE__) );
define('APPNAME', 'index');
try {
	require_once ('../lib/base.php');
	RC::run(APPNAME);
} catch(CException $e) {	
	echo $e->errorMessage();
} 

?>
