<?php

/**
 * @file
 *
 * @brief 
 * 开放认证
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class WechatOAuth extends COAuth
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function WechatOAuth($name, $options=array())
	{
		$this->__construct($name, $options);

	}	
	
	protected function _init()
	{
		parent::_init();

		if (!empty($this->_options['params'])) {
			$params = CJson::decode($this->_options['params']);
			if (is_array($params)) {
				$this->_options = array_merge($this->_options, $params);			
			}
		}		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $this->_options);
	}
	
	public function getConfigInfo(&$params=array())
	{
		
		$url = 'https://open.weixin.qq.com/connect/qrconnect';
		$redirect_uri = urlencode($params['callback']);
		$client_id = $params['appid'];
		$state = $params['id'];
		
		$ourl = "$url?appid=$client_id&state=$state&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_login#wechat_redirect";	
		
		
		$params['url'] = $ourl;
		//icon
		$params['_icon'] = '<i class="fa fa-wechat"></i>';
		$params['class'] = 'green-jungle';		
		
		return $params;
	}

	public function getDefaultBgColor($ioparams=array())
	{
		return 'btn-success';
	}

	public function getDefaultIcon($ioparams=array())
	{
		return '<i class="fa fa-wechat"></i>';
	}
	
	
	
	// 第二步：通过 code 换取网页授权access_token
	/*
		
	https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
		
		
	用户同意授权后
		
	如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE。
		
	   code说明：
		
	   code作为换取access_token的票据，每次用户授权带上的 code 将不一样，code只能使用一次，5分钟未被使用自动过期。
		
		
	    https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
		
		请求方法
		
	   获取 code 后，请求以下链接获取access_token：
		
	   https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
		
	参数 	是否必须 	说明
	appid 	是 	公众号的唯一标识
	secret 	是 	公众号的appsecret
	code 	是 	填写第一步获取的 code 参数
	grant_type 	是 	填写为authorization_code
		
		
	正确时返回的 JSON 数据包如下：
		
	{
	 "access_token":"ACCESS_TOKEN",
	 "expires_in":7200,
	 "refresh_token":"REFRESH_TOKEN",
	 "openid":"OPENID",
	 "scope":"SCOPE",
	 "is_snapshotuser": 1,
	 "unionid": "UNIONID"
	}
		
	参数 	描述
	access_token 	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
	expires_in 	access_token接口调用凭证超时时间，单位（秒）
	refresh_token 	用户刷新access_token
	openid 	用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
	scope 	用户授权的作用域，使用逗号（,）分隔
	is_snapshotuser 	是否为快照页模式虚拟账号，只有当用户是快照页模式虚拟账号时返回，值为1
	unionid 	用户统一标识（针对一个微信开放平台帐号下的应用，同一用户的 unionid 是唯一的），只有当 scope 为"snsapi_userinfo"时返回
		
		
	错误时微信会返回 JSON 数据包如下（示例为 Code 无效错误）:
		
	{"errcode":40029,"errmsg":"invalid code"}
		
		
		
	*/
	protected function getAccessToken($params)
	{
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$state = $params['state'];
		$code = $params['code'];//回调返回的授权码code
		if (!$code) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no code!");
			return false;
		}
		
		$client_id = $params['appid'];//App ID
		$client_secret = $params['appsecret'];//App Secret
		
		//请求令牌链接
		//https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code		
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$client_id&secret=$client_secret&code=$code&grant_type=authorization_code";
		
		//请求令牌
		$res = curlGET($url);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET failed!url=$url");
			return false;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $url, $res);		
		$tinfo = json_decode($res, true);
		if (!$tinfo) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "get access token failed!", $res);
			return false;
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $tinfo);	
		// {"errcode":41004,"errmsg":"appsecret missing, rid: 63a6c415-6705a686-3cc19b95"}
		if (isset($tinfo['errcode']) && $tinfo['errcode']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "access token failed!url=$url", $tinfo);
			return false;
		}
		
		
		// 第四步：拉取用户信息(需 scope 为 snsapi_userinfo)
		//    https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
		/*
		参数 	描述
		access_token 	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
		openid 	用户的唯一标识
		lang 	返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语*/
		
		
		//如果网页授权作用域为snsapi_userinfo，则此时开发者可以通过access_token和 openid 拉取用户信息了。		
		$access_token = $tinfo['access_token'];
		if (!isset($tinfo['access_token']) || !isset($tinfo['openid'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid access token failed!access_token=$access_token", $tinfo);
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
		$openid = $tinfo['openid'];
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$client_id = $params['appid'];//App ID
		$client_secret = $params['appsecret'];//App Secret
		
		//根据令牌获取到用户信息
		$info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
		$data = curlGET($info_url);
		$wxuserinfo = json_decode($data, true);
		if (!$wxuserinfo) { 
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Access user info failed!'url=$info_url'!");
			return false;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $wxuserinfo);
		/*
		{   
		"openid": "OPENID",
		"nickname": NICKNAME,
		"sex": 1,
		"province":"PROVINCE",
		"city":"CITY",
		"country":"COUNTRY",
		"headimgurl":"https://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
		"privilege":[ "PRIVILEGE1" "PRIVILEGE2"     ],
		"unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
		}
				
				
		*/

		$userinfo = array();

		if (isset($wxuserinfo['openid'])) {

			$user_id = !empty($wxuserinfo['unionid'])?$wxuserinfo['unionid']:$wxuserinfo['openid'];

			$userinfo['user_id'] = $user_id;
			$userinfo['name'] = $this->_name.'_'.$user_id;
			$userinfo['nickname'] = $wxuserinfo['nickname'];

			$userinfo['sex'] = $wxuserinfo['sex'];
			$userinfo['avatar'] = $wxuserinfo['headimgurl'];
			$userinfo['unionid'] = $wxuserinfo['unionid'];
			$userinfo['country'] = $wxuserinfo['country'];
			$userinfo['province'] = $wxuserinfo['province'];
			$userinfo['city'] = $wxuserinfo['city'];

		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"OUT");
		return $userinfo;
		
	}



	private function getSchema()
    {
        return 'WECHATPAY2-SHA256-RSA2048';
    }



    private function buildMessage($nonce, $timestamp, $requestUrl, $body = '')
    {
        $method = 'POST';
        $urlParts = parse_url($requestUrl);
        $canonicalUrl = ($urlParts['path'] . (!empty($urlParts['query']) ? "?{$urlParts['query']}" : ""));
        return strtoupper($method) . "\n" .
            $canonicalUrl . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";
    }

    private function sign($message)
    {
        if (!in_array('sha256WithRSAEncryption', openssl_get_md_methods(true))) {
            rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "当前PHP环境不支持SHA256withRSA");
            return false;
        } 

        $res = $this->_options['prikey']; //file_get_contents($this->privateKeyPath);
        rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'res='.$res);

        if (!openssl_sign($message, $sign, $res, 'sha256WithRSAEncryption')) {
            rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "签名验证过程发生了错误");
            return false;
        }

        return base64_encode($sign);
    }


    protected function getToken($requestUrl, $reqParams=array())
    {
        $body = $reqParams ?  json_encode($reqParams) : '';
        $nonce = randName(32);
        $timestamp = time();
        $message = $this->buildMessage($nonce, $timestamp, $requestUrl,$body);
        $sign = $this->sign($message);
        $serialNo = $this->_options['serialnum'];
        return sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->_options['mchid'], $nonce, $timestamp, $serialNo, $sign
        );
    }


	protected function getAuthStr($requestUrl, $reqParams=array())
    {
        $schema = $this->getSchema();
        $token = $this->getToken($requestUrl, $reqParams);
    
        $auth = $schema.' '.$token;

        return $auth;
    }


	protected function curlPost($url, $postData = array(), $options = array())
    {
		$auth = $this->getAuthStr($url, $postData);

		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "auth=".$auth);

        if (is_array($postData)) {
            $postData = json_encode($postData);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: '.$auth,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
        ));

        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'Error:' . curl_error($ch));
		}
        curl_close($ch);

        rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $data);
        
        return $data;
    }


	public function payOrder($orderinfo, &$ioparams=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");

		//

		$apiurl = 'https://api.mch.weixin.qq.com/v3';

		$appid = $this->_options['appid'];
		$appsecret = $this->_options['appsecret'];
		$mchid = $this->_options['mchid'];
		
		$notifyurl = $this->_options['notifyurl'];
		$returnUrl = $this->_options['returnurl'];

		$description = $orderinfo['name'];
		$oid = $orderinfo['id'];
		$order_id = $orderinfo['order_id'];
		$fee = $orderinfo['fee'];
		
		//{"mchid":"1649552828","serailnum":"6B40EB209E0F546E3D91DD22B8D372E60FFF66C6"}

		
		$reqParams = array(
            'appid' => $appid,        //公众号或移动应用appid
            'mchid' => $mchid,        //商户号
            'description' => $description,     //商品描述
            'attach' => $oid,              //附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用
            'notify_url' => $notifyurl,       //通知URL必须为直接可访问的URL，不允许携带查询串。
            'out_trade_no' => $order_id,      //商户系统内部订单号，只能是数字、大小写字母_-*且在同一个商户号下唯一，详见【商户订单号】。特殊规则：最小字符长度为6
            'amount'=>array(
                'total'=> floatval($fee) * 100, //订单总金额，单位为分
                'currency'=> 'CNY', //CNY：人民币，境内商户号仅支持人民币
            ),
            'scene_info'=>array(        //支付场景描述
                'payer_client_ip'=>'127.0.0.1'   //调用微信支付API的机器IP
            )
        );

        $reqUrl = $apiurl.'/pay/transactions/native';
        $response = $this->curlPost($reqUrl, $reqParams);

        $result = json_decode($response, true);

        $status = 0;
		$msg = '';
		if (isset($result['code']) && !isset($result['code_url'])){
		    rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $result['code'].':'.$result['message']);
		    $status = $result['code'];
		    $msg = $result['message'];
		}

		//url
		$qr = Factory::GetQRCode();
		$data = $qr->qrData($result['code_url']);
		
		$payinfo = array();
		$payinfo['qrcode'] = $data;
		$payinfo['url'] = $result['code_url'];

		$payinfo['status'] = $status;
		$payinfo['msg'] = $msg;

		return $payinfo;
	}

	protected function decrypt($ciphertext, $key, $iv = '', $aad = '')
    {
    	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $ciphertext, $key, $iv, $aad);

    	$cipher = 'aes-256-gcm';
    	$block_size = 16;

		if (!in_array($cipher, openssl_get_cipher_methods())) {
			rlog(RC_LOG_ERROR, __FILE__,__LINE__, __FUNCTION__, "NOT SUPPORT '$cipher'!");
			return false;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $ciphertext);
        $ciphertext = base64_decode($ciphertext);

        $authTag = substr($ciphertext, $tailLength = 0 - $block_size);
        $tagLength = strlen($authTag);

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $authTag, $tagLength, $tailLength, $ciphertext);
       
        /* Manually checking the length of the tag, because the `openssl_decrypt` was mentioned there, it's the caller's responsibility. */
        if ($tagLength > $block_size || ($tagLength < 12 && $tagLength !== 8 && $tagLength !== 4)) {
            rlog(RC_LOG_ERROR, __FILE__,__LINE__, __FUNCTION__, 'The inputs `$ciphertext` incomplete, the bytes length must be one of 16, 15, 14, 13, 12, 8 or 4.');

            return false;
        }

        $plaintext = openssl_decrypt(substr($ciphertext, 0, $tailLength), 
        	$cipher, $key, OPENSSL_RAW_DATA, $iv, $authTag, $aad);

        if (false === $plaintext) {
            rlog(RC_LOG_ERROR, __FILE__,__LINE__, __FUNCTION__, 'Decrypting the input $ciphertext failed, please checking your $key and $iv whether or nor correct.');
            return false;
        }

        return $plaintext;
    }


	/*
	$params = array(
    'id' => '065586e5-2e9c-522a-ab60-b042c6c2f04d',
    'create_time' => '2023-08-14T21:36:02+08:00',
    'resource_type' => 'encrypt-resource',
    'event_type' => 'TRANSACTION.SUCCESS',
    'summary' => '支付成功',
    'resource' => array(
            'original_type' => 'transaction',
            'algorithm' => 'AEAD_AES_256_GCM',
            'ciphertext' => 'r5kawOit3UTTN1DSUoiBftN92wrMDwxBE+dwqqDSsUnfDtvWufxYbbSRmN2cndLg84RMgk3mA3pb0a3ZmHruxhDflla70wCdUX3zyqNyP/SVBXA/Kc/azqfUxm17Vyjv587ip2KVIyVJhJpS/jPHZtiIeu2a7eRTonG903ymwv7vD3CjFl/6TPW5A8MJ6P/1MLNDsFdQaySZRxYHOjB7PoCfd2NNplSvIjIBVi/Hfc84ewAQjgh1iTatijBPX4gaR6ZJ9iXdr0Ps3VxwZTVlG8c6DVWNQkv/Lg/QPbSRi2IEceIoBcG2rqMUDt+nLFUcQdtN1i7tsCgcicTwN3u6BKeGMj3dz6qaUYea0nzHJ85OE2KFFQr3D9UMK3zU9FbQfdruDfVZPni9r2TbHeLj9w0Eu9NL5X0I2t0JZoiO8uhLeI3tBQEhFqYrlXkzi8kMPJMirFk6D8Zl8ulpItHU+JSDgTEwbUoT4gq71dyWnSWAOBZ6qQBinWG2RAeBexSRaYdpwcDRRSNIJ82VKwSuKkTXdpYJfkcvN8jfnUI+U2De50BhT/OVMapxxKKurev3PMUj',
            'associated_data' => 'transaction',
            'nonce' => 'BmwknDv3aEdC',
        ),
	);

	*/
	public function checkPayNotify(&$params)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN", $params);
		$resource = $params['resource'];

		$algorithm = $resource['algorithm'];
		$ciphertext = $resource['ciphertext'];
		$aad = $resource['associated_data'];
		$nonce = $resource['nonce'];

		$apiv3Key = $this->_options['appsecret'];

		$inBodyResource = $this->decrypt($ciphertext, $apiv3Key, $nonce, $aad);
		if (!$inBodyResource) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "decrypt failed!", $params);		
			return false;
		}

		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $inBodyResource);		
		/*
		{
		"mchid":"1649552828",
		"appid":"wxa04e47806adb8e2f",
		"out_trade_no":"011692020140000004",
		"transaction_id":"4200001901202308140898337744",
		"trade_type":"NATIVE",
		"trade_state":"SUCCESS",
		"trade_state_desc":"支付成功",
		"bank_type":"OTHERS",
		"attach":"4",
		"success_time":"2023-08-14T21:36:02+08:00",
		"payer":{"openid":"obuHp6aqT9wvk6QhRfMmUQ6kX-9k"},
		"amount":{
		"total":1,
		"payer_total":1,
		"currency":"CNY",
		"payer_currency":"CNY"}}

		*/
		$data = CJson::decode($inBodyResource);

		$params['data'] = $data;

		$params['id'] = $data['attach'];
		$params['order_id'] = $data['out_trade_no'];
		$params['pay_account'] = $data['payer']['openid'];
		//单位分，转换为人民币：元
		$pay_total = nformat_f2y($data['amount']['payer_total']);
		$params['pay_total'] = $pay_total;
		$params['status'] =  $data['trade_state'] == 'SUCCESS'?0:-1;
		
		return true;
	}
}