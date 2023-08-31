<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define ('FT_VIDEO', 0x1);
define ('FT_AUDIO', 0x2);
define ('FT_IMAGE', 0x4);
define ('FT_DOC',   0x8);
define ('FT_TAR',   0x10);
define ('FT_CODE',  0x20);
define ('FT_OTHER', 0xFF);


class CFileType 
{
	 /**
	 * 文件类型表
	 *
	 * @var mixed 
	 *
	 */
	static private $_ext2type = array(
		/* video */ 
		'asf' => array('id'=>1, 'type'=>1, 'typename'=>'video','mimetype'=>'video/x-ms-asf'), 
		'avi' => array('id'=>2, 'type'=>1, 'typename'=>'video','mimetype'=>'video/avi'), 
		'mov' => array('id'=>3, 'type'=>1, 'typename'=>'video','mimetype'=>'video/quicktime'), 
		'mpeg'=> array('id'=>4, 'type'=>1, 'typename'=>'video','mimetype'=>'video/mpeg'), 
		'mpg' => array('id'=>5, 'type'=>1, 'typename'=>'video','mimetype'=>'video/mpeg'), 
		'm2p' => array('id'=>6, 'type'=>1, 'typename'=>'video','mimetype'=>'video/mpeg'),
		'wmv' => array('id'=>7, 'type'=>1, 'typename'=>'video','mimetype'=>'video/x-ms-wmv'),
		'flv' => array('id'=>8, 'type'=>1, 'typename'=>'video','mimetype'=>'video/x-flv'), 
		'mp4' => array('id'=>9, 'type'=>1, 'typename'=>'video','mimetype'=>'video/mp4', 'h5v'=>true),
		'rmvb'=> array('id'=>10, 'type'=>1, 'typename'=>'video','mimetype'=>'video/x-pn-realvideo'),
		'mkv' => array('id'=>11, 'type'=>1, 'typename'=>'video','mimetype'=>'video/mkv'),
		
		/* audio */ 
		'ac3' => array('id'=>20, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/ac3'), 
		'aiff'=> array('id'=>21, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/x-aiff'), 
		'au'  => array('id'=>22, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/x-au'), 
		'mid' => array('id'=>23, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/midi'), 
		'midi'=> array('id'=>24, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/midi'), 
		'mp3' => array('id'=>25, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/mpeg3', 'h5v'=>true), 
		'm4a' => array('id'=>26, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/mpeg4', 'h5v'=>true), 
		'ogg' => array('id'=>27, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/ogg'), 
		'ra'  => array('id'=>28, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/ra'), 
		'rm'  => array('id'=>29, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/x-pn-realaudio'), 
		'wav' => array('id'=>30, 'type'=>2, 'typename'=>'audio','mimetype'=>'audio/wav'),
		
		/* image */ 
		'3ds' => array('id'=>41, 'type'=>4, 'typename'=>'image','mimetype'=>'image/3ds'), 
		'bmp' => array('id'=>42, 'type'=>4, 'typename'=>'image','mimetype'=>'image/bmp'), 
		'gif' => array('id'=>43, 'type'=>4, 'typename'=>'image','mimetype'=>'image/gif'), 
		'jpeg'=> array('id'=>44, 'type'=>4, 'typename'=>'image','mimetype'=>'image/jpeg'), 
		'jpg' => array('id'=>45, 'type'=>4, 'typename'=>'image','mimetype'=>'image/jpeg'), 
		'png' => array('id'=>46, 'type'=>4, 'typename'=>'image','mimetype'=>'image/png'), 
		'ppm' => array('id'=>47, 'type'=>4, 'typename'=>'image','mimetype'=>'image/ppm'), 
		'psd' => array('id'=>48, 'type'=>4, 'typename'=>'image','mimetype'=>'image/psd'), 
		'svg' => array('id'=>49, 'type'=>4, 'typename'=>'image','mimetype'=>'image/svg'), 
		'tif' => array('id'=>50, 'type'=>4, 'typename'=>'image','mimetype'=>'image/tif'),
		'tiff'=> array('id'=>51, 'type'=>4, 'typename'=>'image','mimetype'=>'image/tiff'), 
		'xcf' => array('id'=>52, 'type'=>4, 'typename'=>'image','mimetype'=>'image/xcf'), 
		'xpm' => array('id'=>53, 'type'=>4, 'typename'=>'image','mimetype'=>'image/xpm'),
		
		/* document */ 
		'doc' => array('id'=>80, 'type'=>8, 'typename'=>'document','mimetype'=>'application/msword'), 
		'docx'=> array('id'=>81, 'type'=>8, 'typename'=>'document','mimetype'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document'), 
		'kwd' => array('id'=>82, 'type'=>8, 'typename'=>'document','mimetype'=>'application/nd.kde.kword'), 
		'odt' => array('id'=>83, 'type'=>8, 'typename'=>'document','mimetype'=>'application/odt'), 
		'pdf' => array('id'=>84, 'type'=>8, 'typename'=>'document','mimetype'=>'application/pdf'),
		'rtf' => array('id'=>85, 'type'=>8, 'typename'=>'document','mimetype'=>'application/rtf'),
		'sdw' => array('id'=>86, 'type'=>8, 'typename'=>'document','mimetype'=>'application/sdw'),
		'qif' => array('id'=>87, 'type'=>8, 'typename'=>'document','mimetype'=>'application/qif'), 
		'tex' => array('id'=>88, 'type'=>8, 'typename'=>'document','mimetype'=>'application/tex'), 
		'txt' => array('id'=>89, 'type'=>8, 'typename'=>'document','mimetype'=>'application/txt'), 
		'ini' => array('id'=>90, 'type'=>8, 'typename'=>'document','mimetype'=>'application/ini'),
		'csv' => array('id'=>91, 'type'=>8, 'typename'=>'document','mimetype'=>'application/csv'),
		'ksp' => array('id'=>92, 'type'=>8, 'typename'=>'document','mimetype'=>'application/ksp'), 
		'ods' => array('id'=>93, 'type'=>8, 'typename'=>'document','mimetype'=>'application/ods'), 
		'sdc' => array('id'=>94, 'type'=>8, 'typename'=>'document','mimetype'=>'application/sdc'), 
		'xls' => array('id'=>95, 'type'=>8, 'typename'=>'document','mimetype'=>'application/xls'), 
		'xlsx'=> array('id'=>96, 'type'=>8, 'typename'=>'document','mimetype'=>'application/xlsx'),
		'conf'=> array('id'=>97, 'type'=>8, 'typename'=>'document','mimetype'=>'application/conf'),
		'cnf' => array('id'=>98, 'type'=>8, 'typename'=>'document','mimetype'=>'application/cnf'),
		'kpr' => array('id'=>99, 'type'=>8, 'typename'=>'presentation', 'mimetype'=>'applicakprtion/octet-stream'), 
		'odp' => array('id'=>100, 'type'=>8, 'typename'=>'presentation', 'mimetype'=>'application/octet-stream'), 
		'ppt' => array('id'=>101, 'type'=>8, 'typename'=>'presentation', 'mimetype'=>'application/octet-stream'), 
		'pptx'=> array('id'=>102, 'type'=>8, 'typename'=>'presentation', 'mimetype'=>'application/octet-stream'), 
		'sdd' => array('id'=>103, 'type'=>8, 'typename'=>'presentation', 'mimetype'=>'application/octet-stream'),
		
		
		/* ar */ 
		'ace'=> array('id'=>160, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/octet-stream'), 
		'arj'=> array('id'=>161, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/octet-stream'),
		'bz2'=> array('id'=>162, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/x-bzip2'),
		'cab'=> array('id'=>163, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/octet-stream'),
		'gz' => array('id'=>164, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/x-gzip'),
		'lha'=> array('id'=>165, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/octet-stream'),
		'rar'=> array('id'=>166, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/x-rar-compressed'),
		'tar'=> array('id'=>167, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/x-tar'),
		'zip'=> array('id'=>168, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/octet-stream'),
		'xz' => array('id'=>169, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/octet-stream'),
		'7z' => array('id'=>170, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/octet-stream'),
		'iso'=> array('id'=>171, 'type'=>16, 'typename'=>'arball', 'mimetype'=>'application/x-cd-image'),
		'deb'=> array('id'=>172, 'type'=>16, 'typename'=>'package','mimetype'=>'application/octet-stream'), 
		'prc'=> array('id'=>173, 'type'=>16, 'typename'=>'package','mimetype'=>'application/octet-stream'), 
		'rpm'=> array('id'=>174, 'type'=>16, 'typename'=>'package','mimetype'=>'application/octet-stream'),
		'bak'=> array('id'=>175, 'type'=>16, 'typename'=>'backup', 'mimetype'=>'application/octet-stream'),
		
		/* code */ 
		'c'    => array('id'=>321, 'type'=>32, 'typename'=>'c', 					'mimetype'=>'text/x-csrc'),
		'cc'   => array('id'=>322, 'type'=>32, 'typename'=>'c',          'mimetype'=>'text/x-csrc'),
		'cpp'  => array('id'=>323, 'type'=>32, 'typename'=>'c',          'mimetype'=>'text/x-c++src'),
		'cs'   => array('id'=>324, 'type'=>32, 'typename'=>'c',          'mimetype'=>'text/x-csharp'),
		'java' => array('id'=>325, 'type'=>32, 'typename'=>'code',       'mimetype'=>'text/x-java'),
		'jsp'  => array('id'=>326, 'type'=>32, 'typename'=>'code',       'mimetype'=>'application/x-jsp'),
		'js'   => array('id'=>327, 'type'=>32, 'typename'=>'code',       'mimetype'=>'text/javascript'),
		'php'  => array('id'=>328, 'type'=>32, 'typename'=>'code',       'mimetype'=>'text/x-php'),
		'psf'  => array('id'=>329, 'type'=>32, 'typename'=>'code',       'mimetype'=>'application/octet-stream'),
		'py'   => array('id'=>330, 'type'=>32, 'typename'=>'code',       'mimetype'=>'text/py'),
		'xml'  => array('id'=>331, 'type'=>32, 'typename'=>'code',       'mimetype'=>'text/xml'),
		'pdb'  => array('id'=>332, 'type'=>32, 'typename'=>'database',   'mimetype'=>'application/octet-stream'),
		'sql'  => array('id'=>333, 'type'=>32, 'typename'=>'database',   'mimetype'=>'text/sql'),
		'exe'  => array('id'=>334, 'type'=>32, 'typename'=>'executable', 'mimetype'=>'application/octet-stream'),
		'jar'  => array('id'=>335, 'type'=>32, 'typename'=>'executable', 'mimetype'=>'application/java-archive'),
		'h'    => array('id'=>336, 'type'=>32, 'typename'=>'h',          'mimetype'=>'text/x-csrc'),
		'htm'  => array('id'=>337, 'type'=>32, 'typename'=>'html',       'mimetype'=>'text/html'),
		'html' => array('id'=>338, 'type'=>32, 'typename'=>'html',       'mimetype'=>'text/html'),
		'xhtml'=> array('id'=>339, 'type'=>32, 'typename'=>'html',       'mimetype'=>'text/html'),
		'css'  => array('id'=>340, 'type'=>32, 'typename'=>'html',       'mimetype'=>'text/css'),
		'less' => array('id'=>341, 'type'=>32, 'typename'=>'html',       'mimetype'=>'text/x-less'),
		'sass' => array('id'=>342, 'type'=>32, 'typename'=>'html',       'mimetype'=>'text/x-sass'),
		'scss' => array('id'=>343, 'type'=>32, 'typename'=>'html',       'mimetype'=>'text/x-scss'),
		
		/* other */ 
		'afm'=> array('id'=>2551, 'type'=>255, 'typename'=>'font',     'mimetype'=>'application/octet-stream'),
		'pcf'=> array('id'=>2552, 'type'=>255, 'typename'=>'font',     'mimetype'=>'application/octet-stream'),
		'ttf'=> array('id'=>2553, 'type'=>255, 'typename'=>'font',     'mimetype'=>'application/octet-stream'),
		'eps'=> array('id'=>2554, 'type'=>255, 'typename'=>'print',    'mimetype'=>'application/octet-stream'),
		'ps' => array('id'=>2555, 'type'=>255, 'typename'=>'print',    'mimetype'=>'application/octet-stream'),
		'pgp'=> array('id'=>2556, 'type'=>255, 'typename'=>'security', 'mimetype'=>'application/octet-stream'),
		'mrp'=> array('id'=>2557, 'type'=>255, 'typename'=>'calc',     'mimetype'=>'application/octet-stream'),
		'ics'=> array('id'=>2558, 'type'=>255, 'typename'=>'calendar', 'mimetype'=>'application/octet-stream'),
		);
				
	static function ext2type($ext)
	{
		$ext = strtolower($ext);		
		if (!isset(self::$_ext2type[$ext]))
			return FT_OTHER;		
		return self::$_ext2type[$ext]['type'];
	}
	
	
	static function ext2mimetype($ext)
	{
		$ext = strtolower($ext);	
		if (!isset(self::$_ext2type[$ext]))
			return 'application/octet-stream';
		return self::$_ext2type[$ext]['mimetype'];
	}
	
	static function ext2typeid($ext)
	{
		$ext = strtolower($ext);	
		if (!isset(self::$_ext2type[$ext]))
			return 0;			
		return self::$_ext2type[$ext]['id'];
	}
			
	static function ext2tinfo($ext) 
	{
		$ext = strtolower($ext);
		return isset(self::$_ext2type[$ext]) ? self::$_ext2type[$ext]:null;
	}
	
	static function getIcon($ext) 
	{
		$ext = strtolower($ext);
		return isset(self::$_ext2type[$ext]) ? self::$_ext2type[$ext]['typename'] : 'unknown';
	}
		
	static function getTypeId($ext)
	{
		$ext = strtolower($ext);
		return isset(self::$_ext2type[$ext]) ? self::$_ext2type[$ext]['type'] : 0;
	}
	
	
	static function getMimetype($ext) 
	{
		$ext = strtolower($ext);
		return isset(self::$_ext2type[$ext]) ? self::$_ext2type[$ext]['mimetype'] : 'application/octet-stream';
	} 
	
	static function isImage($ext)
	{
		$ext = strtolower($ext);	
		return self::$_ext2type[$ext]['type'] == 4;
	}
	
	static function isVideo($ext)
	{
		$ext = strtolower($ext);	
		return self::$_ext2type[$ext]['type'] == 1;
	}	
}
