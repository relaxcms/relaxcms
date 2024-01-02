<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CCaptchaComponent extends CUIComponent
{
	function __construct($name, $option)
	{
		parent::__construct($name, $option);
		if (!extension_loaded('gd') || !function_exists('gd_info')) {
			return false;
		}
	}
	
	function CCaptchaComponent($name, $option)
	{
		$this->__construct($name, $option);
	}
	
	
	/* Show Captcha Image */
	protected function showCaptchaImage($char_number=5, $font_size=12, $width = 88, $height = 23)
	{
		$this->genSecCodeImage(true); //显示
		
		exit;
	}	
	
	public function show(&$ioparams=array())
	{
		$this->showCaptchaImage();
		exit;
	}
}
