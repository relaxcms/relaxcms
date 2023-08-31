<?php

require_once RPATH_SUPPORTS.DS."phpqrcode".DS."phpqrcode.php";

/**
 * @file
 *
 * @brief 
 * PHP生成二维码
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CQRCode
{
	protected $_name;
	protected $_options;
	
	public function __construct($name, $options=array())
	{
		$this->_name = $name;	
		$this->_options = $options;
	}
	
	public function CQRCode($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
		
	static function &GetInstance($name, $options=array())
	{
		static $instances;
		
		if (!isset( $instances )) {
			$instances = array();
		}
		
		$sig = serialize($name);		
		if (empty($instances[$sig])) {	
			$instance	= new CQRCode($name, $options);
			$instances[$sig] =&$instance;
		}
		
		return $instances[$sig];
	}
		
	public function QR($value, $qrfile='qr.png')
	{
		//容错级别分别是 L（QR_ECLEVEL_L，7%），M（QR_ECLEVEL_M，15%），Q（QR_ECLEVEL_Q，25%），H（QR_ECLEVEL_H，30%）；
		$errorCorrectionLevel = isset($this->_options['errorCorrectionLevel']) ? $this->_options['errorCorrectionLevel']:'H';
		//生成图片大小,默认为3，
		$matrixPointSize = isset($this->_options['matrixPointSize']) ? $this->_options['matrixPointSize'] : 6;
		
		QRcode::png($value, $qrfile, $errorCorrectionLevel, $matrixPointSize, 2);
	}


	public function qrData($value)
	{
		$qrfile = RPATH_CACHE.DS.randName().time().".png";

		//容错级别分别是 L（QR_ECLEVEL_L，7%），M（QR_ECLEVEL_M，15%），Q（QR_ECLEVEL_Q，25%），H（QR_ECLEVEL_H，30%）；
		$errorCorrectionLevel = isset($this->_options['errorCorrectionLevel']) ? $this->_options['errorCorrectionLevel']:'H';
		//生成图片大小,默认为3，
		$matrixPointSize = isset($this->_options['matrixPointSize']) ? $this->_options['matrixPointSize'] : 6;
		
		$res = QRcode::png($value, $qrfile, $errorCorrectionLevel, $matrixPointSize, 2);

		$data = s_read($qrfile);

		$data64 = 'data:image/png;base64,'.base64_encode($data);

		@unlink($qrfile);

		return $data64;
	}
}