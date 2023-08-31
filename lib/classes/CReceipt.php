<?php

class CReceipt extends CObject
{
	protected $_name;
	protected $_options;
	
	protected $_title2name = array(
		'开票日期' => 'date',
		'机器编号' => 'mno',
		'收款人'=>'pay',
		'复核'=>'check',
		'开票人'=>'opt',
		'发票号码'=>'rno',
		'发票代码'=>'rcode',
		'价税合计大写'=>'amount',
		'名称'=>'cname',
		'纳税人识别号'=>'cid',
		'开户行及账号'=>'cacount',
		'合计金额'=>'total_amount',
		'合计税额'=>'total_tfee',
		'价税合计大写'=>'all_amount_text',
		'小写'=>'all_amount',
		);
	
	function __construct($name, $options=array())
	{
		$this->_name = $name;
		$this->_options = $options;
	}
	
	function CReceipt($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	static function &GetInstance( $name, $options=array())
	{
		static $instance;
		if (!is_object($instance)) {
			$instance = new CReceipt($name, $options);
		}
		return $instance;
	}

	
	/*
	
	[126]=>
	 array(3) {
	   [62]=>
	   string(13) "合计"
	   [439]=>
	   string(7) "￥87.59"
	   [551]=>
	   string(7) "￥11.39"
	 }
	
	
	[126]=>
  array(6) {
    [74]=>
    string(3) "合"
    [110]=>
    string(3) "计"
    [431]=>
    string(3) "￥"
    [440]=>
    string(7) "3356.48"
    [559]=>
    string(3) "￥"
    [568]=>
    string(5) "52.83"
  }*/
	protected function parseReceiptAmount($rows, $i, $nr, &$rinfo)
	{
		//金额
		$amount = 0;
		//税额
		$taxfee = 0;
		
		for(; $i<$nr; $i++) {
			$val = $rows[$i];
			
			if ($val == '￥' || !is_numeric($val))
				$val = substr($val, 2);
				
			//rlog(RC_LOG_DEBUG,__FILE__, __LINE__, $val);							
			if (is_numeric($val)) {
				$val = floatval($val);
				if ($amount == 0)
					$amount = $val;
				else if ($taxfee == 0)
					$taxfee = $val;
			}
		}
		$rinfo['total_amount'] = $amount; //合计金额
		$rinfo['total_tfee'] = $taxfee; //合计税额
		$rinfo['total_trate'] = $amount > 0 ? round($taxfee/$amount, 2):0;//计算税率
		
	}
	
	/*
	[224]=>
  array(18) {
    [25]=>
    string(1) "*"
    [29]=>
    string(12) "运输服务"
    [42]=>
    string(36) "货物或应税劳务、服务名称"
    [65]=>
    string(1) "*"
    [68]=>
    string(15) "客运服务费"
    [190]=>
    string(12) "规格型号"
    [252]=>
    string(6) "单位"
    [256]=>
    string(3) "次"
    [293]=>
    string(9) "数　量"
    [326]=>
    string(1) "1"
    [351]=>
    string(9) "单　价"
    [356]=>
    string(7) "1764.36"
    [416]=>
    string(9) "金　额"
    [439]=>
    string(7) "1764.36"
    [483]=>
    string(6) "税率"
    [486]=>
    string(6) "免税"
    [532]=>
    string(9) "税　额"
    [562]=>
    string(9) "＊＊＊"
  }
  [210]=>
  array(7) {
    [25]=>
    string(1) "*"
    [29]=>
    string(12) "运输服务"
    [65]=>
    string(1) "*"
    [68]=>
    string(15) "客运服务费"
    [441]=>
    string(7) "-168.94"
    [486]=>
    string(6) "免税"
    [562]=>
    string(9) "＊＊＊"
  }
  [196]=>
  array(10) {
    [25]=>
    string(1) "*"
    [29]=>
    string(12) "运输服务"
    [65]=>
    string(1) "*"
    [68]=>
    string(15) "客运服务费"
    [256]=>
    string(3) "次"
    [326]=>
    string(1) "1"
    [356]=>
    string(7) "2003.78"
    [439]=>
    string(7) "2003.78"
    [491]=>
    string(2) "3%"
    [567]=>
    string(5) "60.11"
  }
*/

	protected function parseReceiptFeeList($rows, &$rinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN parseReceiptFeeList", $rows);
		
		//货物或应税劳务、服务名称 规格型号 单位 数量 单价 金额 税率 税额
		if (!isset($rinfo['list'])) {
			$rinfo['list'] = array();
		}
		
		
		$fnames = array('name', 'model', 'unit', 'num', 'price', 'amount', 'trate', 'tfee');
		$ftitles = array('货物或应税劳务、服务名称', '规格型号', '单位', '数量', '单价', '金额', '税率', '税额');
		
		if (!isset($rinfo['list']['fdb'])) {
			
			//字段行
			$fdb = array();
			$nr_field = count($ftitles);
			$vdb = array();
			
			foreach ($rows as $x=>$v) {
				$text = str_replace(array(' ','　',"\n","\r",":"),array('','','',''), $v);	
				for($i=0; $i<$nr_field; $i++) {
					if ($text == $ftitles[$i]) { //字段名
						break;
					}	
				}
				if ($i< $nr_field) { //字段名
					$name = $fnames[$i];
					$fdb[$name] = array('name'=>$name, 'title'=>$ftitles[$i], 'x'=>$x);
				} else {
					$vdb[$x] = $v;
				}			
				
			}			
			$rinfo['list']['fdb'] = $fdb;	
			$rows = $vdb;		
		}
		
		$_rows = array();
		$_xs = array();
		foreach ($rows as $key=>$v) {
			$v = trim($v);
			if (!$v)
				continue;
			$_rows[] = $v;
			$_xs[] = $key;
		}
		
		$nr = count($_rows);
		if ($nr < 4) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid _rows", $_rows);
			return false;
		}
		$fdb = $rinfo['list']['fdb'];
				
		//'金额', '税率', '税额'
		$item = array();
		
		$name = 'tfee';//'税额';
		$index = $nr-1;
		if (is_numeric($_rows[$index])) {
			$item[$name] = $_rows[$index];
		} else {
			$item[$name] = 0;
		}
		
		$name = 'trate';//'税率';
		$index --;
		if (strrchr($_rows[$index], '%') !== false) {
			$item[$name] = floatval(str_replace('%', '', $_rows[$index]))/100.0;
		} else {
			$item[$name] = 0;
		}
		
		
		$name = 'amount'; //'金额';
		$index --;
		if (!is_numeric($_rows[$index])) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid item!", $_rows);
			return false;
		} else {
			$item[$name] = $_rows[$index];
		}
		
		
		//'名称', '规格型号', '单位', '数量', '单价'
		$item['name'] = $_rows[0];
		
