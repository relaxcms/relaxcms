<?php

defined('RPATH_BASE') or die();
class ContentModel extends CContentModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function ContentModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}
}