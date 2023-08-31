<?php

/**
 * @file
 *
 * @brief 
 * Paypal PAY
https://developer.paypal.com/api/rest/

    1. 获取付款Token
    2. 处理付款
    3. 处理付款确认

 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class PaypalOAuth extends COAuth
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function PaypalOAuth($name, $options=array())
	{
		$this->__construct($name, $options);
	}

    /*

    Button URL: change the endpoint from https://www.sandbox.paypal.com/connect? to https://www.paypal.com/connect?

    https://www.paypal.com/connect?flowEntry=static&client_id= ARfDleH_j-C17kxbdUzYivR70xP5Uy5N_DvNGBaPB_QNbwWkgF7lMsemGJycLRFVwaM&response_type=code&scope=openid%20profile%20email%20address&redirect_uri=https%3A%2F%2Fwww.google.com%3Fstate=123456

2023-07-31 23:50:03 : client 127.0.0.1 : 7/0x00000000: 7: CApplication: render: 1349: GET /api/oauthCallback/paypal?code=C21AAJHHnzgmBl7e_IDKVmbqOw5-8nJ6C3gAPKgSJjxB6l0baK4J1fGjzjFvgO8BHd7WXOJJTj55zEEQ3-whHFO2liM3pnK-A&scope=openid&state=1 | REQU: aname=oauth,cname=oauth,tname=oauthCallback
2023-07-31 23:50:03 : client 127.0.0.1 : 7/0x00000000: 7: F:\dvlp\rc8\trunk\src\web\apps\oauth\components\oauth.php: 52: Array
(
    [code] => C21AAJHHnzgmBl7e_IDKVmbqOw5-8nJ6C3gAPKgSJjxB6l0baK4J1fGjzjFvgO8BHd7WXOJJTj55zEEQ3-whHFO2liM3pnK-A
    [scope] => openid
    [state] => 1
)

*/
    
    public function getConfigInfo(&$params=array())
    {
        $url = 'https://www.paypal.com/connect'; //'https://www.sandbox.paypal.com/signin/authorize'
        $redirect_uri = urlencode(trim($params['callback']));
        $client_id = trim($params['appid']);
        $state = $params['id'];

        $scope = urlencode("openid");
        
        $ourl = "$url?flowEntry=static&client_id=$client_id&response_type=code&scope=$scope&redirect_uri=$redirect_uri&state=$state"; 
        
        $params['url'] = $ourl;
        //icon
        $params['_icon'] = '<i class="fa fa-paypal"></i>';
        $params['class'] = 'btn-primary';
                
        return $params;
    }

    public function getDefaultBgColor($ioparams=array())
    {
        return 'blue';
    }

    public function getDefaultIcon($ioparams=array())
    {
        return '<i class="fa fa-paypal"></i>';
    }

    


    protected function encrypt($input)
    {
    	$secretKey = $this->_options['appsecret'];

        // Create a random IV. Not using mcrypt to generate one, as to not have a dependency on it.
        $iv = substr(uniqid("", true), 0, 16);
        // Encrypt the data
        $encrypted = openssl_encrypt($input, "AES-256-CBC", $secretKey, 0, $iv);
        // Encode the data with IV as prefix
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypts the input text from the cipher key
     *
     * @param $input
     * @return string
     */
    protected function decrypt($input)
    {
    	$secretKey = $this->_options['appsecret'];
    	
        // Decode the IV + data
        $input = base64_decode($input);
        // Remove the IV
        $iv = substr($input, 0, 16);
        // Return Decrypted Data
        return openssl_decrypt(substr($input, Cipher::IV_SIZE), "AES-256-CBC", $secretKey, 0, $iv);
    }


	private function getPHPBit()
    {
        switch (PHP_INT_SIZE) {
            case 4:
                return '32';
            case 8:
                return '64';
            default:
                return PHP_INT_SIZE;
        }
    }

    private function getUserAgent($sdkName, $sdkVersion)
    {
        $featureList = array(
            'platform-ver=' . PHP_VERSION,
            'bit=' . $this->getPHPBit(),
            'os=' . str_replace(' ', '_', php_uname('s') . ' ' . php_uname('r')),
            'machine=' . php_uname('m')
        );
        if (defined('OPENSSL_VERSION_TEXT')) {
            $opensslVersion = explode(' ', OPENSSL_VERSION_TEXT);
            $featureList[] = 'crypto-lib-ver=' . $opensslVersion[1];
        }
        if (function_exists('curl_version')) {
            $curlVersion = curl_version();
            $featureList[] = 'curl=' . $curlVersion['version'];
        }
        return sprintf("PayPalSDK/%s %s (%s)", $sdkName, $sdkVersion, implode('; ', $featureList));
    }

  	private function getCACertFilePath()
    {
        return __DIR__.'/../certs/cert.pem';
    }

    private function request($url, $method, $data, $headers = [], $options = [])
    {
        rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "url=".$url, $data, $headers);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // 不直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Determine Curl Options based on Method
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }

