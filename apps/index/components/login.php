<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class LoginComponent extends CLoginComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function LoginComponent($name, $options)
	{
		$this->__construct($name, $options);	
	}
	
	protected function show(&$ioparams=array())
	{
		$res = parent::show($ioparams);
		
		
		
		$scf = Factory::GetSiteConfiguration();
		!isset($scf['logo']) && $scf['logo'] = $ioparams['_dstroot'].'/img/logo.png';
		$this->assign('scf', $scf);		
		
		$this->setTpl('login4');
		return $res;
	}

	/**
 	 * @api {get} /getLoginToken getLoginToken 获取登录令牌
 	 * @apiName getLoginToken
 	 * @apiVersion 0.8.16
 	 * @apiGroup USER
  
 	 * @apiSuccess {String} json Login Token
 	 */
	protected function getLoginToken(&$ioparams=array())
	{
		return parent::getLoginToken($ioparams);
	}

	/**
 	 * @api {post} /login login 登录
 	 * @apiName login
 	 * @apiDescription API方式登录
 	 *
 	 * @apiVersion 0.8.16
 	 * @apiGroup USER

 	 * @apiParam {String} params[username]    用户名.
	 * @apiParam {String} params[password]    口令，用PKEY加密传码，加密方法参考： encrypt.js
	 * @apiParam {String} [params[seccode]]   可选，安全码，防止暴力破解使用
	 *
	 * @apiParamExample {json} 请求格式:
	 	{
	 		"params[username]":"a",
			"params[password]":"N0wzR2JCSmo0L0xCSXF2eTNyV0g5UT09",
			"sbt":"18e8a79526970e4f9a4e39638c3f67a2",
	 	}
	 	
	 	注：sbt是提交码字段，所有向服务端提交数据，首先用/api/getRequestToken获取提交码。


 	 * @apiExample 调试登录示例

	
	 	// 注：以下是调用及加密password的示例

		// 1. 常规方式页面引用encrypt
		//<script src="../../static/js/encrypt.min.js" type="text/javascript"></script>

    	//取 key
		var pkey = Login.pkey;
		var sbt = Login.sbt;

		var formData = $(form).serializeArray();
		formData.push({name:"sbt", value:sbt})
	    
	    for (i=0; i<formData.length; i++) {
	  		if (formData[i].name == "params[password]") {
	  			var val = formData[i].value;
	  			var _val = encrypt(val, pkey);
	  			formData[i].value = _val;
	  		}
	   }
	   
	   var apiLoginUrl = "https://localhost/lgm2/api/login";
	           
	   $.post(apiLoginUrl, formData).then(function(res) {
            if (res.status == 0) {
                var url = res.data.backurl;
                redirect(url);                
            } else {
                var msg = '操作失败:'+res.status;
                if (res.msg)
                    msg = res.msg;
                showTError(msg);
            }
		});


		// 2. VUE框架导入
		import encrypt from 'encrypt'
		axios.post('/api/login',{
	        'params[username]': this.username,
	        'params[password]': encrypt.encrypt(this.password, pkey),
	        remember : this.remember ? 1 : 0,
	        sbt: sbt,
	    })

 	 * @apiSuccess {String} json Login Result
 	 */
	protected function login(&$ioparams=array())
	{
		return parent::login($ioparams);
	}
	

	/**
 	 * @api {post} /register register 注册
 	 * @apiName register
 	 * @apiVersion 0.8.16
 	 * @apiGroup USER

 	 * @apiParam {String} params[name]       	用户名.
	 * @apiParam {String} params[password]    	口令，用PKEY加密传码，加密方法参考： encrypt.js
	 * @apiParam {String} params[password2]    	确认口令，用PKEY加密传码，同params[password] 
	
	 * @apiParam {String} [params[seccode]]    	安全码，可选，防止暴力破解使用

 	 * @apiSuccess {String} json Register Result
 	 */
	protected function register(&$ioparams=array())
	{
		return parent::register($ioparams);
	}
	
}