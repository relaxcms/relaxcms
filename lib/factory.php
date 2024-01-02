<?php

class Factory
{
	public static $application = null;
	public static $request = null;
	/**
	 * 创建应用程序
	 *
	 * @param mixed $name 名称
	 * @param mixed $options This is a description
	 * @return mixed 成功返回对象, 失败false
	 *
	 */
	static function GetApplication($name = null, $options = array())
	{
		if (!self::$application)
		{
			if (!$name)
			{
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no application '$name'");
				return false;
			}			
			self::$application = CApplication::GetInstance($name, $options);
		}		
		return self::$application;
	}
	
	
	static function GetApp($name = null, $options = array())
	{
		if (!$name) {
			$name = APPNAME;
			$app = Factory::GetApplication($name, $options);
			return $app;
		}
		return CApplication::GetInstance($name, $options);
	}
	
	/**
	 * 获取插件
	 *
	 * @return mixed 插件二维数组
	 *
	 */
	static function GetApps($name=null)
	{
		$file = RPATH_CONFIG.DS."apps.php";
		if (file_exists($file)) {
			require $file;	
		}
		if (!isset($apps))
			$apps = array();
		if (!$name)
			return $apps;
		else
			return $apps[$name];
	}
	
	
	static function &GetRequest($options = array())
	{
		if (!self::$request)
		{
			self::$request = CRequest::GetInstance($options);
		}		
		return self::$request;
	}
	
	static function &GetParams()
	{
		$r = Factory::GetRequest();
		$params = array();
		$r->getRequestParams($params);
		return $params;
	}
		
	/**
	 * 创建附件对象
	 *
	 * @return mixed This is the return value description
	 *
	 */
	static function &GetAttach()
	{
		static $instance;		
		if (!is_object($instance)) {			
			$instance = Attach::GetInstance();
		}
		return $instance;
	}
	
	
	/**
	 * 创建excel对象
	 *
	 * @param mixed $excel_file 加载excel文件
	 * @return mixed 成功返回对象，失败null
	 *
	 */
	static function GetExcelFile($excel_file)
	{
		$file =  RPATH_PHPEXCEL.DS.'Classes/PHPExcel/IOFactory.php';
		if (file_exists($file)) {
			require $file;
			$excel = PHPExcel_IOFactory::load($excel_file);
			return $excel;
		}
		return false;
	}
	
	static function GetExcel()
	{
		static $instance;		
		if (!is_object($instance))
		{
			require RPATH_PHPEXCEL.DS.'Classes/PHPExcel/IOFactory.php';
			$instance = new PHPExcel();
		}
		return $instance;
	}
	
	//创建配置
	static function GetConfig($name='system', $options = array())
	{
		return CConfig::GetInstance($name, $options);
	}
	
	static function GetDBConfig($name='db0', $options = array())
	{
		return CDBConfig::GetInstance($name, $options);
	}
	
	static function GetSVN($options = array())
	{
		return Factory::GetModel('svn', $options);
	}
	
	
	static function GetLog()
	{
		return CLog::GetInstance();
	}
	
	
	static function &GetAdmin()
	{
		return Factory::GetModel('admin');
	}
	
		
	static function &GetModel($name, $options=array())
	{
		return CModel::GetInstance($name, $options);
	}
	
	
	/**
	 * 创建用户对象
	 *
	 * @param mixed $type 用户类型
	 * @param mixed $options 配置参数
	 * @return mixed This is the return value description
	 *
	 */
	static function &GetUser($options=array())
	{
		return Factory::GetModel('user', $options);
	}
	
	static function &GetGroup($options=array())
	{
		return Factory::GetModel('group', $options);
	}
	
	static function &GetRole($options=array())
	{
		return Factory::GetModel('role', $options);
	}	
		
	static function &GetCluster($name, $options=array())
	{
		return CCluster::GetInstance($name, $options);
	}
		
	static function &GetSite($options=array())
	{
		return Factory::GetModel('site', $options);
	}
	
	static function &GetPolicy($options=array())
	{
		return Factory::GetModel('policy', $options);
	}		
	
