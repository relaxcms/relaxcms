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

class CFeeModel extends CTableModel
{
	//_map2field
	protected $_map2field = array(
		'221101'=>array('readonly'=>true,    'map2field'=>'total_salary'),	//应付职工工资
		'22110101'=>array('readonly'=>true,  'map2field'=>'s01_salary'),	//应付基本工资
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
		
		'221104'=>array('readonly'=>true, 'map2field'=>'sb_all_unit'),	//应付社会保险费
		'22110401'=>array('readonly'=>true, 'map2field'=>'yl_fee_unit'),	//应付养老保险（单位）
		'22110402'=>array('readonly'=>true, 'map2field'=>'yb_fee_unit'),	//应付医疗保险（单位）
		'22110403'=>array('readonly'=>true, 'map2field'=>'gs_fee_unit'),	//应付工伤保险（单位）
		'22110404'=>array('readonly'=>true, 'map2field'=>'sy_fee_unit'),	//应付失业保险（单位）
		'22110405'=>array('readonly'=>true, 'map2field'=>'syu_fee_unit'), //应付生育保险（单位）
		'221105'=>array('readonly'=>true, 'map2field'=>'gj_all_unit'),	//应付住房公积金

		'22110120'=>array('readonly'=>true, 'map2field'=>'sb_all_person'), //代扣代缴社保
		'2211012001'=>array('readonly'=>true, 'map2field'=>'yl_fee_person'), //代扣代缴养老保险
		'2211012002'=>array('readonly'=>true, 'map2field'=>'yb_fee_person'), //代扣代缴医疗保险
		'2211012003'=>array('readonly'=>true, 'map2field'=>'gs_fee_person'), //代扣代缴工伤保险
		'2211012004'=>array('readonly'=>true, 'map2field'=>'sy_fee_person'), //代扣代缴失业保险
		'2211012005'=>array('readonly'=>true, 'map2field'=>'syu_fee_person'), //代扣代缴生育保险
		
		'22110121'=>array('readonly'=>true, 'map2field'=>'gj_all_person'), //代扣代缴公积金
		'22110132'=>array('readonly'=>true, 'map2field'=>'tax_all'), //代扣代缴个税
	);
		
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CFeeModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function isReadOnly($cinfo)
	{
		//代扣代缴社保,代扣代缴公积金,代扣代缴个税
		if (isset($this->_map2field[$cinfo['cno']]))
			return $this->_map2field[$cinfo['cno']]['readonly'];			
		return false;
	}
	
	
	protected function initSB(&$params)
	{
		//3017.01 + 412.1 (基数调整)
		//3017.01 + 263.7 (基数调整)
		
		!isset($params['sb_base']) && $params['sb_base'] = 3429.11; //社保基数, 2021:3017.01	
		//$params['yb_base_unit'] = 3429.06; //医保单位基数/0.064
		//$params['yb_base_person'] = 3429.00; //基本医疗保险费基数	
		//公积金基数	
		!isset($params['gj_base']) && $params['gj_base'] = 0; 
		
		!isset($params['yb_rate_unit']) && $params['yb_rate_unit'] = 0.064;  // 医保单位
		!isset($params['yb_rate_person']) && $params['yb_rate_person'] = 0.02; // 医保个人
		
		!isset($params['yl_rate_unit']) && $params['yl_rate_unit'] = 0.16;   //0.16		
		!isset($params['yl_rate_person']) && $params['yl_rate_person'] = 0.08; //0.08	
		
		!isset($params['sy_rate_unit']) && $params['sy_rate_unit'] = 0.005;  //失业保险单位
		!isset($params['sy_rate_person']) && $params['sy_rate_person'] = 0.005; 
		
		!isset($params['gs_rate_unit']) && $params['gs_rate_unit'] = 0.001; //单位工伤保险
		!isset($params['gs_rate_person']) && $params['gs_rate_person'] = 0;
		!isset($params['syu_rate_unit']) && $params['syu_rate_unit'] = 0.00; //单位生育保险
		!isset($params['syu_rate_person']) && $params['syu_rate_person'] = 0;
		
		!isset($params['gj_rate_unit']) && $params['gj_rate_unit'] = 0; //单位公积金
		!isset($params['gj_rate_person']) && $params['gj_rate_person'] = 0;
		
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
		$params['yb_fee_person'] = $sb_base * $params['yb_rate_person'];
		$params['yl_fee_person'] = $sb_base * $params['yl_rate_person'];
		$params['sy_fee_person'] = $sb_base * $params['sy_rate_person'];
		$params['gs_fee_person'] = $sb_base * $params['gs_rate_person'];
		$params['syu_fee_person'] = $sb_base * $params['syu_fee_person'];
		
		$params['gj_fee_person'] = $gj_base * $params['gj_rate_person'];
		
		$params['yb_fee_unit'] = $sb_base * $params['yb_rate_unit'];
		$params['yl_fee_unit'] = $sb_base * $params['yl_rate_unit'];
		$params['sy_fee_unit'] = $sb_base * $params['sy_rate_unit'];
		$params['gs_fee_unit'] = $sb_base * $params['gs_rate_unit'];
		$params['syu_fee_unit'] = $sb_base * $params['syu_fee_unit'];
		
		$params['gj_fee_unit'] = $gj_base * $params['gj_rate_unit'];
		
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
		$params['gj_all_unit'] = $params['gj_all_unit'];
		$params['gj_all_person'] = $params['gj_fee_person'];
		
		$gj_all = $params['gj_all_unit'] + $params['gj_all_person'];
		$params['gj_all'] = $gj_all;
	}
	
