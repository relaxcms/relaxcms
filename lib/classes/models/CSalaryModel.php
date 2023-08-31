<?php
/**
 * @file
 *
 * @brief 
 * 
 * 费类基类
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CSalaryModel extends CTableModel
{
	//_map2field
	protected $_map2field = array(
		'221101'=>array('readonly'=>true,    'map2field'=>'total_salary'),	//应付职工工资
		'22110101'=>array('readonly'=>true,  'map2field'=>'base_salary2'),	//应付基本工资
		'22110102'=>array('readonly'=>false, 'map2field'=>'job_salary'),	//应付岗位工资
		'22110103'=>array('readonly'=>false, 'map2field'=>'s03_salary'),	//应付住宿补助
		'22110104'=>array('readonly'=>false, 'map2field'=>'s04_salary'),	//应付用餐补助
		'22110105'=>array('readonly'=>false, 'map2field'=>'s05_salary'),	//应付交通补助
		'22110106'=>array('readonly'=>false, 'map2field'=>'s06_salary'),	//应付通讯补助
		'22110107'=>array('readonly'=>false, 'map2field'=>'s07_salary'),	//应付加班工资
		'22110108'=>array('readonly'=>false, 'map2field'=>'s08_salary'),	//应付奖金津贴补贴
		'22110109'=>array('readonly'=>false, 'map2field'=>'s09_salary'),	//应付业绩提成
		'22110110'=>array('readonly'=>false, 'map2field'=>'s10_salary'),	//应付满勤工资
		'22110111'=>array('readonly'=>false, 'map2field'=>'s11_salary'),	//应付工龄工资
		'22110112'=>array('readonly'=>false, 'map2field'=>'s12_salary'),	//应付福利费
		
		'221104'=>array('readonly'=>true, 'map2field'=>'sb_all_unit'),		//应付社会保险费
		'22110401'=>array('readonly'=>true, 'map2field'=>'yl_fee_unit'),	//应付养老保险（单位）
		'22110402'=>array('readonly'=>true, 'map2field'=>'yb_fee_unit'),	//应付医疗保险（单位）
		'22110403'=>array('readonly'=>true, 'map2field'=>'gs_fee_unit'),	//应付工伤保险（单位）
		'22110404'=>array('readonly'=>true, 'map2field'=>'sy_fee_unit'),	//应付失业保险（单位）
		'22110405'=>array('readonly'=>true, 'map2field'=>'syu_fee_unit'),	//应付生育保险（单位）
		'221105'=>array('readonly'=>true, 'map2field'=>'gj_all_unit'),		//应付住房公积金

		'22110120'=>array('readonly'=>true, 'map2field'=>'sb_all_person'),	  //代扣代缴社保
		'2211012001'=>array('readonly'=>true, 'map2field'=>'yl_fee_person'),  //代扣代缴养老保险
		'2211012002'=>array('readonly'=>true, 'map2field'=>'yb_fee_person'),  //代扣代缴医疗保险
		'2211012003'=>array('readonly'=>true, 'map2field'=>'gs_fee_person'),  //代扣代缴工伤保险
		'2211012004'=>array('readonly'=>true, 'map2field'=>'sy_fee_person'),  //代扣代缴失业保险
		'2211012005'=>array('readonly'=>true, 'map2field'=>'syu_fee_person'), //代扣代缴生育保险
		
		'22110121'=>array('readonly'=>true, 'map2field'=>'gj_all_person'), //代扣代缴公积金
		'22110132'=>array('readonly'=>true, 'map2field'=>'tax_all'), //代扣代缴个税
	);
	
	protected $_catalog2salary = array();
		
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CFeeModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	/**
	* getCatalog2Salary 查询职工工资科目
	*
	* @return mixed This is the return value description
	*
	*/
	public function getCatalog2Salary()
	{
		if (!$this->_catalog2salary) {
			$m = Factory::GetModel('fm_catalog');
			$pcno = '2211'; //职工薪酬
			$cdb =  $m->select(array('cno'=>array('llike'=>$pcno)));
			
			$_cdb = array();
			foreach ($cdb as $key=>$v) {
				$cno = $v['cno'];
				if ($cno == $pcno)
					continue;
					
				if (isset($this->_map2field[$cno])) {
					$v['readonly'] = $this->_map2field[$cno]['readonly'];
					$v['map2field'] = $this->_map2field[$cno]['map2field'];
				}
				
				$v['is_total_salary'] = (strncmp($cno, "221101", 6) === 0 && !$v['readonly'])?true:false;
				$v['is_unit_salary'] = (strncmp($cno, "221101", 6) !== 0 && !$v['readonly'])?true:false;
				
				$_cdb[$cno] = $v;
			}
			
			$this->_catalog2salary = $_cdb;
		}
				
		return $this->_catalog2salary;
	}
	
	protected function isDir($cinfo)
	{
		$m = Factory::GetModel('fm_catalog');
		return $m->isDir($cinfo);
	}
	
	protected function isReadOnly($cinfo)
	{
		$cdb = $this->getCatalog2Salary();
		//代扣代缴社保,代扣代缴公积金,代扣代缴个税
		if (isset($cdb[$cinfo['cno']]))
			return $cdb[$cinfo['cno']]['readonly'];			
		return false;
	}
	
	protected function getDefaultParams()
	{
		$params = array();
		$params['is_sb'] = 1; 
		$params['is_gj'] = 0; 
		$params['is_tax'] = 1; 
		
		$params['sb_base'] = 3429.11; //社保基数, 2021:3017.01	
		//$params['yb_base_unit'] = 3429.06; //医保单位基数/0.064
		//$params['yb_base_person'] = 3429.00; //基本医疗保险费基数	
		//公积金基数	
		$params['gj_base'] = 0; 
		
		$params['yb_rate_unit'] = 0.064;  // 医保单位
		$params['yb_rate_person'] = 0.02; // 医保个人
		
		$params['yl_rate_unit'] = 0.16;   //0.16		
		$params['yl_rate_person'] = 0.08; //0.08	
		
		$params['sy_rate_unit'] = 0.005;  //失业保险单位
		$params['sy_rate_person'] = 0.005; 
		
		$params['gs_rate_unit'] = 0.001; //单位工伤保险
		$params['gs_rate_person'] = 0;
		$params['syu_rate_unit'] = 0.00; //单位生育保险
		$params['syu_rate_person'] = 0;
		
		$params['gj_rate_unit'] = 0; //单位公积金
		$params['gj_rate_person'] = 0;
		
		
		//config
		$m = Factory::GetModel('fm_params');
		$cfgdb = $m->getParams();
		foreach ($params as $key=>$v) {
			if (isset($cfgdb[$key])) {
				$params[$key] = $cfgdb[$key];
			}
		}
		
		return $params;
	}
	
	protected function clcSBGJ(&$params)
	{
		//3017.01 + 412.1 (基数调整)
		//3017.01 + 263.7 (基数调整)
		$defparams = $this->getDefaultParams();
		
		!isset($params['is_sb']) && $params['is_sb'] = $defparams['is_sb']; 
		!isset($params['is_gj']) && $params['is_gj'] = $defparams['is_gj']; 
		!isset($params['is_tax']) && $params['is_tax'] = $defparams['is_tax']; 
		
		!isset($params['sb_base']) && $params['sb_base'] = $defparams['sb_base']; //社保基数, 2021:3017.01	
		//公积金基数	
		!isset($params['gj_base']) && $params['gj_base'] = $defparams['gj_base']; 
		
		!isset($params['yb_rate_unit']) && $params['yb_rate_unit'] = $defparams['yb_rate_unit'];  // 医保单位
		!isset($params['yb_rate_person']) && $params['yb_rate_person'] = $defparams['yb_rate_person']; // 医保个人
		
		!isset($params['yl_rate_unit']) && $params['yl_rate_unit'] = $defparams['yl_rate_unit'];   //0.16		
		!isset($params['yl_rate_person']) && $params['yl_rate_person'] = $defparams['yl_rate_person']; //0.08	
		
		!isset($params['sy_rate_unit']) && $params['sy_rate_unit'] = $defparams['sy_rate_unit'];  //失业保险单位
		!isset($params['sy_rate_person']) && $params['sy_rate_person'] = $defparams['sy_rate_person']; 
		
		!isset($params['gs_rate_unit']) && $params['gs_rate_unit'] = $defparams['gs_rate_unit']; //单位工伤保险
		!isset($params['gs_rate_person']) && $params['gs_rate_person'] = $defparams['gs_rate_person'];
		!isset($params['syu_rate_unit']) && $params['syu_rate_unit'] = $defparams['syu_rate_unit']; //单位生育保险
		!isset($params['syu_rate_person']) && $params['syu_rate_person'] = $defparams['syu_rate_person'];
		
		!isset($params['gj_rate_unit']) && $params['gj_rate_unit'] = $defparams['gj_rate_unit']; //单位公积金
		!isset($params['gj_rate_person']) && $params['gj_rate_person'] = $defparams['gj_rate_person'];
		
		/*
		个人医保代缴	person_fee_yb	double	NULL	
		个人养老代缴	person_fee_yl	double	NULL	
		个人失业代缴	person_fee_sy	double	NULL	
		个人工伤代缴	person_fee_gs	double	NULL	
		单位医保缴费	unit_fee_yb	double	NULL	
		单位养老缴费	unit_fee_yl	double	NULL	
		单位失业缴费	unit_fee_sy	double	NULL	
		单位工伤缴费	unit_fee_gs	double	NULL	
		*/
		$sb_base = $params['sb_base'];	
		if (!$params['is_sb'])
			$sb_base = 0;
				
		$params['yb_fee_person'] = $sb_base * $params['yb_rate_person'];
		$params['yl_fee_person'] = $sb_base * $params['yl_rate_person'];
		$params['sy_fee_person'] = $sb_base * $params['sy_rate_person'];
		$params['gs_fee_person'] = $sb_base * $params['gs_rate_person'];
		$params['syu_fee_person'] = $sb_base * $params['syu_fee_person'];
		
		$params['yb_fee_unit'] = $sb_base * $params['yb_rate_unit'];
		$params['yl_fee_unit'] = $sb_base * $params['yl_rate_unit'];
		$params['sy_fee_unit'] = $sb_base * $params['sy_rate_unit'];
		$params['gs_fee_unit'] = $sb_base * $params['gs_rate_unit'];
		$params['syu_fee_unit'] = $sb_base * $params['syu_fee_unit'];
		
		
		//社保单位累加
		$sb_all_unit = $params['yb_fee_unit']
			+ $params['yl_fee_unit']
			+ $params['sy_fee_unit']
			+ $params['gs_fee_unit']
		+ $params['syu_fee_unit'];
		$params['sb_all_unit'] = $sb_all_unit;
		
		//社保个人累加
		$sb_all_person = $params['yb_fee_person']
			+ $params['yl_fee_person']
			+ $params['sy_fee_person']
			+ $params['gs_fee_person']
			+ $params['syu_fee_person'];
		$params['sb_all_person'] = $sb_all_person;
		
		//社保单位与个人累加	
		$sb_all = $sb_all_unit + $sb_all_person;
		$params['sb_all'] = $sb_all;
		
		//住房公积金 
		//gj_all_person
		$gj_base = $params['gj_base'];
		if (!$params['is_gj'])
			$gj_base = 0;
			
		$params['gj_fee_unit'] = $gj_base * $params['gj_rate_unit'];
		$params['gj_fee_person'] = $gj_base * $params['gj_rate_person'];
		
		$params['gj_all_unit'] = $params['gj_fee_unit'];
		$params['gj_all_person'] = $params['gj_fee_person'];
		
		$gj_all = $params['gj_all_unit'] + $params['gj_all_person'];
		$params['gj_all'] = $gj_all;
	}
	
	protected function clcSalary(&$params)
	{
		$cdb = $this->getCatalog2Salary();
		$feedb = isset($params['feedb'])?$params['feedb']:array();
		
		$_cdb = array();
		foreach ($cdb as $key=>$v) {
			$_cdb[$v['id']] = $v;
		}
		//基本工资
		$base_salary = $params['base_salary'];
		$total_salary = $base_salary;
		$unit_salary = 0;
		
		foreach ($feedb as $key=>$v) {
			if (isset($cdb[$key])) 
				$cinfo = $cdb[$key];
			else if (isset($_cdb[$key])) 
				$cinfo = $_cdb[$key];
			else
				continue;
			
			$fee = floatval(is_array($v)?$v['fee']:$v);	
			if ($cinfo['is_total_salary']) {
				//format后，直接parse, 入库
				$total_salary += $fee;
			}
			
			if ($cinfo['is_unit_salary']) {
				$unit_salary += $fee;
			}			
			
		}		
		
		$total_salary += $sum_salary;
				
		//应付员工工资
		$params['total_salary'] = $total_salary;
		//单位为员工支付的相关福利，不计个税部分
		$params['unit_salary'] = $unit_salary;
		
	}
	
	
	protected function clcTax(&$params)
	{
		$m = Factory::GetModel('fm_params');
		$cfgdb = $m->getParams();
		
		
		$tax_all = 0;
		
		//实发去除代缴
		$salary = $params['total_salary'];	
		//代扣个人社保
		$salary -= $params['sb_all_person'];
		//代扣个人公积金
		$salary -= $params['gj_fee_person'];		
		
		//去掉扣除项
		
		//查找级别
		for ($i=7; $i>0; $i--) {
			$level = $cfgdb['tax_level'.$i];
			//rlog('$level='.$level);
			if ($salary > $level) {					
				$rate = $cfgdb['tax_level'.$i.'_rate'];
				$sub = $cfgdb['tax_level'.$i.'_sub'];	
				
				//去掉起征点
				$salary -= $cfgdb['tax_level1']; //直征点去掉
							
				$tax_all =  $salary*$rate - $sub;
				break;
			}
		}
	
		$params['tax_all'] = $tax_all;
		//rlog($params);exit;
		
		return true;
	}
	
	//计算工资全额（含单位社保等）
	protected function clcSalary2(&$params)
	{
		//实发去除代缴
		$real_salary = $params['total_salary'];	
		//代扣个人社保
		if ($params['is_sb'])
			$real_salary -= $params['sb_all_person'];		
		//代扣个人公积金
		if ($params['is_gj'])
			$real_salary -= $params['gj_fee_person'];
		//代扣个税
		if ($params['is_tax'])
			$real_salary -= $params['tax_all'];
		
		//实发薪资总额
		$params['real_salary'] = $real_salary;
		
		//工资总额 + 单位社保 + 单位公积金 + 单位员工其它费等
		$params['all_salary'] = $params['total_salary'] + $params['sb_all_unit'] 
			+ $params['gj_all_unit']+ $params['unit_salary'];
			
		$params['payed_salary'] = 0;
	}
	
	protected function clcSalary3(&$params)
	{
		//应付基本工资 : 基本工资 - 代扣代缴
		$base_salary2 = $params['base_salary'];
		
		//代扣个人社保
		if ($params['is_sb'])
			$base_salary2 -= $params['sb_all_person'];		
		//代扣个人公积金
		if ($params['is_gj'])
			$base_salary2 -= $params['gj_fee_person'];
		//代扣个税
		if ($params['is_tax'])
			$base_salary2 -= $params['tax_all'];
		
		$params['base_salary2'] = $base_salary2;		
	}
	
	
	protected function clcAll(&$params)
	{
		//计算工资
		$this->clcSalary($params);
		//计算社保、公积金费
		$this->clcSBGJ($params);
		//计算工资全额（含单位社保等）
		$this->clcSalary2($params);
		//计个税费
		$this->clcTax($params);
		
		//计算应付基本工资
		$this->clcSalary3($params);
	}
	
	protected function parseInputSalary(&$params)
	{
		$this->clcAll($params);
		
		$cdb = $this->getCatalog2Salary();
		$feedb = isset($params['feedb'])?$params['feedb']:array();
		
		$_feedb = array();
		
		//format : feedb[cno] = fee, eg: feedb[22110102]=1000
		foreach ($feedb as $key=>$v) {
			if (isset($cdb[$key])) {
				$cinfo = $cdb[$key];
				
				//format后，直接parse, 入库
				$fee = is_array($v)?$v['fee']:$v;	
							
				$_feedb[$cinfo['id']] = $fee;
			}
		}
		
		foreach ($cdb as $key=>$v) {
			$fieldname = $v['map2field'];
			if (isset($params[$fieldname])) {
				$_feedb[$v['id']] = $params[$fieldname];
			}	
		}
		
		$params['feedb'] = $_feedb;
				
		return true;
	}
	
	protected function formatInputSalary(&$params)
	{
		$this->clcAll($params);
		
		$cdb = $this->getCatalog2Salary();
		
		$feedb = isset($params['feedb'])?$params['feedb']:array();
		
		$_feedb = array();
		
		//format : feedb[cno] = fee, eg: feedb[22110102]=1000
		foreach ($feedb as $key=>$v) {
			if (isset($cdb[$key])) {
				$cinfo = $cdb[$key];
				$v['fee'] = $v;
			}
		}
		
		foreach ($cdb as $key=>&$v) {
			$cno = $v['cno'];
			$id = $v['id'];
			$v['fee'] = 0;			
			if (isset($feedb[$key])){
				$v['fee'] = $feedb[$key];
			}
			if (isset($feedb[$id])){
				$v['fee'] = $feedb[$id];
			}
						
			$fieldname = $v['map2field'];
			if (isset($params[$fieldname])) {//
				$v['fee'] = $params[$fieldname];
			}	
			
			$isdir = $this->isDir($v);
			$readonly = $isdir || $this->isReadOnly($v);
			
			
			$v['readonly'] = $readonly?'readonly':'';
			$v['class'] = $v['is_total_salary']?'catalog2salary':($v['is_unit_salary']?'unit_salary':'cno_'.$cno);
		}
		
		//rlog($cdb);
		$params['feedb'] = $cdb;	
			
		return true;
	}
	

	
	public function getFieldsForDetail($params=array(), &$ioparams=array())
	{
		$res = parent::getFieldsForDetail($params, $ioparams);
		
		$id = $params['id'];
		$params = $this->get($id);
		$this->formatInputSalary($params);
		
		$ioparams['feedb'] = $params['feedb'];
		
		
		return $res;
		
	}
}
