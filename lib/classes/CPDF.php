<?php
/**
 * @file
 *
 * @brief 
 * PDF
 *
 * Copyright (c), 2023, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


define ('RC_PDF_CREATOR', 'RC');
define ('RC_PDF_HEADER_LOGO', RPATH_STATIC.DS.'img'.DS.'no.png');
define ('RC_PDF_HEADER_LOGO_WIDTH', 10);
define ('RC_PDF_HEADER_TITLE', ' ');
define ('RC_PDF_HEADER_STRING', '');

include_once(RPATH_SUPPORTS.DS.'tcpdf'.DS.'config'.DS.'tcpdf_config.php');
include_once(RPATH_SUPPORTS.DS.'tcpdf'.DS.'tcpdf.php');


class CPDF
{
	protected $_name;
	protected $_options;
	protected $_pdf;

	public function __construct($name, $options=array())
	{
		$this->_name = $name;
		$this->_options = $options;

		$this->_init();
	}
	
	public function CPDF($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	static function &GetInstance($name='_', $options=array())
	{
		static $instances;
		
		if (!isset( $instances )) {
			$instances = array();
		}
		
		$sig = serialize($name);		
		if (empty($instances[$sig])) {	
			$instance	= new CPDF($name, $options);
			$instances[$sig] =&$instance;
		}
		
		return $instances[$sig]->getPDF();
	}


	protected function _init()
	{
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// set document information
		$pdf->SetCreator(RC_PDF_CREATOR);
		$pdf->SetAuthor(RC_PDF_CREATOR);
		$pdf->SetTitle(RC_PDF_HEADER_TITLE);
		$pdf->SetSubject('RC pdf document');
		$pdf->SetKeywords('RC');
		
		// set default header data
		$pdf->SetHeaderData(RC_PDF_HEADER_LOGO, 
				RC_PDF_HEADER_LOGO_WIDTH, RC_PDF_HEADER_TITLE, RC_PDF_HEADER_STRING);
		$pdf->setFooterData(array(0,64,0), array(0,64,128));
		
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		
		// set default font subsetting mode
		$pdf->setFontSubsetting(true);
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('stsongstdlight', '', 14, '', true);
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();
		
		// set text shadow effect
		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 
					'color'=>array(196,196,196),
					'opacity'=>1, 'blend_mode'=>'Normal'));
		
		
		
		$this->_pdf = $pdf;
	}

	public function getPDF()
	{
		return $this->_pdf;
	}

}