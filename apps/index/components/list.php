<?php
/***
 * 
 * @file
 * 
 * 列表
 * 
 * 
 * */

defined( 'RMAGIC' ) or die( 'Restricted access' );
class ListComponent extends CListviewComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function ListComponent($name, $options=null)
	{
		$this->__construct($name, $options);	
	}
	
	
	protected function istmpl($name)
	{
		$tmpls = array('list', 'large');
		return isset($tmpls[$name]);
	}
	
	public function show(&$ioparams=array())
	{
		$cid = $this->_cid = $this->_id;
		$keyword = $this->request('q');
		!$keyword && $keyword = $this->request('keyword');
		$page = $this->requestInt('page', 0);
		$count = $this->requestInt('count', 10);
		$listviewid = 'listview'.$cid;
		$tmpl = isset($_COOKIE[$listviewid])?$_COOKIE[$listviewid]:'';
		// list/<ID>[/<PAGE>/<KEYWORD>]
		$id = 0;
		$idx = 0;
		
		$vpath = $ioparams['vpath'];
		
		$nr = count($vpath);
		switch($nr) {
			case 1:
				break;
			case 2:
				$page = intval($vpath[1]);
				break;
			case 3:
				$page = intval($vpath[1]);
				!$keyword && $keyword = $vpath[2];
				break;				
		}
				
		$catalog = get_catalog();
		$cdb = array();
		if ($cid) {
			$cdb = $catalog[$cid];		
			if ($cdb['linkurl'] && !is_start_with($cdb['linkurl'],'#')) {
				redirect($cdb['linkurl']);
				return false;
			}
				
			
			//查看当前目录是否生成页面，不成功页面，查询第一个子节点，作为当前页面内容
			if (($cdb['status']&0x2) === 0) {
				foreach ($catalog as $key=>$v) {
					if ($cid === $v['pid']) {
						$cid = $v['id'];
						$cdb = $catalog[$cid];
						break;
					}
				}
			}			
			
			$metakeyword = $cdb['metakeyword'];
			$metadescrip = $cdb['metadescrip'];
			$status = $cdb['status'];
			$name = $cdb['name'];
			$tpl_list = $cdb['tpl_list'];
			
		}  else {
			$metakeyword = '';
			$metadescrip = '';
			$status = 0;
			$name = i18n('Search');
			$tpl_list = '';
		}
		
		
		$this->assign("metakeyword", $metakeyword);
		$this->assign("metadescrip", $metadescrip);
		$this->assign("cata", $cdb);
		$this->assign("page", $page);
		
		//模板
		!$tmpl && $tmpl = ($status&0x32)?'large':'list';
			
		$this->assign('tmpl', $tmpl);
		
		$this->assign("_catalog_title", $name);
		$this->assign("_content_title", '');
		$this->assign("_keyword", $keyword);
		
				
		$this->setTpl($tpl_list? $tpl_list: 'list');
	}


	/** 特别处理 */
	protected function org(&$ioparams=array())
	{
		$oid = intval($ioparams['vpath'][0]);
		if (!$oid)
			exit('error');

		$this->assign('oid', $oid);
		$this->_template = 'orghome';
	}
}
