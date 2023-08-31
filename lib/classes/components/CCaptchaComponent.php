<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CCaptchaComponent extends CComponent
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
	

	private function generate_string()
	{
		return "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
	}
	
	/* Show Captcha Image */
	protected function showCaptchaImage($char_number=5, $font_size=12, $width = 88, $height = 23)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		//fonts = array("AntykwaBold", "Duality", "Jura", "StayPuft");
		$fonts = array("Duality", "Jura");
		$fontname = $fonts[array_rand($fonts)];
		$tt_font = RPATH_SUPPORTS.DS."fonts".DS.$fontname.".ttf";

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'ttfont='.$tt_font);
		
		$chars_number = rand(4, $char_number);		
		$string = $this->generate_string();
		
		//$im = imagecreate($width, $height);
		$im = imagecreatetruecolor($width, $height);
		if (!$im){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecreatetruecolor failed!w=".$width.',h='.$height);
		}
		
		/* Set a White & Transparent Background Color */
		$bgcolor = imagecolorallocatealpha($im, 255, 255, 255, 0); // (PHP 4 >= 4.3.2, PHP 5)
		if (!$bgcolor){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagecolorallocatealpha failed!w=".$width.',h='.$height);
		}
		//填充
		imagefill($im, 0, 0, $bgcolor);
		
		$capt = "";
		for ($i=0; $i<$chars_number; $i++)
		{
			$char = $string[rand(0, strlen($string)-1)];
			$capt .= $char;
			$factor = 14;
			$x = ($factor * ($i + 1)) - 6;
			$y = rand(15, 17);
			$angle = rand(1, 15);
			$textcolor = imagecolorallocate($im, mt_rand(0,120), mt_rand(0,120), mt_rand(0,120));
			 
			//imagettftext — 用 TrueType 字体向图像写入文本
			$res = imagettftext($im, $font_size, $angle, $x, $y, $textcolor, $tt_font, $char);
			if (!$res){
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagettftext failed!, $tt_font,char=$char,font_size=$font_size, angle=$angle, x=$x, y=$y");
			}
		}
		
		for ($i=0; $i<150; $i++){
			$fontcolor = imagecolorallocate($im,mt_rand(180,255),mt_rand(180,255),mt_rand(180,255));
			$res = imagesetpixel($im, mt_rand(0,$width), mt_rand(0,$height), $fontcolor);
			if (!$res){
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call imagettftext failed!");
			}
		}
		
		header('Cache-control: private'); // IE 6 FIX
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT'); 
		header('Cache-Control: no-store, no-cache, must-revalidate'); 
		header('Cache-Control: post-check=0, pre-check=0', false); 
		header('Pragma: no-cache');
		
		$_SESSION['seccode'] = strtolower($capt); 
		
		/* Output the verification image */
		header("Content-type: image/png");
		$res = imagepng($im);
		imagedestroy($im);
		
		if (!$res){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call ImageColorAllocateAlpha failed!");
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		exit;
	}	
	
	public function show(&$ioparams=array())
	{
		$this->showCaptchaImage();
		exit;
	}
}
