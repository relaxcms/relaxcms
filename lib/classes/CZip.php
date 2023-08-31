<?php
/**
 * @file
 *
 * @brief 
 * ZIP
 *
 * @author Jonny <xjlicn@163.com>
 * @date	2014-08-07
 *
 * Copyright (c), 2014, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class CZip
{
	public function __construct()
	{
	}
	
	public function CZip()
	{
		$this->__construct();
	}	
	
	public function compress($zipfile, $files, $dir=null)
	{
		!$dir && $dir = dirname($zipfile);
		$zip = new ZipArchive();
		if ($zip->open($zipfile, ZipArchive::CREATE) === TRUE) {
			foreach ($files as $k=>$v) {
				$zip->addFile($dir.DS.$v['name'], $v['name']);
			}
			$zip->close();
			return true;
		} else {
			return false;
		}
	}
		
	public function extract($zipfile, $tdir)
	{
		!is_dir($tdir) && mkdir($tdir);
		
		$zip = new ZipArchive();
		if ($zip->open($zipfile) === TRUE) {
			$zip->extractTo($tdir);
			$zip->close();
			return true;
		} else {
			return false;
		}
	}
	
	public function uncompress($zipfile, $tdir)
	{
		return $this->extract($zipfile, $tdir);
	}
}