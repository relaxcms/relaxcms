<?php

defined('RPATH_BASE') or die();
class CContent2moduleModel extends CTableModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CContent2moduleModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	public function trigger($event, $args=array())
	{
		$res = false;

		$cinfo = $args;
		
		$content_id = $cinfo['id'];
		$cid = $cinfo['cid'];
		
		$status = $cinfo['status'];
		$flags = $cinfo['flags'];
		$hits = $cinfo['hits'];
		
		$m = Factory::GetModel('module_params');
		$tdb = $m->select();
		foreach ($tdb as $key=>$v) {
			$id = $v['id'];
			$mid = $v['mid'];
			$max = intval($v['maxnum']);
			$num = intval($v['num']);
			
			$params = array();
			$params['cid'] = $content_id;//content_id
			$params['mid'] = $mid;	
			$old = $this->getOne($params);
			if ($old && $num > 0) {
				$res = $this->delete($params);
				$num --;
				$_params = array();
				$_params['id'] = $id;
				$_params['num'] = $num;			
				$res = $m->update($_params);	
			}
			
			if ($v['cid'] > 0 && $v['cid'] != $cid) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid cid '$cid' expected '$v[cid]'!");
				continue;
			} 
			
			$_flags = intval($v['flags']);
			if ($_flags == 0) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid param_flags=$_flags!");
				continue;
			}
						
			if (($_flags & $flags ) != $_flags) { //最低要求flags	
				$delta_flags = ($_flags & $flags );	
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "expected param_flags=$param_flags, flags=$flags, delta_flags=$delta_flags");									
				continue;
			}
			
			
			//tag
			$tags = trim($v['tags']);
			if ($tags) {
				$pattern = str_replace(',', '|', $tags);
				$pattern = str_replace(' ', '|', $pattern);
				$pattern = "#$pattern#i"; //tag1|tag2|tag3
				$content = $cinfo['name'].' '.$cinfo['summary'].' '.$cinfo['content'];
				$res = preg_match($pattern, $content);
				if (!$res) {//no match
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tags=$tags");				
					continue;
				}
			}
			
			//hists
			/*$min_hits = $v['min_hits'];
			if ($min_hits > 0 && $min_hits < $hits) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "less hits=$hits");	
				continue;
			}*/
			//入队
			$res = $this->set($params);
			if (!$res) {//no match
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "set site content2module failed!", $params);				
				continue;
			}
			
			if ($res)
				$num ++;
				
			$params = array();
			$params['id'] = $id;
			$params['num'] = $num;			
			$res = $m->update($params);	
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
			if (!$res) {//no match
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "update site tag failed!", $params);				
				continue;
			}
		}
		return $res;
	}
	
}