//        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'parseResponseHeaders'));

        if (strpos($url, "https://") === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($caCertPath = $this->getCACertFilePath()) {
            curl_setopt($ch, CURLOPT_CAINFO, $caCertPath);
        }

        //Execute Curl Request
        $result = curl_exec($ch);
        //Retrieve Response Status
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //Retry if Certificate Exception
        if (curl_errno($ch) == 60) {
            rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "Invalid or no certificate authority found - Retrying using bundled CA certs file");
            curl_setopt($ch, CURLOPT_CAINFO, $this->getCACertFilePath());
            $result = curl_exec($ch);
            //Retrieve Response Status
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }

        //Throw Exception if Retries and Certificates doenst work
        if (curl_errno($ch)) {
            rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 
                curl_error($ch),
                curl_errno($ch)
            );

            var_dump(curl_error($ch));
            curl_close($ch);
            return false;

        }

        // Get Request and Response Headers
//        $requestHeaders = curl_getinfo($ch, CURLINFO_HEADER_OUT);

        //Close the curl request
        curl_close($ch);


        //More Exceptions based on HttpStatus Code
        if ($httpStatus < 200 || $httpStatus >= 300) {
            
            rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "Got Http response code $httpStatus when accessing {$url}. " . $result, 'ERROR');
            return false;
        }


        rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $result);


        return $result;
    }

 	private function dealHeaders($headers)
    {
        $ret = [];
        foreach ($headers as $k => $v) {
            $ret[] = "$k: $v";
        }
        return $ret;
    }

 	private function toArray($data)
    {
        if (!$data) return [];
        return json_decode($data, true);
    }

    /*



      curl -v -X POST "https://api-m.sandbox.paypal.com/v1/oauth2/token"\
 -u "CLIENT_ID:CLIENT_SECRET"\
 -H "Content-Type: application/x-www-form-urlencoded"\
 -d "grant_type=client_credentials"
    


    curl -v -X POST "https://api-m.sandbox.paypal.com/v1/oauth2/token" -u "AVxIZsjyuttgAwLBWE1rWGCtEgaxD70FK1E9YMlCG8AfQysxDuFXPj0qXsul3Li3fPdCbkxsPSuRQ_co:EHfNmXfiDcuBnuhC5sf6aHGCHGCijcxperBq0EoqbrofFPL8iYto2e6gNsM0YYrk2CTAake9ReV5DExm"  -H "Content-Type: application/x-www-form-urlencoded" -d "grant_type=client_credentials"


    const SDK_NAME = 'PayPal-PHP-SDK';
    const SDK_VERSION = '1.14.0';
	*/

	private function updateAccessToken()
    {
    	$SDK_NAME = 'PayPal-PHP-SDK';
    	$SDK_VERSION = '1.14.0';


        $headers = array(
            "User-Agent"    => $this->getUserAgent($SDK_NAME, $SDK_VERSION),
            "Authorization" => "Basic " . base64_encode($this->_options['appid'] . ":" . $this->_options['appsecret']),
            "Accept"        => "*/*",
        );

        $params = [
            'grant_type' => 'client_credentials'
        ];

        $oauthUrl = $this->getApiDomain().'/v1/oauth2/token';

        $result = $this->request($oauthUrl, 'POST', http_build_query($params), $this->dealHeaders($headers));
        $result = json_decode($result, true);

        // 写入文件缓存
        if ($this->_options['cacheEnabled']) {

        	$client_id = $this->_options['appid'];

            // 注意不能覆盖其他账号的缓存
            $tokens = [];
            if (file_exists($this->_options['cacheFileName'])) {
                $data = file_get_contents($this->_options['cacheFileName']);
                if ($data) {
                    $tokens = $this->toArray($data);
                }
            }

            $tokens[$client_id] = [
                'clientId' => $client_id,
                'accessTokenEncrypted' => $this->encrypt($result['access_token']),// token加密存储
                'tokenCreateTime' => time(),
                'tokenExpiresIn' => $result['expires_in']
            ];

            if (!file_put_contents($this->_options['cacheFileName'], json_encode($tokens))) {
            	rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,
            	"Failed to write cache, path: " . $this->_options['cacheFileName']);
            	return false;
            };
        }

        rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "access_token=".$result['access_token']);

        return $result['access_token'];
    }


	private function getAccessToken()
    {
        if ($this->_options['cacheEnabled'] && file_exists($this->_options['cacheFileName'])) {
            $data = file_get_contents($this->_options['cacheFileName']);
            if ($data) {
                $data = json_decode($data, true);
                $client_id = $this->_options['appid'];

                if (isset($data[$client_id]) &&
                    $data[$client_id]['accessTokenEncrypted'] &&
                    ($data[$client_id]['tokenCreateTime'] + $data[$client_id]['tokenExpiresIn'] - 120 > time() ))
                {
                    return $this->decrypt($data[$client_id]['accessTokenEncrypted']);
                }
            }
        }

        // 重新获取token
        return $this->updateAccessToken();
    }


	private function getRequestHeaders()
    {
        return array(
			'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken()
        );
    }    

   private function getApiDomain()
    {
        return $this->_options['apiurl'];
    }