		for ($i=$index-1; $i>0; $i--) {
			$val = $_rows[$i];
			$x = $_xs[$i];
			
			for ($j=5+$i-$index; $j>0; $j--){
				$k1 = $fnames[$j];
				$k2 = $fnames[$j-1];
				
				$x1 = $fdb[$k1]['x'];
				$x2 = $fdb[$k2]['x'] + strlen($fdb[$k2]['title'])*14;
				
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,  "x1=$x1, x2=$x2, key=$k1, k2=$k2");
				
				if ($x >= $fdb[$key]['x'] || ($x > $x2 && strlen($val) > strlen($fdb[$k1]['title']))) {
					$item[$k1] = $val;
					break;
				}
			}
		}
		
		/*
		
		
		$fdb = $rinfo['list']['fdb'];
		array_sort_by_field($fdb, 'x', true);
		
		
		foreach ($rows as $x=>$v) {
			//name
			$found = false;
			foreach ($fdb as $k2=>$v2) {
				if ($v2['x'] < $x) {
					$val = $v;
					$name = $k2;
					
					$item[$name] = $val;
					$found = true;
					break;
				} else {
					$p_v = $k2;
				}
			}
			
			if (!$found) { //未找到
				$item['名称'] = $v.$item['名称'];
			}
		}
		
		if (!is_numeric($item['金额'])) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid item!", $item);
			return false;
		}*/

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,  $item);
		
		
		if (!isset($rinfo['list']['rows'])) { 
			$rinfo['list']['rows'] = array();
		}
		$rinfo['list']['rows'][] = $item;		
	}
	/*
		
		//发票代码
		//开票日期
		//校 验 码
		//价税合计(大写)
		//价税合计(小写)
		
		合       计
		价税合计(大写)(小写)
		销名        称:
		纳税人识别号:
		地 址、电 话:
		开户行及账号:
		收款人:复核:
		开票人:
		销售方:(章)
		备注
		
		发票代码:    
		发票号码:  
		开票日期:  年  月  日  
		校 验 码:
		购  名        称:  
		密  
		纳税人识别号:买  码  
		地 址、电 话:方  
		开户行及账号:  区  
		货物或应税劳务、服务名称      规格型号   单位   数 量     单 价  金  额    税率          税  额
		机器编号:
	*/
	
	
	public function getReceiptInfo($receiptfile)
	{
		$rinfo = array();
		
		$pdf = Factory::GetPDF($receiptfile, $this->_options);
		
		$pages = $pdf->getPages();
		$page = $pages[0];
		$tm = $page->getDataTm();
		//var_dump($tm);
						
		$tdb = array();
		foreach ($tm as $key=>$v) {
			$y = abs(floor($v[0]['5']));
			$y = $y-$y%14;			
			
			$x = abs(ceil($v[0]['4']));
			if (!isset($tdb[$y])) {
				$tdb[$y] = array();
			}
			$tdb[$y][$x] = $v[1];
		}
		
		foreach ($tdb as $key=>&$v1) {
			ksort($v1);			
		}
		
		ksort($tdb);
		
		//检查坐标
		$y0 = 0;
		$y1 = 0;		
		$y2 = 0;
		foreach ($tdb as $key=>$v) {
			if ($y0 == 0)
				$y0 = $key;
			if (in_array('发票代码:', $v))	{
				$y1 = $key;
			}			
			$y2 = $key;			
		}		
		$d0 = $y1 - $y0;
		$d1 = $y2 - $y1;
		
		if ($d0 > $d1)//倒过来
			krsort($tdb);
		$ls_y1 = 0;
		$ls_y2 = 0;
		
		foreach ($tdb as $key=>$v) {
			
			$rows = array();
			foreach ($v as $k2=>$v2) 
				$rows[] = $v2;
			$nr = count($rows);
			
			for($i=0; $i<$nr; $i++) {
				$text = str_replace(array(' ','　',"\n","\r",":",'(',')','（','）'),array('','','','','','','',''), $rows[$i]);	
				if (strstr($text, '增值税电子')) {//上海增值税电子普通发票
					$rinfo['name'] = $text;
					if (strstr($text, "普通发票")) {
						$rinfo['type'] = '普票';
					} else {
						$rinfo['type'] = '专票';
					}
				} 
				
				switch ($text) {
					//开票日期
					case '开票日期':											
					//'收款人','复核','开票人'
					case "机器编号":
					case "收款人":
					case "复核":
					case "开票人":
					case "发票号码":
					case "发票代码":
					case "价税合计大写":					
						$name = $text; 
						if (isset($this->_title2name[$name]))
							$name = $this->_title2name[$name]; 
							
						$val = $rows[++$i];
						$rinfo[$name] = $val;	
											
						break;			
					case "名称": //购方与销方
					case "纳税人识别号":
					case "开户行及账号":
						$name = $text; 
						if (isset($this->_title2name[$name]))
							$name = $this->_title2name[$name]; 
						
						if (isset($rinfo[$name])) 
							$name .= '2';
						
						$rinfo[$name] = $rows[++$i];;
						break;
					case '合'://合计
					case '合计'://合计
						$this->parseReceiptAmount($rows, $i++, $nr, $rinfo);
						$ls_y2 = $key;
						break;
					
					case '小写'://价税合计
						/* string(12) "（小写）"
						[501]=>
						string(3) "￥"
						[510]=>
						string(7) "3409.31"*/
						$name = 'all_amount'; //价税合计
						$val = $rows[++$i];
						if ($val == "￥")
							$val = $rows[++$i];
						//￥123.12
						if (!is_numeric($val))
							$val = substr($val,1);
						if (!is_numeric($val))
							$val = substr($val,1);
						
						$rinfo[$name] = $val;		
						break;
						
					case '货物或应税劳务、服务名称':
						$this->parseReceiptFeeList($v , $rinfo);	
						$ls_y1 = $key;						
						break;
					default:
						break;
				}
			}
		}
		
		//list
		if ($ls_y1 > $ls_y2) {
			$y = $ls_y1;
			$ls_y1 = $ls_y2;
			$ls_y2 = $y;
		}
		foreach ($tdb as $key=>$v) {
			if ($key > $ls_y1 && $key < $ls_y2)
				$this->parseReceiptFeeList($v , $rinfo);	
		}
		//var_dump($tdb);
		
		//date
		if ($rinfo['date']) { //2022   10   12
			$date = $rinfo['date'];
			$year = substr($date, 0, 4);
			$md = trim(substr($date, 5));
			$month = substr($md, 0, 2);
			$day = trim(substr($md, 3));
			
			$rinfo['date'] = $year.'-'.$month.'-'.$day;
		}			
				
		unset($pdf);		
		return $rinfo;
	}
	
	
	public function parseImg2text($data)
	{
		//rlog($data);
		
		$rinfo = array();
		
		$rows = explode("\n", $data);
		$nr = count($rows);
		
		for($i=0; $i<$nr; $i++) {
			$val = str_replace(array(' ','　',"\n","\r",":",'(',')','（','）'),
					array('','','','','','','',''), $rows[$i]);	
			
			//type
			if (strstr($val, '普通发票')) {
				$rinfo['type'] = '普票';
			} else if(strstr($val, '专用发票')) {
					$rinfo['type'] = '专票';
				}
			
			//开票日期:2022年06月10日
			if (strstr($val, '开票日期')) { //
				//开票日期:2022年06月10日			
				//开票日期购买方名称2022-01-22913401003943381183收款员岳洋岗
				$date = '';
				$pattern = "/(\d{1,4})(\-|年)(\d{1,2})(\-|月)(\d{2})/i";
				if (preg_match($pattern, $val, $matches)) {
					$date = $matches[1].'-'.$matches[3].'-'.$matches[5];
				}
				$rinfo['date'] = $date;		
			}
			
			//货物或应税劳务、服务名称
			if (($liststr=strstr($val, '货物或应税劳务、服务名称')) || ($liststr=strstr($val, '项目'))) { //name 开始标记
				$list_begin = ++$i;
			}
			
			if (($amountstr = strstr($val, '小写')) ||($amountstr=strstr($val, '大写'))) { //name 开始标记
				$list_end = $i;
				$amount = '';
				$pattern = "/￥(\d+\.\d{2})/i";			
				if (preg_match($pattern, $amountstr, $matches)) {
					$amount = $matches[1];
				}
				if ($amount)
					$rinfo['amount'] = $amount;
			}
		}
		
		for ($i=$list_begin; $i<$list_end; $i++) {
			$name = trim($rows[$i]);
			if (($pos = strpos($name, ' ')) !== false) {
				$name = substr($name, 0, $pos);			
			}
			$rinfo['name'] = $name;
			break;		
		}
		
		if (!$rinfo['name']) {
			if (($liststr=strstr($data, '货物或应税劳务、服务名称')) || ($liststr=strstr($data, '项目'))) { //name 开始标记
				
				$pos = strpos($liststr, '*');
				$str1 = substr($liststr, 0, $pos);
				$str2 = substr($liststr, $pos);
				
				$name1 = strrchr($str1, ' ');
				$pos = strpos($str2, ' ');
				$name2 = substr($str2, 0, $pos); 
				
				//$name
				$name = $name1.$name2;
				$rinfo['name'] = $name;
			}
		}
		return $rinfo;
	}
}

?>