	/**
	 * 创建数据库对象
	 *
	 * @param mixed $dbtype 驱动类型,如mysql,sqlite
	 * @param mixed $options 配置参数
	 * @return mixed This is the return value description
	 *
	 */
	static function GetDBO($name='db0', $options=array())
	{		
		return CDatabase::GetInstance($name, $options);
	}
	
	
	/**
	 * 创建组对象
	 *
	 * @param mixed $name This is a description
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	static function &GetComponent($name, $options=null)
	{
		return CComponent::GetInstance($name, $options);
	}
	
	
	/**
	 * 获取插件 
	 *
	 * @param mixed $name This is a description
	 * @param mixed $options This is a description
	 * @return mixed This is the return value description
	 *
	 */
	static function &GetPlugin($name, $options=null)
	{
		return CPlugin::GetInstance($name, $options);
	}
	
	static function &GetPortal($name, $options=null)
	{
		return CComponent::GetInstance($name, $options);
	}
	
	static function &GetTemplate($tpl="default")
	{
		return CTemplate::GetInstance($tpl);
	}
	
	//缓存操作
	static function &GetCache()
	{
		static $instance;		
		if (!is_object( $instance )) 
		{
			$instance = CCache::GetInstance();
		}		
		return $instance;
	}
	
	//创建zip
	static function &GetZip()
	{
		static $instance = null;		
		if (!is_object( $instance )) 
		{
			$instance = new CZip();
		}		
		return $instance;
	}
	
	//创建tar
	static function &GetTar()
	{
		static $instance = null;		
		if (!is_object( $instance )) {
			$instance = new CTar();
		}		
		return $instance;
	}
	
	
	//字串数组
	static function GetI18ns()
	{
		$app = Factory::GetApp();
		if ($app) {
			return$app->getI18n();
		}
		return false;
	}
	
	static function GetLanguage($appname=null)
	{
		$app = Factory::GetApp($appname);
		if ($app) {
			return$app->getI18n();
		}
		return array();
	}
		
	/**
	 * 菜单对象
	 *
	 * @return mixed This is the return value description
	 *
	 */
	static function GetMenu($app=null)
	{
		return CMenu::GetInstance($app);
	}
	
	/**
	 * 提取菜单
	 *
	 * @return mixed This is the return value description
	 *
	 */
	static function GetMenus()
	{
		$m = CMenu::GetInstance();
		return $m->getMenus();
	}

	
	//模块
	static function &GetModule($name, $attribs=array())
	{
		return CModule::GetInstance($name, $attribs);
	}
	
	
	//变量
	static function GetVar()
	{
		$file = RPATH_CACHE_TABLE.DS.'var.php';
		if (file_exists($file)) {
			require $file;
			return $vardb;
		}
		return null;
	}
	
	static function &GetSocket()
	{
		static $instance;	
			
		if (!is_object( $instance )) 
		{
			$instance = CSocket::GetInstance();
		}		
		return $instance;
	}
	
	static function &GetHttp($surl)
	{
		static $instances;		
		if (!isset( $instances )) {
			$instances = array();
		}
		
		$sig = serialize($surl);		
		if (empty($instances[$sig])) {
			$instance = new HttpRequest($surl);	
			$instances[$sig] = &$instance;
		}			
		return $instances[$sig];
	}
	
