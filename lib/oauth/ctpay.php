<?php

/**
 * @file
 *
 * @brief 
 * 开放认证
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CtpayOAuth extends COAuth
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CtpayOAuth($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	public function payOrder($orderinfo, &$ioparams=array())
	{
		$cid = $this->_options['appid'];
		$key = $this->_options['appsecret'];
		$notifyurl = $this->_options['notifyurl'];
		
		$subject = $orderinfo['name'];
		$order_id = $orderinfo['order_id'];
		$fee = $orderinfo['fee'];
		
		$params = array(
			'attach' => $subject,
			'cid' => $cid,
			"expire_in" => "36000",
			"notify" => $notifyurl,
			'order_code' => $order_id,
			'subject' => $subject, 
			'total_fee' => $fee*100, //转换成'分'
		);		

		$string = '';

		ksort($params);
		reset($params);

		foreach ($params as $k => $v) {
		     $string .= $k . '=' . $v . '&';
		}
		//去掉最后一个&字符
		$string .= 'key='.$key;

		//如果存在转义字符，那么去掉转义
		if (get_magic_quotes_gpc()) {
		    $string = stripslashes($string);
		}

		$base64 = base64_encode($string);
		$sign = md5($base64);

		$params['sign'] = $sign;

		$ch = curl_init();
		$data = json_encode($params);

		$url = 'https://payment.17shualian.com/trade/create_order';
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$headers = array();
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$res = curl_exec($ch);
		if (curl_errno($ch)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'Error:' . curl_error($ch));
			curl_close($ch);
			return false;
		}


		curl_close($ch);
				
		/*
		string(726) "{"data":{"order_id":"1629808050875535360",
		"qr_code":"https://qr.alipay.com/bax08895pdkaexjxveil55de",
		"href":"alipays://platformapi/startapp?appId=20000067&url=https%3a%2f%2fqr.alipay.com%2fbax08895pdkaexjxveil55de",
		"weixin_scheme":null,"nonce_str":"O6tQo0DqmI3QbHTQ","is_error":false,"sub_msg":null},
		"data_content":"{\"order_id\":\"1629808050875535360\",
		\"qr_code\":\"https://qr.alipay.com/bax08895pdkaexjxveil55de\",
		\"href\":\"alipays://platformapi/startapp?appId=20000067&url=https%3a%2f%2fqr.alipay.com%2fbax08895pdkaexjxveil55de\",
		\"nonce_str\":\"O6tQo0DqmI3QbHTQ\",\"is_error\":false}","is_error":false,"nonce_str":"6sxv22WeUzQoUHUR",
		"return_code":"0","return_message":"success","sign":"c5813d0b54b741a8748bd1c977c20c49"}"


		{"is_error":true,"nonce_str":"PG3H02MuiKNzprP0","return_code":"-1","return_message":"单笔订单最小限额是【1.00】元"}

		*/		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res);
		
		$res = json_decode($res, true);

		if (isset($res['data'])) {
			$data = $res['data'];
		} else {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'request failed!', $res);
			$data = $res;
		}
		
		$payinfo = $data;

		$payinfo['qrcode'] = isset($data['qr_code_image'])?$data['qr_code_image']:'';
		$payinfo['url'] = isset($data['href'])?$data['href']:'';		
		$payinfo['status'] = intval($data['return_code']);
		$payinfo['msg'] = $data['return_message'];

		return $payinfo;
	}

}