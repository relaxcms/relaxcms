<?php

/**
 * @file
 *
 * @brief 
 * 
 * 数据模型基类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


define ('CT_VIDEO', 0x1);
define ('CT_AUDIO', 0x2);
define ('CT_IMAGE', 0x4);
define ('CT_DOC',   0x8);
define ('CT_TAR',   0x10);
define ('CT_CODE',  0x20);


class CModel
{
	
	/**
	 * 模型名称，如: user
	 *
	 * @var mixed 
	 *
	 */
	protected $_name;
	
	
	/**
	 * 创建模型可选项
	 *
	 * @var mixed 
	 *
	 */
	protected $_options;

		
	/**
	 * 真实数据模型名称，如: user, 一般与 $_name相同。
	 * 当不同模型对应相相同数据存储时，则与$_name不同，如 $_name = 'myfile', $_modname= 'file'
	 *
	 * @var mixed 
	 *
	 */
	protected $_modname = null;

			
	/**
	 * 模型字段，类似SQL数据Field格式，如：
	+-------------+-------------+-------------------+------+-----+---------+-------+---------------------------------+---------+
	| Field       | Type        | Collation         | Null | Key | Default | Extra | Privileges                      | Comment |
	+-------------+-------------+-------------------+------+-----+---------+-------+---------------------------------+---------+
	| custom_id   | int(11)     | NULL              | NO   | PRI | NULL    |       | select,insert,update,references |         |
	| name        | varchar(32) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| description | text        | latin1_swedish_ci | YES  |     | NULL    |       | select,insert,update,references |         |
	| idtype      | tinyint(4)  | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	| idno        | varchar(32) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| mobile      | varchar(64) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| telephone   | varchar(64) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| email       | varchar(64) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| pid         | int(11)     | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	| level       | tinyint(4)  | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	| type        | tinyint(4)  | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	| status      | tinyint(4)  | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	+-------------+-------------+-------------------+------+-----+---------+-------+---------------------------------+---------+
	12 rows in set (0.02 sec)
	
	注：FIELDS字段类型须标准SQL格式，转换成SQL语句能正常执行。
	 * 
	 * @var mixed 
	 *
	 */
	protected $_fields = array();
	
	
	/**
	 * DB connection object
	 * 
	 * 数据库连接对象,创建模型对像自动初始化此字段。
	 * 
	 * 初始化语句如：$db = Factory::GetDBO($dbtype);
	 *
	 * @var mixed 
	 *
	 */
	protected $_db = null;
	
	/**
	 * TABLE NAME
	 * 表名
	 * 
	 * 格式：表前缀+下划线+真实数据模型名称, 如：$_tablename = 'cms_'.$_modname
	 *
	 * @var mixed 
	 *
	 */
	protected $_tablename;
	
	
	/**
	 * 默认排序字段名称
	 * 一般为主键名称， 如：ID
	 *
	 * @var mixed 
	 *
	 */
	protected $_default_sort_field_name;
	
	
	/**
	 * 默认排序方向
	 * 
	 * 默认为降序
	 *
	 * @var mixed 
	 *
	 */
	protected $_default_sort_field_mode = 'desc';
	
	
	/**
	 * primary key name
	 * 
	 * 主键名
	 * 
	 *
	 * @var mixed 
	 *
	 */
	protected $_pkey=null;
	
	/**
	 * Title key name
	 * 
	 * 标题键名
	 *
	 * @var mixed 
	 *
	 */
	protected $_tkey=null;
	
	
	protected $_default_actions = array();
		
	
	/**
	 * __construct 构造函数
	 * 
	 * 自动调用
	 *
	 * @param mixed $name 模型名称, 如：file
	 * @param mixed $options 可选参数，当$options['modname']设置则自动改变 $_modname，否则$_modname与$_name相同
	 * @return mixed 无
	 *
	 */
	public function __construct($name, $options=array())
	{
		$this->_name = $name;
		$this->_options = $options;
		if (isset($options['modname'])) {
			$this->_modname = $options['modname'];
		} else {
			$this->_modname = $name;
		}
		
		$this->_init();
	}
	
	/**
	 * CModel PHP4兼容构造函数
	 */
	public function CModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	/* ==========================================================================
	 *　
	 * INIT HELPER FUNCTIONS 
	 * 
	 * 模型创建自动调用初始化函数。
	 * =========================================================================*/	
	
	protected function getDBO($name='db0')
	{
		$db = Factory::GetDBO($name);
		
		return $db;
	}
	
	/**
	 * _initDB 初始化默认数据库连接
	 *
	 * @return mixed 数据库对象
	 *
	 */
	protected function _initDB()
	{
		$this->_tablename = 'cms_'.$this->_modname;
		
		$db = $this->getDBO();
		
		$this->_db = $db;
		
		return $db;
	}
	
	/**
	 * _initFieldEx 单个字段初始化扩展函数
	 *
	 * @param mixed $f 字段数组
	 * @return mixed 成功:true, 失败：false
	 *
	 */
	protected function _initFieldEx(&$f)
	{
		return $this->_init_field($f);
	}
	
	
	/**
	 * _init_field 单个字段初始化扩展函数
	 * <=6.0之前模型兼容函数
	 *
	 */
	protected function _init_field(&$f)
	{
		return false;
	}
	
	
	/**
	 * _initFeild 模型字段初始化，默认查询DB表字段来初始化
	 *
	 * @return mixed 成功: true, 失败: false
	 *
	 */
	protected function _initFeild()
	{
		//查字段表
		$fields = $this->_db->queryFields($this->_tablename);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "query field '{$this->_tablename}' failed!");
			return false;
		}
				
		if ($fields) {
			$column = 0;
			$sort = 0;
			/*
			DBMS fill fields:
			
			$v['type'] = $type;
			$v['length'] = $length;
			$v['is_primary_key'] = $is_primary_key;
			
			$v['is_field'] = true;
			$v['is_string'] = $is_string;
			$v['is_null'] = $is_null;
			$v['required'] = !$is_null;
								
			*/
			foreach ($fields as $key => &$v) {
				//init default for model
				$v['name'] = $key;
				$v['title'] = empty($v['Comment'])?$key:$v['Comment'];
				$v['description'] = $v['Comment'];
				$v['show'] = true;
				$v['add'] = true;
				$v['edit'] = true;
				$v['detail'] = true;
				$v['sortable'] = false;
				$v['visible'] = true;	
				$v['isTitle'] = false;
				$v['isImage'] = false;
				$v['readonly'] = false;	
				$v['input_max_length'] = $v['length'];	//eg: varchar(255), 255			
				$v['searchable'] = false;
				$v['input_type'] = $v['type'];		
				$v['column'] = $column ++;
				$v['sort'] = $sort;
				
				if ($v['is_primary_key']) {
					$this->_pkey = $key; //主建
					$this->_default_sort_field_name = $key;	
					$v['add'] = false;
					$v['edit'] = false;
					$v['sortable'] = true;	
					$v['searchable'] = true;			
				}
				
				switch($key) {
					case 'name':
						$v['sortable'] = true;
						$v['searchable'] = true;
						if (!$this->_tkey)
							$this->_tkey = $key;	
						break;
					case 'title':
						$this->_tkey = $key;
						$v['searchable'] = true;
						break;
					default:
						break;
				}
				
				$this->_initFieldEx($v);
				
				$sort += 5;	 //排序字段预留一些给动态字段，如: group模型中的privilege。		
			}	
		} else {
			$fields = array();
		}
		
		$this->_fields = $fields;
	}
	
	
	/**
	 * _sortField 默认排序
	 *
	 * @return mixed 成功: true, 失败: false
	 *
	 */
	protected function _sortField()
	{
		//按sort升序排序
		$res = array_sort_by_field($this->_fields, "sort", false);	
		return $res;
	}
	
	protected function _initActions()
	{
		$this->_default_actions = array(
				'detail'=>array(
					'name'=>'detail',
					'icon'=>'fa fa-file-o',
					'title'=>'详细',
					'sort'=>1,
					'class'=>'btn-primary',
					'enable'=>true,
					'showbutton'=>1,
					),
				'edit'=>array(
					'name'=>'edit',
					'icon'=>'fa fa-pencil',
					'title'=>'编辑',
					'class'=>'btn-primary',
					'sort'=>3,
					'enable'=>true,
					),
				'del'=>array(
					'name'=>'del',
					'icon'=>'fa fa-trash-o',
					'title'=>'删除',
					'action'=>'submit',
					'sort'=>10,
					'enable'=>true,
					'showbutton'=>1,
					'class'=>'btn-danger needconfirm',
					'msg'=>'确认删除吗？',
					
					),
			);
	}
	
	/**
	 * _init 初始化
	 *
	 * @return mixed 无
	 *
	 */
	protected function _init()
	{
		$this->_initDB();
		$this->_initFeild();
		$this->_sortField();		
		$this->_initActions();		
	}
	
	
	/**
	 * 单键模式创建
	 * 
	 * GetInstance 当对像未创建时先创建，否则直返回。
	 *
	 * @param mixed $name 模型名称
	 * @param mixed $options 可选参数
	 * @return mixed 模型对象
	 *
	 */
	static function &GetInstance($name, $options=array())
	{
		static $instances;
		
		if (!isset( $instances )) {
			$instances = array();
		}
		$sig = serialize($name);
		if (empty($instances[$sig])) {
			
			$modpathinfo = Factory::GetModelPathInfo($name);
			if ($modpathinfo)  {
				$filename = $modpathinfo['modpath'];
				$appname  = $modpathinfo['appname'];
				if (file_exists($filename)) {
					require_once($filename);					
				} else {
					$filename = RPATH_APPMODELS.DS.$name.'.php';
					if (file_exists($filename))
						require_once($filename);
				}
			} else {				
				$filename = RPATH_MODELS.DS.$name.'.php';
				if (file_exists($filename))
					require_once($filename);			
			}
			
			$class = ucfirst($name)."Model";
			if (!class_exists($class)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no class '$class' use default 'CModel'");
				$class = 'CModel';
			} 
			$options['class'] = $class;
			
			$instance	= new $class($name, $options);
			$instances[$sig] =&$instance;
		}		
		return $instances[$sig];
	}
	/* ==========================================================================
	* DB HELPER FUNCTIONS 
	* ==========================================================================*/
	
	public function reconnect()
	{
		$this->_db->close();
		$this->_db = Factory::GetDBO();
	}
	
	/* ==========================================================================
	 * Utility HELPER FUNCTIONS 
	 * 实用操作函数
	 * ==========================================================================*/
	
		
	/**
	 * getPKey 取模型主键名称
	 *
	 * @return mixed 返回模型主键名称
	 *
	 */
	public function getPKey()
	{
		return $this->_pkey;
	}
	
	public function getName()
	{
		return $this->_name;
	}
		
	/**
	 * getTitleFieldName 取模型标题字段名称，一般常见标题字段名称为：name,title
	 *
	 * @return mixed 返回模型标题字段名称
	 *
	 */
	public function getTitleFieldName()
	{
		return $this->_tkey;
	}
	public function getTKey()
	{
		return $this->_tkey;
	}
	
	
	/* ==========================================================================
	 * Actions HELPER FUNCTIONS 
	 * ==========================================================================*/
	protected function addAction($action)
	{
		$item['enable'] = true;		
		$this->_default_actions[$action['name']] = $action;
	}

	protected function enableAction($name, $enable=true)
	{
		if (isset($this->_default_actions[$name]))
			$this->_default_actions[$name]['enable']=$enable;
	}
	
	protected function getActions($row=array())
	{
		return $this->_default_actions;
	}
	
	/* ==========================================================================
	 * FORMAT HELPER FUNCTIONS 
	 * ==========================================================================*/
	
	/**
	 * formatRadioBoxForView 格式化布尔型字段，真:1, 假:0
	 *
	 * @param mixed $params 记录
	 * @param mixed $field 字段信息表
	 * @param mixed $ioparams 可回带上下参数
	 * @return mixed 返回格式化后的值字符串
	 *
	 */
	protected function formatRadioBoxForView($params, $field, &$ioparams=array())	
	{
		$name = $field['name'];
		$value = $params[$name];
		$id = $params[$this->_pkey];
		
				
		$key = 'sel_'.$this->_name.'_'.$name;
		$i18n = get_i18n();
		$udb = array();
		if (array_key_exists($key, $i18n)) {
			$udb = $i18n[$key];
		} else {
			$key = 'sel_yesno';
			if (array_key_exists($key, $i18n)) {
				$udb = $i18n[$key];
			} 
		}
		
		$onText = '';
		$offText = '';
		
		if ($udb) {
			foreach ($udb as $k=>$v) {
				if ($k == $value) {
					$title = $v;
					break;
				}
			}
		} else {
			$title = $value?'YES':'NO';
		}
		
		
		if ($value == 1) {
			$label = 'btn-primary';
		} else {
			$label = 'default';
		}
		
		
		
		$res =  "<a href='#' class='btn btn-xs $label onoff' data-model='{$this->_name}' data-field='$name' data-id='$id'>$title</a>";
		
			
		return $res;
		
	}
	
	
	protected function formatOnoffForView($params, $field, &$ioparams=array())	
	{
		$name = $field['name'];
		$value = $params[$name];
		$id = $params[$this->_pkey];
		
		//<script src="../assets/pages/scripts/components-bootstrap-switch.min.js" type="text/javascript"></script>
		
		//$res = '<input type="checkbox" checked class="make-switch" id="test" data-size="mini">';
		$udb = $this->getVarValList($field, 'onoff');
		
		$onText = '';
		$offText = '';
		$checked = $value?'checked':'';
		$title = '';
		foreach ($udb as $k=>$v) {			
			if ($v['value'] == 1) {
				$onText = $v['title'];				
			} else {
				$offText = $v['title'];	
			}
			if ($value == $v['value']) {
				$title = $v['title'];
			}
		}
		
		!$onText && $onText= 'ON';
		!$offText && $offText= 'OFF';
		
		//$res =  "<a href='#' class='btn btn-xs $label onoff' data-model='{$this->_name}' data-field='$name' data-id='$id'>$title</a>";
		
		//<input type="checkbox" checked class="make-switch" id="test" data-size="small">
		
		if ($ioparams['detail']) {
			$res = $title;
		} else {
			$onoffurl = $ioparams['_base']."/onoff?id=$id&modname={$this->_name}&field=$name";		
			$res =  "<input type='checkbox' $checked class='param_$name onoff' id='param_$name{$id}' data-size='small' data-model='{$this->_name}' data-field='$name' data-id='$id' data-on-text='$onText' data-off-text='$offText' data-onoffurl='$onoffurl'>";
		}
		
		return $res;
		
	}
	
	
	/**
	 * formatSwitchForList 格式化切换标签
	 *
	 * @param mixed $value 值
	 * @param mixed $title 标题
	 * @param mixed $id 记录ID
	 * @param mixed $name 记录字段名
	 * @return mixed 格式化切换标签
	 *
	 */
	protected function formatSwitchForList($value, $title, $id, $name)
	{
		switch ($value) {
			case '0':
				$label = 'default';
				break;			
			case '1':
				$label = 'green';
				break;			
			default:
				$label = 'default';
				break;
		}
		return "<button class='btn btn-xs $label tlink json tooltips' data-original-title='点击切换' title='点击切换' data-id='$id' data-task='toggle$name' >$title</button>";
	}
	
	
	protected function getSelectorData($params, $field, &$ioparams=array())
	{
		$name = $field['name'];
		
		//查询选择器数据源		
		$selectorName = isset($field['selector'])?'sel_'.$field['selector']:'sel_'.$this->_name.'_'.$name;
		$ddb = get_i18n($selectorName);				
		if (!$ddb) {
			if (!($ddb = get_i18n('sel_'.$this->_modname.'_'.$name))) {
				$ddb = array();
			}
		}
		
		return $ddb;
	}
	
	
	/**
	 * formatSelectorForView 格式化selector选择器字段
	 *
	 * @param mixed $params 记录
	 * @param mixed $field 字段信息表
	 * @param mixed $ioparams 可回带上下参数
	 * @return mixed 返回格式化后的值字符串
	 *
	 */
	protected function formatSelectorForView($params, $field, &$ioparams=array())
	{
		$name = $field['name'];
		$value = $params[$name];
		
		//查询选择器数据源		
		$ddb = $this->getSelectorData($params, $field, $ioparams);	
		
		foreach ($ddb as $k=>$v) {
			if ($k == $value) 
				return $v;
		}
		
		
		return $value;
	}
	
	
	protected function formatSelectorText($value, $field)
	{
		$name = $field['name'];
		$ddb = get_i18n('sel_'.$this->_modname.'_'.$name);		
		if (!$ddb) {
			$ddb = array();
			if (isset($field['selector'])) {
				$ddb = get_i18n('sel_'.$field['selector']);
				if (!$ddb) {
					$ddb = array();
				}
			}
		}
		
		foreach ($ddb as $k=>$v) {
			if ($k == $value) 
				return $v;
		}
		return $value;
	}
	
	protected function formatVarSelectorForView($params, $field, &$ioparams=array())
	{
		$name = $field['name'];
		$value = $params[$name];
		$ddb = $this->getVarValList($field);
		
		foreach ($ddb as $k=>$v) {
			if ($value == $v['value']) 
				return $v['title'];
		}
		return $value;
	}
	
	
	protected function getLabelColorName($value, &$icon='')
	{
		switch ($value) {
			default:
			case '0':
				$label = 'default';
				break;			
			case '1':
				$label = 'success';
				$icon = 'fa-plus';
				break;			
			case '2':
				$label = 'warning';
				$icon = 'fa-bell-o';
				break;			
			case '3':
				$label = 'danger';
				$icon = 'fa-bolt';
				break;			
			case '4':
				$label = 'info';
				$icon = 'fa-bullhorn';
				break;			
			case '5'://primary				
				$label = 'primary';
				break;
		}		
		return $label;		
	}
	
	
	/**
	 * formatLabelColorForView 格式化标签背景色
	 *
	 * @param mixed $value 值
	 * @param mixed $title 标题
	 * @return mixed 格式化后标签
	 *
	 */
	protected function formatLabelColorForView($value, $title)
	{
		$label = $this->getLabelColorName($value);
		return "<span class='label label-sm label-$label'>$title</span>";		
	}
	
		
	/**
	 * 外键缓存表
	 *
	 * @var mixed 
	 *
	 */
	protected $_foreigndb = array();
	
	
	/**
	 * formatForeignKeyForView 格式化外键
	 *
	 * @param mixed $row 记录
	 * @param mixed $field 字段
	 * @param mixed $name unused
	 * @param mixed $value unused
	 * @param mixed $fparams 外键记录信息
	 * @param mixed $ioparams 可回带请求上下文参数
	 * @return mixed 格式化外键信息
	 *
	 */
	protected function formatForeignKeyForView($row, $field, $name, $value, $fparams, &$ioparams = array())
	{
		$fkey = isset($field['foreignname'])?$field['foreignname']:$fparams['_tkey'];
		return $fparams[$fkey];
	}
	
	
	/**
	 * formatModelForView 格式化模型字段
	 *
	 * @param mixed $row 记录
	 * @param mixed $field 字段信息
	 * @param mixed $ioparams 可回带请求上下文参数
	 * @return mixed 返回模型字段对应的标题字串
	 *
	 */
	protected function formatModelForView($row, $field, &$ioparams = array())
	{
		$modelname = isset($field['model'])?$field['model']:$this->_name;
		$name = $field['name'];
		$value = $row[$name];
		
		if (empty($value))
			return $value;
		
		if (!isset($this->_foreigndb[$name])) 
			$this->_foreigndb[$name] = array();
		
		if (!isset($this->_foreigndb[$name][$value])) {
			$m = Factory::GetModel($modelname);
			$res = $m->get($value);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$value' for '$modelname'!");
				return $value;
			}
			//title
			$tkey = $m->getTitleFieldName();
			$res['_tkey'] = $tkey;
			$this->_foreigndb[$name][$value] = $res;
		}
		
		$fparams = $this->_foreigndb[$name][$value];
		
		$res = $this->formatForeignKeyForView($row, $field,$name, $value, $fparams, $ioparams);
		
		return $res;
	}
	
	
	/**
	 * formatUIDForView 格式化UID字段
	 *
	 * @param mixed $name 字段名，eg: uid
	 * @param mixed $value 字段值
	 * @param mixed $ioparams 可回带请求上下文参数
	 * @return mixed 格式化后显示名称
	 *
	 */
	protected function formatUIDForView( $name, $value, &$ioparams = array())
	{
		if (!isset($this->_foreigndb[$name])) 
			$this->_foreigndb[$name] = array();
		
		if (!isset($this->_foreigndb[$name][$value])) {
			$m = Factory::GetAdmin();
			$res = $m->get($value);
			if (!$res)
				return $value;
			$tkey = $m->getTitleFieldName();
			$res['_tkey'] = $tkey;
			
			$this->_foreigndb[$name][$value] = $res;
		}
		
		$res = $this->_foreigndb[$name][$value];
		$tkey = $res['_tkey'];
		
		$showname = $res[$tkey]?$res[$tkey]:$res['name'];
		
		return $showname;
	}
	
	
	
	/**
	 * formatVarMultiCheckBoxForView  格式化多选框字段
	 *
	 * 数据源为: 以sel_<MODNAME>_<FIELDNAME>为默认的变量选择器
	 * 
	 * @param mixed $params 记录
	 * @param mixed $field 字段
	 * @param mixed $ioparams 可回带请求上下文参数
	 * @return mixed 返回格式化后多选框字段
	 *
	 */
	protected function formatVarMultiCheckBoxForView($params, $field, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = intval($params[$name]);
		else 
			$val = 0;
		
		$disabl_key = $name.'_disablemask';
		if (isset($params[$disabl_key]))
			$disabled_mask = intval($params[$disabl_key]);
		else {
			if (isset($ioparams[$disabl_key])){
				$disabled_mask = $ioparams[$disabl_key];
			} else {
				$disabled_mask = (isset($ioparams['detail']) && $ioparams['detail'])?0xffffffff:0;
			}
		}
		$res = "";
		$vardb = $this->getVarValList($field);
		
		$res = "<div class='checkbox-list'>";		
		
		$id = $params[$this->_pkey];
		
		foreach ($vardb as $key=>$v)	{
			$checked = "";
			$mask = 0x1 << $key; // mask
			
			$ck = $mask & $val;			
			if ($ck !== 0) $checked = "checked";
			
			$disabled = ($mask & $disabled_mask)?"disabled":'';
			
			$mckurl = $ioparams['_base']."/mck?id=$id&modname={$this->_name}&fieldname=$name&key=$key";
			
			$res .= "<label class='checkbox-inline'><input type='checkbox' class='mck' data-mckurl='$mckurl' name='params[$name][]' value='$key' $checked $disabled >$v[title]</label>";
		}
		
		$res .= "</div>";
		
		return $res;         
	}
	
	
	
	/**
	 * formatSizeForView 格式化字节，如：输入：1024，输出: 1 KB
	 *
	 * @param mixed $val 字节
	 * @return mixed 格式化字节字串，如：输入：1024，输出: 1 KB
	 *
	 */
	protected function formatSizeForView($val)
	{
		return nformat_human_file_size($val);		
	}
	
	
	/**
	 * formatGalleryForView 格式图集字段
	 *
	 * @param mixed $params 记录
	 * @param mixed $field 字段信息
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 返回图集格式化字串
	 *
	 */
	protected function formatGalleryForView($params, &$field, $ioparams=array())
	{
		try {
			$ac = Factory::GetApp()->getActiveComponent();
			if($ac)
				$ac->enableJSCSS(array('bgallery', 'gallery'));
		} catch(CException $e) {
		}
		
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$sbt = $ioparams['sbt'];
		$_base = $ioparams['_base'];
		$mname = $this->_name;
		$mid = 0;
		if (isset($params[$this->_pkey]))
			$mid = $params[$this->_pkey];
		
		$w = '';
		$h = '';	
		if (isset($field['width']))
			$w = 'data-width="'.$field['width'].'"';
		if (isset($field['height']))
			$h = 'data-height="'.$field['height'].'"';
		
		$res = "<div id='param_$name' class='gallery' data-url='$_base/gallery' data-name='$name' data-model='$mname' data-noabar=1 data-mid='$mid' $w $h   class='form-control' > </div> ";
		
		return $res;
	}

	protected function formatMoneyForView($params, $field, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';

		$res = nformat_money($val,2);

		$res = '￥'.$res;

		return $res;
	}
	
	protected function formatSortForView($params, $field, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$id = $params[$this->_pkey];
			
		$res = "<input type='text' name='params[$name][$id]' value='$val' class='form-control input-xsmall' />	";
		//
		/*
		$("#touchspin_3").TouchSpin({
            verticalbuttons: true
        });*/
		
		//$res = "<input id='param_$name' class='param_$name' type='text' value='$val' name='param_$name'><script> $('.param_$name').TouchSpin({verticalbuttons: true}); </script>";
		
		return $res;		
	}
	
	
	protected function formatContentForView($params, $field, &$ioparams=array())
	{
		$field['model'] = 'content';
		$res =  $this->formatModelForView($params, $field, $ioparams);
		
		return $res;
	}
	
	
	public function taxis($params)
	{
		if (!$params)
			return false;
		
		foreach ($params as $key=>$v) {
			$name = $key;
			$taxisdb = $v;
		}
		
		foreach($taxisdb as $id=>$v) {
			$_params = array();
			$_params['id'] = $id;
			$_params[$name] = $v;
			
			$this->update($_params);			
		}
		
		return true;
	}
	

	/**
	 * formatForView 格式化记录显示
	 *
	 * @param mixed $row 回带格式化记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 成功：true, 失败：false
	 *
	 */
	public function formatForView(&$row, &$ioparams = array())
	{
		$fields = $this->getFields();		
		
		foreach ($row as $key => $value) {
			if (!isset($fields[$key]))
				continue;
			if (isset($fields[$key]['type'])) {
				if ($fields[$key]['type'] == 'int') { //值类型	
					$row[$key] = intval($row[$key]);		
				} else if ($fields[$key]['is_string'] && is_string($value)) {
						$row[$key] = $value = stripslashes($value);					
					}
			}
			
			$input_type = strtolower(trim($this->_fields[$key]['input_type']));	
			
			switch ($input_type) {
				case 'onoff': // NO or OFF
				case 'radiobox': // YES or NO
					$row['_'.$key] = $this->formatOnoffForView($row, $fields[$key], $ioparams);
					//$row['_'.$key] = $value;
					break;
				case 'yesno':
					$fields[$key]['selector'] = 'yesno';
				case 'selector': // 下拉选择
					$row['_'.$key] = $this->formatSelectorForView($row, $fields[$key], $ioparams);
					//$row['_'.$key] = $value;
					break;	
				case 'varselector': // 下拉选择
					$row['_'.$key] = $this->formatVarSelectorForView($row, $fields[$key], $ioparams);
					break;	
				case 'yearmonth':
				case 'yyyymm':
					$row['_'.$key] = tformat($value, 'Y-m');
					//$row['_'.$key] = $value;
					break;	
				case 'date':
					$row['_'.$key] = tformat($value, 'Y-m-d');
					//$row['_'.$key] = $value;
					break;					
				case 'datetime':
					$row['_'.$key] = tformat($value);
					//$row['_'.$key] = $value;
					break;
				case 'timestamp':
					$row['_'.$key] = is_numeric($value)?tformat($value):$value;
					//$row['_'.$key] = $value;
					break;
				case 'treemodel':
					if (!isset($fields[$key]['model']))
						break;
					//break;
				case 'autocomplete':
				case 'model':
					//外键
					$row['_'.$key] = $this->formatModelForView($row, $fields[$key], $ioparams);
					break;
				case 'cuid':
				case 'uid':
					$row['_'.$key] = $this->formatUIDForView($key, $value, $ioparams);
					break;
				case 'multicheckbox':					
				case 'varmulticheckbox':
					$row['_'.$key] = $this->formatVarMultiCheckBoxForView($row, $fields[$key], $ioparams);
					//$row['_'.$key] = $value;
					break;
				
				case 'size':
					$row['_'.$key] = $this->formatSizeForView($value);
					//$row['_'.$key] = $value;
					break;
				case 'gallery':
					$row['_'.$key] = $this->formatGalleryForView($row, $fields[$key], $ioparams);
					//$row['_'.$key] = $value;
					break;
				//case 'money':
				//	$row['_'.$key] = nformat_money($value);
					//$row['_'.$key] = $value;
				//	break;
				case 'money':
					$row['_'.$key] = $this->formatMoneyForView($row, $fields[$key], $ioparams);
					break;
				case 'sort':
					$row['_'.$key] = $this->formatSortForView($row, $fields[$key], $ioparams);
					break;
				case 'content':
					$row['_'.$key] = $this->formatContentForView($row, $fields[$key], $ioparams);
					break;
				default:
					break;
			}
		}
		
		return true;
	}
	
	
	public function formatRowsForView(&$rows, &$ioparams=array())
	{
		foreach ($rows as $key=>&$v) {
			$this->formatForView($v, $ioparams);
		}
		return $rows;	
	}
	
	/**
	 * formatOperate 格式化操作列
	 *
	 * @param mixed $row 记录 unused
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 操作列字串
	 *
	 */
	protected function formatOperate($row, &$ioparams=array())
	{
		$id = $row[$this->_pkey];
		
		$optdb = array();
		
		$actions = $this->getActions($row);//array('detail', 'edit', 'del');
		foreach ($actions as $key=>$v){
			if (!$v['enable']) 
				continue;
				
			$item = $v;			
			$name = $item['name'];			
			if (hasPrivilegeOf($ioparams['component'], $name)) {
				$item['id'] = $id;
				$item['name'] = $name;
				$title = ucfirst($name);
				if (!isset($item['title']))
					$item['title'] = isset($ioparams['_i18ndb'][$title])?$ioparams['_i18ndb'][$title]:$title;
				$optdb[$name] = $item;
			}
		}
		
		return $optdb;
	}
	
	
	/* =======================================================================================
	 * Field Input Parser functions
	 * 
	 * 字段输入值解析常用函数
	 * ======================================================================================*/
	
	
	/**
	 * parseInputMultiCheckBox 解析多选框输入值
	 *
	 * @param mixed $value 多选框输入值
	 * 
	 * @return mixed 多选框输入按位组合为整数值
	 */
	protected function parseInputMultiCheckBox($value)
	{
		$flags = 0x0;	
		if (is_numeric($value)) {
			$flags = $value;			
		} else if (is_array($value)) {
				foreach($value as $key=>$v) {
					$flags |= 0x1<<$v;
			}
		}	
		return $flags;
	}
	
		
	/**
	 * parseInputVarMultiCheckBox 解析多选框输入值
	 *
	 * @param mixed $value 多选框输入值
	 * @return mixed 多选框输入按位组合为整数值
	 *
	 */
	protected function parseInputVarMultiCheckBox($value)
	{
		return $this->parseInputMultiCheckBox($value);
	}
	
	/**
	 *  parseInputMoney 解析货币输入
	 *
	 * @param mixed $value 货币，eg : 2,1000
	 * 
	 * @return mixed 去除','后的数值
	 *
	 */
	protected function parseInputMoney($value)
	{
		return nformat_money_reserve($value);
	}
	
	/**
	 * parseInputSize 解析字节输入，如：1KB=1024
	 *
	 * @param mixed $value 字节字串，eg: 1KB/1G/1T
	 * @return mixed 数值, 如：1024/1073741824/1099511627776
	 *
	 */
	protected function parseInputSize($value)
	{
		return nformat_get_human_file_size($value);
	}
	
	
	/**
	 * parseInputDatetime 解析日期时间输入，输入：2022-01-01 10:20:24
	 *
	 * @param mixed $value 日期时间，如：2022-01-01 10:20:24
	 * @return mixed UINUX时间截，从1970年以来的秒数
	 *
	 */
	protected function parseInputDatetime($value)
	{
		$res =  s_mktime($value);
		//rlog('res='.$res.', $value='.$value);
		
		return $res;
	}
	
	
	/**
	 * 解析类型值
	 *
	 * @param mixed $$field, 字段信息
	 * @param mixed $val 字串
	 * @return mixed 数值
	 *
	 */
	protected function parseValue($field, $val)
	{
		if (is_array($val))
			return $val;
			
		if (is_string($val))
			$val = trim($val);
		
		$type = $field['type']; //类型，如：int|double
		switch($type) {
			case 'double':
				$val = floatval($val);
				break;
			case 'tinyint':
			case 'int':				
				$val = intval($val);
			default:
				break;
		}
		
		if (isset($field['is_percent']) && $field['is_percent']) {
			$val /= 100.0;
		}
		return $val;
	}
	
	protected function trimValue($field, $val)
	{
		if (is_array($val)) {
			foreach ($val as $key=>&$v) {
				if (is_string($v)) {
					$v = trim($v);
				}
			}
			return $val;
		}
						
		$val = trim($val);
		
		return $val;
	}
	
	
	
	/**
	 * parseInputVarValSelector 解析变量值选择器
	 * 
	 * 记忆输入值入选择器
	 *
	 * @param mixed $name 字段名称
	 * @param mixed $value 输入值
	 * @return mixed 无
	 *
	 */
	protected function parseInputVarValSelector($name, $val)
	{	
		$vname = $this->_modname.'_'.$name;
		$m = Factory::GetModel('var');
		$varinfo = $m->getByName($vname);
		if (!$varinfo) {
			$params = array();
			$params['name'] = $vname;
			$res = $m->set($params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set var failed", $params);
				return false;
			}
		}
		
		$vardb = $m->getVarListByName($vname);	
		if (!$vardb[$val]) {
			$params = array();
			$params['name'] = $val;
			$params['title'] = $val;
			$params['value'] = $val;
			$params['pid'] = $varinfo['id'];
			
			$m->set($params);
		}		
		return true;
	}
	
	protected function parseInputAutoComplete($field, $params)
	{
		$model = isset($field['model'])?$field['model']:$this->_name;
		$name = $field['name'];
		$value = $params[$name];
		
		
		$m = Factory::GetModel($model);
		$id = $m->getIdByName($value);
		if ($id) {
			return $id;
		}		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);	
				
		return $value;
	}
	
	protected function parseInputGallery($field, &$params, &$ioparams=array())
	{
		$name = $field['name'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "parse gallery", $params[$name]);
		
		if (is_array($params[$name])) {
			$fids = array_keys($params[$name]);	
			$params[$name.'_checked'] = $params[$name];				
			$params[$name] = implode(',', $fids);
			
		} else {
			$params[$name] = '';
		}
		
		return true;
	}
	
	
	/**
	 * parseInput 解析输入
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 返回格式化后的字串
	 *
	 */
	protected function parseInput(&$params, &$ioparams=array())
	{
		$res = true;
		foreach($params as $key=>$v) {
			if (!isset($this->_fields[$key])) {
				continue;
			}
			
			$field = $this->_fields[$key];
			
			//trim
			$params[$key] = $this->trimValue($field, $params[$key]);
			
			//prepare
			$input_type = strtolower(trim($field['input_type']));	
			switch($input_type) {
				case 'gallery':
					$this->parseInputGallery($field, $params, $ioparams);						
					break;
				default:
					break;
			}			
			
			//检查长度 
			$input_max_length = $this->_fields[$key]['length'];			
			if ($field['is_string'] && $input_max_length > 0) {
				$input_length = strlen($params[$key]);				
				if ($input_length > $input_max_length) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: input too long! input_max_length=$input_max_length, input_length=$input_length");
					$res = false;
				}
			}
			
			switch($input_type) {
				case 'multicheckbox':
					$params[$key] = $this->parseInputMultiCheckBox($params[$key]);
					break;
				case 'varmulticheckbox':
					$params[$key] = $this->parseInputVarMultiCheckBox($params[$key]);
					break;
				case 'varvalselector':
					$this->parseInputVarValSelector($key, $params[$key]);
					break;
				case 'autocomplete':
					$params[$key] = $this->parseInputAutoComplete($this->_fields[$key], $params);
					break;
				case 'yearmonth':
				case 'yyyymm':
				case 'datetime':
				case 'date':
					$params[$key] = $this->parseInputDatetime($params[$key]);
					break;		
				case 'money'://eg: 2,1000
					$params[$key] = $this->parseInputMoney($params[$key]);
					break;	
				case 'size'://万, eg: 2,1000
					$params[$key] = $this->parseInputSize($params[$key]);
					break;	
				
				default:
					break;
			}
		}
		
		//默认非输入字段值
		foreach($this->_fields as $key=>$v) {
			switch($v['input_type']) {
				case 'CUID':
					if (empty($params[$this->_pkey])) {
						$userinfo = get_userinfo();
						if ($userinfo)
							$params[$key] = $userinfo['id'];
					}	
					break;
				case 'UID':// uid
					if (!$v['readonly'] || empty($params[$this->_pkey])) {
						$userinfo = get_userinfo();
						if ($userinfo)
							$params[$key] = $userinfo['id'];
					}					
					break;				
				case 'TIMESTAMP':
					if (!$v['readonly'] ||  empty($params[$this->_pkey])) {
						$params[$key] = time();						
					}					
					break;
				default:
					break;	
			}
		}
				
		return $res;
	}
	
	/* ==========================================================================
	 *  UTILITY FUNCTIONS 
	 * ==========================================================================*/
	
	
	/**
	 * writeLog 写模型操作日志
	 *
	 * @param mixed $level 日志级别 0-8, 0最高，8最低
	 * @param mixed $action 操作，一般指函数名称，如：del
	 * @param mixed $status 结果，1成功，<0失败
	 * @param mixed $oldParams 原对象，删除或编辑对象时有效
	 * @param mixed $newParams 新对象，添加新建有效
	 * @return mixed 成功：true,失败：false
	 *
	 */
	protected function writeLog($level, $action, $errno, $oldParams=array(), $newParams=array(), $mid=0)
	{
		$uid = get_uid();
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$level/$action/$status", $oldParams, $newParams);
		
		$m = Factory::GetModel('log');
		
		//ts, ip, des, uid, subsys, loglevel, cmd, object, oid
		$ip = get_client_ip();
		
		
		$oldobj = $oldParams?serialize($oldParams):'';
		$newobj = $newParams?serialize($newParams):'';
		if ($errno === false) {
			$errno = -1;
		} else if ($errno === true) {
				$errno = 0;
		} else {
				$errno = intval($errno);
		}	
		$status = $errno >= 0?1:0;
		
		$key = $errno >= 0?"str_model_{$action}_ok" : "str_model_{$action}_faild";
		$desc = i18n($key);
		
		
		$params = array(
				'uid'=>$uid,
				'ip'=>$ip,
				'level'=>$level,
				'description'=>$desc,
				'modname'=>$this->_name,
				'mid'=>$mid,
				'action'=>$action,
				'errno'=>$errno,
				'status'=>$status,
				'oldobj'=>$oldobj,
				'newobj'=>$newobj,
				);
		
		$res = $m->set($params);
		
		return $res;
	}
		
	
	/**
	 * checkParams 检查输入字段是否合法并格式化输入字段值
	 * 
	 * 
	 *
	 * @param mixed $params 输入参数
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 成功: true, 失败：false
	 *
	 */
	protected function checkParams(&$params, &$ioparams=array())
	{
		$res = $this->parseInput($params, $ioparams);
				
		return $res;
	}
	
	/* ==========================================================================
	 * TABLE HELPER FUNCTIONS 
	 * 表记录操作函数，如：add, edit, delete etc.
	 * ==========================================================================*/
	
	/**
	 * exists 是否存在
	 *
	 * @param mixed $id 标识
	 * @return mixed 成功：true, 失败：false
	 *
	 */
	protected function exists($id)
	{
		$res = $this->get($id);
		return !!$res;
	}	
	
	public function get_max_id()
	{
		return $this->_db->get_max_id($this->_tablename, $this->_pkey);;
	}
	
	/**
	 * newID 取当前数据模型表存储最大值
	 *
	 * @return mixed 表最大ID+1
	 *
	 */
	protected function newID(&$params=array())
	{
		$id =  $this->_db->get_max_id($this->_tablename, $this->_pkey);
		$params[$this->_pkey] = $id;	
		return $id;
	}
	
	protected function parseDefaultValue($field, $val)
	{
		if ($val == '') {
			$type = $field['type']; //类型，如：int|double
			switch($type) {
				case 'double':
				case 'tinyint':
				case 'int':				
					$val = 0;
				default:
					break;
			}
		}
		return $val;
	}
	
	public function insert(&$params, &$ioparams=array())
	{
		$_params = array();
		foreach ($params as $key => $v) {
			if (!isset($this->_fields[$key]['is_field'])) 
				continue;
			if ($this->_fields[$key]['is_view']) 
				continue;
			
			if (isset($this->_fields[$key])) {
				//escap
				if ($this->_fields[$key]['is_string']) {
					$v = $this->_db->escape_string($v); //字串过滤
				} else {
					$v = $this->parseDefaultValue($this->_fields[$key], $v);
				}			
				$_params[$key] = $v;
			}
		}
		
		
		//如果非空字段，给出默认值
		foreach ($this->_fields as $key => $v) {
			if ($key == $this->_pkey)
				continue;
			if (!isset($v['is_field'])) 
				continue;
			if (isset($v['is_view'])) 
				continue;
			if (!$v['is_null'] && !isset($_params[$key])) //默认值
				$_params[$key] = $v['is_string']?'':0;
		}
		
		if (!$_params) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no params!", $params);
			return false;
		}
		
		$res = $this->_db->insert($this->_tablename, $_params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call db insert failed!res=$res");
		}
		$this->writeLog(RC_LOG_NOTICE, __FUNCTION__, $res, null, $_params, $_params['id']);
		return $res;
	}		
	
	
	/**
	 * update 更新记录
	 *
	 */
	public function update(&$params=array(), &$ioparams=array())
	{
		$id = $params['id'];
		
		$_params = array();
		foreach ($params as $key=>$v) {
			if ($key == $this->_pkey) //主键不更新
				continue;
			
			//只读字段不更新
			if ($this->_fields[$key]['readonly']) 
				continue;
			
			if (isset($this->_fields[$key])) {
				//escap
				if ($this->_fields[$key]['is_string']) {
					$v = $this->_db->escape_string($v); //字串过滤
				}	else {
					$v = $this->parseDefaultValue($this->_fields[$key], $v);
				}
				
				$_params[$key] = $v;
			}
		}
		
		if (!$_params) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no params!", $params);
			return false;
		}
		
		$res = $this->_db->update($this->_tablename, $this->_pkey, $id, $_params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call db update failed!");
		}
		$this->writeLog($res?RC_LOG_NOTICE:RC_LOG_ERROR, __FUNCTION__, $res, $old, $_params, $id);
		return $res;
	}
	
	public function trigger($event, $args=array())
	{
		rlog(RC_LOG_DEBUG, __CLASS__, __FUNCTION__, "TODO...");
		return false;
	}	
	
	/**
	 * add 添加记录
	 *
	 * @param mixed $params 参数
	 * @return mixed 成功:true, 失败: false
	 *
	 */
	protected function add(&$params, &$ioparams=array())
	{
		if (empty($params[$this->_pkey])) {
			$id = $this->newID($params);		
		}		
		
		$res = $this->insert($params, $ioparams);
		
		return $res;
	}
	
	
	/**
	 * edit 编辑记录
	 *
	 * @param mixed $params 参数
	 * @return mixed 成功：true, 失败：false
	 *
	 */
	protected function edit(&$params, &$ioparams=array())
	{
		$id = $params['id'];
		if (!($old = $this->get($id))) {
			$res = $this->insert($params, $ioparams);			
		} else {
			$res = $this->update($params, $ioparams);
			if ($res) {
				$params['__old'] = $old;
			}
		}
				
		return $res;
	}
	
	
	
	
	
	/**
	 * addN 字段值+n
	 *
	 * @param mixed $id 记录ID
	 * @param mixed $field 字段名称，此字段应该是数值型字段
	 * @param mixed $n 增加值
	 * @return mixed 成功：true, 失败：false
	 *
	 */
	protected function addN($id, $field, $n)
	{
		$res = $this->get($id);
		if ($res) {
			$new = $res[$field] + $n;
			$params = array($this->_pkey=>$id, $field=>$new);
			$res = $this->update($params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call update failed!", $params);
			}
			
			return $res;
		} else {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call update failed! field=$field", $res);
			return false;
		}
	}
	
	
	/**
	 * inc 字段+1
	 *
	 * @param mixed $id 记录标识
	 * @param mixed $field 字段名称
	 * @return mixed 成功：true, 失败：false
	 *
	 */
	protected function inc($id, $field)
	{
		return $this->addN($id, $field, 1);
	}
	
	public function incHits($id)
	{
		return $this->inc($id, 'hits');
	}
	
	/**
	 * dec 字段-1
	 *
	 * @param mixed $id 记录标识
	 * @param mixed $field 字段名称
	 * @return mixed 成功：true, 失败：false
	 *
	 */
	protected function dec($id, $field)
	{
		return $this->addN($id, $field, -1);
	}
	
	
	/**
	 * delete 删除
	 *
	 * @param mixed $params 删除条件
	 * @return mixed 成功：true, 失败：false
	 *
	 */
	protected function delete($params=array())
	{
		$this->parseFilterParams($params);	
		$res = $this->_db->delete($this->_tablename, $params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "delete from table '{$this->_tablename}' failed", $filter);
			return false;
		}
		return $res;
	}
	
	
	/**
	 * clean 清理
	 * 
	 * 与delete方法相同
	 *
	 * @param mixed $params 清理条件
	 * @return mixed 成功：true, 失败：false
	 *
	 */
	public function clean($params=array())
	{
		return $this->delete($params);
	}
	
	/**
	 * cache 缓存数据模型
	 *
	 * 适用小表
	 * 
	 @return mixed 成功：true, 失败：false
	 *
	 */
	public function cache()
	{
		return false;
	}
	
	
	/**
	 * get 按ID读取一条记录
	 *
	 * @param mixed $id 记录ID
	 * @return mixed 记录信息
	 *
	 */
	public function get($id)
	{
		$res = $this->_db->get($this->_tablename, $this->_pkey, $id);
		
		return $res;
	}

	public function getInfo($params=array())
	{
		$res = $this->getOne($params);
		return $res;
	}
	
	public function getInfoForView($params=array(), &$ioparams=array())
	{
		$res = $this->getOne($params);
		if ($res) 
			$this->formatForView($res, $ioparams);
		return $res;
	}
	

	protected function formatOperateForView(&$row, $ioparams)
	{
		$this->_default_actions['detail']['enable'] = false;
		$optdb =  $this->formatOperate($row, $ioparams);
		array_sort_by_field($optdb, 'sort');		
		$row['optdb'] = $optdb; 
	}
	
	/**
	 * getForView 按ID读取记录，并对记录格式化
	 *
	 * @param mixed $id This is a description
	 * @param mixed $fields This is a description
	 * @param mixed $ioparams This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function getForView($id, &$ioparams = array())
	{
		$res = $this->get($id, $ioparams);
		if ($res) {
			$ioparams['row'] = $res;
			$this->formatForView($res, $ioparams);			
			$this->formatOperateForView($res, $ioparams);
			
		}
		return $res;
	}	
	
	/**
	 * getParents 递归获取记录所有上级记录，并以次记录
	 *
	 * @param mixed $id 记录ID
	 * @param mixed $parentdb 回带上级记录
	 * @return mixed 返回 true
	 *
	 */
	public function getParents($id, &$parentdb=array()) 
	{
		$res = "";		
		if (!$id)
			return true;
		
		$row = $this->get($id);
		$parentdb[] = $row;
		
		$id = $row['pid'];		
		$res = $this->getParents($id, $parentdb);
		
		return $res;
	}
	
	
	public function getDepth($id, &$parentdb=array()) 
	{
		$parentdb = array();
		
		$res = $this->getParents($id, $parentdb);
		
		$depth = count($parentdb);
		
		return $depth;		
	}
	
	
	/**
	 * hasChildren 是否有子记录
	 *
	 * @param mixed $id This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function hasChildren($id)
	{
		$res = $this->getOne(array('pid'=>$id));		
		return $res;
	}
	
	
	/**
	 * getPostions 获取位置信息
	 *
	 * @param mixed $id This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function getPostions($id)
	{
		$positions = array();
		
		$pdb = array();
		if (($res = $this->getParents($id, $pdb))) {			
			foreach ($pdb as $key => $v2) {
				$p = array('name'=>$v2['name'], 'id'=>$v2['id']);
				array_unshift($positions, $p);
			}	
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$positions='.$positions, $pdb);
		
		return $positions;
	}
	
	
	
	/**
	 * find 按条件过滤记录集，同select
	 *
	 * @param mixed $params 过滤条件
	 * @return mixed 记录集
	 *
	 */
	public function find($params)
	{
		return $this->select($params);
	}
	
	
	/**
	 * findOne 按条件读取一条记录
	 *
	 * @param mixed $params 过滤条件
	 * @param mixed $sort 排序字段
	 * @return mixed 成功：记录，失败：false
	 *
	 */
	public function findOne($params, $sort=array())
	{
		$this->parseFilterParams($params, true);
		$this->parseSortParams($params);
		$res = $this->_db->findOne($this->_tablename, $params, $sort);
		if (!$res) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING:call findOne failed!", $filter);
		}
		return $res;
	}
	
	
	/**
	 * getOne 按条件读取一条记录，同 findOne
	 *
	 * @param mixed $params 过滤条件
	 * @param mixed $sort 排序字段
	 * @return mixed 成功：记录，失败：false
	 *
	 */
	public function getOne($params, $sort=array())
	{
		return $this->findOne($params, $sort);
	}
	
		
	/**
	 * getCount 查询记录数
	 *
	 * @param mixed $params 过滤条件，可选，默认全部
	 * @return mixed 记录数
	 *
	 */
	public function getCount($params=array())
	{
		$this->parseFilterParams($params, true);
		$res = $this->_db->getCount($this->_tablename, $params);
		return $res;
	}
	
	
	/**
	 * getTotal 查询记录总数，同：getCount
	 *
	 *
	 * @param mixed $params 过滤条件，可选，默认全部
	 * @return mixed 记录数
	 *
	 */
	
	public function getTotal($params=array())
	{
		return $this->getCount($params);
	}
	
	
	/**
	 * getIdByName 按名称查询ID
	 *
	 * @param mixed $name 名称
	 * @return mixed 记录ID
	 *
	 */
	public function getIdByName($name)
	{
		$res = $this->getOne(array('name'=>$name));
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no name '$name'!");
			return false;
		}
		return $res['id'];
	}
	
	
	/**
	 * getByName 按名称查询记录
	 *
	 * @param mixed $name 记录名称
	 * @return mixed 成功：记录信息，失败：false
	 *
	 */
	public function getByName($name)
	{
		$res = $this->getOne(array('name'=>$name));
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no name '$name'!");
			return false;
		}
		return $res;
	}
	
	public function getNameById($id)
	{
		$res = $this->getOne(array('id'=>$id));
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		return $res['name'];
	}
	
	
	/**
	 * group 分组统计
	 *
	 * @param mixed $params 过滤及分组数参数，
	 *  
	 * @return mixed 记录集
	 * 
	 
	 *
	 */
	public function group($params=array())
	{
		$res =  $this->select($params);
						
		return $res;
	}
	
	
	/**
	 * compute 计算函数
	 *
	 * @param mixed $fn 函数，如：sum,min, max, count, avg等
	 * @param mixed $field 字段名称
	 * @param mixed $params 过滤参数
	 * @return mixed 计算结果
	 *
	 */
	public function compute($fn, $field, $params=array())
	{
		$this->parseFilterParams($params, true);
		return $this->_db->compute($this->_tablename, $fn, $field, $params);		
	}	
	
	
	/**
	 * sum 求和
	 *
	 * @param mixed $field 字段名称，eg: fee
	 * @param mixed $params 过滤条件
	 * @return mixed 累加和
	 *
	 */
	public function sum($field, $params=array())
	{
		return $this->compute('sum', $field, $params);		
	}
	
	
	/**
	 * max 求最大值
	 *
	 * @param mixed $field 字段名称，eg: fee
	 * @param mixed $params 过滤条件
	 * @return mixed 成功：最大值，失败：false
	 *
	 */
	public function max($field, $params=array())
	{
		return $this->compute('max', $field, $params);		
	}
	
	
	
	/**
	 * min 求最小值
	 *
	 * @param mixed $field 字段名称，eg: fee
	 * @param mixed $params 过滤条件
	 * @return mixed 成功：最小值，失败：false
	 *
	 */
	public function min($field, $params=array())
	{
		return $this->compute('min', $field, $params);		
	}
	
	/**
	 * avg 求平均值
	 *
	 * @param mixed $field 字段名称，eg: fee
	 * @param mixed $params 过滤条件
	 * @return mixed 成功：平均值，失败：false
	 *
	 */
	public function avg($field, $params=array())
	{
		return $this->compute('avg', $field, $params);		
	}
	
	
	/**
	 * count 求数量
	 *
	 * @param mixed $field 字段名称，eg: fee
	 * @param mixed $params 过滤条件
	 * @return mixed 成功：数量，失败：false
	 *
	 */
	public function count($field, $params=array())
	{
		return $this->compute('count', $field, $params);		
	}
		
	/* ====================================================================
	 * DB table set functions
	 * 
	 * 数据库表设置相关函数 
	 * ==================================================================*/		
	
	/*
	Array
	(
	   [id] => 1
	   [name] => 11
	   [type] => 2
	   [aids] => 28,27,26
	   [status] => 0
	)
	*/
	
	/**
	 * parseInputPostParamsForGallery 解析GALLERY
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 上下文参数
	 * @return mixed 成功：true,  失败：false
	 * 
	 * 应用场景：
	 * 1. 一组图片上传后，每一张图片都有相应名称、描述等
	 * 2. 当图片用于不同的作品，可能被赋于不同的名称
	 * 
	 *
	 */
	protected function parseInputPostParamsForGallery($field, &$params, &$ioparams=array())
	{
		$name = $field['name'];
		$val = 	$params[$name];
		$checkeddb = 	$params[$name.'_checked'];
		
		$m = Factory::GetModel('file');
		$m1 = Factory::GetModel('file2model');
		$gdb = explode(',', $val);
		
		$mid = $params[$this->_pkey];
		//查询
		$olddb = $m1->select(array('mid'=>$mid, 'modname'=>$this->_name));
		
		$res = $m1->delete(array('mid'=>$mid, 'modname'=>$this->_name));
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'res='.$res);
		
		$fdb = array();
		foreach ($olddb as $key=>$v) {
			$fid = $v['fid'];
			$fdb[$fid] = $v;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $fdb);
		
		foreach ($gdb as $key=>$aid) {
			$checked = intval($checkeddb[$aid]);
			
			$finfo = $m->get($aid);
			if ($finfo) {
				$_params = array();
				$_params['fid'] = $aid;
				$_params['modname'] = $this->_name;
				$_params['mid'] = $mid;
				$_params['checked'] = $checked;
				
				if (isset($fdb[$aid])) {
					$oldinfo = $fdb[$aid];
					$_params['description'] = $oldinfo['description'];
					$_params['taxis'] = $oldinfo['taxis'];
					$_params['title'] = $oldinfo['title'];
				}
				$res = $m1->set($_params);
				
			}
		}
		
		return $res;
	}
	
	/**
	 * parseInputPostParams 解析提交完毕后的参数并处理
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 上下文参数
	 * @return mixed 成功：true,  失败：false
	 *
	 */
	protected function parseInputPostParams(&$params, &$ioparams=array())
	{
		$res = true;
		
		
		
		foreach($params as $key=>$v) {
			if (!isset($this->_fields[$key])) {
				continue;
			}	
			
			$field = $this->_fields[$key];					
			$input_type = strtolower(trim($field['input_type']));	
			switch($input_type) {
				case 'gallery':
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "recv gallery", $params);
					$res = $this->parseInputPostParamsForGallery($field, $params, $ioparams);
					break;
				default:
					break;
			}
		}		
		return $res;
	}
	
	/**
	 * postParams 添加或更新记录成功后调用此函数，处理相应字段
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 上下文参数
	 * @return mixed 成功：true,  失败：false
	 *
	 */
	protected function postParams(&$params, &$ioparams=array())
	{
		$res = $this->parseInputPostParams($params, $ioparams);
		return $res;
	}
	
	/**
	 * set 设置记录（新建或更新）
	 *
	 * @param mixed $params 可回带入参数，当“新建”记录时，回带ID
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 成功: true, 失败: false
	 *
	 */
	public function set(&$params, &$ioparams=array())
	{
		if (!$this->checkParams($params, $ioparams)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__,"Invalid input params!");
			return false;
		}
		
		if (isset($params['id'])) //探测是不是主键名称不是 'id'
			$id = $params['id'];
		else if (isset($params[$this->_pkey])) 
			$id = $params[$this->_pkey];
		else 
			$id = 0;
		
		if (!$id) {
			$res = $this->add($params, $ioparams);			
		} else {
			$params['id'] = $id;		
			$res = $this->edit($params, $ioparams);
		}
		
		if ($res) {
			$this->postParams($params, $ioparams);
		} 
			
		return $res;
	}
	
	public function getParams($params=array())
	{
		return $this->getInfo($params);
	}
	
	public function setParams(&$params=array())
	{
		return $this->set($params);
	}
	
	
	public function setForce(&$params, &$ioparams=array())
	{
		$ioparams['force'] = true;
		return $this->set($params, $ioparams);
	}
	
	
	public function mck($id, $mask, $fieldname='')
	{
		$info  = $this->get($id);
		if (!$info) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$id'");
			return false;
		}
		
		!$fieldname && $fieldname = 'flags';
		
		$old = $info[$fieldname];		
		$new = $old ^ $mask; //指定位取反
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "id=$id, flagsMask=$flagsMask, oldstatus=$old, newstatus=$new");
		
		$params = array();
		$params['id'] = $id;
		$params[$fieldname] = $new;
		
		$res = $this->update($params);
		
		return $res;
	}
	
	
	public function onoff($id, $name)
	{
		$res = $this->get($id);
		if ($res) {
			$old = intval($res[$name]);
			$new = $old == 1?0:1;
			$params = array();
			$params['id'] = $id;
			$params[$name] = $new;
			
			$res = $this->update($params);	
		}	
		
		return $res;
	}
	
	
	/**
	 * del 删除记录
	 *
	 * @param mixed $id 记录ID
	 * @return mixed 不存在返回false, 存在返回删除的记录
	 *
	 */
	public function del($id)
	{
		$old = $this->get($id);
		if (!$old) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		$res = $this->delete(array($this->_pkey=>$id));
		
		$this->writeLog(RC_LOG_NOTICE, __FUNCTION__, $res, $old, null, $id);
		
		return $old;
	}
	
	
	/**
	 * delAll 批量删除，如：删除ids=1,2,3
	 *
	 * @param mixed $ids 多个记录ID标识以逗号分隔组合成的字串
	 * @return mixed 成功：返回最小一个被删除记录，失败：false
	 *
	 */
	public function delAll($ids)
	{
		if (!is_array($ids))		
			$ids = explode(',', $ids);
		
		foreach ($ids as $id) {
			$res = $this->del($id);			
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "del id '$id' failed!");
				break;
			}
		}
		
		return $res;
	}
	
	public function delChildren($id=0, $all=true)
	{
		$res = true;
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "delChildren id=".$id);
		
		$udb = $this->selectChildren($id);
		foreach ($udb as $key=>$v) {
			$cid = $v['id'];
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "delChildren cid=".$cid);
			
			if ($this->hasChildren($cid)) {
				$this->delChildren($cid, $all);
			} else {
				
				$res = $this->del($cid);			
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "delChildren del id '$cid' failed!");
					break;
				}
			}
		}
		
		if ($all && $id > 0) {
			$res = $this->del($id);			
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "delChildren del id '$id' failed!");
			}
		}
		
		return $res;
	}
	
	
	/**
	 * truncate 清理
	 * 
	 * 注：有很大破坏情，慎用！
	 *
	 * @return mixed 成功:true, 失败: false
	 *
	 */
	public function truncate()
	{
		$res = $this->_db->truncate($this->_tablename);		
		$this->writeLog(RC_LOG_NOTICE, __FUNCTION__, $res);
		return $res;
	}
	
	/* ============================= 
	 * the model records select functions
	 * 
	 * 记录集查询相关函数(处理：搜索，排序，分页）
	 * 
	 * ============================*/
	
	public function setModelFilterForKeyword($keyword, &$params)
	{
		$params['or'] = array($this->_tkey=>array('like'=>$keyword));				
	}
	
	protected function parseFilterParamsKeywordForModel($field, $keyword, &$or_wheres)
	{
		$name = $field['name'];
		
		$modelname = isset($field['model'])?$field['model']:$this->_name;
		$m = Factory::GetModel($modelname);
		
		$pkey = $m->getPKey();
		
		$params = array();
		$params['__fields'] = array($pkey);
		$m->setModelFilterForKeyword($keyword, $params);
				
		$udb = $m->select($params);
		
		$ids = array();
		
		foreach ($udb as $key=>$v) {
			$ids [] = $v[$pkey];
		}	
		
		if ($ids) {
			$or_wheres[] = array('op'=>'in', 'key'=>$name, 'type'=>$field['type'], 'value'=>$ids);
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params, $ids, $or_wheres);
		
		return $res;
	}
	
	
	/**
	 * parseFilterParamsKeyword 解析关键词过滤参数
	 *
	 * @param mixed $keyword 关键词
	 * @param mixed $or_wheres 回带解析后的条件数组
	 * @return mixed 成功：true, 失败: false
	 *
	 */
	protected function parseFilterParamsKeyword($keyword, &$or_wheres)
	{
		$keyword = trim($keyword);
		if (!$keyword)
			return false;
		
		foreach ($this->_fields as $key => $v) {
			if (isset($v['searchable']) 
					&& $v['searchable']) { //多个字段模糊查询
				if ($v['is_string']) {
					//$or_wheres[] = " $key like '%$keyword%' ";	
					$or_wheres[] = array('op'=>'like','key'=>$key, 'type'=>$v['type'], 'value'=>$keyword);					
				}  else {
					//外键
					if ($v['input_type'] == 'model') {
						$this->parseFilterParamsKeywordForModel($v, $keyword, $or_wheres);
					} else {						
						$val = $this->parseValueExp($v, $keyword);
						if ($val > 0 && is_numeric($keyword))  {
							//$or_wheres[] = "$key=$val";
							$or_wheres[] = array('op'=>'eq','key'=>$key, 'type'=>$v['type'], 'value'=>$val);
						}
					}
				} 
			}
		}		
		return true;
	}	
	
	
	protected function parseValueExp($field, $val)
	{
		//rlog(RC_LOG_DEBUG, __FUNCTION__, 'val', $val);
		if (is_array($val))
			return $val;
		
		if (is_string($val))
			$val = trim($val);
			
		if (is_numeric($val) || is_string($val)) {
			$type = $field['type']; //类型，如：int|double
			switch($type) {
				case 'double':
					$val = floatval($val);
					break;
				case 'tinyint':
				case 'int':				
					$val = intval($val);
				default:
					break;
			}
		}
		
		return $val;
	}
	
	protected function parseFilterParamsValue($val, $op, $fieldinfo, &$wheres)
	{
		$key = $fieldinfo['name'];
		
		$val = $this->parseValueExp($fieldinfo, $val);
		
		$wheres[] = array('op'=>$op, 'key'=>$key, 'type'=>$fieldinfo['type'], 'value'=>$val);		
	}
	/**
	 * This is method parseFilterParamsCD
	 *
	 * //eg : array('type'=>array('or'=>array(1,2)))
	 * 
	 * @param mixed $params This is a description
	 * @param mixed $strict This is a description
	 * @param mixed $fieldinfo This is a description
	 * @param mixed $and_wheres This is a description
	 * @param mixed $or_wheres This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function parseFilterParamsCD($cd, $valArr, $strict, $fieldinfo, &$wheres)
	{
		$key = $fieldinfo['name'];
		
		//运算符：<, <=, >, >=, =, %%, 
		$_operator = array('lt'=>1,'<'=>1, 'lte'=>2,'<='=>2, 'gt'=>3,'>'=>3,'gte'=>4, '>='=>4, 
				'eq'=>5,'='=>5, 'like'=>6, 'llike'=>7, 'rlike'=>8, 'in'=>9);
		
		switch($cd) {
			case 'min':
				$valArr = array('gte'=>$valArr);//'min'=>2
				break;
			case 'max':
				$valArr = array('lte'=>$valArr); //'max'=>1
				break;
			default:
				if (isset($_operator[$cd])) {
					$valArr = array($cd=>$valArr);
				} else {
					$valArr = explode(',', $valArr);
				}
				break;
		}
		
		foreach ($valArr as $op=>$v) {
			if (!isset($_operator[$op])) {
				//$op = $_operator[$op];
				switch($op) {
					case 'min':
						$op = 'gte';
						break;
					case 'max':
						$op = 'lte';
						break;
					default:
						$op = 'eq';
						if (!$strict && $fieldinfo['is_string']) 
							$op = 'like';
						break;
				}
			}
						
			$this->parseFilterParamsValue($v, $op, $fieldinfo, $wheres);
		}
		
		/*if (!$fieldinfo['is_string']) { //数值型				
			if (isset($params['min'])) {
				$minval = $this->parseValue($fieldinfo, $params['min']);
				$and_wheres[] = array('type'=>'gte','key'=>$key, 'value'=>$minval);
			} 
			if (isset($v['max'])) {						
				$maxval = $this->parseValue($fieldinfo, $params['max']);
				$and_wheres[] = array('type'=>'lte','key'=>$key, 'value'=>$maxval);							
			}
			
			if (isset($params['or'])) {	
				foreach ($params['or'] as $k=>$v) {
					$or_wheres[] = array('type'=>'eq','key'=>$key, 'value'=>$v);	
				}
			}
		} else { //字串
			$val = trim($params);				
			if ($strict) {
				$and_wheres[] = array('type'=>'eq','key'=>$key, 'value'=>$val);
			} else {			
				$and_wheres[] = array('type'=>'like','key'=>$key, 'value'=>$val);
			}
		}*/
	}
	
	
	protected function parseFilterParamsCD2($params, &$wheres)
	{
		foreach ($params as $key =>$v) {			
			if (isset($this->_fields[$key])) { //多个字段模糊查询
				$field = $this->_fields[$key];
				if (is_array($v)) { //array('or'=>array('fee'=>array('>'=>123),))
					foreach ($v as $k2=>$v2) {
						$op = $k2;
						$val = $v2;
						break;
					}					
					$wheres[] = array('op'=>$op,'key'=>$key, 'type'=>$v['type'], 'value'=>$val);					
				}  else {
					$val = $this->parseValueExp($field, $v);
					$wheres[] = array('op'=>'eq','key'=>$key, 'type'=>$field['type'], 'value'=>$val);					
				} 
			}
		}		
		return true;
	}	
	
	
	/**
	 * parseFilterParams 解析查询过滤参数
	 *
	 * @param mixed $params 过滤参数
	 * @return mixed 成功：过滤语句，失败：false
	 *
	 */
	protected function parseFilterParams(&$params, $strict=false)
	{
		
		$fdb = array();
		
		if (!isset($params))
			return false;
		if (isset($params['__strict']))
			$strict = $params['__strict'];
		//type,key,value
		
		$and_wheres =  array();
		$or_wheres =  array();
		
		//索引字段优先，索引有效，查询加速
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "TODO...");
		//过滤器
		$filterParams = (isset($params['__filter']))?$params['__filter']:$params;
			
		foreach ($filterParams as $key => $v) {
			if ($v === '')
				continue;
			
			//a=1 or b=1 or c=1
			//a=1 and b=2 and c=3
			switch($key) {
				case '__keyword':
					$this->parseFilterParamsKeyword($v, $or_wheres);
					continue;
				case 'or':
					$this->parseFilterParamsCD2($v, $or_wheres);
					continue;
				case 'and':
					$this->parseFilterParamsCD2($v, $and_wheres);
					continue;
				default:
					if (!isset($this->_fields[$key]))
						continue;
					break;
			}
			
			
				
			$fieldinfo = $this->_fields[$key];
			
			if (!$fieldinfo['is_field'])
				continue;
				
			
			//保留原过滤条件
			$fdb[$key] = $v;
			
			if (!is_array($v)) {
				$op = (!$strict && $fieldinfo['is_string']) ? 'like':'eq';
				$this->parseFilterParamsValue($v, $op, $fieldinfo, $and_wheres);
			} else {
				//array('type'=>array('or'=>array(1,2))), 表达式： type=1 || type=2
				foreach ($v as $cd=>$v2) {
					switch ($cd) {
						case 'or':
							$this->parseFilterParamsCD($cd, $v2, $strict, $fieldinfo, $or_wheres);
							break;
						case 'and':
						default:
							$this->parseFilterParamsCD($cd, $v2, $strict, $fieldinfo, $and_wheres);
							break;
					}		
				}
			}			
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $and_wheres);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $or_wheres);
		
		$filter = array();		
		$filter['and_wheres'] = $and_wheres;
		$filter['or_wheres'] = $or_wheres;
		//$filter['fdb'] = $fdb;		
		
		$params['____filter'] = $filter;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		//$this->_db->buildFilterSQL($filter);
		
		return true;
	}
	
	
	/**
	 *  parseSortParams 解析排序参数
	 *
	 * @param mixed $params 排序参数，如：array('order'=>'fee', 'dir'=>'desc')
	 * 注：当$params未指定时，则使用模型默认排序字段及排序方向
	 * 
	 * @return mixed 成功：排序语句，失败：false
	 *
	 */
	protected function parseSortParams(&$params)
	{
		
		$orderby = array();
		
		if (isset($params['__orderby'])) {// array('fee'=>'desc','id'=>'asc')
			foreach($params['__orderby'] as $key=>$v) {
				if (isset($this->_fields[$key])) {
					$orderby[$key] = $v;
					$params['order'] = $key;
					$params['dir'] = $v == 'asc'?'asc':'desc';					
				} 
			}	
			if (!$orderby) { //无效的参数，使用默认
				$orderby[$this->_default_sort_field_name] = $this->_default_sort_field_mode == 'asc'?'asc':'desc';
				
				$params['order'] = $this->_default_sort_field_name;
				$params['dir'] = $this->_default_sort_field_mode;				
			}		
		} else if ($this->_default_sort_field_name) {
			$orderby[$this->_default_sort_field_name] = $this->_default_sort_field_mode == 'asc'?'asc':'desc';
			
			$params['order'] = $this->_default_sort_field_name;
			$params['dir'] = $this->_default_sort_field_mode;			
		}
		
		if ($orderby) {
			$params['____orderby'] = $orderby;			
		}
		
		
		/*
		if (isset($params['order']) && isset($this->_fields[$params['order']])) {
			$orderby['order_field'] = $params['order'];
		} else if ($this->_default_sort_field_name) {
				$orderby['order_field'] = $this->_default_sort_field_name;
			}
			
		if (isset($params['dir']) && $orderby['order_field'] && $params['dir']) {
			$orderby['order_dir'] = $params['dir'];			
		} else if ($this->_default_sort_field_name && $orderby['order_field']) {
			$orderby['order_dir'] = $this->_default_sort_field_mode;			
		}	*/
		
		//$this->_db->buildSortSQL($orderby);
		
		return true;	
	}
	
	/**
	 * parsePaginationParams 分页参数解析
	 *
	 * @param mixed $params 回带分页参数
	 * 如：
	 * $params['page'] ：页号，默认1页
	 * $params['page_size']：页大小，默认最大值 PHP_INT_MAX
	 * 
	 * @return mixed 无
	 *
	 */
	protected function parsePaginationParams(&$params)
	{
		$pagination = '';
		
		$page = isset($params['page'])?intval($params['page']):1;
		$limit = isset($params['limit'])?intval($params['limit']):PHP_INT_MAX;
		$page_size = isset($params['page_size'])?intval($params['page_size']):$limit;
		
		if ($page < 1)
			$page = 1;
			
		if ($page_size <= 0)
			$page_size = PHP_INT_MAX;
		
		$params['page'] = $page;
		$params['page_size'] = $page_size;
		//fixed for MSSQL top
		$params['pkey'] = $this->_pkey;
		
		return true;
		
	}
	
	
	/**
	 * select 查询记录集
	 *
	 * 
	 * @param mixed $params 
	 * @return mixed 成功：
	 *
	 */
	
	
	/**
	 * select 查询记录集
	 *
	 * @param mixed $params 过滤排序分页参数
	 * @param mixed $ioparams 请求上下文参数，回带分页等信息，如下：
	 * 
	 * 
	 *  $ioparams['total'] : 查询记录总数
		$ioparams['page_size'] : 分页
		$ioparams['nr_page']   : 页数
		$ioparams['page']      : 页号
		$ioparams['start']     : 起始页
		
		$ioparams['nr_row']	   : 记录数
		$ioparams['rows']      : 记录集
		       
		  
	 * @return mixed 成功：记录集，失败：
	 *
	 */
	public function select($params=array(), &$ioparams=array())
	{
		//过滤条件
		$this->parseFilterParams($params);		
		//排序
		$this->parseSortParams($params);
		//分页
		$this->parsePaginationParams($params);
		
		$rows = $this->_db->select($this->_tablename, $params);
		
		$ioparams['total'] = $params['total'];
		$ioparams['page_size'] = $params['page_size'];
		$ioparams['nr_page'] = $params['nr_page'];
		$ioparams['page'] = $params['page'];
		$ioparams['start'] = $params['start'];
		
		$ioparams['nr_row'] = count($rows);
		$ioparams['rows'] = $rows;
		
		return $rows;		
	}
	
	public function gets($params=array(), &$ioparams=array())
	{
		return $this->select($params, $ioparams);
	}
	
	public function getList($params=array(), $num=0, &$ioparams=array())
	{
		$params['limit'] = $num;
		
		return $this->select($params, $ioparams);
	}
	
	
	public function getListForView($params=array(), $num=0, &$ioparams=array())
	{
		$udb = $this->getList($params, $num, $ioparams);
		foreach ($udb as $key=>&$v) {
			$this->formatForView($v, $ioparams);
		}
		
		return $udb;
	}
	
	public function selectChildren($id)
	{
		return $this->select(array('pid'=>$id));
	}
	
	
	protected function findChildren($id, &$all=array())
	{
		//all
		$udb = $this->selectChildren($id);    
		if (!empty($udb)) {
			foreach ($udb as $v) {
				$all[$v['id']] = $v;				
				if ($this->hasChildren($v['id'])) {
					$this->findChildren($v['id'], $all);
				} 
			}
		} 
	}
	
	
    public function getAllChildren($id, $includeme=false)
    {
		$all = array();
		
		$info = $this->get($id);
		if ($info) {
			if ($includeme)
				$all[$info['id']] = $info;
						
			$this->findChildren($id, $all);
		}
		
		return $all;
    }
	
	
	/**
	 * selectForTreeNode 当前节点，及当前节点的上级节点
	 *
	 * @param mixed $id This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function selectForTreeNode($id)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "in selectForTreeNode ...id=$id");
		//顶层
		$depth = 0;
		$nodeinfo = array();
		$rows = $this->select(array('pid'=>0));
		
		$ninfo['id'] = 0;
		$ninfo['depth'] = $depth++;
		$ninfo['rows'] = $rows;
		$ninfo['nr'] = count($rows);
		
		$nodeinfo[] = $ninfo;
		
		$pdb = array();
		$res = $this->getParents($id, $pdb);
				
		$pdb = array_reverse($pdb);
			
		foreach ($pdb as $key=>$v) {
			
			$rows = $this->select(array('pid'=>$v['id']));
			
			$ninfo = $v;
			$ninfo['depth'] = $depth;
			$ninfo['rows'] = $rows;
			$ninfo['nr'] = count($rows);		
			
			$nodeinfo[] = $ninfo;
			
			//parent
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "id=$id, pid=".$v['pid']);
			foreach($nodeinfo[$depth-1]['rows'] as $k2=>&$v2) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "id=".$v2['id']);
				
				if ($v2['id'] == $v['id']) {
					$v2['selected'] = true;
				}
			}
			$depth ++ ;
		}
				
		return $nodeinfo;
	}



	
	/**
	 * selectForView 查询记录集并作格式化显示
	 *
	 * @param mixed $params 过滤及排序分页条件
	 * @param mixed $ioparams 输入上下文参数
	 * @return mixed 成功：记录集，失败: false
	 *
	 */
	public function selectForView(&$params=array(), &$ioparams=array())
	{
		$rows = $this->select($params, $params);	
		
		$fields = $this->getFields();
		$params['fdb'] = $fields;
		
		if ($rows) {
			foreach ($rows as $key=>&$v) {
				$this->formatForView($v, $ioparams);				
				$optdb = $this->formatOperate($v, $ioparams);				
				array_sort_by_field($optdb, 'sort');				
				$v['optdb'] = $optdb; 
			}
		}	

		$params['rows'] = $rows;	

		return $rows;
	}
	
	public function selectForListview(&$params, &$ioparams=array())
	{
		//filterfieldcfg
		$fields = array();
		$ffcfg = isset($params['filterfieldcfg'])?$params['filterfieldcfg']:'';
		
		$_fdb = $this->getFields();		
		if ($ffcfg) {
			foreach ($_fdb as $key=>$v) {
				if (array_key_exists($key, $ffcfg)) {
					$v['sortable'] = $v['sortable']?true:false;
					$fields[$key] = $v;
				}
			}
		} else {
			$fields = $_fdb;
		}
		
		//search
		
		//treeview
		$treeview = isset($params['treeview'])?$params['treeview']:0;
		if ($treeview) 
			$params['page_size'] = PHP_INT_MAX;
		
		//filter rows 
		$org_rows = $this->selectForView($params, $ioparams);
		$rows = $params['rows'];
		
		$pid = isset($params['pid'])?intval($params['pid']):0;		
		$positions = $pid >0?$this->getPostions($pid):array();
		
		foreach ($rows as $key=>&$v) {
			//$v['name'] = $v['title'];
			$v['time'] = tformat_timelong($v['ts']);
			
			//previewUrl
			if (!isset($v['previewUrl']))
				$v['previewUrl'] = $ioparams['_dstroot'].'/img/nopic.png';
				
			
			//$treeview
			if ($treeview) {//检查是否有子节点
				$v['hasChild'] = $this->hasChildren($v['id']);
			}			
		}
		
		
		$data = array(
				'name'=>$this->_name,
				'fields'=>$fields,
				'pkey'=>$this->_pkey,
				'hasOptmenu'=>true,
				'hasCheckAll'=>true,
				'positions'=>$positions,
				'total'=>$params['total'],
				'page'=>$params['page'],
				'pages'=>$params['pages'],
				'page_size'=>$params['page_size'],
				'num'=>count($rows),
				'order'=>$params['order'],
				'dir'=>$params['dir'],
				'treeview'=>$params['treeview'],
				'pid'=>$params['pid'],
				'rows'=>$rows
		);
		return $data;		
		
	}
	
	/**
	 * getModelInfo 取模型信息
	 *
	 * @return mixed 模型信息
	 * 
		$modinfo['name'] : 名称;
		$modinfo['modname'] : 模型名称
		$modinfo['pkey'] =  : 主键;
		$modinfo['default_sort_field_name'] : 默认排序字段
		$modinfo['default_sort_field_mode'] : 默认排序方向
		
		$modinfo['fdb'] : 字段表;	
	 *
	 */

	public function getModelInfo()
	{
		$modinfo = array();
		$modinfo['name'] = $this->_name;
		$modinfo['modname'] = $this->_modname;
		$modinfo['pkey'] = $this->_pkey;
		$modinfo['default_sort_field_name'] = $this->_default_sort_field_name;
		$modinfo['default_sort_field_mode'] = $this->_default_sort_field_mode;
		
		$modinfo['fdb'] = $this->getFields();		
		
		return $modinfo;
	}
	
	
	/**
	 * getGalleryForSelected 
	 *
	 * @param mixed $id 记录ID
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 该记录所引用的所有文件组合的文件记录集
	 *
	 */
	public function getGalleryForSelected($id, $name, $aids='', $ioparams=array())
	{
		$fdb = array();
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN", $ioparams);
		
		
		$info = $this->get($id);		
		if ($info) {
			$fiddb = explode(',', $info[$name]);
		} else if ($aids) {
			$fiddb = explode(',', $aids);
		}
				
		if ($fiddb) {			
			$m = Factory::GetModel('file');
			$m2 = Factory::GetModel('file2model');
			
			$taxis = 1;
			foreach ($fiddb as $key=>$fid) {
				$finfo = $m->getForView($fid, $ioparams);
				if ($finfo) {
					
					$item = array();
					$item['id'] = $fid;
					$item['name'] = $finfo['name'];
					$item['previewUrl'] = $finfo['previewUrl'];
					$item['lpreviewUrl'] = $finfo['lpreviewUrl'];
					$item['spreviewUrl'] = $finfo['spreviewUrl'];
					$item['downloadurl'] = $finfo['downloadurl'];
					
					$item['url'] = $finfo['url'];
					$item['mimetype'] = CFileType::ext2mimetype($finfo['extname']);
					$item['taxis'] = $taxis++;//$finfo['taxis'];
					$item['title'] = $finfo['alias'];
					$item['type'] = $finfo['type'];
					$item['_type'] = $finfo['_type'];
					$item['hits'] = $finfo['hits'];
					$item['checked'] = 0;
				
					//查询file2model
					$f2minfo = $m2->getOne(array('fid'=>$fid,'modname'=>$this->_name, 'mid'=>$id));
					if ($f2minfo) {
						//$item['id'] = $f2minfo['id'];
						$item['f2m_id'] = $f2minfo['id'];
						$item['description'] = $f2minfo['description'];
						$item['linkurl'] = $f2minfo['linkurl'];
						$item['checked'] = $f2minfo['checked'];
						//$item['taxis'] = $f2minfo['taxis'];
					} else {
						//$item['id'] = 0;
					}
					
					$fdb[] = $item;
				}
			}
		}
		
		//sort
		array_sort_by_field($fdb, 'taxis');
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		return $fdb;		
	}
	
	/**
	 * getFields 获取字段
	 *
	 * @return mixed 字段表
	 *
	 */
	public function getFields2()
	{
		return $this->_fields;
	}
	
	public function getFields()
	{
		$mi18n = get_i18n('mod_'.$this->_name);
		$fdb = $this->_fields;
		if (!$mi18n) {
			$mi18n = get_i18n('mod_'.$this->_modname);
			if (!$mi18n) {
				$mi18n = array();
			}
		}
		foreach ($fdb as $key => &$v) { //fixed the 字段标题/描述
			if (isset($mi18n[$key]['title']))  
				$v['title'] = $mi18n[$key]['title'];
			if (isset($mi18n[$key]['description'])) 
				$v['description'] = $mi18n[$key]['description'];		
			
			if (isset($mi18n[$key]['comment'])) 
				$v['comment'] = $mi18n[$key]['comment'];
			else
				$v['comment'] = '';
			
			if (isset($mi18n[$key]['comment2'])) 
				$v['comment2'] = $mi18n[$key]['comment2'];
			else
				$v['comment2'] = '';
			
			
			//validate
			if (isset($mi18n[$key]['validate'])) {
				$v['validate'] = $mi18n[$key]['validate'];
			} else {
				$v['validate'] = array('rules'=>array(), 'messages'=>array());
			}
		}	
		return $fdb;
	}
	
	
	/**
	 * newField 创建字段
	 *
	 * @param mixed $name 字段名称
	 * @param mixed $params 字段参数
	 * @return mixed 新字段信息表
	 *
	 */
	protected function newField($name, $params=array()) 
	{
		$newfield = $params;
		
		$newfield['name'] = $name;
		$newfield['title'] = isset($params['title'])?$params['title']:$name;
		$newfield['description'] = isset($params['description'])?$params['description']:$name;
		
		$newfield['show'] = isset($params['show'])?$params['show']:true;			
		$newfield['add'] = isset($params['add'])?$params['add']:true;
		$newfield['edit'] = isset($params['edit'])?$params['edit']:true;
		$newfield['detail'] = isset($params['detail'])?$params['detail']:true;
		$newfield['sortable'] = isset($params['sortable'])?$params['sortable']:false;
		$newfield['searchable'] = isset($params['searchable'])?$params['searchable']:false;
		$newfield['required'] = isset($params['required'])?$params['required']:'false';
		$newfield['isTitle'] = isset($params['isTitle'])?$params['isTitle']:false;
		$newfield['isImage'] = isset($params['isImage'])?$params['isImage']:false;
		$newfield['visible'] = isset($params['visible'])?$params['visible']:true;	
		$newfield['input_type'] = isset($params['input_type'])?$params['input_type']:'custom';	
		$newfield['input_max_length'] = isset($params['input_max_length'])?$params['input_max_length']:255;	
		
		$newfield['sort'] = isset($params['sort'])?$params['sort']:0;	
		
		$this->_fields[$name] = $newfield;
		
		return $newfield;
		
	}
	
	public function setRequired($name, $required=true)
	{
		$this->_fields[$name]['required'] = $required;		
	} 
	
	/* ============================================================================
	 * build input form control functions
	 * 
	 * 构建输入表单位控件
	 *  
	 * ===========================================================================*/
	
	/**
	 * buildInputForText 构建单行文本输入框
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 文本框表单位控件
	 *
	 */
	protected function  buildInputForText(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = isset($field['default'])?$field['default']:'';
		
		$disable = isset($field['disable'])?'disabled':'';
		
		$maxlength = $field['input_max_length'] > 0 ? "maxlength='{$field['input_max_length']}'" : '';
		
		$paramId = isset($field['paramId'])?$field['paramId']: "param_$name";	
		$paramName = isset($field['paramName'])?$field['paramName']: "params[$name]";	
		$placeholder = $field['title'];
		
		$res =  "<input type='text' value='$val'  name='$paramName' id='$paramId' $maxlength data-required='1' class='form-control' placeholder='$placeholder' $disable />";
		
		return $res;
	}
	
	
	protected function buildInputForTextAddon(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = isset($field['default'])?$field['default']:'';
		
		$disable = isset($field['disable'])?'disabled':'';
		
		$maxlength = $field['input_max_length'] > 0 ? "maxlength='{$field['input_max_length']}'" : '';
		
		$paramId = isset($field['paramId'])?$field['paramId']: "param_$name";	
		$paramName = isset($field['paramName'])?$field['paramName']: "params[$name]";	
		$placeholder = $field['title'];
		
		$res = "
			<div class='input-group'>
				
				<input type='text' id='$paramId' name='$paramName' value='$value' $maxlength data-required='1' class='form-control' placeholder='$placeholder' $disable  />
				<span class='input-group-addon'>
				<i class='fa fa-search'></i>
				</span>        
			</div>";
			
		return $res;
	}
	/**
	 * buildInputForTextarea 构建文多行本输入框
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 多行文本框表单位控件
	 *
	 */
	protected function  buildInputForTextarea(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		//<textarea id="maxlength_textarea" class="form-control" maxlength="225" rows="2" placeholder="This textarea has a limit of 225 chars."></textarea>
		$maxlength = $field['input_max_length'] > 0 ? "maxlength='{$field['input_max_length']}'" : '';
		
		
		$res =  "<textarea name=\"params[$name]\" id=\"param_$name\" class=\"form-control\" $maxlength  rows=\"2\" >$val</textarea>";
		
		if (isset($field['comment2'])) 
			$res .= "<span>$field[comment2]</span>";
		
		//var_dump($field); exit;
		
		return $res;
	}
	
	
	protected function buildInputSelector($ddb, &$field, $params, &$ioparams=array(), $issearch=false)
	{
		/*
		<select class="form-control select2me" name="params[layout]" id="param_layout">
			$layout_select
		*/
		//[Type] => enum('On','Off')
		
		$name = $field['name'];
		$disabled = isset($field['disable'])?'disabled':'';
		
		$default_value = $field['Default'];
		$all_title = i18n('All');
		
		if (isset($params[$name]))
			$default_value = $params[$name];
		else 
			$default_value = '';
		
		$paramId = isset($field['paramId'])?$field['paramId']: "param_$name";	
		$paramName = isset($field['paramName'])?$field['paramName']: "params[$name]";	
		
		$selector = "<select class='form-control form-filter' name='$paramName' id='$paramId' $disabled>";
		if ($issearch) {
			$selector .= "<option value='' > $all_title</option>";
		}
		
		foreach ($ddb as $key => $v) {
			$selected = ($default_value!=='' && $default_value == $key) ? 'selected' : '';
			
			$selector .= "<option value='$key' $selected > $v </option>";
		}
		$selector .= "</select>";
		if ($disabled )
			$selector .= "<input type='hidden' name='params[$name]' value='$default_value' />";	
		
		return $selector;								
	}
	
	
	/**
	 * buildInputForSelector 构建下拉单选框（数据源：i18n）
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 下拉单选框表单位控件
	 *
	 */	
	protected function buildInputForSelector(&$field, $params, &$ioparams=array(), $issearch=false)
	{
		$name = $field['name'];		
		
		//查询选择器数据源
		$ddb = $this->getSelectorData($params, $field, $ioparams);	
		
		
		$res =  $this->buildInputSelector($ddb, $field, $params, $ioparams, $issearch);
		
		return $res;					
	}
	
	/**
	 * getVarValList 获取变量值列表
	 *
	 * @param mixed $name 字段名称
	 * @return mixed 娈量值记录集
	 *
	 */
	protected function getVarValList($field, $vname='')
	{
		$name = $field['name'];	
		!$vname && $vname = $this->_modname.'_'.$name;
		
		$m = Factory::GetModel('var');
		$vardb = $m->getVarListByName($vname);	
		if (!$vardb && isset($field['selector'])) {
			$vardb = $m->getVarListByName($field['selector']);	
			
		}	
		
		if (!$vardb) { //初始化
			//初始值
			$ddb = get_i18n('sel_'.$vname);		
			if (!$ddb) {
				$ddb = array();
				if (isset($field['selector'])) {
					$ddb = get_i18n('sel_'.$field['selector']);
					if (!$ddb) {
						$ddb = array();
					}
				}
			}
			
			//变量
			$params = array();
			
			//查询
			$params = $m->getOne(array('name'=>$vname));
			if (!$params) {
				$params = array();
				$params['name'] = $vname;
				$params['title'] = i18n($vname);			
				$res = $m->set($params);
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set var failed!");
					return false;
				}
			} 
			
			//变量值
			$pid = $params['id'];				
			foreach ($ddb as $key=>$v) {
				$params = array();
				$params['value'] = $key;
				$params['title'] = $v;
				$params['pid'] = $pid;			
				$res = $m->set($params);
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set var value failed!", $params);
					break;				
				}
			}
			
			//	
			$vardb = $m->getVarListByName($vname);
		}	
		
		return $vardb;
	}
	
	
	/**
	 * buildInputForVarSelector 构建变量值选择器（数据源：var） 
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 下拉列表选择器表单控件
	 */
	protected function buildInputForVarSelector(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$res = "";
		//$vid = $field['vid'];
		
		//valselector
		$udb = $this->getVarValList($field);
		
		$ddb = array();
		foreach ($udb as $key=>$v) {
			$ddb[$v['value']] = $v['title'];
		}
		$res =  $this->buildInputSelector($ddb, $field, $params, $ioparams, $issearch);
				
		/*$valselector = '';		
		
		$paramId = isset($field['paramId'])?$field['paramId']: "param_$name";	
		$paramName = isset($field['paramName'])?$field['paramName']: "params[$name]";	
		
		$res = "<select class='form-control form-filter' name='$paramName' id='$paramId' $disabled>";
		$res .= "<option value='' > 请选择 </option>";
		foreach ($udb as $key => $v) {
			$res .= "<option value='$v[value]' > $v[title] </option>";
		}
		$res .= "</select>";*/
		return $res;
	}
	
	
	/**
	 * buildInputForValSelector 构建下拉单选框（数据源：var）
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 下拉单选框表单位控件
	 *
	 */
	protected function buildInputForValSelector(&$field, $params, &$ioparams=array())
	{
		//valselector
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		
		$res =  "<input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		
		$mod = get_i18n('mod_'.$this->_name);		
		if ($mod && isset($mod[$name])) {
			
			$enums = $mod[$name]['valselector'];
			if (!$enums)
				$enums = array();
			
			
			$valselector = '';
			
			$valselector = "<select class='form-control valselector' data-id='param_$name'>";
			foreach ($enums as $key => $v) {
				$valselector .= "<option value='$key' > $v </option>";
			}
			$valselector .= "</select>";
			
			
			$res = "<label class='col-md-6' style='padding-left:0;'>".$res."</label> <label class='col-md-6'> $valselector </label>";
		}
		return $res;
	}
	
	/**
	 * buildInputForRadioBox 构建单选框
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 单选框表单位控件
	 *
	 */
	protected function  buildInputForRadioBox(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = intval($params[$name]);
		else 
			$val = 0;
		
		$title_Y = 'YES';
		$title_N = 'NO';
		$key = 'sel_yesno';
		
		$i18n = get_i18n();
		if (isset($i18n[$key])) {
			$title_N = 	$i18n[$key]['0'];	
			$title_Y = 	$i18n[$key]['1'];				
		} 
		
		
		/*
		<div class="radio-list">
		              <label class="radio-inline">
		              <input type="radio" name="optionsRadios" id="optionsRadios4" value="option1" checked> Option 1 </label>
		              <label class="radio-inline">
		              <input type="radio" name="optionsRadios" id="optionsRadios5" value="option2"> Option 2 </label>
		              <label class="radio-inline">
		              <input type="radio" name="optionsRadios" id="optionsRadios6" value="option3" disabled> Disabled </label>
		          </div>
		*/
		
		$id1 = $name.'_radio1';
		$id2 = $name.'_radio2';
		
		if ($val == 1)
		{
			$checked_Y ="CHECKED";
			$checked_N = "";
		}
		else
		{
			$checked_Y ="";
			$checked_N = "CHECKED";
		}
		
		return "<div class='radio-list'>
				<label class='radio-inline'>
				<input type='radio' name='params[$name]' id='$id1' value='0' $checked_N > $title_N </label>
				<label class='radio-inline'>
				<input type='radio' name='params[$name]' id='$id2' value='1' $checked_Y > $title_Y </label>
				</div>";            
	}
	
	/**
	 * buildInputForMultiCheckBox 构建复选框
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 复选框表单位控件
	 *
	 */
	protected function  buildInputForMultiCheckBox(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = intval($params[$name]);
		else 
			$val = 0;
		
		$res = "";
		$key = 'sel_'.$this->_modname.'_'.$name;
		$udb = get_i18n($key);
		if (!$udb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no key '$key'");
			$udb = array('0'=>'Enable');
		}
		
		$disabl_key = $name.'_disablemask';
		if (isset($params[$disabl_key]))
			$disabled_mask = intval($params[$disabl_key]);
		else 
			$disabled_mask = 0;
		
		
		$res = "<div class='checkbox-list'>";
		
		foreach($udb as $k=>$v) {
			$mask = 1 << $k;
			$flag = $val & $mask;
			$checked = $flag?'checked':'';
			$id = $this->_name.$name.$k;
			
			$disabled = ($disabled_mask & $mask)?"disabled":'';
			
			$res .= "<label class='checkbox-inline'>
					<input type='checkbox' name='params[$name][]' id='$id' value='$k' $checked $disabled > $v </label>";	
			
		}
		$res .= "</div>";
		
		return $res;            
	}
	
