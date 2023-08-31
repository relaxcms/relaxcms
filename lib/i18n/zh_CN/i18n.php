<?php
$i18n = array(
		'str_system_name' => 'RC',
		'str_system_subtitle' => 'Relax Content Management System',
		'str_system_title' =>  '简木内容管理系统',
		'str_system_corporation' =>  '3NWARE',
		'str_system_copyright_corp' =>  '3N',
		
		/* 基本目录 */
		
		'menu_my'				=>	'我的面板',
		'menu_main'				=>  '我的面板',
		'menu_my_info'			=>	'我的账户',
		'menu_my_password'		=>	'修改密码',
		'menu_my_ip'			=>	'登录IP限制',
		
		
		'menu_storage'				=>	'存储管理',
		'menu_file'				=>	'文件管理',
		'menu_help' =>'帮助',
		'menu_help_version' 	=> '版本信息',
		'menu_help_changelog' 	=> '更新记录',
		'menu_help_sysinfo' 	=> '系统信息',
		'menu_help_license' 	=> '许可证信息',
		'menu_help_manual' 	=> '系统帮助',
		'menu_help_download' 	=> '工具下载',
		'menu_help_upgrade'	=>	'系统升级',
		'menu_help_about'	=>	'关于我们',
		
		/* error strings */
		'status_0' => '操作成功',
		'status_-1' => '操作失败!(-1)',
		'status_-2' => '系统忙!(-2)',
		'status_-10001' => '用户名或密码错误!(-10001)',
		'status_-10002' => '提交码失效请刷新重试!(-10002)',
		'status_-10003' => '验证码错误或失效!(-10003)',
		'status_-10006' => '帐号已锁定请稍后再试或联系管理员!(-10006)',
		'status_-10007' => '禁止操作!(-10007)',
		'status_-10008' => '用户名无效!(-10008)',
		'status_-10009' => '用户名已存在!(-10009)',
		'status_-10010' => '验证码无效!(-10010)',
		'status_-10011' => '帐户名无效!(-10011)',
		
		/* 时区 */
		'sel_timezone' => array(
			'-1200' => '(标准时-12:00) 日界线西',
			'-1100' => '(标准时-11:00) 中途岛、阿洛菲',
			'-1000' => '(标准时-10:00) 夏威夷、艾德克岛',
			'-0900' => '(标准时-9:00) 阿拉斯加(安克雷奇市)',
			'-0800' => '(标准时-8:00) 太平洋时间(道森,白马市,雅库塔特)',
			'-0700' => '(标准时-7:00) 美国山地时间(洛杉矶,凤凰城,温哥华)',
			'-0600' => '(标准时-6:00) 美国中部时间(墨西哥城,蒙特雷,哥斯达黎加)',
			'-0500' => '(标准时-5:00) 美国东部时间(芝加哥,瓜亚基尔,哈瓦那,Jamaica)',
			'-0400' => '(标准时-4:00) 大西洋时间(纽约,底特律,圣地亚哥,多伦多,百慕大群岛)',
			'-0300' => '(标准时-3:00) 阿根廷,布宜诺斯艾利斯,巴西',
			'-0200' => '(标准时-2:00) 中大西洋,格陵兰岛',
			'-0100' => '(标准时-1:00) 亚速尔群岛、佛得角群岛',
			'+0000' => '(世界标准时 00:00) 西欧时间,伦敦,卡萨布兰卡',
			'+0100' => '(标准时+1:00) 中欧时间,柏林,巴黎,安哥拉,利比亚',
			'+0200' => '(标准时+2:00) 东欧时间,开罗,雅典',
			'+0300' => '(标准时+3:00) 巴格达,科威特,莫斯科',
			'+0400' => '(标准时+4:00) 迪拜,毛里求斯',
			'+0500' => '(标准时+5:00) 叶卡捷琳堡、伊斯兰堡、卡拉奇',
			'+0530' => '(标准时+5:30) 斯里兰卡,孟买,新德里',
			'+0600' => '(标准时+6:00) 阿拉木图、 达卡',
			'+0700' => '(标准时+7:00) 曼谷、河内、雅加达',
			'+0800' => '(标准时+8:00) 北京、上海、台湾、香港、新加坡',
			'+0900' => '(标准时+9:00) 东京、汉城、大阪、雅库茨克',
			'+0930' => '(标准时+9:30) 阿德莱德、达尔文',
			'+1000' => '(标准时+10:00) 悉尼、塞班岛',
			'+1100' => '(标准时+11:00) 马加丹、库页岛',
			'+1200' => '(标准时+12:00) 奥克兰、斐济',
			),
		/* 常用字串 */
		'str_success' => '操作成功',
		'str_failed' => '操作失败',
		'str_keyword_invalid' => '关键字无效，请检查',
		'str_parameter_error'  => '参数错误，请检查！',
		'str_add'=> '添加',
		'str_edit'=> '编辑',
		'str_not_implement'=> '模块未实现，请稍等……',
		'str_error_no_privilege'=> '无操作权限，请检查或联系管理员',	
		
		'msg_alert_types' => array(
			'error' => array(
				'msg_title'=>'操作错误',
				'msg_alert_type'=> 'danger',
				'msg_alert_css'=> 'red',
				),
			'success' => array(
				'msg_title'=>'操作成功',
				'msg_alert_type'=> 'success',
				'msg_alert_css'=> 'green',
				),
			),
		
		/* 常用模板字串 */
		'Home' => '首页',
		'Expired' => '已过期',
		

		'timeago' => array(
			'just now' => '刚刚',
			'sec'=>'秒前',
			'secs'=>'秒前',
			'min'=>'分前',
			'mins'=>'分前',
			'hour'=>'小时前',
			'hours'=>'小时前',
			'day'=>'天前',
			'days'=>'天前',
			'week'=>'周前',
			'weeks'=>'周前',
			'mon'=>'月前',
			'mons'=>'月前',
			'year'=>'年前',
			'years'=>'年前',
			),

		'timelater' => array(
			'expried' => '已过期',
			'just' => '即将过期',
			'sec'=>'秒后',
			'secs'=>'秒后',
			'min'=>'分后',
			'mins'=>'分后',
			'hour'=>'小时后',
			'hours'=>'小时后',
			'day'=>'天后',
			'days'=>'天后',
			'week'=>'周后',
			'weeks'=>'周后',
			'mon'=>'月后',
			'mons'=>'月后',
			'year'=>'年后',
			'years'=>'年后',
			),
			
		'sec2time' => array(
			'sec'=>'秒',
			'min'=>'分',
			'hour'=>'时',
			'day'=>'天',
			'week'=>'周',
			'mon'=>'月',
			'year'=>'年',
			),
		
		
		't_i18ndb' => array(
			'Error' => '错误',
			'Dashboard' => '主控面板',
			'Return' => '返回',
			'Back' => '返回',
			'Cancel' => '取消',
			'Refresh' => '刷新',
			'Columns' => '列',
			'Submit' => '提交',
			'Close' => '关闭',
			'add' => '新建',	
			'Add' => '新建',	
			'edit' => '编辑',
			'Edit' => '编辑',
			'Detail' => '详细',
			'Delete' => '删除',
			'Default'=>'默认',
			'All' => '全部',
			'Query' => '查询',	
			'Operate' => '操作',	
			'Search' => '搜索',
			'The field is required.'=>'*该字段必填',
			'Are you sure to delete?'=> '确定删除吗？',
			'Get Security Code'=>'获取验证码',
			'Retry Get Security Code' => '重新获取',
			'Email'=>'电子邮件',
			'Mobile'=> '手机号',			
			'Security Code'=>'验证码',
			'Unbinding'=>'解绑',
			'Binding'=>'绑定',
			'Pay'=>'支付',
			'My Profile'=>'个人信息',
			),
		/*common selector*/
		'sel_yesno' => array('0'=>'否','1'=>'是'),
		
		
		//LOGIN 
		't_i18ndb_login' => array(
			'Name' => '账号登录',
			'Scaning' => '扫码登录',
			'Seccode' => '验证码登录',
			'Name/Email/Mobile'=>'用户名/邮件/手机号',
			'Mobile/Email'=>'手机号/邮件',
			'Sign' => '注册',
			'Username' => '用户名',
			'Password' => '密码',
			'Login' => '登录',
			'Remember' => '记忆登录',
			'Welcome login' => '欢迎登录',
			'Please enter username and password.' => '请检查输入用户名、密码及验证码',
			'Captcha'=> '验证码',
			'Cannot see the captcha, please click me for reloading'=> '看不清楚?请点击图片再次载入',
			"Username is required." => "请输入用户名称",
			"Password is required." => "请输入密码", 
			"Captcha is required." => "请输入验证码",  
			"Forget Password" => "忘记密码",
			"Register a account" => "注册帐号",
			"User Home Center" => '用户中心',
			'Enter your e-mail address below to reset your password'=>'请输入电子邮件地址',
			'Email is required'=>'请输入电子邮件地址',	
			'Please enter a valid email address'=>'请输入有效电子邮件地址',	
			'Please Scan the QR with APP' => '请打开APP扫一扫登录',
			'Account is required.'=>'请输入帐户，手机号或邮件地址',	
			'Seccode is required.'=>'请输入验证码',
			'Retry Password'=>'确认密码',
			'This field is required.'=>'必填字段',
			),
		
		//user
		'sel_user_type' => array('0'=>'管理',  '1'=>'普通'),				
		'sel_user_account_type' => array('2'=>'邮件', '3'=>'手机', '255'=>'其他'),				

		
		//my
		'str_user_password_not_empty' => '用户密码不能为空',
		'str_user_changepassword_ok' => '修改密码成功',
		'str_user_password_update_failed' => '修改密码失败',
		't_i18ndb_my_password' => array(
			'newpassword is required.'=> '新密码不能为空',
			'Old password'=> '原密码',
			'New password'=> '新密码',
			'New password again'=> '新密码确认',
			),	
		
		//main
		't_i18ndb_main' => array(
			'My Dashboard'=> '我的面板',
			'Last Access'=>'最近访问',
			),
		
		'sel_language' => array('zh_CN'=>'简体中文'/*, 'zh_TW'=>'繁体中文'*/, 'en'=>'English'),
		'sel_layout' => array('fluid'=>'自适应', 'boxed'=> '固定宽度'),
		'sel_theme' => array('default'=>'默认'),		
		'sel_template' => array('default'=>'默认'),	
		
		'sel_enable' => array('1'=>'启用', '0'=>'禁用'),
		'sel_onoff' => array('1'=>'开启', '0'=>'关闭'),
				
		/* file */
		'str_model_file_add_ok' => '文件上传成功',
		'str_model_file_set_ok' => '文件设置成功',
		'sel_file_flags' => array(
			'1' =>'发布',
			'2' =>'下载',
			'3' =>'只读',
			'4' =>'分享',
			//'4' =>'单位共享',
			//'5' =>'已使用',
			'0' =>'审核',
			//'7' => '可播放',
			//'8' => '转码',
			//'9' => '截图',			
			),
		'sel_file_type' => array(
			'4'=>'图片',
			'8'=>'文档',
			'16'=>'压缩',
			'1'=>'视频',
			'2'=>'音频',
			'0'=>'其它',			
			),
		'sel_file_status' => array(
			'0'=>'临时',
			'1'=>'正常',
			'2'=>'待转码',
			'3'=>'转码中',
			'4'=>'已转码',
			//'5'=>'回收站',
			//'6'=>'待删除',
			'7'=>'删除中',
			'8'=>'已删除',
			),
		'mod_file' => array(
			'modelname'=> '文库',
			'id' => array('title' => 'ID'),
			'name' => array('title' => '名称'),
			'alias' => array('title' => '别名', 'comment'=>'剧集、专辑显示名称，如：01、02等'),
			'extname' => array('title' => '扩展名',),
			'path' => array('title' => '路径'),
			'size' => array('title' => '字节'),
			'type' => array('title' => '类型',),
			'mimetype' => array('title' => 'MIME类型',),
			'ctime' => array('title' => '创建时间',),
			'description' => array('title' => '描述',),
			'width' => array('title' => '宽度', 'comment'=>'视频图像分辨率宽度'),
			'height' => array('title' => '高度', 'comment'=>'视频图像分辨率高度'),
			'downloads' => array('title' => '下载次数',),
			'isdir' => array('title' => '是否目录',),
			'is_default' => array('title' => '是否默认',),
			'taxis' => array('title' => '排序',),
			'model' => array('title' => '引用模型',),
			'mid' => array('title' => '引用模型ID',),
			'pid' => array('title' => '上级',),
			'convert_id' => array('title' => '转码ID','comment'=>'通过此ID关联转码文件'),
			'snap_id' => array('title' => '截图ID','comment'=>'通过此ID关联截图文件'),
			'flags' => array('title' => '标志'),
			'cuid' => array('title' => '创建者'),
			'uid' => array('title' => '更新者'),
			'gid' => array('title' => '集ID'),
			'sid' => array('title' => '存储ID'),
			'oid' => array('title' => '组ID'),
			'ts' => array('title' => '最后更新'),
			'hits' => array('title' => '点击'),
			'uses' => array('title' => '引用'),
			'status' => array('title' => '状态'),
			
			),	
		/* user auth */
		'sel_user_auth_idtype'=>array('1'=>'身份证', '2'=>'营业执照', '255'=>'其他'),
		'sel_user_auth_type'=>array('1'=>'个人', '2'=>'企业'),
		'sel_user_auth_status'=>array('0'=>'未审', '1'=>'已审', '2'=>'审核未通过'),
);
?>