/*
Array
(
    [user_id] => https://www.paypal.com/webapps/auth/identity/user/wx_zy_C1tCLnigezg5sJxAgOAt3IFjZARbxYvBgIxNU
    [sub] => https://www.paypal.com/webapps/auth/identity/user/wx_zy_C1tCLnigezg5sJxAgOAt3IFjZARbxYvBgIxNU
    [name] => Doe John
    [email] => sb-pr1dw26909139@business.example.com
    [verified] => true
    [address] => Array
        (
            [locality] => Shanghai
            [region] => Shanghai
            [country] => C2
        )

    [email_verified] => 1
)

*/
    public function getUserInfo($params=array())
    {
        $url = $this->getApiDomain()."/v1/identity/openidconnect/userinfo?schema=openid";
        $result = $this->request(
            $url,
            'GET',
            null,
            $this->dealHeaders($this->getRequestHeaders())
        );
        $res = $this->toArray($result);

        if (!$res) { 
            rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Access user info failed!'url=$url'!");
            return false;
        }
        
        rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
        /*
        */
        //查询绑定本地帐户，登录，
        $userinfo = array();
        $userinfo['user_id'] = $res['id'];
        $userinfo['name'] = $res['login'];
        $userinfo['avatar'] = $res['avatar_url'];
        
        rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"OUT");
        return $userinfo;
        
    }




    /*
    curl -v -X POST https://api-m.sandbox.paypal.com/v2/checkout/orders \
-H 'Content-Type: application/json' \
-H 'PayPal-Request-Id: 7b92603e-77ed-4896-8e78-5dea2050476a' \
-H 'Authorization: Bearer 6V7rbVwmlM1gFZKW_8QtzWXqpcwQ6T5vhEGYNJDAAdn3paCgRpdeMdVYmWzgbKSsECednupJ3Zx5Xd-g' \
-d '{
  "intent": "CAPTURE",
  "purchase_units": [
    {
      "reference_id": "d9f80740-38f0-11e8-b467-0ed5f89f718b",
      "amount": {
        "currency_code": "USD",
        "value": "100.00"
      }
    }
  ],
  "payment_source": {
    "paypal": {
      "experience_context": {
        "payment_method_preference": "IMMEDIATE_PAYMENT_REQUIRED",
        "payment_method_selected": "PAYPAL",
        "brand_name": "EXAMPLE INC",
        "locale": "en-US",
        "landing_page": "LOGIN",
        "shipping_preference": "SET_PROVIDED_ADDRESS",
        "user_action": "PAY_NOW",
        "return_url": "https://example.com/returnUrl",
        "cancel_url": "https://example.com/cancelUrl"
      }
    }
  }
}' 

    */
	public function createOrder($params)
    {
        $result = $this->request(
            $this->getApiDomain()."/v2/checkout/orders",
            'POST',
            json_encode($params),
            $this->dealHeaders($this->getRequestHeaders())
        );
        $res = $this->toArray($result);

        rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res);

        return $res;
    }







}