	protected function initSalary(&$params)
	{
		//基本工资
		$base_salary = $params['base_salary'];
		
		//应付基本工资 : 基本工资 - 代扣代缴
		$params['22110101'] = $base_salary - $params['sb_all_person'] - $params['tax_all'];
		
		//应付职工工资
		//$params['221101']   = $params['salary'];   //应付职工工资
		
		$total_salary = $base_salary;
		foreach ($this->_map2field as $key=>$v) {
			if (!$v['readonly']) {
				if (isset($params[$key])) {
					$total_salary += $params[$key];
				} 
			}
		}
		
		$params['221101'] = $total_salary;
		
	}
	
	
	protected function formatSalaryInput(&$params)
	{
		$feedb = isset($params['feedb'])?$params['feedb']:array();
		
		$base_salary = $params['base_salary'];
		
		$sum_salary = 0;
		$m = Factory::GetModel('fm_catalog');
		foreach ($feedb as $key=>$v) {
			if (isset($this->_map2field[$key])) {
				$mf = $this->_map2field[$key];
				if (!$mf['readonly']) {
					$sum_salary += $feedb[$key];
				}
			}
			
		}
		
		$total_salary += $base_salary ;
		$total_salary += $sum_salary;		
		$params['total_salary'] = $total_salary;
		
		
		return false;
	}
	
	
	protected function getCatalog2Salary($feedb=array())
	{
		//rlog($feedb); 
		
		$m = Factory::GetModel('fm_catalog');
		$c2sdb = $m->getCatalog2Salary();
		
		foreach ($c2sdb as $key=>&$v) {
			$id = $v['id'];
			$cno = $v['cno'];
			$isdir = $m->isDir($v);
			$readonly = $isdir || $this->isReadOnly($v);
			
			if (isset($feedb[$id])) {
				$v['fee'] = $feedb[$id];
			} else if (isset($feedb[$cno])){
				$v['fee'] = $feedb[$cno];
			} else if (isset($this->_map2field[$cno])) { //fee2map
					$fname = $this->_map2field[$cno]['map2field'];
					if (isset($feedb[$fname])) {
						$v['fee'] = $feedb[$fname];
					}
			} else {
				$v['fee'] = "0.00";
			}
					
			$v['readonly'] = $readonly?'readonly':'';
			$v['class'] = $readonly?'cno_'.$cno:'catalog2salary';
		}
		
		return $c2sdb;
	}
	
	public function getSalaryCatalogID()
	{
		$m = Factory::GetModel('fm_catalog');
		$res = $m->getOne(array('cno'=>'221101'));
		
		return $res['id'];
	}
	
	public function sumCatalogSalary($feedb)
	{
		$sum_salary = 0;
		$m = Factory::GetModel('fm_catalog');
		$c2sdb = $m->getCatalog2Salary();
		foreach ($c2sdb as $key=>$v) {
			if (isset($feedb[$v['id']])) {
				$mf = $this->_map2field[$v['cno']];
				if (!$mf['readonly']) {
					$sum_salary += $feedb[$v['id']];
				}
			}
		}
		
		return $sum_salary ;
	}
}
