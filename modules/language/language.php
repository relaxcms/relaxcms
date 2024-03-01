<?php
/**
 * @file
 *
 * @brief 
 * 语言栏模块
 *
 */
class LanguageModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function LanguageModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$ioparams=array())
	{
		$i18ns = Factory::GetI18ns();
		$ldb  = array();
		if (isset($i18ns['sel_language']))
			$ldb = $i18ns['sel_language'];

		$cf = get_config();
		$langname = $cf['lang'];
		if (!$langname)
			$langname = 'zh_CN';
		
		$languages = array();
		foreach ($ldb as $key => $value) {
			$active = $key == $cf['lang']; 
			$languages[$key] = array('name' =>$key, 'title'=>$value, 'active'=>$active);
		}

		if (isset($ldb[$langname]))
			$current_language = $ldb[$langname];
		else 
			$current_language = '';

		$this->assign('current_language', $current_language);
		$this->assign("languages", $languages);
		return true;
	}	
}