	/**
	 * 获取组件信息
	 *
	 * @return mixed 二维数组
	 *
	 */
	static function GetComponentPathInfo($cnaame)
	{
		static $instances;
		if (!isset( $instances )) {
			$file = RPATH_CACHE.DS."components.php";
			if (file_exists($file)) {
				require $file;
				$instances = $components;
			} 	
		}
		return isset( $instances[$cnaame] )?$instances[$cnaame]:null;
	}
	
	
	/**
	 * 获取模型路径
	 *
	 * @return mixed 二维数组
	 *
	 */
	static function GetModelPathInfo($modnaame)
	{
		static $instances;
		if (!isset( $instances )) {
			$file = RPATH_CACHE.DS."models.php";
			if (file_exists($file)) {
				require $file;
				$instances = $models;
			} 	
		}
		return isset( $instances[$modnaame] )?$instances[$modnaame]:null;
	}
	/**
	 * get site configuraation
	 *
	 * @return mixed $array
	 * cache/site_configuration.php
	 */
	static function GetSiteConfiguration()
	{
		/*$scf = array();
		$file = RPATH_CONFIG.DS.'site_configuration.php';
		if (file_exists($file)) {
			require $file;
			$scf =  $site_configuration;
		}
		
		!isset($scf['title']) && $scf['title'] = 'a relacms front page';
		!isset($scf['metakeyword']) && $scf['metakeyword'] = $scf['title'];
		!isset($scf['metadescrip']) && $scf['metadescrip'] = $scf['title'];
		!isset($scf['page_size']) && $scf['page_size'] = 12;
		!isset($scf['list_count']) && $scf['list_count'] = 20;
		!isset($scf['searchtime']) && $scf['searchtime'] = 1;
		!isset($scf['searchmax']) && $scf['searchmax'] = 100;
		!isset($scf['htmlupdate'])  && $scf['htmlupdate'] = 3;
		!isset($scf['rss_itemnum'])  && $scf['rss_itemnum'] = 20;
		!isset($scf['rss_update']) && $scf['rss_update'] = 2;
		!isset($scf['rss_imagenum'])  && $scf['rss_imagenum'] = 1;
		!isset($scf['htmlpub'])  && $scf['htmlpub'] = 0;
		!isset($scf['is_open_comment'])  && $scf['is_open_comment'] = 0;
		!isset($scf['template'])  && $scf['template'] = 'default';
		
		//copyright
		!isset($scf['copyright'])  && $scf['copyright'] = '';
		//beian
		!isset($scf['beian'])  && $scf['beian'] = 'default';
		*/
		$m = Factory::GetModel('site_config');
		$scf = $m->getParams();
		
		return $scf;
	}
	
	
	static function &GetEncrypt()
	{
		static $instance = null;		
		if (!is_object( $instance )) 
		{
			$instance = new CEncrypt();
		}		
		return $instance;
	}
	
		
	/**
	* 树
	*
	* @return mixed This is the return value description
	*
	*/
	static function GetTree($name)
	{
		return CTree::GetInstance($name);
	}
	
	static function &GetWebDAV($name="RC", $options=array())
	{
		return CWebDAV::GetInstance($name, $options);
	}
	
	static function &GetStorage($sid=0)
	{
		static $instances;		
		if (!isset( $instances )) {
			$instances = array();
		}		
		if (empty($instances[$sid])) {
			$instance = new CStorage($sid);	
			$instances[$sid] = &$instance;
		}			
		return $instances[$sid];
	}
	
	static function &GetImage()
	{
		static $instance = null;		
		if (!is_object( $instance )) 
		{
			$instance = new CImage();
		}		
		return $instance;
	}
	
	
	static function &GetFile()
	{
		return Factory::GetModel('file');
	}
	
	static function &GetMail()
	{
		static $instance = null;		
		if (!is_object( $instance )) {
			$instance = new CMailer();
		}		
		return $instance;
	}
	
	static function GetSms($name="default", $options=array())
	{
		return CSms::GetInstance($name, $options);
	}		
	
	static function GetCatalog()
	{
		return Factory::GetModel('catalog');
	}	

	static function &GetLunar()
	{
		static $instance = null;		
		if (!is_object( $instance )) {
			$instance = new CLunar();
		}		
		return $instance;
	}	
	
	static function &GetUpgrade()
	{
		static $instance = null;		
		if (!is_object( $instance )) {
			$instance = new CUpgrade();
		}		
		return $instance;
	}	
	
	
	static function &GetPHPPDF($pdfilename, $options=array())
	{
		static $instance;		
		if (!is_object($instance))
		{
			require RPATH_LIB.DS.'phppdf/phppdf.php';
			$instance = new PHPPDF($pdfilename, $options);
		}
		return $instance;
	}	
	
	
	static function &GetReceipt($name='', $options=array())
	{
		return CReceipt::GetInstance($name, $options);
	}	
	
	
	static function &GetOAuth($name='', $options=array())
	{
		return COAuth::GetInstance($name, $options);
	}
	static function &GetPay($name='', $options=array())
	{
		return CPay::GetInstance($name, $options);
	}	
	
	
	static function &GetQRCode($name='', $options=array())
	{
		return CQRCode::GetInstance($name, $options);
	}
	
	static function &GetMarkdown()
	{
		static $instance = null;		
		if (!is_object( $instance )) {
			$instance = new CParseMarkDown();
		}		
		return $instance;
	}
	
	static function &GetPDF($name='_', $options=array())
	{
		return CPDF::GetInstance($name, $options);
	}
	
}