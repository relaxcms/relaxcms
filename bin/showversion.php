<?php

define('RPATH_BASE', dirname(__FILE__) );
define('RPATH_ROOT', dirname(RPATH_BASE) );
require_once (RPATH_ROOT.'/lib/version.php');
echo SYS_VERSION;
exit;