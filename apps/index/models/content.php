<?php
/**
 * @file
 *
 * @brief 
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class ContentModel extends CContentModel
{
	public function __construct($name, $options=array())
	{
		//$options['modname'] = 'content';
		parent::__construct($name, $options);
	}
	
	public function ContentModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);

		switch ($f['name']) {
			case 'cid':
				$f['searchable'] = false;
				$f['show'] = false;
				break;
			case 'description':
			case 'status':
			case 'taxis':
			case 'flags':
				$f['show'] = false;
				break;
			default:
				break;	
		}
		return true;
	}

	protected function _initActions()
	{
		parent::_initActions();
		
		$this->_default_actions['edit']['enable'] = false;
		$this->_default_actions['del']['enable'] = false;
	}

	public function getInfoByID($webid, &$ioparams=array())
	{
		$_client = $ioparams['_client'];
		$_useragent = $ioparams['_useragent'];
				
		$tid = md5($webid);
		$tinfo = $this->getBy("where tid='$tid'");
		if (!$tinfo) {
			
			$m = Factory::GetModel('tmconfig');
			if (!method_exists($m, 'getConfig'))
				return false;
				
			$tmcfg = $m->getConfig();
			if ($tmcfg['savewebclient'] != 1) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING:no save webclient");
				return false;
			}
						
			$tinfo = array();
			$tinfo['tid'] = $tid;
			$tinfo['type'] = 2;
			$tinfo['name'] = "WEB".$_client;		
			$tinfo['systeminfo'] = $webid;
			
			$tinfo['ip'] = $_client;
			$tinfo['online'] = 1; //在线		
			$tinfo['last_access_time'] = time();
			
			$res = $this->set($tinfo);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call set failed!",$tinfo);
			}
		} else { 
			$tinfo['ip'] = $_client;
			$tinfo['last_access_time'] = time();
			$tinfo['last_access_id'] = 0; //最后访问
			$tinfo['online'] = 1; //在线
			$res = $this->set($tinfo);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call set failed!", $tinfo);
			}			
		}	
		
		return $tinfo;
	}
	
	
	protected function formatForViewForFrontend(&$row, &$ioparams=array())
	{
		$m = Factory::GetModel('catalog');
		$catalog = $m->getCatalogById($row['cid']);
		
		$mid = $row['mid'];
		$mmod = Factory::GetModel('model_'.$mid);
		$res = $mmod->get($row['id']);
		if ($res) {
			$row = array_merge($row, $res);
		}
		
		if (strlen($row['link']) > 0) {//内嵌链接受
			$row['url'] = $row['link'];
			$row['target'] = "target=\"_blank\"";
		} else {
			$row['url'] = $ioparams['_webroot']."/content/$row[id]";
		}
		if ($catalog)
			$row['listurl'] = $ioparams['_webroot']."/list/$catalog[id]";
		
		
		//附件
		$content = $row['content'];
		$pattern = "/\[attach\](\d+)\[\/attach\]/";		
		$res = preg_match_all($pattern, $content, $attachs);
		if ($res && count($attachs[1]) > 0)
		{
			$aid = implode(",", $attachs[1]);
			$m = Factory::GetModel('file');
			$udb = $m->gets(array("id"=>array('in'=>$aid)));
			
			$pa = array();
			$ra = array();
			foreach($udb as $key=>$v)
			{
				$pa[] = "[attach]".$v['id']."[/attach]";				
				$ra[] = "<div> <i class='fa ft ft-$v[extname]'></i> <a href='".$ioparams['_webroot']."/f/download/$v[id]/$v[filename]'>$v[name]</a> </div>";
				
			}			
			$content = str_replace($pa, $ra, $content);	
			$row['content'] = $content;		
			
		}
		
		$time_format = isset($ioparams['time_format'])?$ioparams['time_format']:'Y-m-d';
		$maxlen = isset($ioparams['maxlen'])?$ioparams['maxlen']:128;
		
		$row['show_time'] = tformat($row['_ts'], $time_format);
		$row['subtitle'] = $row['title'];
		if ($maxlen > 0) {
			$row['subtitle'] = utf8_substr($row['subtitle'], 0, $maxlen);			
		}
		
	}
	
	public function formatForView(&$row, &$ioparams = array())
	{
		$res = parent::formatForView($row, $ioparams);
		
		$this->formatForViewForFrontend($row, $ioparams);
		
	}
	public function select($params=array(), &$ioparams=array())
	{
		$params['status'] = 1;
		return parent::select($params, $ioparams);
	}
		
}
