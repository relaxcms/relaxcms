<?php

/**
 * @file
 *
 * @brief 
 * 开放认证
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class QqOAuth extends COAuth
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function QqOAuth($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	
	public function getConfigInfo(&$params=array())
	{
		
		$url = 'https://graph.qq.com/oauth2.0/authorize';
		$redirect_uri = urlencode($params['callback']);
		$client_id = $params['appid'];
		$state = $params['id'];
		
		$ourl = "$url?response_type=code&client_id=$client_id&state=$state&redirect_uri=$redirect_uri&scope=auth_user";	
			
		
		$params['url'] = $ourl;
		//icon
		$params['_icon'] = '<i class="fa fa-qq"></i>';
		$params['class'] = 'blue';
		
				
		return $params;
	}
	

	public function getDefaultBgColor($ioparams=array())
	{
		return 'blue';
	}

	public function getDefaultIcon($ioparams=array())
	{
		return '<i class="fa fa-qq"></i>';
	}


	
	protected function getAccessToken($params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN", $params);
		
		$code = $params['code'];//回调返回的授权码code
		if (!$code) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no code!");
			return false;
		}
		
		$client_id = $params['appid'];//App ID
		$client_secret = $params['appsecret'];//App Secret
		$redirect_uri = urldecode($params['callback']);
		
		//请求令牌链接
		//https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=[YOUR_APP_ID]&client_secret=[YOUR_APP_Key]&code=[The_AUTHORIZATION_CODE]&redirect_uri=[YOUR_REDIRECT_URI]
		//$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$client_id&secret=$client_secret&code=$code&grant_type=authorization_code";
		$url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=$client_id&client_secret=$client_secret&code=$code&redirect_uri=$redirect_uri";
		
		//请求令牌
		$res = curlGET($url);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET failed!url=$url");
			return false;
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $url, $res);		
		//failed: callback( {"error":100002,"error_description":"param client_secret is wrong or lost "} );
		//success: access_token=BE4C240D1C371DFFFF52B5062546607F&expires_in=7776000&refresh_token=3A4409C1BAD9ED4101F92973804782AF
		
		//https://wiki.connect.qq.com/%e5%bc%80%e5%8f%91%e6%94%bb%e7%95%a5_server-side
		//Step4：使用Access Token来获取用户的OpenID
		//https://graph.qq.com/oauth2.0/me?access_token=YOUR_ACCESS_TOKEN
		
		$tinfo = array();
		$udb = explode('&', $res);
		foreach ($udb as $key=>$v) {
			list($k2, $v2) = explode('=', $v);
			$k2 = trim($k2);
			$v2 = trim($v2);
			$tinfo[$k2] = $v2;
		}
		
		if (!isset($tinfo['access_token']) && !$tinfo['access_token']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "access token failed!url=$url", $tinfo);
			return false;
		}
		
		$access_token = $tinfo['access_token'];
		
		
		//根据令牌获取到用户信息
		//https://graph.qq.com/oauth2.0/me?access_token=YOUR_ACCESS_TOKEN
		$info_url = "https://graph.qq.com/oauth2.0/me?access_token=$access_token";
		$data = curlGET($info_url);
		// 519: callback( {"client_id":"102035654","openid":"17E9BA48826A03869539D090377C85A6"} );
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $data);
		$p1 = strpos($data, '{');
		$p2 = strrpos($data, '}');
		
		$data2 = substr($data, $p1, $p2-$p1+1); 
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $data2);
		$tinfo2 = json_decode($data2, true);
		// {"client_id":"102035654","openid":"17E9BA48826A03869539D090377C85A6"}
		
		if (!$tinfo2 || !isset($tinfo2['openid']) || !$tinfo2['openid']) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "get openid failed!'url=$info_url'!");
			return false;
		}
		
		$tinfo['openid'] = $tinfo2['openid'];
		$tinfo['client_id'] = $tinfo2['client_id'];
		
		return $tinfo;
	}
	
	
	public function getUserInfo($params=array())
	{	
		$tinfo = $this->getAccessToken($params);
		if (!$tinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "get token failed!");
			return false;
		}
		

		$access_token = $tinfo['access_token'];
		$openid = $tinfo['openid'];		
		
		$client_id = $params['appid'];//App ID
		
		//Step5：使用Access Token以及OpenID来访问和修改用户数据
		//https://graph.qq.com/user/get_user_info?access_token=YOUR_ACCESS_TOKEN&oauth_consumer_key=YOUR_APP_ID&openid=YOUR_OPENID
		$info_url = "https://graph.qq.com/user/get_user_info?access_token=$access_token&oauth_consumer_key=$client_id&openid=$openid";
		$data = curlGET($info_url);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $data);
		
		$qqinfo = json_decode($data, true);
		if (!$qqinfo || !isset($qqinfo['city']) || !$qqinfo['nickname']) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "get userinfo failed!'url=$info_url'!", $qqinfo);
			return false;
		}
		/*
		{
		  "ret": 0,
		  "msg": "",
		  "is_lost":0,
		  "nickname": "Jonny",
		  "gender": "男",
		  "gender_type": 2,
		  "province": "广东",
		  "city": "深圳",
		  "year": "1990",
		  "constellation": "",
		  "figureurl": "http:\/\/qzapp.qlogo.cn\/qzapp\/102035654\/17E9BA48826A03869539D090377C85A6\/30",
		  "figureurl_1": "http:\/\/qzapp.qlogo.cn\/qzapp\/102035654\/17E9BA48826A03869539D090377C85A6\/50",
		  "figureurl_2": "http:\/\/qzapp.qlogo.cn\/qzapp\/102035654\/17E9BA48826A03869539D090377C85A6\/100",
		  "figureurl_qq_1": "http://thirdqq.qlogo.cn/g?b=oidb&k=719o8MdCo2lxfEDytWBURA&kti=Y6b5-QAAAAI&s=40&t=1483286748",
		  "figureurl_qq_2": "http://thirdqq.qlogo.cn/g?b=oidb&k=719o8MdCo2lxfEDytWBURA&kti=Y6b5-QAAAAI&s=100&t=1483286748",
		  "figureurl_qq": "http://thirdqq.qlogo.cn/g?b=oidb&k=719o8MdCo2lxfEDytWBURA&kti=Y6b5-QAAAAI&s=100&t=1483286748",
		  "figureurl_type": "0",
		  "is_yellow_vip": "0",
		  "vip": "0",
		  "yellow_vip_level": "0",
		  "level": "0",
		  "is_yellow_year_vip": "0"
		}
			
		*/
		$userinfo = array();

		if (isset($qqinfo['nickname'])) {
			$user_id = $openid;

			$userinfo['user_id'] = $user_id;
			$userinfo['name'] = $this->_name.'_'.$user_id;
			$userinfo['nickname'] = $qqinfo['nickname'];
			$userinfo['gender'] = $qqinfo['gender'];
			$userinfo['avatar'] = $qqinfo['figureurl_qq'];
			$userinfo['unionid'] = $qqinfo['unionid'];
			$userinfo['country'] = $qqinfo['country'];
			$userinfo['province'] = $qqinfo['province'];
			$userinfo['city'] = $qqinfo['city'];
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"OUT", $userinfo);
		return $userinfo;
		
	}
	
	
}