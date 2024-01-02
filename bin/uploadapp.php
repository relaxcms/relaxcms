<?php

/* 
 * 通过php <WWWDIR>/bin/uploadapp.php <app.tar.gz> 执行, 
 */

define('APPNAME', 'system');
define('RPATH_BASE', dirname(__FILE__) );
require_once (RPATH_BASE.'/../lib/base.php');


$tgz = isset($argv[1])?$argv[1]:'relaxcms-0.8.5.151-update.tar.gz';
//apiurl
$apiurl = 'http://localhost/rc8/api';
$token = 'f8aa5c93345dbec7fadcbcea2fed30c7';
$secret = '47b76ce7e87078da959603ee11746457c0560367';

for ($i=1; $i<$argc; $i++) {
	$val = $argv[$i];
	switch ($val) {
		case '-a':
		case '--apiurl':
			$apiurl = $argv[++$i];
			break;
		case '-t':
		case '--token':
			$token = $argv[++$i];
			break;
		case '-s':
		case '--secret':
			$secret = $argv[++$i];
			break;
		default:
			if (file_exists($val))
				$tgz = $val;
			break;
	}
}


if (!$apiurl)
	exit("no apiurl!");
if (!$token)
	exit("no token!");
if (!$secret)
	exit("no secret!");

if (!file_exists($tgz))
	exit("no tgz '$tgz'!");

$tokenUrl = $apiurl.'/getToken';

$params = array();
$params['token'] = $token;
$params['ts'] = time();
$params['sign'] = sign($secret, $params);
$res = curlPOST($tokenUrl, $params);
$oparams = json_decode($res, true);
$ssid = $oparams['data'];
if (!$ssid) {
	exit("no token!\n");
}

//
$url = $apiurl.'/postLastVersion';
$params = array();
$params['ssid'] = $ssid;

$res = curlPOST($url, $params, $tgz);

var_dump($res);

exit;
