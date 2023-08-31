<?php

/**
 * @file
 *
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SystemAttachComponent extends CAttachComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);		
	}
	
	function SystemAttachComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
}