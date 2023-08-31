<?php

/**
 * @file
 *
 * @brief 
 * 开放认证

 * https://opendocs.alipay.com/common/02nk10?pathHash=a7475006
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

//require_once RPATH_SUPPORTS.DS.'alipay/aop/AopClient.php';
//require_once RPATH_SUPPORTS.DS.'alipay/aop/AopCertification.php';
//require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayTradeQueryRequest.php';
//require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayTradeWapPayRequest.php';
//require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayTradeAppPayRequest.php';
//require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipaySystemOauthTokenRequest.php';
//require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayUserInfoShareRequest.php';


class AlipayOAuth extends COAuth
{
	protected $_aop = null;
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function AlipayOAuth($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	
	
	public function getConfigInfo(&$params=array())
	{
				
		$url = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';
		$redirect_uri = urlencode($params['callback']);
		$client_id = $params['appid'];
		$state = $params['id'];
		
		$ourl = "$url?app_id=$client_id&state=$state&redirect_uri=$redirect_uri&scope=auth_user";	
				
		
		$params['url'] = $ourl;
		//icon
		$params['class'] = 'alipay';

		return $params;
	}

	public function getDefaultBgColor($ioparams=array())
	{
		return 'btn-primary';
	}


	public function getDefaultIcon($ioparams=array())
	{
		$img = $ioparams['_dstroot']."/img/alipay.png";
		return '<img src="'.$img.'">';
	}
	
	protected function getAccessToken()
	{
		return true;	
	}

	/** 
	 * initAOP
	 */
	protected function initAOP()
	{
		$params = $this->_options;

		require_once RPATH_SUPPORTS.DS.'alipay/aop/AopCertClient.php';
		require_once RPATH_SUPPORTS.DS.'alipay/aop/AopCertification.php';
		require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayTradeQueryRequest.php';
		require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayTradeWapPayRequest.php';
		require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayTradeAppPayRequest.php';
		require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipaySystemOauthTokenRequest.php';
		require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayUserInfoShareRequest.php';
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		
		$aop = new AopCertClient();
		//APPID $appid = '你的APPID';
		
		$appid = $params['appid'];
		$client_secret = $params['appsecret'];
		$rsaPrivateKey = $params['prikey'];
		$alipayrsaPublicKey = $params['pubkey'];
		
		$appCertContent = $params['app_cert'];
		$alipayCertContent = $params['platform_cert'];
		$alipayRootCertContent = $params['platform_rootcert'];


		//"应用证书路径（要确保证书文件可读），例如：/home/admin/cert/appCertPublicKey.crt";
		$appCertPath = RPATH_CONFIG_SSL.DS."appCertPublicKey.crt";
		//"支付宝公钥证书路径（要确保证书文件可读），例如：/home/admin/cert/alipayCertPublicKey_RSA2.crt";
		//$alipayCertPath = RPATH_CONFIG_SSL.DS."alipayCertPublicKey_RSA2.crt"; 
		//"支付宝根证书路径（要确保证书文件可读），例如：/home/admin/cert/alipayRootCert.crt";
		//$rootCertPath = RPATH_CONFIG_SSL.DS."alipayRootCert.crt";
		
		$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
		$aop->appId = $appid;

		$aop->rsaPrivateKey = $rsaPrivateKey;
		$aop->format = "json";
		$aop->charset= "UTF-8";
		$aop->signType= "RSA2";
		//调用getPublicKey从支付宝公钥证书中提取公钥
		//$aop->alipayrsaPublicKey = $aop->getPublicKey($alipayCertPath);
		$aop->alipayrsaPublicKey = $aop->getPublicKeyFromContent($alipayCertContent);
		//是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
		$aop->isCheckAlipayPublicCert = true;
		//调用getCertSN获取证书序列号
		//$aop->appCertSN = $aop->getCertSN($appCertPath);
		$aop->appCertSN = $aop->getCertSNFromContent($appCertContent);
		//调用getRootCertSN获取支付宝根证书序列号
		//$aop->alipayRootCertSN = $aop->getRootCertSN($rootCertPath);
		$aop->alipayRootCertSN = $aop->getRootCertSNFromContent($alipayRootCertContent);

		//rlog(RC_LOG_DEBUG, '$aop->appCertSN='.$aop->appCertSN);
		//rlog(RC_LOG_DEBUG, '$aop->alipayrsaPublicKey='.$aop->alipayrsaPublicKey);
		//rlog(RC_LOG_DEBUG, '$aop->alipayRootCertSN='.$aop->alipayRootCertSN);

		$this->_aop = $aop;

	}



	protected function getAOP()
	{
		if (!$this->_aop)		
			$this->initAOP();

		return $this->_aop;
	}
	
	public function getUserInfo($params=array())
	{
		$aop = $this->getAOP();

		$code = $params['auth_code'];
		
		
		//获取access_token 
		$request = new AlipaySystemOauthTokenRequest();
		$request->setGrantType("authorization_code");		
		$request->setCode($code);
		
		//可选刷新令牌，上次换取访问令牌时得到。本参数在 grant_type 为 authorization_code 时不填；为 refresh_token 时必填，且该值来源于此接口的返回值 app_refresh_token（即至少需要通过 grant_type=authorization_code 调用此接口一次才能获取）。
		//$request->setRefreshToken("201208134b203fe6c11548bcabd8da5bb087a83b");
		
		
		// [sub_msg] => 缺少签名参数
		
		$result = $aop->execute($request);
		
		//var_dump($result);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $result, $params); 
		/*
		 stdClass Object
		(
		  [alipay_system_oauth_token_response] => stdClass Object
		      (
		          [access_token] => publicpB025c80f7080c4b1cb4e58ef88d6dfE59
		          [alipay_user_id] => 20880080730000899122201922715959
		          [auth_start] => 2022-12-08 20:02:55
		          [expires_in] => 300
		          [re_expires_in] => 360
		          [refresh_token] => publicpB05db0a0afdaa48f99c6aea1f7b9d9X59
		          [user_id] => 2088302635178591
		      )
		
		  [sign] => UzhjM+c7giN1d+VNVH6MOwHwWpguTdweNLmUrw7DWyO9G0OnRw8biqsLIHD/7xcSGqDdtBVc7vDSRbmy30pN3rvIRYgBuO0vNDJmgQLwCsJSU+SMdnSkdTid85dz4fdHEe777lQbYaYLVC1nJF+T0bKklhgrrFkF9Nc9AWXdNqBCC5Sgw4gM/2d4b4uOtJoicPQxGEjuXt1hs1ETr8xAQBFJSCtjOjvhzgFi7Kpq0pUYqG9XbnjmyhpfQU2J5iDPKNX9IdwadogVyeyvrDqlSKxftTkAJ+6pnGXwlg38lmzhnwZ7hU/mFalycX1et/zpr8Grl/lCHwNBlExYwNw7Rw==
		)
		*/
		
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		
		$access_token = $result->$responseNode->access_token;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "$responseNode: access_token=$access_token"); 
		
		//获取用户信息 
		$request_a = new AlipayUserInfoShareRequest();	
		$result_a = $aop->execute($request_a, $access_token);
		/*
		  [error_response] => stdClass Object
		      (
		          [code] => 40006
		          [msg] => Insufficient Permissions
		          [sub_code] => isv.insufficient-isv-permissions
		          [sub_msg] => ISV权限不足，建议在开发者中心检查对应功能是否已经添加，解决办法详见：https://docs.open.alipay.com/common/isverror
		      )
		  [alipay_user_info_share_response] => stdClass Object
		      (
		          [code] => 20001
		          [msg] => Insufficient Token Permissions
		          [sub_code] => aop.invalid-auth-token
		          [sub_msg] => 无效的访问令牌
		      )
				
				
				
		  [alipay_user_info_share_response] => stdClass Object
		      (
		          [code] => 10000
		          [msg] => Success
		          [user_id] => 2088041975161028
		      )
				
				
		 [alipay_user_info_share_response] => stdClass Object
		      (
		          [code] => 10000
		          [msg] => Success
		          [avatar] => https://tfs.alipayobjects.com/images/partner/TB1SFymXNem.eJkUQtiXXa0lpXa
		          [nick_name] => 土豆
		          [user_id] => 2088302635178591
		      )
				
				
		
		*/
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $result_a); 
		
		//这里传入获取的access_token 
		$responseNode_a = str_replace(".", "_", $request_a->getApiMethodName()) . "_response";
		//用户唯一id 		
		$user_id = $result_a->$responseNode_a->user_id;  
		
		//用户头像 
		$headimgurl = $result_a->$responseNode_a->avatar;  
		
		//用户昵称
		$nick_name = $result_a->$responseNode_a->nick_name;    
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$user_id='.$user_id); 
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$headimgurl='.$headimgurl); 
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$nick_name='.$nick_name); 
		
		//查询绑定本地帐户，登录，
		if ($user_id) {
			$userinfo ['user_id'] = $user_id;
			$userinfo ['name'] = $this->_name.'_'.$user_id;
			$userinfo ['avatar'] = $headimgurl;
			$userinfo ['nickname'] = $nick_name;			
		}		

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "TOUT");		
		return $userinfo;	
	}


	public function payOrder($orderinfo, &$ioparams=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");


		$notifyurl = $this->_options['notifyurl'];
		$returnUrl = $this->_options['returnurl'];
		if (empty($returnUrl))
			$returnUrl = $ioparams['_baseurl'];



		$subject = $orderinfo['name'];
		$order_id = $orderinfo['order_id'];
		$fee = $orderinfo['fee'];
		

		$aop = $this->getAOP();

		require_once RPATH_SUPPORTS.DS.'alipay/aop/request/AlipayTradePagePayRequest.php';

		$request = new AlipayTradePagePayRequest();

		//异步接收地址，仅支持http/https，公网可访问
		$request->setNotifyUrl($notifyurl);
		//同步跳转地址，仅支持http/https
		$request->setReturnUrl($returnUrl);

		/******必传参数******/
		$params = array();
		//商户订单号，商家自定义，保持唯一性
		$params['out_trade_no'] = $order_id;
		
		//支付金额，最小值0.01元
		$params['total_amount'] = $fee;

		//订单标题，不可使用特殊符号, eg: 'test'
		$params['subject'] = $subject;

		//电脑网站支付场景固定传值FAST_INSTANT_TRADE_PAY
		$params['product_code'] ='FAST_INSTANT_TRADE_PAY';

		/******可选参数******/
		//$params['time_expire'] = '2022-08-01 22:00:00';
		////商品信息明细，按需传入
		// $goodsDetail = [
		//     [
		//         'goods_id'=>'goodsNo1',
		//         'goods_name'=>'子商品1',
		//         'quantity'=>1,
		//         'price'=>0.01,
		//     ],
		// ];
		// $object->goodsDetail = $goodsDetail;
		// //扩展信息，按需传入
		// $extendParams = [
		//     'sys_service_provider_id'=>'2088511833207846',
		// ];
		//  $object->extend_params = $extendParams;

		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params, $notifyurl, $returnUrl);


		$json = json_encode($params);//CJson::encode($params);
		$request->setBizContent($json);
		//$result = $aop->pageExecute( $request); 
		$result = $aop->pageExecute( $request,"POST"); 



		/*
		: payOrder: <form id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=UTF-8' method='POST'><input type='hidden' name='biz_content' value='{
    "out_trade_no": "011691831424000006",
    "total_amount": "0.01",
    "subject": "alipay",
    "product_code": "FAST_INSTANT_TRADE_PAY",
    "time_expire": "2022-08-01 22:00:00"
}'/><input type='hidden' name='app_id' value='2021003171672051'/><input type='hidden' name='version' value='1.0'/><input type='hidden' name='format' value='json'/><input type='hidden' name='sign_type' value='RSA2'/><input type='hidden' name='method' value='alipay.trade.page.pay'/><input type='hidden' name='timestamp' value='2023-08-12 09:10:25'/><input type='hidden' name='alipay_sdk' value='alipay-sdk-PHP-4.19.1.ALL'/><input type='hidden' name='charset' value='UTF-8'/><input type='hidden' name='sign' value='itQ3yUwer2RZqDXhx4zq1QzPWMqpuzoVyYX5AgPUVtWhmfPgq9+yTb2KFxHOxZd3Vahd57ndflr9Ihg0IPiufye+Rc/8JEXIF/GtTWDdt+0qIhlTh3o0yalggNLNyLoGveZ1T1buyLiD1+uq/aC9wmRgxOlPhq5yELn/8q+pg3mGkELFgjekCuOIKbdfyFJlxx6ayqpd34n4KFD4yRESZoG08eJNdOzjPp9r7yfq6PCqWpRfoX60LLmq+QmZEYmOQS+q7c/95QXVE5LUOiCHm4dYaZ2ZNxXsjHL9ckcOk2xczD26EyRJaK8NfdyE1mVJTZ/fyagIpL34LbKwzVvdzw=='/><input type='submit' value='ok' style='display:none;''></form><script>document.forms['alipaysubmit'].submit();</script>
*/



		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $result);

		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		$status = -1;
		$msg = '';
		if (!empty($resultCode) && $resultCode == 10000) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"失败");
			
			$msg = 'failed';
		} else {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"成功");
			$status = 0;
			$msg = 'success';
		}


		$payinfo = array();

		$payinfo['form'] = $result;
		$payinfo['url'] = $result;

		$payinfo['status'] = $status;
		$payinfo['msg'] = $msg;

		return $payinfo;
	}

	/**
	 * 
	 * checkPayNotify
	 *

	Array
	(
	    [gmt_create] => 2023-08-16 07:05:14
	    [charset] => UTF-8
	    [gmt_payment] => 2023-08-16 07:05:52
	    [notify_time] => 2023-08-16 07:05:53
	    [subject] => 充值
	    [sign] => BQzxNk/rXc8fZL4oD7TCZdW1IXpnukSRPqgUry94khb9SlbHo9rRE6jSN0wjNhTxJmoI7j8pBnPO5TiiRXZuM53wPp5eBuhxW8Dm7PFxaXQdoXmbEjCHHwpSrk+UZG9sEyJZUNIrH0y2aGqcUunyCZGyUW20azsAGWbVDNCm3+AebwGti/cg8qRvRDwAglbHXjJ4tvDxb1i414hhOXf/dBMNaVpGUdE617R7rQkhM9O6aXwbK582nZ9x0S2jJSr4YT5DFkrkqppXI/btYAgo2Ix7n8v2FagNyMLEyU9eWzKOnCH7RwsqSFsk/nP/IA/mvhvRq6D8QPRuzXWvL7WVgQ==
	    [buyer_id] => 2088302635178591
	    [invoice_amount] => 0.01
	    [version] => 1.0
	    [notify_id] => 2023081601222070553078591424614226
	    [fund_bill_list] => [{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]
	    [notify_type] => trade_status_sync
	    [out_trade_no] => 011692140709000015
	    [total_amount] => 0.01
	    [trade_status] => TRADE_SUCCESS
	    [trade_no] => 2023081622001478591435450158
	    [auth_app_id] => 2021003171672051
	    [receipt_amount] => 0.01
	    [point_amount] => 0.00
	    [buyer_pay_amount] => 0.01
	    [app_id] => 2021003171672051
	    [sign_type] => RSA2
	    [seller_id] => 2088041975161028
	)
	*/
	public function checkPayNotify(&$params)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN", $params);
		
		//检验签名 : https://opendocs.alipay.com/open/270/105902?pathHash=d5cd617e
		$sign_type = $params['sign_type'];
		
		$aop = $this->getAOP();
		$res = $aop->rsaCheckV1($params,null);
		if ($res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "check sign failed!");
			return false;
		}

		$params['order_id'] = $params['out_trade_no'];
		$params['pay_account'] = $params['buyer_id'];
		$params['pay_total'] = $params['buyer_pay_amount'];
		$params['status'] =  $params['trade_status'] == 'TRADE_SUCCESS'?0:-1;
		
		return true;
	}
}