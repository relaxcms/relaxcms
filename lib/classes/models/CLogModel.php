<?php

/**
 * @file
 *
 * @brief 
 * 
 * 日志模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CLogModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function CLogModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
			case 'type':
			case 'subsys':
			case 'oid':
			case 'cmd':
				$f['show'] = false;
				break;
			case 'mid':
			case 'nickname':
			case 'username':
			case 'newobj':
				$f['searchable'] = true;			
			case 'oldobj':
				$f['show'] = false;
				break;
			case 'level':
			case 'status':
				$f['input_type'] = 'selector';
				break;
			case 'uid':
				$f['input_type'] = 'UID';
				$f['searchable'] = 'true';
				break;
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				break;
			default:
				break;
		}

		return true;
	}


	//上下文菜单
	protected function formatOperate($row, &$ioparams=array())
	{
		//$id = $row[$this->_pkey];
		
		//$defOpt = parent::formatOperate($row, $ioparams);
		
		$optdb[] = $this->_default_actions['detail'];
		
		return $optdb;
		
		//return "<button type='button' class='btn green btn-xs btn-circle tlink tooltips detail' data-original-title='详细' title='详细' data-id='$id' data-task='detail'>
		//		<i class='fa fa-file-text-o ' ></i></button>";
	}


	public function formatForView(&$row, &$ioparams = array())
	{
		$res =  parent::formatForView($row, $ioparams);
		
		$row['_status'] = $this->formatLabelColorForView($row['status']==0?3:1, $row['_status']);

		$level_color = 0;

		switch ($row['level']) {
			case '0':
			case '1':
			case '2':
			case '3':
			$level_color = 3;
				break;
			case '4':
				$level_color = 2;
				break;
			case '5':
				$level_color = 4;
				break;
			case '6':
			case '7':
				$level_color = 1;
				break;
			
			default:
				# code...
				break;
		}

		$row['_level'] = $this->formatLabelColorForView($level_color, $row['_level']);	
		
				
		$row['_newobj'] = $row['newobj']?CJson::encode(unserialize($row['newobj'])):'';//"<span title='".$row['newobj']."'>".cutstr($row['newobj'], 100)."</span>":'';		
		$row['_oldobj'] = $row['oldobj']?CJson::encode(unserialize($row['oldobj'])):'';//"<span title='".$row['oldobj']."'>".cutstr($row['oldobj'], 100)."</span>":'';		
	}
	
	
	protected function writeLog($level, $action, $status, $oldParams=array(), $newParams=array(), $mid=0)
	{
		return false;
	}
	
}