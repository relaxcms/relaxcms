<?php

//error_reporting(E_ALL^E_STRICT);
//error_reporting(E_ALL^E_NOTICE);	
define('RPATH_BASE', dirname(__FILE__) );
define('APPNAME', 'system');
try {
	require_once ('../lib/base.php');
	RC::run(APPNAME);
} catch(CException $e) {	
	echo $e->errorMessage();
} catch(Exception $e) {
	echo $e->getMessage();
}

?>
