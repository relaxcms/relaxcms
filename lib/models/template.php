<?php
/**
 * @file
 *
 * @brief 
 * 
 * template model
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class TemplateModel extends CTemplateModel
{
	public function __construct($name, $options=array())
	{		
		parent::__construct($name, $options);
	}
		
	public function TemplateModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	
}