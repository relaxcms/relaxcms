<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class CLicenseConfig extends CConfig
{
	//构造
	public function __construct($name, $options= array())
	{
		parent::__construct($name, $options);
	}	

	function CLicenseConfig($name, $options= array()) 
	{
		$this->__construct($name, $options);
	}
	
	public function load($reload=false)
	{
		$cfg = parent::load($reload);
		
		$status = isset($cfg['status'])?$cfg['status']: -1;
		$statusName = isset($cfg['statusName'])?$cfg['statusName']: "未激活";
		
		if ($status == 1) {
			$statusName = '<span class="greenf">'.$statusName.'</span>';
		} else {
			$statusName = '<span class="redf">'.$statusName.'</span>';			
		}
				
		//过期时间
		$ts = time();
		!isset($cfg['expired']) &&  $cfg['expired'] = $ts;
		
		$delta = $cfg['expired'] - $ts;
		if ($delta <= 0)
			$expiredTag = '<span class="redf">已过期</span>';
		elseif  ($delta <= RC_TIMESEC_MONTH) //不足1个月
			$expiredTag = '<span class="yellowf">即将过期</span>';
			
		$cfg['statusName'] = $statusName;
		$cfg['expiredName'] = tformat_cstdate($cfg['expired']) . ' '. $expiredTag;
		
		return $cfg;	
	}
	
}
