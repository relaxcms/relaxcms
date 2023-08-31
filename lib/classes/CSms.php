<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CSms
{
	protected $_name;
	protected $_options;
	
	public function __construct($name, $options)
	{
		$this->_name = $name;
		$this->_options = $options;		
	}
	
	public function CSms($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	//创建
	static function &GetInstance($name, $options)
	{
		static $instances;
		
		if (!isset( $instances )) 
		{
			$instances = array();
		}
		
		$sig = md5($name);
		if (empty($instances[$sig]))
		{	
			$class = "";
			$cn = explode('_', $name);
			foreach($cn as $key=>$v) {
				$class .= ucfirst($v);
			}
			$class = $class.'Sms';
			
			$classfile = RPATH_SMSS.DS.$name.DS.$name.".php";
			if (file_exists($classfile)) {				
				require_once $classfile;	
			}
									
			if (!class_exists($class)) {
				$class	= "CSms";
			}
			
			$options['class'] = $class;
			$options['classfile'] = $classfile;
			
			$instance	= new $class($name, $options);
			$instances[$sig] =& $instance;
		}
		
		return $instances[$sig];
	}
	
/*
appCode	string	是	用户授权码，参考API调用
signId	Number	是	签名id，在我的服务->短信服务->签名管理里面查看
templateId	Number	是	模板id，在我的服务->短信服务->模板管理里面查看
phone	String	是	要发送的国内手机号码
params	Json	否	模板变量 使用Json对象格式
*/
	public function send($params = array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		
		$params['signId'] = intval($params['signId']);
		$params['templateId'] = intval($params['templateId']);
		
		if (!$params['appCode']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no appCode");
			return false;
		}
		
		if (!$params['signId']){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no signId");
			return false;
		}
		
		if (!$params['templateId']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no templateId");
			return false;
		}
		
		if (!$params['phone']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no phone");
			return false;
		}
		//https://api.topthink.com/sms/send

		$res = curlPOST($params['url'], $params);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
		
		return $res;		
	}
}