//	/**
//	 * initSelector 初始化选择器
//	 *
//	 * @param mixed $name 字段名称
//	 * @param mixed $vname 选择器名称
//	 * @return mixed 成功：true, 失败：false
//	 *
//	 */
//	protected function initSelector($name, $vname)
//	{
//		$varmultidb = get_i18n('sel_'.$vname);
//		if (!$varmultidb) {
//			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no sel of '$vname'!");
//			$varmultidb = array();
//		}
//		
//		$m = Factory::GetModel('var');	
//		$params = array();
//		$params['name'] = $vname;
//		$params['title'] = i18n($vname);
//		
//		$res = $m->set($params);
//		if (!$res) {
//			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set var failed!");
//			return false;
//		}
//		
//		$pid = $params['id'];				
//		foreach ($varmultidb as $key=>$v) {
//			$params = array();
//			$params['value'] = $key;
//			$params['title'] = $v;
//			$params['pid'] = $pid;			
//			$res = $m->set($params);
//			if (!$res) {
//				break;				
//			}
//		}	
//		
//		return $res;	
//	}
//	
		
//	/**
//	 * getVarMultiDB 按字段名称取出变量记录集
//	 * 
//	 * 1. 组合变量名称
//	 * 2. 查询变量信息，pid=变量ID
//	 * 3. 按pid查询变量记录集
//	 *
//	 * @param mixed $name 字段名
//	 * @return mixed 变量记录集
//	 *
//	 */
//	protected function getVarMultiDB($name)
//	{
//		$vname = $this->_modname.'_'.$name;
//		$m = Factory::GetModel('var');
//		$vardb = $m->getVarListByName($vname);
//		if (!$vardb) {
//			$this->initSelector($name, $vname);
//			$vardb = $m->getVarListByName($vname);
//		}
//		
//		return $vardb;
//	}
//	
	
	/**
	 * buildInputForVarMultiCheckBox 构建复选框(数据源：var)
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 复选框表单位控件
	 *
	 */
	protected function  buildInputForVarMultiCheckBox(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = intval($params[$name]);
		else 
			$val = 0;
		
		$disabl_key = $name.'_disablemask';
		if (isset($params[$disabl_key]))
			$disabled_mask = intval($params[$disabl_key]);
		else 
			$disabled_mask = 0;
		
		$res = "";
		
		$vardb = $this->getVarValList($field);
				
		$res = "<div class='checkbox-list'>";		
		
		foreach ($vardb as $key=>$v)	{
			$checked = "";
			$mask = 0x1 << $key; // mask
			
			$ck = $mask & $val;			
			if ($ck !== 0) $checked = "checked";
			
			$disabled = ($mask & $disabled_mask)?"disabled":'';
			
			$res .= "<label class='checkbox-inline'><input type='checkbox' name='params[$name][]' value='$key' $checked $disabled>$v[title]</label>";
		}
		
		$res .= "</div>";
		
		return $res;
		
	}
	
		
	
	
	/**
	 * buildInputForVarValSelector 构建变量值选择器（数据源：var） 
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 下拉列表选择器补全表单位控件
	 *
	 * 适用场景：
	 * 
	 * 编辑一篇新闻内容的来源，如：新华网，第一次录入后记到选择器中，下次直接选择，不用再录。
	 * 
	 */

	protected function buildInputForVarValSelector(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$res = "";
		//$vid = $field['vid'];
				
		//valselector
		$udb = $this->getVarValList($field);
		
		$res =  "<input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		
		$valselector = '';		
		if ($udb) {
			$valselector = "<select class='form-control valselector' data-id='param_$name'>";
			$valselector .= "<option value='' > 请选择 </option>";
			foreach ($udb as $key => $v) {
				$valselector .= "<option value='$v[value]' > $v[title] </option>";
			}
			$valselector .= "</select>";
		}		
		$res = "<label class='col-md-6' style='padding-left:0;'>".$res."</label> <label class='col-md-6'> $valselector </label>";
		return $res;
	}
	
	
	
	
	
	
	/**
	 * buildInputForRegionValSelector　构建区域选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @return mixed 区域选择器表单位控件
	 * 
	 */
	protected function buildInputForRegionValSelector(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		$pid = $field['region_pid'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$res = "";
		
		$res =  "<input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		
		$valselector = '';
		
		$m = Factory::GetModel('region');
		if (method_exists($m, "getRegionListByPid")) {
			$regiondb = $m->getRegionListByPid($pid);	
			if ($regiondb) {
				$valselector = "<select class='form-control valselector' data-id='param_$name'>";
				$valselector .= "<option value='' > 请选择 </option>";
				foreach ($regiondb as $key => $v) {
					$valselector .= "<option value='$v[name]' > $v[name] </option>";
				}
				$valselector .= "</select>";
			}	
		}
		
		$res = "<label class='col-md-8' style='padding-left:0;'>".$res."</label> <label class='col-md-4'> $valselector </label>";
		return $res;
	}
	
	
	
	/**
	 * buildInputForDate 构建日期选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @param mixed $format 默认格式，yyyy-mm-dd
	 * @return mixed 日期表单位控件
	 *
	 */
	protected function  buildInputForDate(&$field, $params, $ioparams=array(), $format='yyyy-mm-dd')
	{
		Factory::GetApp()->getActiveComponent()->enableJSCSS('datepicker,datetimepicker');
		
		$name = $field['name'];
		if (isset($params[$name]))
			$val = intval($params[$name]);
		else 
			$val = 0;
		
		$res = "";
		/*
		<div class='input-group input-medium date date-picker' data-date-format='dd-mm-yyyy' data-date-start-date='+0d'>
		<input type='text' class='form-control' readonly>
		<span class='input-group-btn'>
		<button class='btn default' type='button'><i class='fa fa-calendar'></i></button>
		</span>
		</div>*/
		$val = tformat($val, 'Y-m-d');
		
		$paramId = isset($field['paramId'])?$field['paramId']: "param_$name";	
		$paramName = isset($field['paramName'])?$field['paramName']: "params[$name]";	
		$placeholder = $field['title'];
		
		$res = "<div class='input-group input-medium date date-picker' data-date-format='$format' >
				<input type='text' class='form-control' name='params[$name]' value='$val' id='$paramId'>
				<span class='input-group-btn'>
				<button class='btn default_value' type='button'><i class='fa fa-calendar'></i></button>
				</span>
				</div>";
		
		return $res;            
	}
	
	/**
	 * buildInputForDate 构建日期时间选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @param mixed $format 默认格式，yyyy-mm-dd hh:ii:ss
	 * @return mixed 日期时间表单位控件
	 *
	 */
	protected function  buildInputForDatetime(&$field, $params, $ioparams=array(), $format='yyyy-mm-dd hh:ii:ss')
	{
		Factory::GetApp()->getActiveComponent()->enableJSCSS('datepicker,datetimepicker');
		
		$name = $field['name'];
		if (isset($params[$name]))
			$val = intval($params[$name]);
		else 
			$val = 0;
		
		$res = "";
		/*
		<div class="input-group date form_datetime">
			<input type="text" size="16" readonly class="form-control">
			<span class="input-group-btn">
			<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
			</span>
		</div>*/
		$val = tformat($val);
		
		$paramId = isset($field['paramId'])?$field['paramId']: "param_$name";	
		$paramName = isset($field['paramName'])?$field['paramName']: "params[$name]";	
		$placeholder = $field['title'];
		
		
		
		$res = "<div class='input-group input-medium date datetime-picker' data-date-format='$format' >
				<input type='text' class='form-control' name='params[$name]' value='$val' id='$paramId'>
				<span class='input-group-btn'>
				<button class='btn default_value date-set' type='button'><i class='fa fa-calendar'></i></button>
				</span>
				</div>";
		
		return $res;            
	}
	
	
	/**
	 * selectForModel 查询模型记录集
	 * 
	 * 应用场景：当选择器的数据源从模型中取中调用此方法。
	 * 
	 *
	 * @return mixed 记录集
	 *
	 */
	public function selectForModel($filter=array())
	{
		return $this->select($filter);
	}
	
	
	/**
	 * buildInputForModel 构建模型字段选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @param mixed $format 默认格式，yyyy-mm-dd hh:ii:ss
	 * @param mixed $has_default 选择器中是否包含默认值选择
	 * @param mixed $names 辅加注解名称 (...)
	 * @return mixed 模型字段选择器表单控件
	 *
	 */
	protected function buildInputForModel(&$field, $params, $ioparams = array(), $has_default=true, $names=array())
	{
		$model = $field['model'];
		$name = $field['name'];
		$default_value = 0;
		if (isset($params[$name]))
			$default_value = $params[$name];
		
		$disabled = (isset($field['disable'])&&!$has_default)?'disabled':'';
		
		$select_options = '';
		$m = Factory::GetModel($field['model']);
		$pkey = $m->getPKey();
		$filter = isset($ioparams[$name.'Filter'])?$ioparams[$name.'Filter']:array();
		$udb = $m->selectForModel($filter);
		
		$namekey = $m->getTitleFieldName();//$this->_tkey;
		$commkey = '';
		if ($names) {
			$namekey = array_shift($names);
			if ($names)
				$commkey = array_shift($names);
		}
		$default_title = i18n('Default');
		
		$selector = "<select class='form-control select2me form-filter filter-select filter-field' name='params[$name]' id='param_$name' $disabled >";
		if ($has_default || (isset($field['default']) && $field['default']))
			$selector .= '<option value=" ">'.$default_title.'</option>';
		
		foreach ($udb as $key => $v) {
			$id = $v[$pkey];
			$_name = $v[$namekey];
			if ($commkey) {
				$_name .= '('.$v[$commkey].')';
			}
			
			$selected = $default_value == $id ? 'selected' : '';
			$selector .= "<option value='$id' $selected > $_name </option>";
		}
		$selector .= "</select>";
		
		if ($disabled )
			$selector .= "<input type='hidden' name='params[$name]' value='$default_value' />";	
		return 	$selector;	
	}
	
	
	/**
	 * selectForTree 查询树型记录集
	 *
	 * 调用：$this->select()
	 * 
	 * @return mixed 记录集
	 *
	 */
	public function selectForTree($filter=array())
	{
		return $this->select($filter);
	}
	
	protected function formatTreeOptionTitle($v)
	{
		return isset($v[$this->_tkey])?$v[$this->_tkey]:$v[$this->_pkey];
	}
	/**
	 * treeOption 生成树型结构选择项（递归）
	 *
	 * @param mixed $depth 深度
	 * @param mixed $select_options 回带选择器选项
	 * @param mixed $tdb 记录集
	 * @param mixed $id 记录ID
	 * @param mixed $pid 记录父ID
	 * @return mixed 成功: true, 失败: false
	 *
	 */
	public function treeOption($depth, &$select_options, $tdb, $id='', $pid=0)
	{
		if ($tdb == null) 
			return false;
		
		$space = "";
		for($i=0; $i<$depth; $i++)
		{
			$space .= "&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		
		
		$_select_options = '';
		foreach ($tdb as $key=>$v)
		{
			if ($v['pid'] != $pid)
			{
				continue;
			}
			
			$disabled = '';
			$val = $v[$this->_pkey];
			$selected = ($id && $id == $val)?'selected':'';
			
			$title = $this->formatTreeOptionTitle($v);
			
			$select_options .= "<option value='$val' $selected $disabled >$space $title </option>";
			
			$depth ++ ;
			$this->treeOption($depth, $select_options, $tdb, $id, $v[$this->_pkey]);
			$depth --;
		}
		
		return true;
	}
	
	/**
	 * buildInputForTreeModel 构建树型结构模型选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文参数
	 * @param mixed $has_default 是否含有默认选择项
	 * @param mixed $names 辅加名称字段，作为标题注解用，如：jack(杰克)
	 * @return mixed 树型结构模型选择器表单控件
	 *
	 */
	public function buildInputForTreeModel(&$field, $params, $ioparams = array(), $has_default=true, $names=array())
	{
		$model = isset($field['model'])?$field['model']:$this->_modname;
		$name = $field['name'];
		$default_value = 0;
		if (isset($params[$name]))
			$default_value = $params[$name];
		
		//树型结构
		$name = $field['name'];
		$default_value = 0;
		if (isset($params[$name]))
			$default_value = $params[$name];
		
		$disabled = isset($field['disable'])?'disabled':'';
		
		$depth= 0;
		
		$select_options = '';
		$m = Factory::GetModel($model);
		
		//filter tree
		$filter = isset($ioparams['filter'])?$ioparams['filter']:array();
		$tdb = $m->selectForTree($filter);
		$pid = isset($filter['pid'])?$filter['pid']:0;				
		$m->treeOption($depth, $select_options, $tdb, $default_value, $pid);
		
		$default_title = i18n('Default');
		
		$paramId = isset($field['paramId'])?$field['paramId']: "param_$name";	
		$paramName = isset($field['paramName'])?$field['paramName']: "params[$name]";
		
		
		$res = "<div class='input-group'>";
		if ($disabled) {
			$res .= "<input type='hidden' name='params[$name]' value='$default_value'/>";
		}
		
		$res .= "<select class='form-control select2me form-filter ' name='$paramName' id='$paramId' $disabled>";
		if ($has_default || $model == $this->_modname || (isset($field['default']) && $field['default']))
			$res .= '<option value=" ">'.$default_title.'</option>';
		
		$res .= $select_options;
		$res .= "</select>";
		
		if (isset($field['addable']) && $field['addable']) {
			$res .= " <span class='input-group-btn'> <button class='btn default' type='button' id='addmodel_for_$name'> <i class='fa fa-plus'></i> </button></span></div>";
			$url = $ioparams['_basename']."/$model/add?dlg=1";
			
			$res .= "<script> $('#addmodel_for_$name').on('click', function(e) { ";
			
			$res .= "  var _self = $(this); ";
			$res .= "  layer.open({
					type: 2,
					title: '新建',
					shadeClose: false,
					shade: 0.2,
					shift:10,
					area: ['55%', '70%'],
					content: '$url',		              
					btn:['确定', '关闭'],
					yes: function(index, layero) {
					//console.log('in yes...');
					var iframeWin = window[\"layui-layer-iframe\" + index];
					 
					var row = iframeWin.row;
					//console.log(row);
					if (_.isUndefined(row))
						return false;
					
					_self.closest('.input-group').find('select').append('<option value='+row.id+' selected>'+row.name+'</option>');
					
					layer.close(index);
					//console.log('out yes'); 
					}
					});";
			
			$res .= "});</script>";
		} else {
			$res .= '</div>';
		}		
		return 	$res;	
	}
	
	
	public function buildInputForAutoComplete(&$field, $params, $ioparams = array(), $has_default=true, $names=array())
	{
		Factory::GetApp()->getActiveComponent()->enableJSCSS('typeahead');
		
		$model = isset($field['model'])?$field['model']:$this->_modname;
		$name = $field['name'];
		$value = $params[$name];
		
		$paramId = isset($field['paramId'])?$field['paramId']: "param_$name";	
		$paramName = isset($field['paramName'])?$field['paramName']: "params[$name]";
		
		$url = $ioparams['_base'].'/autocomplete?modname='.$model.'&field='.$name;
		
		$res = "
			<div class='input-group'>
				
				<input type='text' id='$paramId' name='$paramName' value='$value' class='form-control' />
				<span class='input-group-addon'>
				<i class='fa fa-search'></i>
				</span>        
			</div>";
			
			
		/*$res .= "<script type=\"text/javascript\">
				var custom = new Bloodhound({
				datumTokenizer: function(d) { return d.tokens; },
				queryTokenizer: Bloodhound.tokenizers.whitespace,
				remote: {
				url: '$url?q=%QUERY',
				wildcard: '%QUERY'
				}
				});
				
				custom.initialize();
				
				
				$('#$paramId').typeahead(null, {
				name: 'datypeahead_example_3',
				displayKey: 'value',
				source: custom.ttAdapter(),
				hint: (App.isRTL() ? false : true),
				templates: {
				suggestion: Handlebars.compile([
				'<div class=\"media\">',
				'<div class=\"pull-left\">',
				'<div class=\"media-object\">',
				'<img src=\"{{img}}\" width=\"50\" height=\"50\"/>',
				'</div>',
				'</div>',
				'<div class=\"media-body\">',
				'<h4 class=\"media-heading\">{{value}}</h4>',
				'<p>{{desc}}</p>',
				'</div>',
				'</div>',
				].join(''))
				}
				});</script>";*/
		
		
		$res .= "<script type=\"text/javascript\">
				var custom = new Bloodhound({
				datumTokenizer: function(d) { return d.tokens; },
				queryTokenizer: Bloodhound.tokenizers.whitespace,
				remote: {
				url: '$url&q=%QUERY',
				wildcard: '%QUERY'
				}
				});
				
				custom.initialize();				
				
				$('#$paramId').typeahead(null, {
				name: 'datypeahead_example_3',
				displayKey: 'value',
				source: custom.ttAdapter(),
				hint: (App.isRTL() ? false : true),
				templates: {
				suggestion: Handlebars.compile([
				'<div class=\"media\">',
				'<div class=\"media-body\">',
				'<h4 class=\"media-heading\">{{value}}</h4>',
				'<p>{{desc}}</p>',
				'</div>',
				'</div>',
				].join(''))
				}
				});</script>";
				
		return $res;	
				
					
	}
	
	public function buildInputForTreeNav(&$field, $params, $ioparams = array())
	{
		$name = $field['name'];
		$value = $params[$name];
		
		$url = $ioparams['_base'].'/treenav';
		
		$param_name = "params[$name]";
		$res = '<div class="treenav" data-url="'.$url.'">
				<input type="hidden" name="'.$param_name.'" value="'.$value.'"/>
				</div>';
				
		return $res;
						
		
	}
	
	protected function buildInputForFile(&$field, $params, $ioparams = array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';		
		
		$res =  "<div class='input-group'><input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		$res .= " <span class='input-group-btn'> <button class='btn green' type='button' id='selectimage_for_$name'> <i class='fa fa-file'></i> </button></span></div>";
				
		$type = isset($field['filtertype'])?$field['filtertype']:0;
		
		$url = $ioparams['_base'].'/selectfile?type='.$type;
		
		$res .= "<script> $('#selectimage_for_$name').on('click', function(e) { ";
		
		$res .= "  var _self = $(this); ";
		$res .= "  layer.open({
				type: 2,
				title: '选择文件',
				shadeClose: false,
				shade: 0.2,
				shift:10,
				area: ['55%', '70%'],
				content: '$url',		              
				btn:['确定', '关闭'],
				yes: function(index, layero) {
				//console.log('in yes...');
				var iframeWin = window[\"layui-layer-iframe\" + index];
				var row = iframeWin.row;
				//console.log(row);
				_self.closest('.input-group').find('input').val(row.url);
				
				layer.close(index);
				//console.log('out yes'); 
				}
				});";
		
		$res .= "});</script>";
		
		return $res;
	}
	
	
	/**
	 * buildInputForImage 构建图片选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 图片选择器表单控件
	 *
	 */
	protected function buildInputForImage(&$field, $params, $ioparams = array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';		
		
		$res =  "<div class='input-group'><input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		$res .= " <span class='input-group-btn'> <button class='btn green' type='button' id='selectimage_for_$name'> <i class='fa fa-image'></i> </button></span></div>";
		
		/*
		$(".selectimage").on('click', function(e) {
		          var _self = $(this);
		          layer.open({
		              type: 2,
		              title: '选择图片',
		              shadeClose: false,
		              shade: 0.2,
		              shift:10,
		              area: ['55%', '70%'],
		              content: 'insert_image3.html',
		              success: function(layero, index) {
		                  console.log('success...');
		                  var body = layer.getChildFrame('body', index);
		              },
		              btn:['确定', '关闭'],
		              yes: function(index, layero) {
		                  //console.log('in yes...');
		                  var iframeWin = window["layui-layer-iframe" + index];
		                  var row = iframeWin.row;
		                  //console.log(row);
		                  _self.closest('.input-group').find('input').val(row.photo);
				
		                  layer.close(index);
		                  //console.log('out yes'); 
		              }
		          });
		  });
		*/
		
		$url = $ioparams['_base'].'/selectfile?type=4';
		
		$res .= "<script> $('#selectimage_for_$name').on('click', function(e) { ";
		
		$res .= "  var _self = $(this); ";
		$res .= "  layer.open({
				type: 2,
				title: '选择图片',
				shadeClose: false,
				shade: 0.2,
				shift:10,
				area: ['55%', '70%'],
				content: '$url',		              
				btn:['确定', '关闭'],
				yes: function(index, layero) {
				//console.log('in yes...');
				var iframeWin = window[\"layui-layer-iframe\" + index];
				var row = iframeWin.row;
				//console.log(row);
				_self.closest('.input-group').find('input').val(row.url);
				
				layer.close(index);
				//console.log('out yes'); 
				}
				});";
		
		$res .= "});</script>";
		
		return $res;
	}
	
	/**
	 * buildInputForVideo 构建视频选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 图片选择器表单控件
	 *
	 */
	protected function buildInputForVideo(&$field, $params, $ioparams = array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		
		$res =  "<div class='input-group'><input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		$res .= " <span class='input-group-btn'> <button class='btn default' type='button' id='selectvideo_for_$name'> <i class='fa fa-film'></i> </button></span></div>";
		
		
		$url = $ioparams['_base'].'/selectfile?type=1';
		
		$res .= "<script> $('#selectvideo_for_$name').on('click', function(e) { ";
		
		$res .= "  var _self = $(this); ";
		$res .= "  layer.open({
				type: 2,
				title: '选择视频',
				shadeClose: false,
				shade: 0.2,
				shift:10,
				area: ['55%', '70%'],
				content: '$url',		              
				btn:['确定', '关闭'],
				yes: function(index, layero) {
				//console.log('in yes...');
				var iframeWin = window[\"layui-layer-iframe\" + index];
				var row = iframeWin.row;
				//console.log(row);
				_self.closest('.input-group').find('input').val(row.playurl);
				
				layer.close(index);
				//console.log('out yes'); 
				}
				});";
		
		$res .= "});</script>";
		
		return $res;
	}
	
	
	/**
	 * buildInputForVideos 构建视频多选框
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 视频多选框表单控件
	 *
	 */
	protected function buildInputForVideos(&$field, $params, $ioparams = array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		
		$res =  "<style>.bootstrap-tagsinput{width:100%;}</style><div class='input-group'><input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control' data-role='tagsinput'/>";
		$res .= " <span class='input-group-btn'> <button class='btn default' type='button' id='selectvideo_for_$name'> <i class='fa fa-film'></i> </button></span></div>";
		
		
		$url = $ioparams['_base'].'/selectfile?type=-1';
		
		$res .= "<script> $('#selectvideo_for_$name').on('click', function(e) { ";
		
		$res .= "  var _self = $(this); ";
		$res .= "  layer.open({
				type: 2,
				title: '选择视频',
				shadeClose: false,
				shade: 0.2,
				shift:10,
				area: ['55%', '70%'],
				content: '$url',		              
				btn:['确定', '关闭'],
				yes: function(index, layero) {
				//console.log('in yes...');
				var iframeWin = window[\"layui-layer-iframe\" + index];
				var rows = iframeWin.rows;
				var aids='';
				
				for (i=0; i<rows.length; i++) {
				if (rows[i]) {
				if (aids)
				aids +=','
				aids += rows[i].id;
				}
				}
				//console.log(row);
				_self.closest('.input-group').find('input').val(aids);
				
				layer.close(index);
				//console.log('out yes'); 
				}
				});";
		
		$res .= "});</script>";
		
		return $res;
	}
	
	
	protected function buildInputForMap(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$id = "selectlink_for_$name";		
		$res =  "<div class='input-group'><input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		$res .= " <span class='input-group-btn'> <button class='btn gray' type='button' id='$id'> <i class='fa fa-map-marker'></i> </button></span></div>";
		
		$url = $ioparams['_base'].'/map';		
		$res .= "<script> $('#$id').on('click', function(e) { ";
		
		$res .= "  var _self = $(this); ";
		$res .= "  layer.open({
				type: 2,
				title: '选择链接地址',
				shadeClose: false,
				shade: 0.2,
				shift:10,
				area: ['55%', '70%'],
				content: '$url',		              
				btn:['确定', '关闭'],
				yes: function(index, layero) {
				//console.log('in yes...');
				var iframeWin = window[\"layui-layer-iframe\" + index];
				var row = iframeWin.row;
				//console.log(row);
				_self.closest('.input-group').find('input').val(row.url);
				
				layer.close(index);
				//console.log('out yes'); 
				}
				});";
		
		$res .= "});</script>";
		
		return $res;
	}
	
	
	/**
	 * buildInputForLink 构建链接选择框
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 链接选择框表单控件
	 *
	 */
	protected function buildInputForLink(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$id = "selectlink_for_$name";		
		$res =  "<div class='input-group'><input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		$res .= " <span class='input-group-btn'> <button class='btn gray' type='button' id='$id'> <i class='fa fa-link'></i> </button></span></div>";
		
		$url = $ioparams['_base'].'/selectlink?dlg=1';		
		$res .= "<script> $('#$id').on('click', function(e) { ";
		
		$res .= "  var _self = $(this); ";
		$res .= "  top.layer.open({
					type: 2,
					title: '选择链接地址',
					shadeClose: false,
					shade: 0.2,
					shift:10,
					'maxmin':true,
					area: ['55%', '70%'],
					content: '$url',		              
					btn:['确定', '关闭'],
					yes: function(index, layero) {
						//console.log('in yes...');
						var iframeWin = top.window[\"layui-layer-iframe\" + index];
						var row = iframeWin.rows[0];
						//console.log(row);
						_self.closest('.input-group').find('input').val(row.url);
						
						top.layer.close(index);
						//console.log('out yes'); 
					}
				});";
		
		$res .= "});</script>";
		
		return $res;
	}
	
	
	/**
	 * buildInputForContent 构建内容选择框(从site_content模型中)
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 内容选择框表单控件
	 *
	 */
	protected function buildInputForContent(&$field, $params, $ioparams = array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$id = "selectlink_for_$name";		
		$res =  "<div class='input-group'><input type='text' value='$val'  name='_params[$name]' id='_param_$name' data-required='1' class='form-control'/>";
		$res .=  "<input type='hidden' value='$val'  name='params[$name]' id='param_$name' />";
		$res .= " <span class='input-group-btn'> <button class='btn gray' type='button' id='$id'> <i class='fa fa-link'></i> </button></span></div>";
		
		$url = $ioparams['_base']."/selectlink?dlg=1";		
		$res .= "<script> $('#$id').on('click', function(e) { ";
		
		$res .= "  var _self = $(this); ";
		$res .= "   top.layer.open({
					type: 2,
					title: '请选择',
					shadeClose: false,
					shade: 0.2,
					shift:10,
					maxmin:true,
					area: ['55%', '70%'],
					content: '$url',		              
					btn:['确定', '关闭'],
					yes: function(index, layero) {
						//console.log('in yes...');
						var iframeWin = top.window[\"layui-layer-iframe\" + index];
						var row = iframeWin.row;
						//console.log(row);
						_self.closest('.input-group').find('#_param_$name').val(row.name);
						_self.closest('.input-group').find('#param_$name').val(row.id);
						
						top.layer.close(index);
						//console.log('out yes'); 
					}
				});";
		
		$res .= "});</script>";
		
		return $res;
	}
	
		
	
	
	/**
	 * buildInputForCKEditor 构建CKEditor控件
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @param mixed $simple 是否为简版，默认false
	 * @return mixed CKEditor控件
	 *
	 * toolbar: [
				[ 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink' ],
				[ 'FontSize', 'TextColor', 'BGColor' ]
				]
	
	 */
	protected function buildInputForCKEditor(&$field, $params, $ioparams = array(), $simple=false)
	{
		Factory::GetApp()->getActiveComponent()->enableJSCSS('ckeditor');
		//<textarea class="ckeditor form-control" name="editor1" rows="6"></textarea>
		
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$simpleToolBar = '';
		if ($simple) {
			$simpleToolBar = "toolbar: [
					[ 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink' ],
					[ 'FontSize', 'TextColor', 'BGColor','Image']
					],";
		}
		
		$_basename = $ioparams['_base'].'/selectfile';
		$var_repconfig = "var repconfig = {
				$simpleToolBar
				filebrowserBrowseUrl : '$_basename',
				filebrowserImageUrl : '$_basename?type=4',
				filebrowserVideoUrl : '$_basename?type=1',
				filebrowserAudioUrl : '$_basename?type=8',
				filebrowserAttachUrl : '$_basename?type=-1',
				
				filebrowserWindowWidth:'800',
				filebrowserWindowHeight:'500'}; ";
		
		$id = "param_$name";
		$res =  "<textarea name='params[$name]' id='$id' class='ckeditor form-control' rows='6' >$val</textarea>";
		
		$res .= "<script>if (typeof(CKEDITOR) != 'undefined') { $var_repconfig CKEDITOR.replace('$id', repconfig); }</script>";
		if(!$simple)
		{
			$res.="<input type=\"checkbox\" name=\"params[imagetolocal]\" value=1 /> 外部图片本地化 <br />";
			$res.="<input type=\"checkbox\" name=\"params[selectimage]\" checked value=1 /> 自动提取第一张图片为本内容图片<br />";
			$res.="<input type=\"checkbox\" name=\"params[autofpage]\" checked value=1 /> 自动分页处理";
		}
		
		return $res;
	}
	
	
	/**
	 * buildInputForSNEditor 构建SNEditor控件
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed SNEditor控件
	 *
	 */
	protected function buildInputForSNEditor(&$field, $params, $ioparams = array())
	{
		Factory::GetApp()->getActiveComponent()->enableJSCSS('sneditor');
		//<textarea class="ckeditor form-control" name="editor1" rows="6"></textarea>
		
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$selectImageUrl = $ioparams['_basename'].'/file/selectimg';
		// <textarea type="text"  name="content" id="summernote"></textarea>
		$res =  "<textarea type='text'  name='params[$name]' id='param_$name'> $val </textarea> <script> $('#param_$name').summernote({height: 300, selectImageUrl:'$selectImageUrl'}); </script> <style>.note-editor.note-frame.fullscreen{ z-index:10050;} </style>";
		
		return $res;
	}
	
	/**
	 * buildInputForFileselector 构建文件选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 文件选择器表单控件
	 *
	 */
	protected function buildInputForFileselector(&$field, $params,$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$sbt = $ioparams['sbt'];
		$_base = $ioparams['_base'];
		$mname = $this->_name;
		$oid = isset($field['oid'])?$field['oid']:0;
		if (isset($params[$this->_pkey]))
			$oid = $params[$this->_pkey];			
		$utype=isset($field['uptype'])?'data-uptype=\''.$field['uptype'].'\'':'';
		$maxsize = isset($field['maxsize'])?'data-maxsize='.$field['maxsize']:'';
		
		
		$res = "<div class='input-group'>";
		$res .= "<span><textarea  class='form-control' rows='3' autocomplete='off' name='params[$name]' id='viewcontent_$name' >$val</textarea></span>";
		$res .= "<span class='input-group-btn vt'>";
		$res .= "<button type='button' class='btn default' id='param_$name'  
				data-url='$_base/filecontent' 
				data-tpl='fileselector' 
				data-viewcontent='#viewcontent_$name' 
				data-model='$mname' 
				data-oid='$oid' 
				data-sbt='$sbt' $utype $maxsize >
				<span class='fileinput-button'>选择<input type='file' name='files[]' class='inputfile'></span>
				</button>
				
				
				</span>
				</div>";
		
		$res .= "<script language='javascript'>jQuery(document).ready(function() { $('#param_$name').tileupload({autoupload:true}); });</script>";		
		
		return $res;
	}
	
	
	protected function buildInputForTileupload(&$field, $params,$ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$sbt = $ioparams['sbt'];
		$_base = $ioparams['_base'];
		$mname = $this->_name;
		$oid = isset($field['oid'])?$field['oid']:0;
		if (isset($params[$this->_pkey]))
			$oid = $params[$this->_pkey];			
		$utype=isset($field['uptype'])?$field['uptype']:'';
		$maxsize = isset($field['maxsize'])?'data-maxsize='.$field['maxsize']:'';
		
		$id = $params['id'];
		
		$data_width = isset($field['width'])? 'data-width='.$field['width']:'';		
		$data_height = isset($field['height'])? 'data-height='.$field['height']:'';		
		
		$res = "<div id='param_$name'  
				data-id='$id' 
				data-name='$name' $data_width $data_height
				data-url='$_base/upload' 
				data-tpl='tile' 
				data-model='$mname' 
				data-oid='$oid' 
				data-sbt='$sbt' $utype $maxsize >
				</div>";
		
		$res .= "<script language='javascript'>jQuery(document).ready(function() { $('#param_$name').tileupload({autoupload:true}); });</script>";		
		
		return $res;
	}
	
	/**
	 * buildInputForFileselector 构建文件选择器
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 文件选择器表单控件
	 *
	 */
	protected function buildInputForFileselectButton(&$field, $params, $ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$sbt = $ioparams['sbt'];
		$_base = $ioparams['_base'];
		$mname = $this->_name;
		$oid = isset($field['oid'])?$field['oid']:0;
		if (isset($params[$this->_pkey]))
			$oid = $params[$this->_pkey];			
		$utype=isset($field['uptype'])?'data-uptype='.$field['uptype']:'';
		$maxsize = isset($field['maxsize'])?'data-maxsize='.$field['maxsize']:'';
		
		$res = "<div id='param_$name' class='tileupload' data-url='$_base/fileupload' data-sbt='$sbt' data-model='$mname' data-oid='$oid' data-tpl='fileselectbutton' $utype $maxsize class='form-control' > </div>";
		$res .= "<script language='javascript'>jQuery(document).ready(function() { $('#param_$name').tileupload({autoupload:true}); });</script>";
		return $res;
	}
	
	
	/**
	 * buildInputForPassword 构建口令输入控件
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 口令输入控件表单控件
	 *
	 */
	protected function buildInputForPassword(&$field, $params, &$ioparams=array())
	{
		$name = $field['name'];
		$val = $params[$name];
		
		if (!isset($field['nohide']) || !$field['nohide'])
			$val = '';
		
		return "<input type='password' value='$val'  name='params[$name]'' id='param_$name' data-required='1' class='form-control'/>";
	}
	
	
	/**
	 * buildInputForFaselector 构建字体选择控件
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 字体选择表单控件
	 *
	 */
	protected function buildInputForFaselector(&$field, $params, $ioparams=array(), $issearch=false)
	{
		
		//valselector
		
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		
		$res =  "<input type='text' value='$val'  name='params[$name]' id='param_$name' data-required='1' class='form-control'/>";
		
		$mod = get_i18n('mod_'.$this->_name);		
		if ($mod && isset($mod[$name])) {
			$enums = $mod[$name]['valselector'];			
		}		
		if (!$enums) 
			$enums = get_i18n('sel_'.$this->_name.'_'.$name);				
		
		//Fontawesome Icons
		
		if ($enums) {			
			$valselector = '';
			$valselector = "<select class='form-control bs-select valselector' data-id='param_$name'>";
			foreach ($enums as $key => $v) {
				$valselector .= "<option value='$key' data-icon='$key'> $v </option>";
			}
			$valselector .= "</select>";
			
			$res = "<label class='col-md-6' style='padding-left:0;'>".$res."</label> <label class='col-md-6'> $valselector </label>";
			//$res = "<div class='input-group'> $res $valselector</div>";
		}
		
		return $res;						
	}
	
	/**
	 * buildInputForSize 构建字节输入控件
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 字节输入控件
	 *
	 */
	protected function  buildInputForSize(&$field, $params, $ioparams=array())
	{
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$disable = isset($field['disable'])?'disabled':'';
		
		$maxlength = $field['input_max_length'] > 0 ? "maxlength='{$field['input_max_length']}'" : '';
		
		$val = nformat_human_file_size($val);
		
		return "<input type='text' value='$val'  name='params[$name]' id='param_$name' $maxlength data-required='1' class='form-control' $disable />";
	}
	
	/**
	 * buildInputForGallery 构建图集控件
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 图集控件
	 *
	 */
	protected function buildInputForGallery(&$field, $params, $ioparams=array())
	{
		Factory::GetApp()->getActiveComponent()->enableJSCSS(array('bgallery', 'jquery_ui', 'gallery'));
		
		$name = $field['name'];
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		$sbt = $ioparams['sbt'];
		$_base = $ioparams['_base'];
		$mname = $this->_name;
		$mid = 0;
		if (isset($params[$this->_pkey]))
			$mid = $params[$this->_pkey];
		
		$w = '';
		$h = '';	
		if (isset($field['width']))
			$w = 'data-width="'.$field['width'].'"';
		if (isset($field['height']))
			$h = 'data-height="'.$field['height'].'"';
		
		
		$res = "<input type='hidden' name='params[$name]' value='$val' id='param_$name' />  <div id='fs_param_$name' class='gallery' data-url='$_base/gallery' data-name='$name' data-model='$mname' data-mid='$mid' $w $h   class='form-control' > </div>";
		
		return $res;
	}
	
	/**
	 * buildInputCustom 构建自定义控件
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 自定义控件
	 *
	 */
	protected function  buildInputCustom(&$field, $params, &$ioparams=array())
	{
		return $this->buildInputForText($field, $params, $ioparams);
	}
	
	/**
	 * buildInput 构建输入控件
	 *
	 * @param mixed $field 字段信息
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @param mixed $default 是否包含默认
	 * @return mixed 输入控件
	 *
	 */
	public function buildInput(&$field, $params, &$ioparams=array(), $default=false)
	{
		$inputtype = strtolower(trim($field['input_type']));
		switch ($inputtype) {
			default:
			case 'varchar':
			case 'char':
				$input = $this->buildInputForText($field, $params, $ioparams);
				break;
			case 'textaddon':
				$input = $this->buildInputForTextAddon($field, $params, $ioparams);
				break;
			case 'tinytext':
			case 'text':
				$input = $this->buildInputForTextarea($field, $params, $ioparams);
				break;
			case 'yesno':
				$field['selector'] = 'yesno';
			case 'selector':
			case 'enum':
				$input = $this->buildInputForSelector($field, $params, $ioparams, $default);
				break;
			case 'onoff':
				$field['selector'] = 'onoff';
				$input = $this->buildInputForSelector($field, $params, $ioparams, $default);
				break;
			case 'valselector':
				$input = $this->buildInputForValSelector($field, $params, $ioparams);
				break;
			case 'radiobox':
				$input = $this->buildInputForRadioBox($field, $params, $ioparams);
				break;
			case 'multicheckbox':
				$input = $this->buildInputForMultiCheckBox($field, $params, $ioparams);
				break;
			case 'varmulticheckbox':
				$input = $this->buildInputForVarMultiCheckBox($field, $params, $ioparams);
				break;
			case 'varselector':
				$input = $this->buildInputForVarSelector($field, $params, $ioparams);
				break;
			case 'varvalselector':
				$input = $this->buildInputForVarValSelector($field, $params, $ioparams);
				break;
			case 'regionvalselector':
				$input = $this->buildInputForRegionValSelector($field, $params, $ioparams);
				break;
			case 'date':
				$input = $this->buildInputForDate($field, $params, $ioparams);
				break;
			case 'datetime':
				$input = $this->buildInputForDatetime($field, $params, $ioparams);
				break;
			case 'yearmonth':
			case 'yyyymm':
				$input = $this->buildInputForDate($field, $params, $ioparams, "yyyy-mm");
				break;
			case 'model':
				$input = $this->buildInputForModel($field, $params, $ioparams, $default);
				break;
			case 'pid':
			case 'treemodel':
				$input = $this->buildInputForTreeModel($field, $params, $ioparams, $default);
				break;
			case 'autocomplete':
				$input = $this->buildInputForAutoComplete($field, $params, $ioparams, $default);
				break;
			case 'file': //文件选择
				$input = $this->buildInputForFile($field, $params, $ioparams);
				break;
			case 'image': //IMAGE url或本地选择
				$input = $this->buildInputForImage($field, $params, $ioparams);
				break;
			case 'video': //VIDEO url或本地选择
				$input = $this->buildInputForVideo($field, $params, $ioparams);
				break;
			case 'videos': //VIDEOs url或本地选择
				$input = $this->buildInputForVideos( $field, $params,$ioparams);
				break;
			case 'map': //地图定位地址选择
				$input = $this->buildInputForMap($field, $params, $ioparams);
				break;
			case 'link': //link url或本地选择
				$input = $this->buildInputForLink($field, $params, $ioparams);
				break;
			case 'content': //content ID
				$input = $this->buildInputForContent($field, $params, $ioparams);
				break;

			case 'ckeditor': //CKEditor
				$input = $this->buildInputForCKEditor($field, $params, $ioparams);
				break;
			case 'ckeditorsimple': //CKEditor of Simple
				$input = $this->buildInputForCKEditor($field, $params, $ioparams, true);
				break;
			case 'sneditor': //SummerNode editor
				$input = $this->buildInputForSNEditor($field, $params, $ioparams);
				break;
			case 'fileselector': //file selector
				$input = $this->buildInputForFileselector($field, $params, $ioparams);
				break;
			case 'tileupload': //file tileupload
				$input = $this->buildInputForTileupload($field, $params, $ioparams);
				break;
			case 'fileselectbutton': //file selector button
				$input = $this->buildInputForFileselectButton($field, $params, $ioparams);
				break;
			case 'gallery':
				$input = $this->buildInputForGallery($field, $params, $ioparams);
				break;
			case 'password': 
				$input = $this->buildInputForPassword($field, $params, $ioparams);
				break;
			case 'faselector': 
				$input = $this->buildInputForFaselector($field, $params,$ioparams);
				break;
			case 'size': 
				$input = $this->buildInputForSize($field, $params, $ioparams);
				break;			
			case 'timestamp':
			case 'cuid':
			case 'uid':
				$field['edit'] = false;
				break;
			case 'custom':
				$input = $this->buildInputCustom($field, $params, $ioparams);
				break;
			
		} 
		
		return $input;
	}
	
	
	/**
	 * getFieldsforInput 取输入字段信息
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 字段记录集
	 *
	 */
	public function getFieldsforInput($params=array(), &$ioparams=array())
	{
		$fdb = $this->getFields();		
		foreach ($fdb as $key => &$v) {
			$v['required'] = $v['required']?'true':'false';
			$v['input'] = $this->buildInput($v, $params, $ioparams);
							
		}
		return $fdb;
		
	}
	
	
	protected function initAddParams(&$params=array(), &$ioparams=array())
	{
		return false;
	}
	
	protected function initEditParams(&$params=array(), &$ioparams=array())
	{
		return false;
	}
	
	/**
	 * getFieldsForInputAdd 取新建字段记录集
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 字段记录集
	 *
	 */
	public function getFieldsForInputAdd($params=array(), &$ioparams=array())
	{
		$this->initAddParams($params, $ioparams);
		
		$res = $this->getFieldsForInput($params, $ioparams);
		return $res;
	}
	
	/**
	 * getFieldsForInputEdit 取编辑字段记录集
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 字段记录集
	 *
	 */
	public function getFieldsForInputEdit($params=array(), &$ioparams=array())
	{
		$this->initEditParams($params, $ioparams);
		
		return $this->getFieldsForInput($params, $ioparams);
	}
	
	
	/**
	 * buildInputForSearchKeyword 构建关键字搜索控件
	 *
	 * @param mixed $params 记录
	 * @param mixed $searchfdb 搜索字段
	 * @return mixed 关键字搜索控件
	 *
	 */
	protected function buildInputForSearchKeyword($params, &$searchfdb)
	{
		$firstkey = '';
		$placeholder = '';
		$name = '__keyword';
		if (isset($params[$name]))
			$val = $params[$name];
		else 
			$val = '';
		
		foreach ($searchfdb as $key => $v) {
			if (($v['searchable']&1) != 1) 
				continue;
				
			$placeholder .= $v['title'].' ';
			if (!$firstkey)
				$firstkey = $key;
		}
		
		$searchinput = "<input type='text' class='form-control form-filter filter-field keyword' name='params[$name]' value='$val' placeholder='$placeholder'/>";
		$searchfdb[$firstkey]['searchinput'] = $searchinput;
		
		return true;
	}
	
	/**
	 * getFieldsForSearch 构建搜索控件
	 *
	 * @param mixed $params 记录
	 * @param mixed $searchfdb 搜索字段
	 * @return mixed 搜索控件
	 *
	 */
	public function getFieldsForSearch($params=array(), &$ioparams=array())
	{	
		$searchfdb = array();
			
		$fdb = $this->getFields();		
		foreach ($fdb as $key => $v) {
			if (!$v['searchable'])
				continue;
			$v['input'] = $this->buildInput($v, $params, $ioparams, true);
			$searchfdb[$key] = $v;
						
		}
		
		if ($searchfdb) {
			$this->buildInputForSearchKeyword($params, $searchfdb);
		}
				
		return $searchfdb;
	}
	
	
	/**
	 * getFieldsForTable 取表格视图字段集（对bool型字段属性格式为1|0）
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 字段集
	 *
	 */
	public function getFieldsForTable($params=array(), &$ioparams=array())
	{	
		$_fdb = array();
		
		$fdb = $this->getFields();		
		foreach ($fdb as $key => $v) {
			foreach ($v as $k2=>&$v2) {
				if (is_bool($v2)) {
					$v2 = $v2?1:0;
				}			
			}
			
			$_fdb[$key] = $v;
		}
		
		return $_fdb;
	}
	
	
	/**
	 * getFieldsForDetail 获取详细页字段集
	 *
	 * @param mixed $params 记录
	 * @param mixed $ioparams 请求上下文
	 * @return mixed 字段集
	 *
	 */
	public function getFieldsForDetail($params=array(), &$ioparams=array())
	{	
		$_fdb = array();
		
		$fdb = $this->getFields();		
		foreach ($fdb as $key => $v) {
			if (!$v['detail'])
				continue;
			$v['required'] = $v['required']?'1':0;	
			
			$_tkey = '_'.$v['name'];
			$v['value'] = isset($params[$_tkey])?$params[$_tkey]:$params[$v['name']];
						
			$_fdb[$key] = $v;
		}
				
		return $_fdb;
	}	
	
	public function formatForModContent($params, $ioparams=array())
	{
		return false;
	}
}
