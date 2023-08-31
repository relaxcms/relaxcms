<?php

/**
 * @file
 *
 * @brief 
 * 开放认证
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class GithubOAuth extends COAuth
{

	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function GithubOAuth($name, $options=array())
	{
		$this->__construct($name, $options);
	}
		
	public function getConfigInfo(&$params=array())
	{
		$url = 'https://github.com/login/oauth/authorize';
		$redirect_uri = urlencode(trim($params['callback']));
		$client_id = trim($params['appid']);
		$state = $params['id'];
		
		$ourl = "$url?client_id=" . $client_id . "&state=$state";//&redirect_uri=" . $redirect_uri;	
		
		$params['url'] = $ourl;
		//icon
		$params['_icon'] = '<i class="fa fa-github"></i>';
		$params['class'] = 'default';
				
		return $params;
	}


	public function getDefaultBgColor($ioparams=array())
	{
		return 'default';
	}


	public function getDefaultIcon($ioparams=array())
	{
		return '<i class="fa fa-github"></i>';
	}
	
	protected function getAccessToken($params)
	{
		
		$code = $params['code'];//GitHub回调返回的授权码
		if (!$code) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no code!");
			return false;
		}
		
		//$ourl = $cf['api_oauth_github_url'];
		$client_id = $params['appid'];//填写github登记表是返回的Client ID
		$client_secret = $params['appsecret'];//填写github登记表是返回的Client Secret
		
		//请求令牌链接
		$access_token_url = "https://github.com/login/oauth/access_token?client_id=$client_id&client_secret=$client_secret&code=$code";
		
		//请求令牌
		$res = curlGET($access_token_url);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET failed!url=$access_token_url");
			return false;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $access_token_url, $res);
		
		
		//string(78) "access_token=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&scope=&token_type=bearer" 
		$tinfo = array();
		parse_str($res, $tinfo);
		$access_token = isset($tinfo['access_token']) ? $tinfo['access_token'] : '';
		if (!$access_token) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no access token '$access_token'!", $res);
			return false;
		}
		
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
		
		//根据令牌获取到用户信息
		$info_url = "https://api.github.com/user?access_token=".$access_token;
		$params = array("Authorization: token $access_token");
		$data = curlGET($info_url, $params);
		$resJson = json_decode($data, true);
		if (!$resJson) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Access user info failed!'url=$info_url'!");
			return false;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $resJson);
		/*
		{
		"login": "relaxcms",
		"id": 102007912,
		"node_id": "U_kgDOBhSEaA",
		"avatar_url": "https://avatars.githubusercontent.com/u/102007912?v=4",
		"gravatar_id": "",
		"url": "https://api.github.com/users/relaxcms",
		"html_url": "https://github.com/relaxcms",
		"followers_url": "https://api.github.com/users/relaxcms/followers",
		"following_url": "https://api.github.com/users/relaxcms/following{/other_user}",
		"gists_url": "https://api.github.com/users/relaxcms/gists{/gist_id}",
		"starred_url": "https://api.github.com/users/relaxcms/starred{/owner}{/repo}",
		"subscriptions_url": "https://api.github.com/users/relaxcms/subscriptions",
		"organizations_url": "https://api.github.com/users/relaxcms/orgs",
		"repos_url": "https://api.github.com/users/relaxcms/repos",
		"events_url": "https://api.github.com/users/relaxcms/events{/privacy}",
		"received_events_url": "https://api.github.com/users/relaxcms/received_events",
		"type": "User",
		"site_admin": false,
		"name": null,
		"company": null,
		"blog": "",
		"location": null,
		"email": null,
		"hireable": null,
		"bio": null,
		"twitter_username": null,
		"public_repos": 1,
		"public_gists": 0,
		"followers": 0,
		"following": 0,
		"created_at": "2022-03-21T03:30:14Z",
		"updated_at": "2022-03-21T09:22:08Z"
		}*/
		//查询绑定本地帐户，登录，
		$userinfo = array();
		if (isset($resJson['id'])) {			
			$userinfo['user_id'] = $resJson['id'];
			$userinfo['name'] = $this->_name.'_'.$resJson['login'];
			$userinfo['nickname'] = $resJson['login'];
			$userinfo['avatar'] = $resJson['avatar_url'];
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"OUT");
		return $userinfo;
		
	}
	
}