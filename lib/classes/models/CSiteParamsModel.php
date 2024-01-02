<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class CSiteParamsModel extends CParamsModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CSiteParamsModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function initDefaultParams(&$params=array())
	{
		parent::initDefaultParams($params);

		!isset($params['name']) && $params['name'] = 'the RelaCMS sample site';
		!isset($params['title']) && $params['title'] = $params['name'];
		!isset($params['metakeyword']) && $params['metakeyword'] = $params['title'];
		!isset($params['metadescrip']) && $params['metadescrip'] = $params['title'];
		!isset($params['root']) && $params['root'] = $this->_webroot;
		!isset($params['count']) && $params['count'] = 15;
		!isset($params['index_script_name']) && $params['index_script_name'] = 'index.php';
		!isset($params['index_shtml_name']) && $params['index_shtml_name'] = 'index.html';
		
		!isset($params['page_size']) && $params['page_size'] = 12;
		!isset($params['searchtime']) && $params['searchtime'] = 1;
		!isset($params['searchmax']) && $params['searchmax'] = 100;
		!isset($params['htmlupdate'])  && $params['htmlupdate'] = 3;
		!isset($params['rss_itemnum'])  && $params['rss_itemnum'] = 20;
		!isset($params['rss_update']) && $params['rss_update'] = 2;
		!isset($params['rss_imagenum'])  && $params['rss_imagenum'] = 1;
		!isset($params['htmlpub'])  && $params['htmlpub'] = 0;
		!isset($params['is_open_comment'])  && $params['is_open_comment'] = 0;
		!isset($params['template'])  && $params['template'] = 'default';
		
		//index_open
		!isset($params['index_open'])  && $params['index_open'] = 1;

		//copyright
		!isset($params['copyright'])  && $params['copyright'] = '';
		//beian
		!isset($params['beian'])  && $params['beian'] = 'default';
		
		return $params;				
	}

}
