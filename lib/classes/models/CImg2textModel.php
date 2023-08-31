<?php
/**
 * @file
 *
 * @brief 
 * 
 * 流水明细
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CImg2textModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CImg2textModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	protected function formatReceiptInfo(&$rinfo)
	{
		$name = $rinfo['name'];
				
		//*电子计算机*电脑
		$tid = 0;
		
		$m2 = Factory::GetModel('am_atype');
		$udb = $m2->gets();
		foreach ($udb as $key=>$v) {
			//正则遍历
			$pattern = isset($v['pattern'])?$v['pattern']:$v['name'];
			if (!$pattern) {
				continue;
			}
			$pattern = "#$pattern#i"; //财智账户卡|到账伴吕|对公收费明细入帐|手续费
			
			$content = $name;
			$res = preg_match($pattern, $content);
			if ($res) {
				$tid = $v['id'];
				break;
			} 			
		}
		
		//分类
		$rinfo['tid'] = $tid;
		//发票类型
		$rinfo['stype'] = $data['type'] == '普票'?1:2;
	}
	
	public function getReceiptInfo($ids)
	{
		if (!$ids)
			return false;
		$iddb = explode(',', $ids);
		if (!$iddb)
			return false;
		
		$m = Factory::GetModel('file');
		$rinfo = array();
		foreach ($iddb as $key=>$v) {
			$rinfo = $m->getReceiptInfo($v);
			if ($rinfo)
				break;
		}
				
		if (!$rinfo)
			return false;
			
			
		//check tid
		$rows = $rinfo['list']['rows'];
		$name = $rows[0]['name'];
		$rinfo['name'] = $data['name'].$name;
		
		$this->formatReceiptInfo($rinfo);
		
		return $rinfo;
		
	}
		
	public function parseImg2text($content)
	{
	
		$m = Factory::GetReceipt();
		$rinfo = $m->parseImg2text($content);
		
		$this->formatReceiptInfo($rinfo);
				
		return $rinfo;	
	}
}
