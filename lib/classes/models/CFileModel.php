<?php

/**
 * @file
 *
 * @brief 
 * 
 * 文件基础模型类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define ('FF_CHECKED', 0x1);
define ('FF_RELEASE', 0x2);
define ('FF_DOWNLOAD', 0x4);
define ('FF_READONLY', 0x8);
define ('FF_SHARE', 0x10);
define ('FF_CONVERTED', 0x20);
define ('FF_SNAPPED', 0x40);
define ('FF_MYFLAGS', FF_RELEASE|FF_DOWNLOAD|FF_READONLY|FF_SHARE);

class CFileModel extends CTableModel
{	
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CFileModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	/* ============================================================================
	* init functions
	* 
	* ===========================================================================*/
	protected function _initFieldEx(&$f)
	{
		
		switch ($f['name']) {
			
			case 'filename':
				$f['searchable'] = false;
				break;
			case 'type':
				$f['show'] = false;
			case 'status':
				$f['input_type'] = 'selector';
				$f['sortable'] = true;
				//$f['edit'] = false;
				break;
			case 'cuid':
				$f['readonly'] = true;
				$f['edit'] = false;
				$f['show'] = false;
				$f['input_type'] = 'UID';
				break;
			case 'uid':
				$f['input_type'] = 'UID';
				$f['edit'] = false;
				$f['show'] = false;
				break;
			case 'ctime':
				$f['show'] = false;
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				$f['sortable'] = true;
				break;
			case 'flags':
				$f['input_type'] = 'multicheckbox';
				$f['show'] = false;
				break;
			case 'description':
				//$f['input_type'] = "ckeditorsimple";
				$f['show'] = false;
				break;
			case 'path':
				$f['sort'] = 1;
			case 'path':
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'downloads':
			case 'hits':
				$f['show'] = false;
			case 'size':
				//$f['input_type'] = "SIZE";
				$f['sortable'] = true;
			case 'uses':
				$f['edit'] = false;
				break;
			case 'extname':
				$f['searchable'] = false;
			case 'fileid':
			case 'convert_id':
			case 'snap_id':
			case 'isdir':
			case 'mtime':
			case 'mimetype':
			case 'width':
			case 'height':
			case 'gid':
			case 'oid':
			case 'mid':
			case 'sid':
			case 'is_default':
			case 'model':
			case 'pid':
				$f['edit'] = false;
			case 'taxis':
				$f['show'] = false;			
			default:
				break;
		}
		
		return true;
	}
	
	/* ============================================================================
	* Utility Helper functions
	* 
	* ===========================================================================*/
	
	
	/**
	 * is_need_convert 是否需要转码（非H5 MP4格式的不能直接播放，须转码后才可在支持H5的浏览器上播放）
	 *
	 * @param mixed $fileinfo 文件信息
	 * @return mixed 需要转码: true, 否则：false
	 *
	 */
	protected function is_need_convert($fileinfo)
	{
		$tinfo = CFileType::ext2tinfo($fileinfo['extname']);
		if (!$tinfo)
			return false;
		if ($tinfo['type'] != FT_VIDEO && $tinfo['type'] != FT_AUDIO)
			return false;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $tinfo);
						
		return !isset($tinfo['h5v']) || !is_h5mp4($fileinfo['opath']);
	}
	
	protected function ext2type($extname)
	{
		return CFileType::ext2type($extname);
	}
	
	protected function ext2mimetype($extname)
	{
		return CFileType::ext2mimetype($extname);
	}
	
	protected function ext2typeid($extname)
	{
		return CFileType::ext2typeid($extname);
	}
	
	/* ============================================================================
	 * UI functions
	 * 
	 * ===========================================================================*/
	
	
	public function get($id)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN....");
		$res = parent::get($id);
		if (!$res) {
			return false;
		}
		
		//查询存储
		$s = Factory::GetModel('storage');
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN1");
		$si = $s->get($res['sid']);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN2", $si);
				 
		$res['opath'] = $si['mountdir'].DS.$res['path'];
		if ($res['type'] == FT_AUDIO || $res['type'] == FT_VIDEO)
			$res['playurl'] = $si['vodrooturl'].'/'.$res['path'];
			
		$res['downloadUrl'] = $si['webpath'].'/'.$res['path'];
		
		return $res;
	}
	
	public function getFileInfo($id, &$ioparams = array())
	{
		$fileinfo = $this->getForView($id, $ioparams);
		if (!$fileinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		$row = $ioparams['row'];

		$fileinfo['type'] = $row['type'];
		$fileinfo['status'] = $row['status'];
		$fileinfo['flags'] = $row['flags'];
		$fileinfo['size'] = $row['size'];
		
		$type = $fileinfo['type'];
		$status = $fileinfo['status'];
		$size = $fileinfo['size'];
		
		if ($type == FT_VIDEO && $fileinfo['snap_id'] > 0) {
		}
		
		return $fileinfo;
	}
	
	/*
	1/202109/14_ba99f7facf1b4a09201204f5245f212b.jpg
	*/
	public function getFileInfoByUrl($url)
	{
		$tdb = explode('/', $url);
		$nr = count($tdb);
		for ($i=$nr-1; $i>0; $i--) {
			$val = $tdb[$i];
			$pos = strpos($val, '_');	
			if ($pos !== false) { //1/202109/14_ba99f7facf1b4a09201204f5245f212b.jpg
				$id = substr($val, 0, $pos);
				$fileinfo = $this->get($id);
				if ($fileinfo) {
					return $fileinfo;
				}				
			} else {// eg: /rc8/f/42/a.tar.gz
				if ($val == 'f' || $val == 'file') {
					$id = $tdb[$i+1];
					$fileinfo = $this->get($id);
					if ($fileinfo) {
						return $fileinfo;
					}	
				}
			}		
		}
		return false;
	}
	
	
	public function getImagePath($id)
	{
		$fileinfo = $this->get($id);
		
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no file of id '$id'!");
			return false;
		}
		
		if ($fileinfo['type'] != FT_IMAGE) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "not img file of id '$id'!");
			return false;
		}
		
		return $fileinfo['opath'];
	}
	
	public function getPostions($pid)
	{
		$positions = array();
		
		$pdb = array();
		if (($res = $this->getParents($pid, $pdb))) {
			
			foreach ($pdb as $key => $v2) {
				$p = array('name'=>$v2['name'], 'id'=>$v2['id']);
				array_unshift($positions, $p);
			}	
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$positions='.$positions, $pdb);
		
		return $positions;
	}
	
	
	public function getPlayUrl($fileinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN getPlayUrl 1 ", $fileinfo);
		
		$m = Factory::GetModel('storage');
		$storageinfo = $m->get($fileinfo['sid']);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN getPlayUrl 2 ", $storageinfo);
		
		$playurl = $storageinfo['vodrooturl'].'/'.$fileinfo['path'];
		
		if ($fileinfo['_status'] == 4 && $fileinfo['convert_id'] > 0) { //被转码完成的VIDEO
			$convert_id = $fileinfo['convert_id'];
			$convertfileinfo = $this->get($convert_id);
			if ($convertfileinfo) {
				$playurl = $storageinfo['vodrooturl'].'/'.$convertfileinfo['path'];
			} 
		}		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT getPlayUrl ............");
		return $playurl;		
	}
	
	public function formatForVideoPlay(&$row, $storageinfo, $ioparams=array())
	{
		$playurl = $storageinfo['vodrooturl'].'/'.$row['path'];
		if ($row['_status'] == 4 && $row['convert_id'] > 0) { //被转码完成的VIDEO
			$convert_id = $row['convert_id'];
			$convertfileinfo = $this->get($convert_id);
			if ($convertfileinfo) {
				$playurl = $storageinfo['vodrooturl'].'/'.$convertfileinfo['path'];
			} 
		}
		
		$row['playurl'] = $playurl;				
		$row['extinfo'] = " <a href='$playurl' data-url='$playurl' class='videobox' data-id='".$row['id']."' title='test video play'><i class='fa fa-film'></i></a>";
		
		if ($row['snap_id'] > 0) {
			$snapinfo = $this->get($row['snap_id']);
			if ($snapinfo) {
				//previewUrl
				$row['previewUrl'] = $ioparams['_webroot'].'/f/'.$snapinfo['id'].'/'.$snapinfo['name'];
			}
		}
	}
	
	
	
	public function formatForAudioPlay(&$row, $storageinfo, $ioparams=array())
	{
		$playurl = $storageinfo['vodrooturl'].'/'.$row['path'];
		
		$row['playurl'] = $playurl;		
		
		$row['extinfo'] = " <a href='$playurl' data-url='$playurl' class='audiobox' data-id='".$row['id']."'><i class='fa fa-music'></i></a>";
	}
	
	public function formatForImageUrl(&$row, $storageinfo, $ioparams=array())
	{
		//
		$row['extinfo'] = "<i class='fa fa-image'></i>";
		if ($row['snap_id'] > 0) { //是从视频中截取来的图			
			$snapinfo = $this->get($row['snap_id']);
			if ($snapinfo) {				
				$playurl = $storageinfo['vodrooturl'].'/'.$snapinfo['path'];
				if ($snapinfo['status'] == 4 && $snapinfo['convert_id'] > 0) { //被转码完成的VIDEO
					$convert_id = $snapinfo['convert_id'];
					$convertfileinfo = $this->get($convert_id);
					if ($convertfileinfo) {
						$playurl = $storageinfo['vodrooturl'].'/'.$convertfileinfo['path'];
					} 
				}
				$row['playurl'] = $playurl;				
			}
		}
	}	
	
	
	public function formatForViewUrl(&$row, $ioparams)
	{
		$fname = $row['id'].'/'.$row['name'];
		
		$fbase = $ioparams['_webroot'].'/f';
		
		//url
		$row['url'] = $fbase.'/'.$fname;
		
		//preview
		$row['previewUrl'] = $fbase.'/preview/'.$fname;
		$row['lpreviewUrl'] = $fbase.'/lpreview/'.$fname;
		$row['spreviewUrl'] = $fbase.'/spreview/'.$fname;
		
		$row['downloadUrl'] = $fbase.'/download/'.$fname;
		$row['shareUrl'] = $ioparams['_weburl'].'/f/'.$fname;
		
		return true;
	}
		
	
	public function formatForView(&$row, &$ioparams = array())
	{
		$type = $row['type'];
		$status = $row['status'];
		$size = $row['size'];
		
		$m = Factory::GetModel('storage');
		$storageinfo = $m->get($row['sid']);
		
		
		parent::formatForView($row, $ioparams);
		
		//title
		$row['title'] = $row['name'];
		
		//status
		$row['status'] = $this->formatLabelColorForView($status, $row['status']);
				
		//icon
		if ($row['isdir']) {
			$row['icon'] = $ioparams['_theroot']."/global/img/filetypes/dir.gif";	
		} else {
			$row['icon'] = $ioparams['_theroot']."/global/img/filetypes/".$row['extname'].".gif";
		}
		
		//url
		$this->formatForViewUrl($row, $ioparams);
		
		
		//default extinfo
		$row['extinfo'] = "";
		
		switch($type) {
			case FT_VIDEO:
				$this->formatForVideoPlay($row, $storageinfo, $ioparams);
				break;
			case FT_AUDIO:
				$this->formatForAudioPlay($row, $storageinfo, $ioparams);
				break;
			case FT_IMAGE:
				$this->formatForImageUrl($row, $storageinfo, $ioparams);
				break;
			default:
				break;
		}
			
		
		
		$row['summary'] = $row['description'];
		
		//mimetype
		$row['mimetypename'] = $this->ext2mimetype($row['extname']);
		
		
		return true;
	}
	
	
	public function selectForView(&$params=array(), &$ioparams=array())
	{
		$data = $this->select($params, $params);	
		foreach ($params['rows'] as $key=>&$v) {
			$this->formatForView($v, $ioparams);
		}
		
		return $data;
	}
	
	
	public function getSubDir($pid, $params=array(), &$ioparams=array())
	{
		$params['pid'] = $pid;		
		$params['isdir'] = 1;		
		
		$rows = $this->select($params, $ioparams);
		
		return $rows;
	}
	
	
	
	public function hasChildren($id)
	{
		
		$cdb = $this->getOne(array('pid'=>$id));
		return !!$cdb;
	}
	
	protected function deleteFile($fileinfo)
	{
		$res = @unlink($fileinfo['opath']);
		if (!$res) 
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call unlink failed!opath='".$fileinfo['opath']."'");
		
		return true;
	}	
	
	protected function deletePreview($fileinfo)
	{
		//s_rmdir($this->_previewdir);	
		
		//del: <id>_*.*
		$id = $fileinfo['id'];
		
		foreach (glob(RPATH_PREVIEW.DS.$id.'_*') as $filename) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "del preview '$filename'");
			unlink($filename);
		}	
	}
	
	
	protected function deleteUpdateStorageSpace($fileinfo)
	{
		if ($fileinfo['isdir'])
			return false;
		
		$delta = - $fileinfo['size'];
		if ($delta >= 0) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid delta! delta=$delta");	
			return false;
		}
		
		//存储id
		$sid = $fileinfo['sid'];		
		//上传者
		$uid = $fileinfo['uid'];
				
		$m = Factory::GetModel('storage');
		$sinfo = $m->getUserStorageInfo($sid, $uid);
		if (!$sinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no user storage info! sid=$sid, uid=$uid");	
			return false;
		}
		
		$m2 = Factory::GetModel('storage_dispatch'); 
		$m2->updateUsedBy($delta, $sid, $sinfo['oid']);
		
		return false;
	}
	
	
	protected function delOne($id)
	{
		$fileinfo = $this->get($id);
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$id'");
			return false;
		}
		
		//检查child
		if ($this->hasChildren($id)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "file has children of id '$id'!");
			return false;	
		}
		
		//$ioparams['fileinfo'] = $fileinfo;	
		
		//查询引用
		/*if ($fileinfo['used'] > 0) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "file '$id' is busy!");
			return false;	
		}*/
		//
		
		//检查文件是否被发布
		/*$res = $this->deleteFile2Org($fileinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call deleteFile2Org failed!");
			return false;
		}*/
		
		$fid = intval($fileinfo['id']);
		$this->deletePreview($fileinfo);						
		$this->deleteFile($fileinfo);
		
		$res = parent::del($id);		
		if ($res) {
			//$this->deleteUpdateStorageSpace($fileinfo);
		}
		
		return $res;
	}
	
	
	/** 删除文件 */
	public function del($ids, &$ioparams=array())
	{
		if (!$ids) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$ids' failed!");
			return false;
		}
		
		if (!is_array($ids)) 
			$ids = explode(',', $ids);
		
		foreach ($ids as $key=>$id) {						
			$res = $this->delOne($id);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "delete file '$id' failed!");
				break;
			}
		}
		
		return $res;
	}
	
	
	public function delByUrl($url)
	{
		$res = false;
		$fileinfo = $this->getFileInfoByUrl($url);
		if ($fileinfo) {
			$res = $this->delOne($fileinfo['id']);
		}		
		return $res;
	}
	
	/* ============================================================================
	 * Preview functions
	 * 
	 * ===========================================================================*/
	
	
	protected function getVideoPreview($src, $dst, $width, $height, &$mimetype)
	{
		//ffmpeg
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "in getVideoPreview");	
		
		$infile = $src;
		
		$dst = $dst.".jpg";		
		$mimetype = "image/jpeg";
		
		if (file_exists($dst))
			return $dst;
		
		//ffmpeg -i input.mp4 -ss 00:00:20 -t 1 -r 1 -q:v 2 -f image2 pic-%03d.jpeg
		//mp4 : 00:00:02
		$bindir = '';// RPATH_SHELL;
		$ffmpegcmdline = "ffmpeg -v quiet -i \"$infile\" -y -f image2 -ss 1 -t 1 -s $width".'x'."$height $dst";
		//rlog($ffmpegcmdline);
		
		if (($res = system($ffmpegcmdline)) != 0) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call system('$ffmpegcmdline') failed!res=$res");
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "out getVideoPreview");		
		return $dst;
	}
	
	
	protected function getOtherFilePreview($fileinfo, $src, $dst, $width, $height, &$mimetype)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "in getOtherFilePreview");
		
		$extname = s_fileext($src);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $extname);
		if ($fileinfo['type'] == FT_VIDEO) {
			return $this->getVideoPreview($src, $dst, $width, $height, $mimetype);
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'isdir='.$fileinfo['isdir']);
		
		if ($fileinfo['isdir']) {
			$mimetype = "image/png";
			$dst = RPATH_THEME.DS."global/img/filetypes".DS."dir.png";
		}  else {
			$mimetype = "image/gif";
			$dst = RPATH_THEME.DS."global/img/filetypes".DS.$extname.".gif";
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT getOtherFilePreview");
		return $dst;
	}	
	
	/**
	 * 缩放图片
	 *
	 * 
	 * @param mixed $src This is a description
	 * @param mixed $dst This is a description
	 * @param mixed $width This is a description
	 * @param mixed $height This is a description
	 * @param mixed $params This is a description
	 * @param mixed $overwrite This is a description
	 * @return mixed This is the return value description
	 *
	 */

	public function resizeImage($src, $dst, $width, $height, &$params=array(), $overwrite = false)
	{ 
		$params = array();
		
		$szs = @getimagesize($src);
		if (!$szs) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call getimagesize filed! szs=$szs, src='$src'");
			return false;
		}			
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__,$szs, $src);
		list($orig_width, $orig_height, $bigType) = $szs;
		//$mimetype = $szs['mime'];
		
		$extname = s_extname($dst);		
		
		switch ($bigType) {
			case 1: 
				$extname = "gif";
				break;	 
			case 2: 
				$extname = "jpg";
				break;	 
			case 3: 
				$extname = "png";
				break;
			default:
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknown bigtype '$bigType'!");
				return false;
		}
		
		$dst .= '.'.$extname;
		
		$orig_width = intval($orig_width);
		$orig_height = intval($orig_height);
		
		$params['orig_width'] = $orig_width;
		$params['orig_height'] = $orig_height;
		$params['dst'] = $dst;
		$params['extname'] = $extname;
		
		if (file_exists($dst) && !$overwrite) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__,__FUNCTION__, "WARNING: dst '$dst' exists!");
			return true;
		}
		
		if ($width >= $orig_width && $height >= $orig_height && !$overwrite) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING Not need create preview for '$dst'");
			$params['dst'] = $src;
			return true;
		}
		
		$new_width = intval($orig_width * $height/$orig_height); 
		$new_height = intval($orig_height * $width / $orig_width); 		
		if ($width > $new_width)
			$width = $new_width;
		if ($height > $new_height)
			$height = $new_height;
		
		
		// Select the format of the new image
		switch ( $bigType )
		{
			case 1: 
				$im = @imagecreatefromgif($src);
				break;			
			case 2: 
				$im = @imagecreatefromjpeg($src); 
				break;
			case 3:
				$im = @imagecreatefrompng($src); 
				break;
			default: 
				return false; 
		}
		if (!$im) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "unknown image '$src'!");
			return false;
		}
		
		$output = imagecreatetruecolor($width, $height);		
		@imagecopyresampled($output, $im, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
		
		switch ($bigType) {
			case 1: 
				$res = imagegif($output, $dst);
				break;	 
			case 2: 
				$res = imagejpeg($output, $dst);
				break;	 
			case 3: 
				$res = imagepng($output, $dst);
				break;
		}		
		
		@imagedestroy($output);
		
		
		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "resize Image failed!res=$res,dst=$dst");
		}		
		return $res;
	}
	
	public function getFilePreview($fileinfo, $src, $dst, $width, $height, &$mimetype)
	{ 
		$previewinfo = array();
		
		if ($fileinfo['type'] == FT_IMAGE) {//图片类
			$res = $this->resizeImage($src, $dst, $width, $height, $previewinfo);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call resizeImage filed!src=$src");
				return false;
			}
			$mimetype = $previewinfo['mimetype'];
		} else {
						
			$dst = $this->getOtherFilePreview($fileinfo,$src, $dst, $width, $height, $mimetype);
			
			$previewinfo['orig_width'] = 0;
			$previewinfo['orig_height'] = 0;
			$previewinfo['dst'] = $dst;
		}
		return $previewinfo;
	}
	
	protected function getPreview($fileinfo, $width, $height, &$mimetype)
	{ 
		$fid = $fileinfo['id'];
		$dstdir = RPATH_PREVIEW;
		if (!is_dir($dstdir))
			s_mkdir($dstdir);
		
		$name = $fid.'_'.$width.'x'.$height;		
		$dst = $dstdir.DS.$name;
		
		$previewinfo = $this->getFilePreview($fileinfo, $fileinfo['opath'], $dst, $width, $height, $mimetype);
		
		$orig_width = intval($previewinfo['orig_width']);
		$orig_height = intval($previewinfo['orig_height']);
		if (!$fileinfo['width'] || !$fileinfo['height']) {
			$_params = array();
			$_params['id'] = $fid;
			$_params['width'] = $orig_width;
			$_params['height'] = $orig_height;
			
			$this->update($_params);			
		}
		
		return $previewinfo['dst'];
	}
	
	public function previewPath($id, $width=72, $height=72, &$mimetype='')
	{
		if ($width <= 8) 
			$width = 8;
		if ($height <= 8) 
			$height = 8;
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		$fileinfo = $this->get($id);
		if (!$fileinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no file id '$id'");
			return false;
		}
		
		
		$previewFile = $this->getPreview($fileinfo, $width, $height, $mimetype);
		if (!$mimetype)
			$mimetype = $this->ext2mimetype($fileinfo['extname']);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $previewFile);		
		return $previewFile;
	}
	
	
	public function preview($id, $width=72, $height=72)
	{
		$previewFile = $this->previewPath($id, $width, $height, $mimetype);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $previewFile);
		
		if ($previewFile) {
			header("Content-Type: $mimetype");
			if (!file_exists($previewFile)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no previewFile=$previewFile=".$previewFile);
				exit;
			}
			
			$res = readfile($previewFile);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call readfile failed!$previewFile=".$previewFile);
			}
		} 
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		exit;
	}
	
	
	public function getReceiptInfo($id)
	{
		$finfo = $this->get($id);
		if (!$finfo) {
			return false;
		}
		if ($finfo['extname'] != 'pdf') {
			return false;
		}
		
		$m = Factory::GetReceipt();
		$rinfo = $m->getReceiptInfo($finfo['opath']);
		
		return $rinfo;
	}	
	
	/* ============================================================================
	 * DIR functions
	 * 
	 * ===========================================================================*/
	
	public function newDirectory($params, $ioparams)
	{
		if (!$params)
			return false;
		
		$name = $params['name'];
		$pid = intval($params['pid']);
		if ($pid > 0) {
			$pinfo = $this->get($pid);	
			if (!$pinfo) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid pid '$pid'");
				return false;
			}
		}
		
		$userinfo = get_userinfo();
		$uid = $userinfo['uid'];
		$oid = $userinfo['oid'];
		
		//find
		$_params = array('pid'=>$pid, 'name'=>$name, 'isdir'=>1, 'cid'=>$uid);
		$res = $this->getOne($_params);
		if ($res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "directory '$name' exists!");
			return false;
		}
		
		$params['isdir'] = 1;
		$params['status'] = 1;
		$params['oid'] = $oid;
		
		$res = $this->set($params);
		
		return $res;
	}
	
	
	
	//newDirectory
	public function createDirectory($path, &$ioparams=array())
	{
		if (!$path) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no path '$path'!", $ioparams);
			return false;
		}
		
		$params = array();			
		$res = $this->parsePath($path, $params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid path '$path'!");
			return false;
		}
		
		if ($params['exists']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "path '$path' exists!");
			return false;
		}
		
		
		$params['isdir'] = 1;		
		$res = $this->newDirectory($params, $ioparams);
		
		
		//rlog($params);
		
		return $res;
	}
	
	
	/* ============================================================================
	 * Upload functions
	 * 
	 * ===========================================================================*/
	
	protected function genFileID($pid, &$filename)
	{
		$fileid = md5($pid.'_'.$filename);
		
		$i = 1;
		
		$name = $filename;
		s_extname2($name, $extname);
		while ($this->getOne(array('fileid'=>$fileid))) { //文件已经存
			$newname = $name.'-'.$i++;	
			if ($extname) {
				$filename = $newname. '.'.$extname;
			} else {
				$filename = $newname. '.'.$extname;
			}
			$fileid = md5($pid.'_'.$filename);
		}
		return $fileid;
	}
	
	protected function getTmpFileInfo($params)
	{
		$params['name'] = $params['filename'];
		$params['ctime'] = time();
		$res = $this->set($params);
		if ($res)
			return $params;
		else
			return false;
	}
	
	
	protected function initUploadParams(&$params=array(), &$ioparams=array())
	{
		$userinfo = get_userinfo();
		
		$params['uid'] = isset($userinfo['id'])?$userinfo['id']:0;
		
		
		$pid = $params['pid'];		
		$path = $params['path'];
		
		//存储
		$m = Factory::GetAdmin();
		$si = $m->getMyStorage();
		
		//target dir
		$tdir = $si['basedir'];
		if (!$tdir) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no dispatch storage for user '$uid'!");
			return false;
		}	
		
		$tdir = str_replace(DS, '/', $tdir);
		if (!is_dir($tdir)) {
			s_mkdir($tdir);
			if (!is_dir($tdir)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no dir '$tdir'!");
				return false;
			}
		}
				
		//检查freespace
		$freespace = $si['free'];
		if ($freespace < $params['need_size']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no space for upload! need_size=$params[need_size], free=$freespace");
			return false;
		}		
		
		//原始文件名				
		$filename = $params['name'];
		$extnames = s_extnames($filename);
		$extname = $extnames['extname'];
		
		//filename
		$fileid = $this->genFileID($pid, $filename);
		
		//tmpfileinfo
		$tfileinfo = $this->getTmpFileInfo(array('filename'=>$filename, 'pid'=>$pid, 'fileid'=>$fileid));
		if (!$tfileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call getTmpFileInfo failed!");
			return false;
		}
		$id = $tfileinfo['id'];
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$name='.$name);		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$filename='.$filename);		
		
		$fname = $id.'_'.$fileid.'.'.$extname;
		$dst = $tdir.DS.$fname;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$dst='.$dst);		
		
		if (file_exists($dst)) {
			$size = s_filesize($dst);
			$mtime = filemtime($dst);		
		} else {			
			$size = 0;	
			$mtime = 0;	
		}
		
		//$path
		$basepath = $si['basepath'];		
		$path = $basepath.'/'.$fname;		
		$type =  $this->ext2type($extname);
		
		$params['id'] = $id;
		$params['pid'] = $pid;
		$params['name'] = $filename;
		$params['filename'] = $filename;
		$params['fileid'] = $fileid;
		$params['path'] = $path;
		$params['opath'] = $dst;
		$params['extname'] = $extname;
		$params['extname2'] = $extnames['extname2'];
		$params['fullextname'] = $extnames['fullextname'];
		$params['type'] = $type;
		$params['size'] = $size;
		$params['ts'] = $ts;
		$params['sid'] = $si['id'];
		$params['oid'] = $si['oid'];		
		$params['basepath'] = $si['basepath'];
		
		$params['status'] = 1;
		
		$params['dst'] = $dst;
		$params['mtime'] = $mtime;	
		$params['tdir'] = $tdir;
		$params['localpath'] = $localpath;
		
		
		//rlog($params);
		
		return true;
	}	
		
	protected function doUpload(&$params, &$ioparams=array())
	{
		$params['pid'] = isset($ioparams['pid'])?$ioparams['pid']:0;
		$res = $this->initUploadParams($params, $ioparams);
		if (!$res)  {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "init upload params failed!", $params);
			return false;
		}
				
		$dst = $params['dst'];		
		$tmpfile = $params['tmp_name'];		
		if (function_exists("move_uploaded_file")) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "move file to '$dst' ...");
			$res = move_uploaded_file($tmpfile, $dst);
		} else {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "copy file to '$dst' ...");
			$res = copy($tmpfile, $dst);			
		}
		
		if (!file_exists($dst)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "copy file to '$dst' failed!");
			return false;
		}
		
		$size = filesize($dst);
				
		$params['size'] = $size;
		$params['tmp_name'] = 0;
		
		if (isset($ioparams['nodbuploadcallback']) && $ioparams['nodbuploadcallback'] == 1 ) {
			return true;
		}
		
		if (isset($ioparams['viewcontent']) && $ioparams['viewcontent'] == 1 ) {
			if ($size < 1024*1024) {
				$params['content'] = base64_encode(s_read($targetfile));						
			} else {
				$params['content'] = '';
			}
			return true;
		}
		
		//video
		//需要转成mp4，无损，H5能直接播
		if ($this->is_need_convert($params)) {
			$params['status'] = 2; //待转码
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "set file failed!");
			return false;
		}
		
		return true;
	}
	
	
	
	/**
	 * 上传 
	 *
	 * @param mixed $ioparams This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function upload(&$ioparams=array())
	{
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");		
		$fdb = array();
		
		foreach ($_FILES as $key => $v) {
			
			if (is_array($v['name'])) {
				
				$nr = count($v['name']);
				
				for($i=0; $i<$nr; $i++) {
					$params = array();
					
					$params['name'] = $v['name'][$i];
					$params['type'] = $v['type'][$i];
					$params['tmp_name'] = $v['tmp_name'][$i];
					$params['error'] = $v['error'][$i];
					$params['size'] = $v['size'][$i];
					$params['need_size'] = $params['size'];
					
					if (!$this->doUpload($params, $ioparams)) {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "do upload failed!");		
						return false;
					}				
					
					$fdb[] = $params;
				}
			} else {
				$params = $v;		
				$params['need_size'] = $params['size'];		
				if (!$this->doUpload($params, $ioparams)) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "do upload failed!");		
					return false;
				}
				$fdb[] = $params;
			}
		}		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $fdb);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return $fdb;
	}
	
	public function filecontent(&$ioparams=array())
	{
		$tmpfile = '';
		$size = 0;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");		
		$fdb = array();		
		foreach ($_FILES as $key => $v) {
			
			if (is_array($v['name'])) {
				
				$nr = count($v['name']);
				
				for($i=0; $i<$nr; $i++) {
					$params = array();
					
					$params['name'] = $v['name'][$i];
					$params['type'] = $v['type'][$i];
					$params['tmp_name'] = $v['tmp_name'][$i];
					$params['error'] = $v['error'][$i];
					$params['size'] = $v['size'][$i];
					$params['need_size'] = $params['size'];
					
					
					$tmpfile = $v['tmp_name'][$i];
					$size = $v['size'][$i];
					
					if ($tmpfile && $size < 1024*1024) {
						$params['content']  = base64_encode(s_read($tmpfile));	
					}
					
					
					$fdb[] = $params;
					
					break;
				}
			} else {
				$tmpfile = $v['tmp_name'];
				$size = $v['size'];
				
				$params = $v;		
				$params['need_size'] = $params['size'];	
				$params['content'] = '';				
				if ($tmpfile && $size < 1024*1024) {
					$params['content']  = base64_encode(s_read($tmpfile));	
				}
				$fdb[] = $params;	
			}
		}		
		
		
		return $fdb;
	}
	
	
	
	/* =========================================================================================
	 *
	 * 下载 functions
	 *
	 * =======================================================================================*/
	
	
	
	protected function getRanges(&$params) 
	{
		// process Range: header if present
		if (isset($_SERVER['HTTP_RANGE'])) {
			
			// we only support standard "bytes" range specifications for now
			if (preg_match('/bytes\s*=\s*(.+)/', $_SERVER['HTTP_RANGE'], $matches)) {
				$params["ranges"] = array();				
				// ranges are comma separated
				foreach (explode(",", $matches[1]) as $range) {
					// ranges are either from-to pairs or just end positions
					list($start, $end) = explode("-", $range);
					$params["ranges"][] = ($start==="") 
						? array("last"=>$end) 
						: array("start"=>$start, "end"=>$end);
				}
			}
		}
	}
	
	protected function _multipart_byterange_header($mimetype = false, 
		$from = false, $to=false, $total=false) 
	{
		if ($mimetype === false) {
			if (!isset($this->multipart_separator)) {
				// initial
				
				// a little naive, this sequence *might* be part of the content
				// but it's really not likely and rather expensive to check 
				$this->multipart_separator = "SEPARATOR_".md5(microtime());
				
				// generate HTTP header
				header("Content-type: multipart/byteranges; boundary=".$this->multipart_separator);
			} else {
				// final 
				
				// generate closing multipart sequence
				echo "\n--{$this->multipart_separator}--";
			}
		} else {
			// generate separator and header for next part
			echo "\n--{$this->multipart_separator}\n";
			echo "Content-type: $mimetype\n";
			echo "Content-range: $from-$to/". ($total === false ? "*" : $total);
			echo "\n\n";
		}
	}
	
	protected function readdir($id, $uid=0)
	{
		$id = $fileinfo['id'];
		if ($uid > 0) {
			$filter = array('pid'=>$id);
		} else {
			$filter = array('pid'=>$id, 'cuid'=>$uid);
		}
		$udb = $this->select($filter);
				
		//$sql = "update cms_file set hits = hits + 1 where id=$id";
		//$this->_db->exec($sql);
		CJson::encodedPrint($udb);		
		exit;
	}	
	
	
	protected function getStream($fileinfo)
	{
		return fopen($fileinfo['opath'], 'rb');
	}
	
	protected function httpStatus($status) 
	{
		// simplified success case
		if ($status === true) {
			$status = "200 OK";
		}
		
		// generate HTTP status response
		header("HTTP/1.1 $status");
		header("X-RC-Status: $status", true);
	}
	
	protected function bytes($str)
	{
		static $func_overload;				
		if (is_null($func_overload)) {
			$func_overload = @extension_loaded('mbstring') ? ini_get('mbstring.func_overload') : 0;
		}		
		return $func_overload & 2 ? mb_strlen($str,'ascii') : strlen($str);
	}
	
	protected function fread($fd, $size)
	{
		$recv_size = 0;
		
		while (!feof($fd)) {
			$buffer = fread($fd, 4096);
			$recv_size  += $this->bytes($buffer);
			echo $buffer;	
			
			if ($size !== -1 && $recv_size >= $size)
				break;
		}
	}
	
	
	protected function readfile($fileinfo) 
	{
		$mimetype = $this->ext2mimetype($fileinfo['extname']);	
		
		$filename = $fileinfo['filename'];
		$filesize = $fileinfo['size'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$mimetype='.$mimetype);
		
		$stream = $this->getStream($fileinfo);
		if (!$stream) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no stream");
			return false;
		}
		
		
		$params = Array();		
		$this->getRanges($params);
		
		if (!headers_sent()) {
			$status = "200 OK";			
			if (!isset($mimetype)) {
				$mimetype = "application/octet-stream";
			}
			header("Content-type: $mimetype");
			
			if (isset($fileinfo['ts'])) {
				header("Last-modified:".gmdate("D, d M Y H:i:s ", $fileinfo['ts'])."GMT");
			}
			
			// GET handler returned a stream
			if (!empty($params['ranges']) && (0===fseek($stream, 0, SEEK_SET))) {
				// partial request and stream is seekable 				
				if (count($params['ranges']) === 1) {
					$range = $params['ranges'][0];					
					if (isset($range['start'])) {
						fseek($stream, $range['start'], SEEK_SET);
						if (feof($stream)) {
							$this->httpStatus("416 Requested range not satisfiable");
							return false;
						}
						
						if (isset($range['end']) && $range['end']) {
							$size = $range['end']-$range['start']+1;
							$this->httpStatus("206 partial");
							header("Content-length: $size");
							header("Content-range: $range[start]-$range[end]/"
									. (isset($filesize) ? $filesize : "*"));
							
							$this->fread($stream, $size);
							/*		
							while ($size && !feof($stream)) {
								$buffer = fread($stream, 4096);
								$size  -= $this->bytes($buffer);
								echo $buffer;
							}*/
						} else {
							if ($range['start'] > 0)
								$this->httpStatus("206 partial");
							if (isset($filesize)) {
								header("Content-length: ".($filesize - $range['start']));
								header("Content-range: ".$range['start']."-".$filesize."/"
										. (isset($filesize) ? $filesize : "*"));
							}
							$this->fread($stream, -1);
							//fpassthru($stream);
						}
					} else {
						header("Content-length: ".$range['last']);
						fseek($stream, -$range['last'], SEEK_END);
						
						$this->fread($stream, -1);
						//fpassthru($stream);
					}
				} else {
					$this->_multipart_byterange_header(); // init multipart
					foreach ($params['ranges'] as $range) {
						// TODO what if size unknown? 500?
						if (isset($range['start'])) {
							$from = $range['start'];	
							$to   = !empty($range['end']) ? $range['end'] : $filesize-1; 
						} else {
							$from = $filesize - $range['last']-1;
							$to   = $filesize -1;
						}
						$total = isset($filesize) ? $filesize : "*"; 
						$size  = $to - $from + 1;
						$this->_multipart_byterange_header($params['mimetype'], $from, $to, $total);
						
						fseek($stream, $from, SEEK_SET);						
						$this->fread($stream, $size);
						
						/*
						while ($size && !feof($stream)) {
							$buffer = fread($stream, 4096);
							$size  -= $this->bytes($buffer);
							echo $buffer;
						}*/
					}
					$this->_multipart_byterange_header(); // end multipart
				}
			} else {//chunk
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "normal request or stream isn't seekable, return full content!");
				
				// normal request or stream isn't seekable, return full content
				if (isset($filesize)) {
					header("Content-length: ".$filesize);
				}
				$this->fread($stream, -1);
				//fpassthru($stream);
				return true; // no more headers
			}
		}
	}
	
	
	public function read($id, &$ioparams=array())
	{		
		if (!$id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid id '$id'!");
			return false;
		}
		$fileinfo = $this->get($id);
		if (!$fileinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		//http://localhost/rc5/file/173/avi
		if ($fileinfo['isdir'] == 1) { //目录
			return $this->readdir($id, $ioparams);
		}
		
		//rlog($fileinfo);
		
		if ($fileinfo['type'] != FT_VIDEO && $fileinfo['type'] != FT_AUDIO && $fileinfo['type'] != FT_IMAGE ) {
			
			//累加下载次数
			$this->inc($id, 'downloads');
			//下载通知
			$m = Factory::GetModel('file2model');
			
			$m->trigger('download', $fileinfo);
			
			$filename= $fileinfo['filename'];			
			
			//加上这条，PDF不能直接打开，且下载也是慢？？
			header("Content-Disposition:attachment;filename=\"".$filename."\"");
		}
		
		$this->readfile($fileinfo);		
		$fid = $fileinfo['id'];
		$this->inc($fid, 'hits');
		
		exit;
	}
	
	/**
	 * 下载
	 *
	 * @param mixed $id This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function download($id)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$fileinfo = $this->get($id);
		if (!$fileinfo)
			return false;
		
		$fid = $fileinfo['id'];
		
		//累加下载次数
		$this->inc($fid, 'downloads');
		//下载通知
		$m = Factory::GetModel('file2model');
		
		$m->trigger('download', $fileinfo);
		
		
		/*if ($fileinfo['size'] > 1024*1024*256) { //超过256M
			redirect($fileinfo['downloadUrl']);			
		} else {*/
		$filename = $fileinfo['filename'];
		header("Content-Disposition:attachment;filename=\"".$filename."\"");
		//header("Content-Type: $mimetype");
		//header("Accept-ranges:bytes");
		//header("Accept-length:".$filesize);			
		$this->readFile($fileinfo);		
		//}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		exit;
	}
	
	/* =================================================================================
	 * HTTP functions for WEBDAV
	 * ================================================================================*/
	
	
	protected function getPathInfo($uid, $_path, &$ioparams = array())
	{
		$params = array();
		//查询
		$path = s_urldecode($_path);
		//to UTF8
		$path = safeEncoding($path, PHP_CHARSET);
		$pathinfo = array();			
		
		if (!$path || $path == '/') {
			$pid = 0;
			$name = 'root';
			$fileinfo['name'] = $name;
			$fileinfo['path'] = '/';
			$fileinfo['ctime'] = time();
			$fileinfo['ts'] = time();
			$fileinfo['isdir'] = 1;
			$fileinfo['isroot'] = true;
			$fileinfo['id'] = 0;
			
			$pathinfo[] = $fileinfo;
			
			$exists = true;
			
		} else {
			$udb = explode('/', $path);		
			$vpath = array();
			foreach ($udb as $key=>$v) {
				$v = trim($v);
				if (!$v)
					continue;
				$vpath[] = $v;
			}
			
			$pid = isset($ioparams['pid'])?$ioparams['pid']:0;	
			$exists = false;	
			
			$nr = count($vpath);
			
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vpath);
			
			for($i=0; $i<$nr; $i++) {			
				$name = $vpath[$i];
				$fileinfo = $this->getOne(array('cuid'=>$uid, 'pid'=>$pid, 'name'=>$name));
				if ($fileinfo) {
					if ($fileinfo['isdir'])				
						$pid = $fileinfo['id'];				
					$exists = true;
				} else {
					$exists = false;
				}
				$pathinfo[] = array('name'=>$name, 'fileinfo'=>$fileinfo, 'exists'=>$exists);
			}
		}
				
		$params['path'] = $path;
		$params['name'] = $name;
		$params['uid'] = $uid;
		$params['pid'] = $pid;
		$params['exists'] = $exists;
		$params['fileinfo'] = $fileinfo;		
		$params['pathinfo'] = $pathinfo;
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		return $params;
	}
	
	protected function http_HEAD($params, &$ioparams=array())
	{
		//rlog($ioparams);
		//HEAD /admin.php/my_file/bigupload/2%2Emp4/2%2Emp4?tt=1&ssid=br9Ouua%2BA0z%2FYPxAVQokwlOnQUSaod5DWg3ZsooUTjw%3D&pid=0&lp=2.mp4
		$fileinfo = $params['fileinfo'];
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $fileinfo);
		
		$mimetype = '';
		if ($fileinfo && $fileinfo['size'] > 0) {				
			// detect resource type
			$mimetype = CFileType::getMimetype($fileinfo['extname']);
			// detect modification time
			// see rfc2518, section 13.7
			// some clients seem to treat this as a reverse rule
			// requiering a Last-Modified header if the getlastmodified header was set
			$mtime = $params['mtime'];
			
			// detect resource size
			$size = $params['size'];
			header("HTTP/1.1 200 OK");
			header("Content-type: $mimetype");
			
			header("Last-modified:".gmdate("D, d M Y H:i:s ", $mtime)."GMT");
			header("Content-length: ".$params['size']);
		} else {
			header("HTTP/1.1 404 Not found");
		}
		
		
		$post_max_size =	nformat_get_human_file_size(ini_get('post_max_size'));
		header("post_max_size: $post_max_size");
		
		exit;
	}
	
	protected function http_GET($params, &$ioparams=array())
	{
		$fileinfo = $params['fileinfo'];
		
		//rlog($fileinfo);		
		
		if ($fileinfo['isdir']) {
			$res = $this->readdir($fileinfo['id'], $params['uid']);
		} else {
			$res = $this->read($fileinfo['id']);	
		}
		
		return $res;
	}
	
	
	protected function preparePutFile(&$params=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN preparePutFile");
		$startpos = 0;
		$endpos = 0;
		$tmpfile = $params['dst'];		
		if ($params['size'] == 0) {
			$fd = fopen($tmpfile, 'wb');				
		} else {
			$fd = fopen($tmpfile, 'ab+');			
			$range = $params['range'];
			if ($range) {
				$startpos = $range['start'];
				$endpos = $range['end'];
				fseek($fd, $startpos, SEEK_SET);
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING : no range");
				$endpos = $params['content_length'];
			}						
		}
		$params['startpos'] = $startpos;
		$params['endpos'] = $endpos;
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT preparePutFile");
		return $fd;		
	}
	
		
	
	
	protected function postPutFile($params, $total)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN postPutFile");
		$tmpfile = $params['dst'];
		$size = s_filesize($tmpfile);
		$lsize = $params['content_length'];
		
		$try = 100;
		while($size != $lsize) {
			usleep(100000);
			system('sync');
			$size = s_filesize($tmpfile);
			if ($try-- <= 0)
				break;						
		}
		
		if ($total != $lsize ) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "big file upload interrupt! size=$size, lsize=$lsize, total=$total, endpos=$endpos");
			return false;
		}
		
		
		$params['size'] = $size;
		$params['opath'] = $params['dst'];
		if ($this->is_need_convert($params)) {
			$status = 2; //待转码
		} else {			
			$status = 1; //正常
		}
		
		$params['status'] = $status;

		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call attach set failed!");
			$this->delTmpFileInfo($id);
			return false;
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT postPutFile...");		
		return $res;
		
	}
	
	protected function doPutFile(&$params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  "IN doPutFile ... ", $params);
		
		$upload_size = 0;				
		$infd = $params["fp"];
		$outfd = $this->preparePutFile($params);		
		
		while (!feof($infd)) {
			$buf = fread($infd, 8192);
			if ($buf === false) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call fread failed!");
				break;
			}
			$upload_size += strlen($buf);
			$res = fwrite($outfd, $buf);
			if ($res < 0) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call fwrite failed! res=".$res);
				break;
			}
		}
		fclose($outfd);
		
		$endpos = $params['endpos'];
		$startpos = $params['startpos'];
		$total_size = $startpos + $upload_size;
		
		if ($res >= 0) {
			system('sync');
			$this->postPutFile($params, $total_size);
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT doUploadBigFile, endpos=$endpos, startpos=$startpos, total_size=$total_size, upload_size=$upload_size, res=$res");
		return true;
	}
	
	/**
	 * 处理 HTTP PUT 命令
	 *
	 * @param mixed $ioparams This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function http_PUT($params, &$ioparams=array())
	{
		$fileinfo = $params['fileinfo'];
		$path = $params['path'];
		
		/*
		[HTTP_OVERWRITE] => T
		[HTTP_TRANSLATE] => f
		*/
		$overwrite = (isset($_SERVER['HTTP_OVERWRITE']) &&  $_SERVER['HTTP_OVERWRITE']== 'T')?true:false;
		if ($params['exists']) {
			if (!$overwrite) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "path '$path' exists!");
				return false;
			}
			$params['id'] = $fileinfo['id'];
		} 
		
		$name = $params['name'];
		$pid = $params['pid'];
		$params['content_length'] = $_SERVER["CONTENT_LENGTH"];
		$params['need_size'] = $params['content_length'] ; //检查可用空间时使用
		
		// get the Content-type 
		if (isset($_SERVER["CONTENT_TYPE"])) {
			// for now we do not support any sort of multipart requests
			if (!strncmp($_SERVER["CONTENT_TYPE"], "multipart/", 10)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does not support mulipart PUT requests");
				return false;
			}
			$params["content_type"] = $_SERVER["CONTENT_TYPE"];
		} else {
			// default content type if none given
			$params["content_type"] = "application/octet-stream";
		}
		
		/* RFC 2616 2.6 says: "The recipient of the entity MUST NOT 
		 ignore any Content-* (e.g. Content-Range) headers that it 
		 does not understand or implement and MUST return a 501 
		 (Not Implemented) response in such cases."
		*/ 
		foreach ($_SERVER as $key => $val) {
			if (strncmp($key, "HTTP_CONTENT", 11)) 
				continue;
			switch ($key) {
				case 'HTTP_CONTENT_ENCODING': // RFC 2616 14.11
					// TODO support this if ext/zlib filters are available
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "The service does not support '$val' content encoding");
					return false;
				
				case 'HTTP_CONTENT_LANGUAGE': // RFC 2616 14.12
					// we assume it is not critical if this one is ignored
					// in the actual PUT implementation ...
					$params["content_language"] = $val;
					break;
				
				case 'HTTP_CONTENT_LENGTH':
					// defined on IIS and has the same value as CONTENT_LENGTH
					break;
				
				case 'HTTP_CONTENT_LOCATION': // RFC 2616 14.14
					/* The meaning of the Content-Location header in PUT 
					 or POST requests is undefined; servers are free 
					 to ignore it in those cases. */
					break;
				
				case 'HTTP_CONTENT_RANGE':    // RFC 2616 14.16
					// single byte range requests are supported
					// the header format is also specified in RFC 2616 14.16
					// TODO we have to ensure that implementations support this or send 501 instead
					if (!preg_match('@bytes\s+(\d+)-(\d+)/((\d+)|\*)@', $val, $matches)) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does only support single byte ranges");
						return false;
					}
					
					$range = array("start" => $matches[1], "end" => $matches[2]);
					if (is_numeric($matches[3])) {
						$range["total_length"] = $matches[3];
					}
					
					$params["range"] = $range;
					
					break;
				
				case 'HTTP_CONTENT_TYPE':
					// defined on IIS and has the same value as CONTENT_TYPE
					break;
				
				case 'HTTP_CONTENT_MD5':      // RFC 2616 14.15
					// TODO: maybe we can just pretend here?
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does not support content MD5 checksum verification"); 
					return false;
				
				default: 
					// any other unknown Content-* headers
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "The service does not support '$key'"); 
					return false;
			}
		}
		
		$res = $this->initUploadParams($params, $ioparams);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid upload params failed!", $params);
			return false;
		}
		
		
		// 保存
		$params["fp"] = fopen("php://input", "rb");
		$res = $this->doPutFile($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call doUploadBigFile file!");
			return false;
		}
		
		return true;
	}
	
	
	
	public function http(&$ioparams=array())
	{		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		
		$userinfo = get_userinfo();
		
		//所有者id
		$uid = $userinfo['id'];		
		$path = $ioparams['_path'];
		$params = $this->getPathInfo($uid, $path, $ioparams);		
		if (!$params) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid uid '$uid' or path '$path'!");
			return false;
		}
		
		//加载文件信息
		
		$method = 'http_'.$ioparams['method'];	
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $method);
		$res = false;
		if (method_exists($this, $method)) {
			$res = $this->$method($params, $ioparams); 
		}  else {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no method '$method'!");
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return $res;
	}
	
	/* ======================================================================
	 * timerProcess
	 * ====================================================================*/
	protected function lock()
	{
		//转马非常费时，创建一个临时文件锁
		$tagfile = RPATH_CACHE.DS.".locked";
		if (file_exists($tagfile)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "file lock tag '$tagfile' exists!");
			return true;
		}
		touch($tagfile);
		return false;
	}
	
	protected function unlock()
	{
		$tagfile = RPATH_CACHE.DS.".locked";
		@unlink($tagfile);
	}
	
	protected function write_tmpinfo_to_lock($tmpinfo)
	{
		$tagfile = RPATH_CACHE.DS.".locked";
		s_write($tagfile, $tmpinfo);
	}
	
	protected function checkConvert()
	{
		$tagfile = RPATH_CACHE.DS.".locked";
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
		if (file_exists($tagfile)) {
			$res = s_read($tagfile);
			$udb = explode('|', $res);
			$id = $udb[0];
			$outfile = $udb[1];
			$mtime = filemtime($outfile);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "############# mtime=".tformat($mtime));
			$ts = time();
			
			if (file_exists($outfile)) {
				$sz = s_filesize($outfile);
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'sz='.$sz);
				
				if ($sz > 0) {//WIN32 PHP 文件不能超过2G
					$params = array('id'=>$id,'size'=>$sz, 'ts'=>$ts);
					$res = $this->set($params);	
					if (!$res) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set file failed!", $params);					
					}
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "update file size=$sz and mtime=".tformat($mtime));
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'WARNING: get size failed!sz='.$sz);
				}	
				
				//检查文件最后一次变更时间
				$delta = $ts - filemtime($outfile);					
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "last modified before {$delta}s, id=$id");
				
				if ($delta > 30) {
					$fileinfo = $this->get($id);
					if ($fileinfo) {
						
						$org_fid = intval($fileinfo['convert_id']); //临时用
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $fileinfo);
						
						//更新	
						$params = array('id'=>$id,'status'=>1, 'convert_id'=>0);
						$res = $this->set($params);	
						if (!$res) {
							rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set file failed!", $params);
						}
						
						//转码完毕		
						$params = array('id'=>$org_fid,'status'=>4);
						$res = $this->set($params);	
						if (!$res) {
							rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set file failed!", $params);
						}
												
						//$this->checkSyncForDir($id);
						
						//clean .lock
						$this->unlock();
						
						rlog(RC_LOG_INFO, __FILE__, __LINE__, "file convert from id '$org_fid' to '$id' done.");						
					}
					
				}
			} else { //文不存在
				$delta2 = $ts - filemtime($tagfile);	
				if ($delta2 > 180) {//记录的文件不存，TAG超时，判定为无效
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid tag '$tagfile'!");
					$this->unlock();
				} else {				
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: no file '$tagfile' in tagfile '$tagfile'!$delta2=".$delta2);
				}
			}
		}		
		return true;
	}
	
	
	/**
	 * 定时器自动转码
	 *
	 * @param mixed $fileinfo This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function timerProcessConvertVideo($fileinfo)
	{
		//存储
		$m = Factory::GetAdmin();
		$uid = $fileinfo['cuid'];
		$usi = $m->getUserStorageInfo($uid);
		
		$basedir = $usi['basedir'];
		$basepath = $usi['basepath'];
		
		
		$m2 = Factory::GetModel('storage');
		$si = $m2->get($fileinfo['sid']);
		$infile = $si['mountdir'].DS.$fileinfo['path'];
		
		//rlog($infile);
		
		$name = $fileinfo['name'].'(转码)';
		$extname = 'mp4'; 
		$filename = $name.'.'.$extname;
		
		//filename
		$fileid = $this->genFileID($fileinfo['pid'], $filename);		
		$params = array('filename'=>$filename, 'cuid'=>$fileinfo['cuid'], 'uid'=>$fileinfo['uid'], 
				'pid'=>$fileinfo['pid'], 'extname'=>$extname, 'fileid'=>$fileid);
		$tfileinfo = $this->getTmpFileInfo($params);
		if (!$tfileinfo) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call getTmpFileInfo failed!", $params);
			return false;
		}
		
		$id = $tfileinfo['id'];
	
		$newfilename = $id.'_'.$fileid.'.'.$extname;
		$newpath = $basepath.'/'.$newfilename;
		$outfile = $basedir.DS.$newfilename;
		
		if (file_exists($outfile)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING file '$outfile' exists!");
			$this->delTmpFileInfo($id);
			return false;
		}
		
		//临时文件路径，写入lock
		$cacheinfo = array();
		$cacheinfo['id'] = $id;
		$cacheinfo['ts'] = time();
		
		$tmpinfo = "$id|$outfile";		
		$this->write_tmpinfo_to_lock($tmpinfo);				
		
		//转码费时，转码之间把状态置为：转码中
		$_params = array();
		$_params['id'] = $fileinfo['id'];
		$_params['convert_id'] = $id;
		$_params['status'] = 3;		
		$res = $this->set($_params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set file failed!", $_params);
			$this->delTmpFileInfo($id);
			return false;
		}
		
		$type = CFileType::ext2type($extname);
		
		//更新		
		$params = array();
		$params['id'] = $id;
		$params['name'] = $filename;
		$params['filename'] = $filename;
		$params['extname'] = $extname;
		$params['type'] = $type;
		$params['path'] = $newpath;
		$params['status'] = 0; //正常		
		$params['size'] = 0;
		$params['convert_id'] = $fileinfo['id'];
		
		$params['uid'] = $fileinfo['uid'];
		$params['cuid'] = $fileinfo['cuid'];
		$params['sid'] = $usi['sid'];
		$params['oid'] = $usi['oid'];
		
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set file failed!", $params);
		}
		//转码
		//$cacheffmpeglog = str_replace(DS, '/', RPATH_CACHE.DS.'cacheffmpeglog.'.$id);
		
		$cmd = 	"ffmpeg -v quiet -i \"$infile\"  -c:v libx264 -c:a aac -r 25 -strict -2 -y \"$outfile\"";	
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $cmd.' ...');
		
		$res = run($cmd, true);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'res='.$res);
		
		return true;	
		
	}	
	
	
	/**
	 * timerProcessSnapImageForVideoFile 定时器任务自动把视频截图 
	 * 
	 * 本方法由：$this->timerProcess 调用
	 *
	 * @param mixed $fileinfo 文件记录
	 * @return mixed 成功: true, 失败: false
	 *
	 */
	protected function timerProcessSnapImageForVideoFile($fileinfo)
	{
		//所有者
		$m = Factory::GetAdmin();
		$uid = $fileinfo['cuid'];
		$usi = $m->getUserStorageInfo($uid);
		
		//存储
		$s = Factory::GetModel('storage');
		$si = $s->get($fileinfo['sid']);
		$infile = $si['mountdir'].DS.$fileinfo['path'];
		
		$basedir = $usi['basedir'];
		$basepath = $usi['basepath'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $infile);
		$name = $fileinfo['name'].'(截图)';
		$extname = 'jpg';
		$filename = $name.'.'.$extname;
		
		//生成临时文件
		$fileid = $this->genFileID($fileinfo['pid'], $filename);		
		$params = array('filename'=>$filename, 'cuid'=>$fileinfo['cuid'], 'uid'=>$fileinfo['uid'], 
				'pid'=>$fileinfo['pid'], 'extname'=>$extname, 'fileid'=>$fileid);			
		$tfileinfo = $this->getTmpFileInfo($params);
		if (!$tfileinfo) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call getTmpFileInfo failed!", $params);
			return false;
		}
		
		$id = $tfileinfo['id'];	
		$newfilename = $id.'_'.$fileid.'.'.$extname;
		$newpath = $basepath.'/'.$newfilename;
		$outfile = $basedir.DS.$newfilename;
		
		if (file_exists($outfile)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING file '$outfile' exists!");
			$this->delTmpFileInfo($id);
			return false;
		}
		
		//截图
		//ffmpeg -v quiet -i \"$infile\" -y -f image2 -ss 1 -t 1 -s $width".'x'."$height $dst		
		$cmd = 	"ffmpeg -v quiet -i \"$infile\"  -y  -ss 1 -t 1  -f image2 -frames:v 1 \"$outfile\"";	
		$res = run($cmd);
		//rlog($cmd.', res='.$res);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call run failed!cmd=$cmd", $fileinfo);
			$this->delTmpFileInfo($id);
			return false;
		} 
		
		$type =  CFileType::ext2type($extname);
		$size = filesize($outfile);
		
		$params = array();
		
		$params['name'] = $filename;
		$params['filename'] = $filename;
		$params['extname'] = $extname;
		$params['type'] = $type;
		$params['size'] = $size;
		$params['path'] = $newpath;
		
		$params['id'] = $id;
		$params['pid'] = $fileinfo['pid'] ;
		$params['snap_id'] = $fileinfo['id'] ; //截图出自
		$params['status'] = 1;
		
		$params['uid'] = $fileinfo['uid'];
		$params['cuid'] = $fileinfo['cuid'];
		
		$params['sid'] = $usi['sid'];
		$params['oid'] = $usi['oid'];
		
		//rlog($params);
		
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set file failed!", $params);
			return false;
		}		
		
		//更新原文件记录中snap_id字段
		$snap_id = $id;
		$params = array('id'=>$fileinfo['id'], 'snap_id'=>$snap_id);
		$res = $this->set($params);
		
		return $res;
	}
	
	
	protected function doFile2Org($f2oinfo)
	{
		/* not use WEBDAV*/
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: NOT use WEBDAV!!!!!");
		return false;
		
		$id = $f2oinfo['id'];
		
		$m = Factory::GetModel('file2org');		
		$m->setStatus($id, 2);
		
		$oid = $f2oinfo['oid'];
		$fid = $f2oinfo['fid'];
		$fileinfo = $this->get($fid);
		if (!$fileinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no file of id '$fid'");
			$m->del($id);
			return false;
		}
		
		$src = $fileinfo['opath'];
		
		
		//dst
		$sid = $f2oinfo['sid'];
		$m2 = Factory::GetModel('storage');
		$dststorageinfo = $m2->get($sid);
		if (!$dststorageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no storage of id '$sid'");
			$m->del($id);
			return false;
		}
		
		$mountdir = $dststorageinfo['mountdir'];
		$dst = $mountdir.DS.$fileinfo['path'];
		//rlog('$src='.$src.', $dst='.$dst);			
		
		if (!file_exists($dst)) {
			$dstdir = dirname($dst);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'dir='.$dstdir);	
			if (!is_dir($dstdir)) { //检查文件是否存在
				if (!s_mkdir($dstdir)) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call mkdir '$dstdir' failed!");
					return false;
				}			
			}
			/* not use WEBDAV mount path 
			$res = copy($src, $dst);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call copy failed! src=$src,dst=$dst");
				$m->del($id);
				return false;
			}*/
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: NOT use WEBDAV!!!!!");
			return false;
		}
		if (!file_exists($dst)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no dst! dst=$dst");
			$m->del($id);
			return false;
		}
		
		//更新发布: playUrl
		//$playUrl = $dststorageinfo['vodrooturl'].'/'.$fileinfo['path'];
		$playUrl = $dststorageinfo['lanvodrooturl'].'/'.$fileinfo['path'];
		
		$m3 = Factory::GetModel('pub2org');
		$m3->updatePlayUrlByFidOid($fid, $oid, $playUrl);					
		
		//更新状态				
		$m->setStatus($id, 3);
	}
	
	protected function timerProcessFile2Org()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		$m = Factory::GetModel('file2org');
		//查找一条需要待镜像的文件
		$f2oinfo = $m->getOne(array('status'=>1));
		if ($f2oinfo) {
			if (!$this->lock()) {
				$res = $this->doFile2Org($f2oinfo);			
				$this->unlock();
			}
		}		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
	}
	
	protected function timerProcessFileDeleted()
	{
		$res = false;
		$params = array('status'=>7);
		$udb = $this->select($params);
		foreach ($udb as $key=>$v) {
			$res = $this->delOne($v['id']);
		}	
		return $res;	
	}	
	
	public function timerProcess()
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		
		//转码
		$filter = array('status'=>2);
		$fdb = $this->select($filter); //待转码
		foreach ($fdb as $key=>$v) {
			if (!$this->lock()) {
				$res = $this->timerProcessConvertVideo($v);
				if (!$res)			
					$this->unlock();
			}
		}
		
		//截图
		$filter = array('type'=>1,'status'=>1, 'snap_id'=>0);
		$fdb = $this->select($filter);		
		foreach ($fdb as $key=>$v) {
			if (!$this->lock()) {
				$this->timerProcessSnapImageForVideoFile($v);
				$this->unlock();
			}
		}
		
		//更新转码服务是否正常并更新转码字节
		$res = $this->checkConvert();
		
		//同步
		//$res = $this->timerProcessFile2Org();
		
		//检查正在删除的文件
		$res = $this->timerProcessFileDeleted();
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  "OUT");
		
		return $res;
		
	}	
	
	public function setNumDelta($id, $delta)
	{
		return $this->addN($id, 'uses', $delta);
	}
}