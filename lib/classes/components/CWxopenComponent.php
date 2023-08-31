<?php

/**
 * @file
 *
 * @brief 
 * 起始页
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define('WXOPEN_TOKEN', 'wxrc_20221215_0208');

class CWxopenComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function WxopenComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	
	
	private function checkSignature()
	{
		$signature = $_REQUEST["signature"];
		$timestamp = $_REQUEST["timestamp"];
		$nonce = $_REQUEST["nonce"];
		
		$token = WXOPEN_TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	/*
	eg:
	<xml><ToUserName><![CDATA[gh_7bfe97f8b946]]></ToUserName>
	<FromUserName><![CDATA[oVMgv5qoOMRyy8uXO689ShtaQwHQ]]></FromUserName>
	<CreateTime>1671089007</CreateTime>
	<MsgType><![CDATA[text]]></MsgType>
	<Content><![CDATA[a]]></Content>
	<MsgId>23924239659343840</MsgId>
	</xml>
	
	*/
	private function readBody()
	{
		$body = '';
		
		//read
		$fp = fopen("php://input", "r");
		while (!feof($fp)) {
			$buf = fread($fp, 8192);
			if ($buf === false) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call fread failed!");
				break;
			}
			$body .= $buf;
		}		
		return $body;
	}
	
	
	
/*
配置：
参数 	描述
signature 	微信加密签名，signature结合了开发者填写的 token 参数和请求中的 timestamp 参数、nonce参数。
timestamp 	时间戳
nonce 	随机数
echostr 	随机字符串


收到消息：
	[signature] => 044ee2b32227ba4aed798be0737b7720fc8dead5
	    [timestamp] => 1671078617
	    [nonce] => 101853232
	    [openid] => oVMgv5qoOMRyy8uXO689ShtaQwHQ
	
	
	openid是微信号
*/

	public function show(&$ioparams=array())
	{
		rlog($_REQUEST);
		$echostr = $_REQUEST['echostr'];
		
		$body = $this->readBody();
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $body);
		
		if ($this->checkSignature()){
			echo $echostr;
		} else {
			echo "error";
		}
		exit;
	}	
}