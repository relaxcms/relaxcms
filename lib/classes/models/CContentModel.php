<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define ('CF_CHECKED',	0x1);
define ('CF_RELEASE',	0x2);
define ('CF_READONLY',  0x4);
define ('CF_SHARE',		0x8);
define ('CF_FLOWER',	0x10);
define ('CF_COMMENT',	0x20);

define ('CF_MYFLAGS', ~(CF_CHECKED|CF_RELEASE|CF_READONLY));


class CContentModel extends CTableModel
{
	protected $_scf = array();
	protected $_catalogdb = array();

	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CContentModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
			case 'id':
				$f['searchable'] = true;	
				break;				
			case 'icon':
			case 'photo':
				$f['input_type'] = 'image';
				$f['show'] = false;		
				break;
			case 'video':
				$f['input_type'] = 'video';
				$f['show'] = false;	
				$f['searchable'] = true;				
				break;
			case 'cid':
				$f['input_type'] = 'treemodel';
				$f['model'] = 'catalog';	
				$f['searchable'] = 2;	
				$f['default'] = true;	
				break;
			case 'mid':
				$f['input_type'] = 'model';
				$f['model'] = 'model';
				$f['default'] = true;	
				$f['show'] = false;	
				break;	
			case 'author':				
			case 'editor':				
			case 'refer':				
				$f['show'] = false;	
				$f['input_type'] = 'varvalselector';
				break;			
			case 'content':
				$f['input_type'] = 'ckeditor';
				$f['show'] = false;	
				break;
			case 'summary':
				$f['show'] = false;		
				break;
			case 'aids':
				$f['input_type'] = 'gallery';
				$f['show'] = false;	
				break;
			case 'link':
				$f['input_type'] = 'link';
				$f['show'] = false;
				break;
			case 'comments':
			case 'cached':
				$f['show'] = false;
			case 'cmts':
			case 'hits':
				$f['edit'] = false;
				break;
			case 'cuid':
				$f['input_type'] = 'UID';
				$f['readonly'] = true;
				$f['edit'] = false;				
				break;
			case 'uid':
				$f['input_type'] = 'UID';
				$f['readonly'] = true;			
			case 'oid':
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'ctime':
				$f['readonly'] = true;
				$f['edit'] = false;
			//case 'taxis':
				$f['show'] = false;
				break;

			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				$f['sortable'] = true;
				break;
			case 'status':
				$f['input_type'] = 'onoff';
				//$f['edit'] = false;
				$f['sortable'] = true;
				break;
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				$f['sortable'] = true;
				break;			
			case 'taxis':
				$f['input_type'] = "sort";
				$f['sortable'] = true;
				break;			
			default:
				break;
		}
		return true;
	}

	public function getCount($params=array())
	{
		return $this->count('id',$params);
	}
	
	public function getSumHits($params=array())
	{
		return $this->sum('hits',$params);
	}
	

	protected function get_content_var_table()
	{
		$m = Factory::GetModel('var');
		return $m->get_var_table(RVAR_CONTENT);
	}
	
	protected function parseFilterParams(&$params, $strict=false)
	{
		//cid
		if (isset($params['cid']) && $params['cid'] == 0)
			unset($params['cid']);
		
		$res = parent::parseFilterParams($params, $strict);
		return $res;
	}
		

	
	
	public function formatForView(&$row, &$ioparams = array())
	{
		parent::formatForView($row, $ioparams);
		
		$id = $row['id'];
		//status
		//$row['_status'] = $this->formatLabelColorForView($row['status'], $row['_status']);
		//$row['_taxis'] = "<input type='text' name='params[taxis][$id]' value='$row[taxis]' class='form-control input-xsmall' />";
		
		$__ctype = 0;
		$isImage = 0;
		$isVideo = 0;
		$isAudio = 0;
		$extinfo = '';		
		
		$_dstroot = $ioparams['_dstroot'];
		$url = $ioparams['_webroot'].'/content/'.$id;
		$row['url'] = $url;

		//photo
		$photo = trim($row['photo']);
		if (!$photo) {
			$photo = $ioparams['_dstroot'].'/img/nopic.png';
		} else {
			$__ctype |= FT_IMAGE;
			$extinfo .= " <a href='$row[photo]' target=_blank class='gallery-img' data-gallery1='$row[photo]' data-id='$id' data-noabar=1 data-norequest=1> <i class='fa fa-image'></i></a>";	
		}
		
		if (is_url($photo)) {
			$row['photoUrl'] = $photo;
		} else {
			$row['photoUrl'] = $ioparams['_rooturl'].s_hslash($photo);
		}		
		//for listview
		$row['previewUrl'] = $row['photoUrl'];
		$row['_photo'] = "<img src='$row[photoUrl]' style='width:100%;'/>";
		
		//video
		$videoUrl = trim($row['video']);
		if ($videoUrl) {
			if (is_url($videoUrl)) {
				$row['videoUrl'] = $videoUrl;
			} else {
				$row['videoUrl'] = $ioparams['_rooturl'].s_hslash($videoUrl);	
			}
			$row['_video'] = $row['videoUrl'];
			$row['playurl'] = $row['videoUrl'];
			$row['previewLargeUrl'] = $row['previewUrl']; //视频封面
			$isVideo = 1;
			$__ctype |= FT_VIDEO;
			
			$extinfo .= " <a href='$url' target=_blank data-url='$videoUrl' class='videobox' data-id='$id'><i class='fa fa-film'></i></a>";			
		}


		$row['extinfo'] = $extinfo;
		$row['__ctype'] = $__ctype;
		
		//$cid = $row['cid'];
		//$row['cid'] = $this->formatModelForList($row, $fields['cid'], $ioparams);
		//$row['_cid'] = $cid;

				
		$name = $row['name'];
		
		$row['_name'] = " <a href='$url' target='_blank'> $name </a> $extinfo";
		
		
		
	}
	protected function formatOperate($row, &$ioparams=array())
	{
		$id = $row[$this->_pkey];
		
		$optdb = parent::formatOperate($row, $ioparams);

		$optdb[] = array(
			'name'=>'preview',
				'title'=>'预览',
				'action'=>'alink',
				'showbutton'=>true,
				'icon'=>'fa fa-search',
				'class'=>'default',
				'url'=>$ioparams['_webroot'].'/content/'.$id,
				'target'=>'_blank',
			);
		return $optdb;
	}

	
	protected function processDelContent($old)
	{
		$id = $old['id'];
		
		//解除模型
		$m = Factory::GetModel('content2model');
		$m->delete(array('cid'=>$id));
		
		//删除
		$modFile = Factory::GetModel('file');
		
		//查询所有引用的file
		$m = Factory::GetModel('file2model');
		$udb = $m->gets(array('mid'=>$id, 'modname'=>$this->_name)); 
		foreach ($udb as $key=>$v) {
			$fid = $v['fid'];
			$num = 0 - $v['num'];
			
			$modFile->setNumDelta($fid, $num);
			
			//删除引用
			$m->del($v['id']);
		}
	}
	
	public function del($ids)
	{
		if (!is_array($ids))
			$ids = explode(",", $ids);
		
		foreach($ids as $key=>$id) {
			$old = parent::del($id);
			if ($old) {
				//查询引用的图片，解除引用
				$this->processDelContent($old);
			}
		}		
		return $old;
	}
	
	//get
	public function get($id)
	{
		$res = parent::get($id);
		if ($res) {
			//查看模型字段信息
			$m = Factory::GetModel('content2model');
			$res2 = $m->getOne(array('cid'=>$id));
			if ($res2) {
				$modname = $res2['modname'];
				$mid = $res2['mid'];				
				$m3 = Factory::GetModel($modname);
				$res3 = $m3->get($mid);
				if ($res3) {
					
					$res = array_merge($res3, $res);
					
					//modname
					$res['modname'] = $modname;
					$res['mid'] = $mid;
					$res['modinfo'] = $res3;
				}
			}
		}
				
		return $res;
	}
	
	
	public function getByName($name)
	{
		return $this->getBy("where title='$name'");
	}
		
	protected function getIdFromUrl($url)
	{
		$pos = strrpos($url, '/');
		if ($pos === false) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid url!");
			return false;
		}
					
		$filename = substr($url, $pos+1);
		$pos = strpos($filename, '_');
		if ($pos === false) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid url!url='$url'");
			return false;
		}
		
		$id = substr($filename, 0, $pos);
		
		return $id;
		
	}	
	
	/*
	1/202109/14_ba99f7facf1b4a09201204f5245f212b.jpg
	*/
	protected function get_fid_by_url($url, &$fileinfo=array())
	{
		$m = Factory::GetModel('file');		
		$fid = $this->getIdFromUrl($url);
		if ($fid !== false) {
			$fileinfo = $m->get($fid);
			if ($fileinfo) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'fid='.$fid);
				return $fid;
			}
		}	
		return 0;
	}
	
	public function getWebPlayUrl($url, &$fileinfo=array())
	{
		if (is_url($url)) 
			return $url;
		$fid = 	$this->get_fid_by_url($url, $fileinfo);
		if (!$fid) //unknown url
			return $url;
		$m = Factory::GetModel('file');		
		
		$playurl =  $m->getPlayUrl($fileinfo);
		
		return $playurl;
	}
		
	
	//管理文件引用
	protected function processPostContent($params, &$ioparams=array())
	{
		$id = $params['id'];
		$selectimage = $params['selectimage'];
		
		$content = $params['content'];
		$photo = $params['photo'];
		$firstimg = '';
		$firstvideo = '';
		
		//检查src=""		
		$m = Factory::GetModel('file2model');
		$nums = array();
		
		$_content = stripslashes($content);
		$res = preg_match_all("/src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $_content, $urls);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $urls);		
		if ($res && count($urls[1]) > 0) {
			
			foreach ($urls[1] as $key=>$v) {
				//fid
				$fileinfo = array();
				$fid = $this->get_fid_by_url($v, $fileinfo);
				if ($fid) {
					if (isset($nums[$fid]))
						$nums[$fid] ++;
					else 
						$nums[$fid] = 1;
					if ($fileinfo['type'] & 0x4) { //图片
						if ($selectimage && !$firstimg) {
							$firstimg = $ioparams['_webroot'].'/file/'.$fileinfo['id'].'/'.$fileinfo['path'];
						};
					}
					/*
					static private $mimetype_type_ids = array(
		'video' => 0x01,
		'audio' => 0x02,
		'image' => 0x04,
		'document' => 0x08,
		'arball' => 0x10,
		);*/
					if ($fileinfo['type'] & 0x01) { //视频
						$firstvideo = $ioparams['_webroot'].'/file/'.$fileinfo['id'].'/'.$fileinfo['path'];						
					}
				}
				
			}
		}
		
		if ($firstimg && !$photo) {
			$_params['id'] = $id;
			$_params['photo'] = $firstimg;
			$this->update($_params);
		}
		
		if ($photo) {
			$fid = $this->get_fid_by_url($photo);
			if ($fid) {
				if (isset($nums[$fid]))
					$nums[$fid] ++;
				else 
					$nums[$fid] = 1;
			}			
		}
		
		//$firstvideo
		if ($firstvideo) {
			$_params['id'] = $id;
			$_params['video'] = $firstvideo;
			
			$this->update($_params);
		}
				
		//查询附件引用
		$pattern = "/\[attach\](\d+)\[\/attach\]/i";		
		$res = preg_match_all($pattern, $_content, $attachs);
		//rlog($attachs); 
		if ($res && count($attachs[1]) > 0) {
			foreach ($attachs[1] as $key=>$fid) {
				if (isset($nums[$fid]))
					$nums[$fid] ++;
				else 
					$nums[$fid] = 1;
			}
		}
		
		foreach ($nums as $fid=>$num) {
			
			$_params = array();					
			$_params['num'] = $num;
			$_params['fid'] = $fid;
			$_params['modname'] = $this->_name;
			$_params['mid'] = $id;			
			$m->set($_params);
		}
		
		
		//$ioparams['data'] = array('autobackurl'=>$ioparams['_base'].'?id='.$params['cid']);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,  __FUNCTION__, $params);
		
		
		return true;		
	}

	//图片本地化
	public function image2local($content, &$ioparams=array())
	{
		$host = $ioparams['_host'];
		
		//rlog($content);
		$_content = stripslashes($content);
		//preg_match_all("/<img[^>]*src=[\s]*\"(http:\/\/.+\.(jpg|gif|bmp|bnp))[\s]*\"/i", $data, $images);
		//preg_match_all("/<img[^>].*src=.*(http[s]?:\/\/.+\.(jpg|gif|bmp|png))/i", $content, $images);
		preg_match_all("/<img[^>]*src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $_content, $images);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $images);
		
		$src = array();
		$res = array ();
		
		$m = Factory::GetModel('file');
		
		$firstimg = '';
				
		foreach($images[1] as $key =>$v)
		{
			$v = trim($v);
			//http https
			if (strncasecmp($v, "http", 4) != 0) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "not start with 'http' url '$v'!");
				continue;			
			}
						
			if (strpos($v, $host) !== false) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "local image '$v' ");
				continue;
			}
			$finfo = $m->get_remote_file($v, $ioparams);	
			if (!$finfo) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "get file  '$v' failed! ");
				continue;
			}	
			
			$src[] = $v;			
			$res[] = $finfo['url'];
			
			if (!$firstimg) 
				$firstimg = $finfo['url'];
		}
		if (!$res) {
			return false;
		}
		
		
		$_content = str_replace($src, $res, $content); //再把内容中图片地址更换成对应的本地图片地址
		
		$ioparams['firstimg'] = $firstimg;
		return $_content;		
	}

	public function getFieldsForInput($params=array(), &$ioparams=array())
	{
		//初始化
		if (isset($params['modname']) && empty($params['id'])) {
			$m = Factory::GetModel($params['modname']);
			$info = $m->get($params['mid']);
			$params = array_merge($params, $info);
			//title特别处理
			if (isset($info['title']))
				$params['name'] = $info['title'];
		}
		
		$fdb = parent::getFieldsForInput($params, $ioparams);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
				
		
		return $fdb;
	}
	
	
	public function getModelFieldsForInput($params, &$ioparams=array())
	{
		$modname = $params['modname'];
		$mid = $params['mid'];
		
		$m = Factory::GetModel($modname);		
		$_params = $m->get($mid);
		
		$udb =  $m->getFieldsForInput($_params, $ioparams);		
		$fdb = array();
		foreach ($udb as $key=>$v) {
			if (!isset($this->_fields[$key]) && $v['edit']) {
				$fdb[] = $v;
			}
		}				
		return $fdb;
	}
	public function getModelFieldsForDetail($params, &$ioparams=array())
	{
		$modname = $params['modname'];
		$mid = $params['mid'];
		
		$m = Factory::GetModel($modname);		
		$_params = $m->get($mid);
		
		$udb =  $m->getFieldsForDetail($_params, $ioparams);		
		$fdb = array();
		foreach ($udb as $key=>$v) {
			if (!isset($this->_fields[$key]) && $v['detail']) {
				$fdb[] = $v;
			}
		}				
		return $fdb;
	}
	
	
	
	
	protected function initAddParams(&$params=array(), &$ioparams=array())
	{
		$params['taxis'] = 100;
	}
	
	protected function checkParams(&$params, &$ioparams=array())
	{
		//处理富文本框图片
		$imagetolocal = isset($params['imagetolocal'])?1:0;
		$selectimage = isset($params['selectimage'])?1:0;
		
		if ($imagetolocal) { //图片本地化
			$res = $this->image2local($params['content'], $ioparams);
			if ($res) {
				$params['content'] = $res;
				if ($selectimage) {
					$params['photo'] = $ioparams['firstimg'];
				}
			}
		}
		
		//cid
		if (isset($params['cid']) && !is_numeric($params['cid'])) {
			$m = Factory::GetModel('catalog');			
			$res = $m->getByName($params['cid']); 
			if ($res) {
				$params['cid'] = $res['id'];
			} 
		}
				
		$res = parent::checkParams($params, $ioparams);		
		
		return $res;
	}
	 
	protected function setContent2model($params, $ioparams)
	{
		$modname = $params['modname'];
		$mid = $params['mid'];	
		$cid = $params['id'];
		
		
		$m = Factory::GetModel('content2model');
		$m2cinfo = $m->getOne(array('cid'=>$cid));
		if (!$m2cinfo) {			
			//关联
			$_params = array();
			$_params['modname'] = $params['modname'];
			$_params['mid'] = $mid;
			$_params['cid'] = $cid;			
			$res = $m->set($_params);				
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set content2model failed!", $_params);
				return false;
			}
		}
		
		//冲突字段不更新
		foreach($this->_fields as $key=>$v) {
			if (isset($params[$key]))
				unset($params[$key]);
		}
		
		$m2 = Factory::GetModel($modname);
		$params['id'] = $mid;
		$res = $m2->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set model '$modname' failed!");
			return false;
		}
		return $res;
	}
	
	
	public function set(&$params, &$ioparams=array())
	{
		$res = parent::set($params, $ioparams);
		if ($res) { //模型
			if (isset($params['mid']) && $params['mid'] > 0) {
				$this->setContent2model($params, $ioparams);				
			}			
			if (isset($params['content']))		
				$this->processPostContent($params, $ioparams);

			$this->triggerContent($params['id']);
		}		
		return $res;
	}

	
	public function getContentListBy($where, $nr=0, &$ioparams=array())
	{
		$udb = $this->gets($where, $nr);
		
		$fields = $this->getFields();
		foreach($udb as $key=>&$v) {
			//$this->prev_content_single($v, $fields, $ioparams);
			$this->filterForListForFrontend($v, $fields, $ioparams);			
		}
		
		return $udb;
	}

	/*public function selectForListview(&$params, &$ioparams=array())
	{
		//filterfieldcfg
		$fields = array();
		$ffcfg = isset($params['filterfieldcfg'])?$params['filterfieldcfg']:array(
			'id'=>array(), 
			'title'=>array());

		if ($ffcfg) {
			foreach ($this->_fields as $key=>$v) {
				if (array_key_exists($key, $ffcfg)) {
					$v['sortable'] = $v['sortable']?true:false;
					$fields[$key] = $v;
				}
			}
		} else {
			$fields = $this->_fields;
		}
		
		//where
		if (isset($_REQUEST['params']))
			$params = array_merge($params, $_REQUEST['params']);
		
		//filter rows 
		$org_rows = $this->selectForView($params, $ioparams);
		$rows = $params['rows'];
		
		foreach ($rows as $key=>&$v) {
			//$v['name'] = $v['title'];
			$v['time'] = tformat_timelong($v['ts']);
			
			//previewUrl
			$previewUrl = $v['photo'];
			if (!$previewUrl) { //nopic
				$previewUrl = $ioparams['_dstroot'].'/img/nopic.png';
			} else {				
				$pattern = "/f\//i";
				$replacement = '${0}/preview/';
				$previewUrl = preg_replace($pattern, $replacement, $previewUrl);
			}
			$v['previewUrl'] = $previewUrl;			
		}
		
			
		$data = array(
				'listview'=> array(
					'name'=>$this->_name,
					'fields'=>$fields,
					'total'=>$params['total'],
					'page'=>$params['page'],
					'pages'=>$params['pages'],
					'page_size'=>$params['page_size'],
					'num'=>$params['num'],
					'sort'=>$params['sort'],
					'order'=>$params['order'],
					'rows'=>$rows
					)
				);
		return $data;		
		
	}*/
	
	

	public function getForView($id, &$fields=array(), &$ioparams = array())
	{
		$res = parent::getForView($id, $fields, $ioparams);
		return $res;
	}


	protected function is_mycontent($cinfo)
	{
		$myinfo = get_userinfo();
		return $myinfo['uid'] == $cinfo['cuid'];
	}



	public function setMyFlags($id, $flagsMask)
	{
		$cinfo  = $this->get($id);
		if (!$cinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no content id '$id'");
			return false;
		}

		if (!$this->is_mycontent($cinfo)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "not my content!");
			return false;
		}

		$old = $cinfo['flags'];
		if ($old & CF_CHECKED) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid old '$old'!");
			return false;
		}
		if ($flagsMask & ~CF_MYFLAGS) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "cannot set CF_MYFLAGS for self!");
			return false;
		}

		$new = $old ^ $flagsMask; //指定位取反
		$new &= ~CF_CHECKED;

		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "id=$id, statusMask=$flagsMask, oldstatus=$old, newstatus=$new");

		$params = array();
		$params['id'] = $id;
		$params['flags'] = $new;
		$res = $this->update($params);
		

		return $res;
	}
	
	protected function changeFlagsForUpdateStatus($cinfo)
	{
		$flags = intval($cinfo['flags']);
		$status = ($flags & (CF_CHECKED|CF_RELEASE)) == (CF_CHECKED|CF_RELEASE)?1:2;
		if ($status == 1) {
			if ($cinfo['status'] == 1) {
				return true;				
			}			
		} else {
			if ($cinfo['status'] != 1) {
				return true;				
			}		
		}
		
		$params = array();
		$params['id'] = $cinfo['id'];
		$params['status'] = $status;
		
		$res = $this->update($params);
		
		return $res;
	}
	protected function triggerContent($id)
	{
		$info = $this->get($id);	
		if ($info) { 
			$m = Factory::GetModel('content2module');
			$m->trigger('change', $info);			
			
			//更新status
			$this->changeFlagsForUpdateStatus($info);			
		}	
	}

	public function mck($id, $flagsMask, $fieldname='')
	{
		$res = parent::mck($id, $flagsMask, $fieldname);
		if ($res) {//flags变动
			$this->triggerContent($id);
		}
		
		return $res;
	}
	
	public function getForWebview($id)
	{
		$cinfo = $this->get($id);
		if (!$cinfo)
			return false;

		//filter video tag
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "TODO filter video tag");

		return $cinfo;
	}

	public function getAidsdb($id, &$ioparams=array())
	{
		$cinfo = $this->get($id);
		if (!$cinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}

		$fdb = array();
		$aids = $cinfo['aids'];
		$content_fid = $this->get_fid_by_url($cinfo['video'], $dfileinfo);
		
		if ($aids) {
			$m = Factory::GetModel('file');
			$udb = $m->getList("where id in ($aids)  order by taxis asc ", 0, $ioparams);
			if (!$udb) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call file gets failed!");
				return false;
			}
			foreach($udb as $key=>$v) {
				/*if ($v['id'] == $content_fid) {
					continue;
				}*/
				
				if ($v['isdir']) { //目录	
					$pid = $v['id'];
					$udb2 = $m->getList("where pid=$pid and type=1 and status=1  order by taxis asc, name asc  ", 0, $ioparams);
					foreach ($udb2 as $k2=>$v2) {
						$fdb[] = $v2;
					}
				} else if ($v['type'] == 1 && $v['_status'] == 1){
					$fdb[] = $v;
				}
			}
		}
		return $fdb;
	}
	
	
	public function getListFromContent2Module($params=array(), $nr=0, &$ioparams=array())
	{
		$m = Factory::GetModel('module');
		
		$res = $m->getOne(array('mid'=>$params['module_id']));
		if (!$res) 
			return false;
		
		$mid = $res['id'];
		$m2 = Factory::GetModel('content2module');
		
		$_params = array('mid'=>$mid);
		if ($nr > 0)		
			$_params['limit'] = $nr;
		
		$udb = $m2->select($_params);
		
		$cdb = array();
		foreach($udb as $key=>$v) {
			$content_id = $v['cid'];
			$info = $this->get($content_id);
			if (!$info) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknown content_id '$content_id'!");
				continue;
			}
			
			$cdb[$v['cid']] = $info;
		}
		
		array_sort_by_field($cdb, 'taxis', true);
		
		
		return $cdb;		
	}
	
	public function getList($params=array(), $nr=0, &$ioparams=array())
	{
		$params['status'] = 1; //发布
		
		$udb = array();
		if (isset($params['module_id'])) 
			$udb = $this->getListFromContent2Module($params, $nr, $ioparams);	
			
		if (!$udb)
			$udb =  parent::getList($params, $nr, $ioparams);
		
		
		foreach($udb as $key=>&$v) {
			$this->formatForView($v, $ioparams);
		}
		
		return $udb;
	}
	
	public function getListForFrontend($params=array(), $nr=0, &$ioparams=array())
	{
		$udb = $this->getList($params, $nr, $ioparams);
		
		foreach ($udb as $key=>&$row) {
			$time_format = isset($ioparams['time_format'])?$ioparams['time_format']:'Y-m-d';
			$maxlen = isset($ioparams['maxlen'])?$ioparams['maxlen']:128;
			
			$row['show_time'] = tformat($row['_ts'], $time_format);
			$row['longtime'] = tformat_timelong($row['_ts']);
			$row['subtitle'] = $row['_title'];
			if ($maxlen > 0) {
				$row['subtitle'] = utf8_substr($row['subtitle'], 0, $maxlen);			
			}
			
		}		
		return $udb;
	}
	

	public function formatForTV($params)
	{
		$tc = array();

		$tc['id'] = $params['id'];
		$tc['name'] = $params['_title'];
		$tc['photoUrl'] = $params['photoUrl'];
		$tc['ctype'] = 2;

		return $tc;

	}
	
	protected function get_live_by_url($videoUrl)
	{
		//是不是直播频道地址
		$m = Factory::GetModel('livechannel');
		$linfo = $m->getBy("where hlsurl='$videoUrl'");
		if (!$linfo) {
			return false;
		}
		return $linfo;			
	}
	
		
	protected function getLocalPlayUrl($oid, $fileinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		$fid = $fileinfo['id'];
		$type = $fileinfo['type'];
		
		if ($type != 1) { //非视频
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "not video type of id '$id'!");
			return false;
		}
				
		//原文件所有ID
		$org_oid = $fileinfo['oid'];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "org_oid=$org_oid,oid=$oid");
		//if ($org_oid != $oid) { //文件复制
			//查询本地镜像是否存在
			$m = Factory::GetModel('file2org');
			$f2oinfo = $m->getBy("where fid=$fid and oid=$oid");
			if ($f2oinfo) { //mountdir
				$sid = $f2oinfo['sid'];
				//检查状态
				$status = $f2oinfo['status'];
				if ($status == 3) {//复制完毕，播放本地
					$m2 = Factory::GetModel('storage');
					$storageinfo = $m2->get($sid);
					if ($storageinfo) {
						$vodrooturl = $storageinfo['vodrooturl'];
						return $vodrooturl.'/'.$fileinfo['path'];
					}
				} else{
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid file2org status! status=".$status.", expected status=3 COPY OK!" );
				}					
			} else {
				$newf2o = array(
					'fid'=>$fid,
					'oid'=>$oid);						
				$res = $m->set($newf2o);
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set file2org failed!");		
				}				
			}
		//}		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return false;
	} 
	
	
	protected function prePub2orgForVideo($playUrl, &$params)
	{
		$oid = $params['oid'];
		$id = $params['mid'];
		
		if (!$playUrl) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no playUrl of '$id'!");
			return false;
		}
		
		$fid = 0;
		
		//video
		$videoUrl = $playUrl;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "videoUrl=$videoUrl");
		$fileinfo = array();
		$fid = $this->get_fid_by_url($videoUrl, $fileinfo);
		if ($fid && $fileinfo['type'] == 1) { //是点播						
			//原文件所有ID
			$playUrl = $fileinfo['playurl'];
			if (($res = $this->getLocalPlayUrl($oid, $fileinfo))) {
				$playUrl = $res;
			}			
		} else if (($liveinfo = $this->get_live_by_url($videoUrl))) { //直播
			$org_oid = $liveinfo['oid'];
			$playUrl = $liveinfo['hlsurl'];
			$fid = $liveinfo['id'];
			
			$name = $liveinfo['name'];
			//rlog("name=$name, oid=$oid");
			$modLivechannel = Factory::GetModel('livechannel');
			$res = $modLivechannel->getBy("where oid=$oid and name='$name'");	
			//rlog($res);									
			if ($res && $res['lanhlsurl']) {
				$playUrl = $res['lanhlsurl'];
			}
			
			//各单位内网设备不可达二级流媒体外网IP
			//if ($org_oid != $oid) { //切换到目标内网播放地址播放
			//}
				
		} else {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "UNKNOWN url=$videoUrl!");
		}
		
		//aid
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "playUrl=$playUrl");
		$params['playUrl'] = $playUrl;
		$params['fid'] = $fid;
		
		return true;
	}
	
	protected function prePub2orgForAids($aids, &$params)
	{
		$oid = $params['oid'];
		$id = $params['mid'];
		
		$m = Factory::GetModel('file');
		$udb = $m->gets("where id in ($aids)");
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call file gets failed!");
			return false;
		}
		
		$fdb = array();
		foreach($udb as $key=>$v) {
			if ($v['isdir']) {//目录
				$pid = $v['id'];
				$udb2 = $m->gets("where pid=$pid and type=1 and status=1");
				foreach ($udb2 as $k2=>$v2) {
					$fdb[] = $v2;
				}
			} else if ($v['type'] == 1 && $v['_status'] == 1){
				$fdb[] = $v;
			}
		}
		
		if (!$fdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid aids '$aids'!");
			return false;
		}
		
		$adb = array();		
		foreach($fdb as $key=>$v) {
			$playUrl = $v['playurl'];
			if (($newPlayUrl = $this->getLocalPlayUrl($oid, $v))) {
				$playUrl = $newPlayUrl;
			}			
			$fid = $v['id'];
			$item = $v['id'];
						
			$adb[] = array('fid'=>$v['id'], 'playUrl'=>$playUrl);	
		}

		if (!$adb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid adb !");
			return false;
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $adb);
		
		$params['adb'] = $adb;	
		return true;
	}
		
	
	protected function prePub2org(&$params)
	{
		$oid = $params['oid'];
		$id = $params['mid'];
		
		$info = $this->get($id);
		if (!$info) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no info of id '$id'!");
			return false;
		}
		
		$res = false;
		
		//video
		$playUrl = trim($info['video']);
		if ($playUrl) {
			$res = $this->prePub2orgForVideo($playUrl, $params);			
		}
		//video
		$aids = trim($info['aids']);
		if ($aids)
			$res = $this->prePub2orgForAids($aids, $params);
		
		return $res;
	}
	
	protected function canMsgQueue($info, $oldflags, $flags, $oldstatus, $status, $forcepub)
	{
		return $forcepub;
	}	
	
}