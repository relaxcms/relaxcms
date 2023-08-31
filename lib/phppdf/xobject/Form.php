<?php

require_once(RPATH_PHPPDF.DS.'Page.php');

/**
 * Class Form
 */
class Form extends Page
{
    public function getText($page = null)
    {
		$header = new Header(array(), $this->_pdf);
		$contents = new PDFObject($this->_pdf, $header, $this->_content, $this->_config);

        return $contents->getText($this);
    }
}
