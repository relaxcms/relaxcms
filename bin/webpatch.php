<?php

/* 
 * 通过php <WWWDIR>/bin/webpatch.php $tgz 执行, 
 */

define('APPNAME', 'system');
define('RPATH_BASE', dirname(__FILE__) );
try {
	require_once (RPATH_BASE.'/../lib/base.php');
	$wp = Factory::GetUpgrade();
	$wp->webpatch($argv);	
} catch(CException $e) {	
	echo $e->errorMessage();
} catch(Exception $e) {
	echo $e->getMessage();
}
exit ;

?>
