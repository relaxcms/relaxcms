<?php

/***
 * 
 * @file
 *
 * @brief 
 *  
 * */

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CSalaryDTComponent extends CDTComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function CSalaryDTComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function initTaxCfg()
	{
		$m = Factory::GetModel('fm_params');
		$cfgdb = $m->getParams();
		
		$taxcfg  = array();
		for ($i=1; $i<=7; $i++) {
			$level = floatval($cfgdb['tax_level'.$i]);
			$rate = floatval($cfgdb['tax_level'.$i.'_rate']);
			$sub = floatval($cfgdb['tax_level'.$i.'_sub']);
			
			$taxcfg[] = array('level'=>$level, 'rate'=>$rate, 'sub'=>$sub);				
			
		}
		
		$_taxcfg = CJson::encode($taxcfg);
		$this->assign('taxcfg', $_taxcfg);
		
	}
	
	
	//树型目录
	protected function treeCatalog($id, $udb)
	{
		$_cdb = array();
		
		foreach ($udb as $key=>$v)
		{
			if ($v['pid'] != $id) 
				continue;
			
			$_cdb[$key] = $v;
		}
		
		foreach ($_cdb as $key=>&$v) {			
			$v['children'] = $this->treeCatalog($v['id'], $udb);
		}	
		
		return $_cdb;		
	}
	
	protected function treeCatalogUL($id, $udb)
	{
		$ul = "<ul>";
		foreach ($udb as $key=>$v)
		{
			$pid = $v['pid'];
			$haschild = $v['children']?true:false;
			
			$i = $haschild?"<i class='fa fa-plus-square-o btnPlus' data-id='$v[id]'></i>":"<i class='fa fa-a'></i>";
			
			$ddisplay = ($id == $pid)?'' : 'hidden';
			
			$ul .= "<li class='$ddisplay node$pid'>";
			$ul .="<div class='row'>
					            <div class='col-sm-12' id='Div1'>    					    
					                <div class='form-group'>
					                    <label class='control-label col-md-3'>$v[name] 										
										</label>
					<div class='col-md-9'>  <div class='input-group form-group'>
					<span class='input-group-btn'>
						<span class='btn btn-xs'>$i</span>
					</span>
					
					 <input type='text' name='params[feedb][$v[cno]]' value='$v[fee]' class='form-control $v[class]' id='cno_$v[cno]' data-type='$v[type]' data-fee='$v[fee]' $v[readonly]  />
					                    </div> </div>
					                </div>
					            </div>					        					            				    
					        </div>";
							
			if ($haschild) {
				$ul .= $this->treeCatalogUL($id, $v['children']);
			}
			
			$ul .= "</li>";
			
		}
		
		$ul .= "</ul>";		
		
		return $ul;
	}
	
	protected function makeTreeCatalog($udb)
	{
		//查询当前
		$id = 0;
		foreach($udb as $key=>$v) {
			$id = $v['pid'];
			break;
		}
		
		$feetreedb = $this->treeCatalog($id, $udb);
		$ul = $this->treeCatalogUL($id, $feetreedb);
		
		//var_dump($ul);exit;
		$this->assign('feedbul', $ul);
		
		return $feetreedb;
	}
	
		
	protected function edit(&$ioparams=array())
	{
		$this->initTaxCfg();
		
		$res = parent::edit($ioparams);
		
		$feedb = $ioparams['feedb'];
		
		//$this->assign('feedb', $feedb);
		
		$feetreedb = $this->makeTreeCatalog($feedb);
		
		//var_dump($feetreedb);exit;
		
		//$this->assign('feedb', $feetreedb);
		$this->assign('feetreedb', $feetreedb);
		
		$this->setTpl('hr_salary_edit');
		
		return $res;
	}
	
	protected function detail(&$ioparams=array())
	{
		$res = parent::detail($ioparams);
		
		$id = $this->_id;
		$m = $this->getModel();
		$info = $m->get($id);
		
		$this->assign('feedb', $info['feedb']);
		
		$this->setTpl('hr_salary_detail');		
	}
}