<?php
/**
 * @file
 *
 * @brief 
 * Tar
 *
 * @author Jonny <xjlicn@163.com>
 * @date	2017-01-08
 *
 * Copyright (c), 2017, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CTar
{
	public function __construct()
	{
	}
	
	public function CTar()
	{
		$this->__construct();
	}	
	
	public function compress($tarfile, $files, $dir=null)
	{
		!$dir && $dir = dirname($zipfile);
		
		$pd = new PharData($tarfile);
		foreach ($files as $k=>$v) {
			$pd->addFile($dir.DS.$v['name']);
		}		
		//$pd->buildFromDirectory("/path/to/contents");
		$res = $pd->compress(Phar::GZ);		
		return $res;
	}
	
	public function extract($zipfile, $tdir)
	{
		!is_dir($tdir) && mkdir($tdir);
		
		$phar = new PharData($zipfile);		
		//路径 要解压的文件 是否覆盖
		$res = $phar->extractTo($tdir, null, true);
		return $res;
	}
}
