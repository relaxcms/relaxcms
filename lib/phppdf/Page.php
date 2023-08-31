<?php


class Page extends PDFObject
{
    /**
     * @var Font[]
     */
    protected $_fonts = null;

    /**
     * @var PDFObject[]
     */
    protected $_xobjects = null;

    /**
     * @var array
     */
    protected $_dataTm = null;

    /**
     * @return Font[]
     */
    public function getFonts()
    {
        if (null !== $this->_fonts) {
            return $this->_fonts;
        }

        $resources = $this->get('Resources');

        if (method_exists($resources, 'has') && $resources->has('Font')) {
            if ($resources->get('Font') instanceof ElementMissing) {
                return array();
            }

            if ($resources->get('Font') instanceof Header) {
                $fonts = $resources->get('Font')->getElements();
            } else {
                $fonts = $resources->get('Font')->getHeader()->getElements();
            }

            $table = array();

            foreach ($fonts as $id => $font) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "id=$id");
                if ($font instanceof Font) {
                    $table[$id] = $font;

                    // Store too on cleaned id value (only numeric)
                    $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                    if ('' != $id) {
                        $table[$id] = $font;
                    }
                }
            }

            return $this->_fonts = $table;
        }

        return array();
    }

    public function getFont( $id)
    {
        $fonts = $this->getFonts();

        if (isset($fonts[$id])) {
            return $fonts[$id];
        }

        // According to the PDF specs (https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDF32000_2008.pdf, page 238)
        // "The font resource name presented to the Tf operator is arbitrary, as are the names for all kinds of resources"
        // Instead, we search for the unfiltered name first and then do this cleaning as a fallback, so all tests still pass.

        if (isset($fonts[$id])) {
            return $fonts[$id];
        } else {
            $id = preg_replace('/[^0-9\.\-_]/', '', $id);
            if (isset($fonts[$id])) {
                return $fonts[$id];
            }
        }

        return null;
    }

    /**
     * Support for XObject
     *
     * @return PDFObject[]
     */
    public function getXObjects()
    {
        if (null !== $this->_xobjects) {
            return $this->_xobjects;
        }

        $resources = $this->get('Resources');

        if (method_exists($resources, 'has') && $resources->has('XObject')) {
            if ($resources->get('XObject') instanceof Header) {
                $xobjects = $resources->get('XObject')->getElements();
            } else {
                $xobjects = $resources->get('XObject')->getHeader()->getElements();
            }

            $table = array();

            foreach ($xobjects as $id => $xobject) {
                $table[$id] = $xobject;

                // Store too on cleaned id value (only numeric)
                $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                if ('' != $id) {
                    $table[$id] = $xobject;
                }
            }

            return $this->_xobjects = $table;
        }

        return array();
    }

    public function getXObject($id)
    {
        $xobjects = $this->getXObjects();

        if (isset($xobjects[$id])) {
            return $xobjects[$id];
        }

        return null;
        /*$id = preg_replace('/[^0-9\.\-_]/', '', $id);

        if (isset($xobjects[$id])) {
            return $xobjects[$id];
        } else {
            return null;
        }*/
    }

    public function getText($page = null)
    {
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN page getText,...");
		/*$fonts = $this->getFonts();
		foreach($fonts as $key=>$v) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "fonts key=$key");		
		}*/
		
		//$content = $this->getContent();
		//var_dump($content);
		
		//$elements = $this->getHeader()->getElements();
		/*
		string(4) "Type"
		string(8) "MediaBox"
		string(9) "Resources"
		string(8) "Contents"
		string(6) "Parent"
		string(6) "Annots"
		*/
			
		if ($contents = $this->get('Contents')) {
	        if ($contents instanceof ElementMissing) {
				return '';
            } elseif ($contents instanceof ElementNull) {
                return '';
            } elseif ($contents instanceof PDFObject) {
                $elements = $contents->getHeader()->getElements();
				if (is_numeric(key($elements))) {
                    $new_content = '';
					
                    foreach ($elements as $element) {
                        if ($element instanceof ElementXRef) {
                            $new_content .= $element->getObject()->getContent();
                        } else {
                            $new_content .= $element->getContent();
                        }
                    }

                    $header = new Header(array(), $this->_pdf);
                    $contents = new PDFObject($this->_pdf, $header, $new_content, $this->_config);
                }
            } elseif ($contents instanceof ElementArray) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "contents is ElementArray ............");	
                // Create a virtual global content.
                $new_content = '';

                foreach ($contents->getContent() as $content) {
                    $new_content .= $content->getContent()."\n";
                }

                $header = new Header(array(), $this->_pdf);
                $contents = new PDFObject($this->_pdf, $header, $new_content, $this->_config);
            }

            /*
             * Elements referencing each other on the same page can cause endless loops during text parsing.
             * To combat this we keep a recursionStack containing already parsed elements on the page.
             * The stack is only emptied here after getting text from a page.
             */
           
		 //rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "className=".get_class($contents).", getContent=".$contents->getContent());	
            $contentsText = $contents->getText($this);
            PDFObject::$recursionStack = array();

            return $contentsText;
        }

        return '';
    }

    /**
     * Return true if the current page is a (setasign\Fpdi\Fpdi) FPDI/FPDF document
     *
     * The metadata 'Producer' should have the value of "FPDF" . FPDF_VERSION if the
     * pdf file was generated by FPDF/Fpfi.
     *
     * @return bool true is the current page is a FPDI/FPDF document
     */
    public function isFpdf()
    {
		$details = $this->_pdf->getDetails();
        if (array_key_exists('Producer', $details) &&
            is_string($details['Producer']) &&
            0 === strncmp($details['Producer'], 'FPDF', 4)) {
            return true;
        }

        return false;
    }

    /**
     * Return the page number of the PDF document of the page object
     *
     * @return int the page number
     */
    public function getPageNumber()
    {
        $pages = $this->_pdf->getPages();
        $numOfPages = count($pages);
        for ($pageNum = 0; $pageNum < $numOfPages; ++$pageNum) {
            if ($pages[$pageNum] === $this) {
                break;
            }
        }

        return $pageNum;
    }

    /**
     * Return the Object of the page if the document is a FPDF/FPDI document
     *
     * If the document was generated by FPDF/FPDI it returns the
     * PDFObject of the given page
     *
     * @return PDFObject The PDFObject for the page
     */
    public function getPDFObjectForFpdf()
    {
        $pageNum = $this->getPageNumber();
        $xObjects = $this->getXObjects();

        return $xObjects[$pageNum];
    }

    /**
     * Return a new PDFObject of the document created with FPDF/FPDI
     *
     * For a document generated by FPDF/FPDI, it generates a
     * new PDFObject for that document
     *
     * @return PDFObject The PDFObject
     */
    public function createPDFObjectForFpdf()
    {
        $pdfObject = $this->getPDFObjectForFpdf();
        $new_content = $pdfObject->getContent();
        $header = $pdfObject->getHeader();
        $config = $pdfObject->_config;

        return new PDFObject($pdfObject->_pdf, $header, $new_content, $config);
    }

    /**
     * Return page if document is a FPDF/FPDI document
     *
     * @return Page The page
     */
    public function createPageForFpdf()
    {
        $pdfObject = $this->getPDFObjectForFpdf();
        $new_content = $pdfObject->getContent();
        $header = $pdfObject->getHeader();
        $config = $pdfObject->_config;

        return new self($pdfObject->_pdf, $header, $new_content, $config);
    }

    public function getTextArray($page = null)
    {
        if ($this->isFpdf()) {
            $pdfObject = $this->getPDFObjectForFpdf();
            $newPdfObject = $this->createPDFObjectForFpdf();

            return $newPdfObject->getTextArray($pdfObject);
        } else {
            if ($contents = $this->get('Contents')) {
                if ($contents instanceof ElementMissing) {
                    return array();
                } elseif ($contents instanceof ElementNull) {
                    return array();
                } elseif ($contents instanceof PDFObject) {
                    $elements = $contents->getHeader()->getElements();

                    if (is_numeric(key($elements))) {
                        $new_content = '';

                        /** @var PDFObject $element */
                        foreach ($elements as $element) {
                            if ($element instanceof ElementXRef) {
                                $new_content .= $element->getObject()->getContent();
                            } else {
                                $new_content .= $element->getContent();
                            }
                        }

                        $header = new Header(array(), $this->_pdf);
                        $contents = new PDFObject($this->_pdf, $header, $new_content, $this->_config);
                    } else {
                        try {
                            $contents->getTextArray($this);
                        } catch (Throwable $e) {
                            return $contents->getTextArray();
                        }
                    }
                } elseif ($contents instanceof ElementArray) {
                    // Create a virtual global content.
                    $new_content = '';

                    /** @var PDFObject $content */
                    foreach ($contents->getContent() as $content) {
                        $new_content .= $content->getContent()."\n";
                    }

                    $header = new Header(array(), $this->_pdf);
                    $contents = new PDFObject($this->_pdf, $header, $new_content, $this->_config);
                }

                return $contents->getTextArray($this);
            }

            return array();
        }
    }

    /**
     * Gets all the text data with its internal representation of the page.
     *
     * Returns an array with the data and the internal representation
     */
    public function extractRawData()
    {
        /*
         * Now you can get the complete content of the object with the text on it
         */
        $extractedData = array();
        $content = $this->get('Contents');
        $values = $content->getContent();
        if (isset($values) && is_array($values)) {
            $text = '';
            foreach ($values as $section) {
                $text .= $section->getContent();
            }
            $sectionsText = $this->getSectionsText($text);
            foreach ($sectionsText as $sectionText) {
                $commandsText = $this->getCommandsText($sectionText);
                foreach ($commandsText as $command) {
                    $extractedData[] = $command;
                }
            }
        } else {
            if ($this->isFpdf()) {
                $content = $this->getPDFObjectForFpdf();
            }
            $sectionsText = $content->getSectionsText($content->getContent());
            foreach ($sectionsText as $sectionText) {
                $extractedData[] = array('t' => '', 'o' => 'BT', 'c' => '');

                $commandsText = $content->getCommandsText($sectionText);
                foreach ($commandsText as $command) {
                    $extractedData[] = $command;
                }
            }
        }

        return $extractedData;
    }

    /**
     * Gets all the decoded text data with it internal representation from a page.
     *
     * @param array $extractedRawData the extracted data return by extractRawData or
     *                                null if extractRawData should be called
     *
     * @return array An array with the data and the internal representation
     */
    public function extractDecodedRawData($extractedRawData = null)
    {
        if (!isset($extractedRawData) || !$extractedRawData) {
            $extractedRawData = $this->extractRawData();
        }
        $currentFont = null; /** @var Font $currentFont */
        $clippedFont = null;
        $fpdfPage = null;
        if ($this->isFpdf()) {
            $fpdfPage = $this->createPageForFpdf();
        }
        foreach ($extractedRawData as &$command) {
            if ('Tj' == $command['o'] || 'TJ' == $command['o']) {
                $data = $command['c'];
                if (!is_array($data)) {
                    $tmpText = '';
                    if (isset($currentFont)) {
                        $tmpText = $currentFont->decodeOctal($data);
                        //$tmpText = $currentFont->decodeHexadecimal($tmpText, false);
                    }
                    $tmpText = str_replace(
                            array('\\\\', '\(', '\)', '\n', '\r', '\t', '\ '),
                            array('\\', '(', ')', "\n", "\r", "\t", ' '),
                            $tmpText
                    );
                    $tmpText = utf8_encode($tmpText);
                    if (isset($currentFont)) {
                        $tmpText = $currentFont->decodeContent($tmpText);
                    }
                    $command['c'] = $tmpText;
                    continue;
                }
                $numText = count($data);
                for ($i = 0; $i < $numText; ++$i) {
                    if (0 != ($i % 2)) {
                        continue;
                    }
                    $tmpText = $data[$i]['c'];
                    $decodedText = isset($currentFont) ? $currentFont->decodeOctal($tmpText) : $tmpText;
                    $decodedText = str_replace(
                            array('\\\\', '\(', '\)', '\n', '\r', '\t', '\ '),
                            array('\\', '(', ')', "\n", "\r", "\t", ' '),
                            $decodedText
                    );
                    $decodedText = utf8_encode($decodedText);
                    if (isset($currentFont)) {
                        $decodedText = $currentFont->decodeContent($decodedText);
                    }
                    $command['c'][$i]['c'] = $decodedText;
                    continue;
                }
            } elseif ('Tf' == $command['o'] || 'TF' == $command['o']) {
				$cdb = explode(' ', $command['c']);
                $fontId = $cdb[0];
                // If document is a FPDI/FPDF the $page has the correct font
                $currentFont = isset($fpdfPage) ? $fpdfPage->getFont($fontId) : $this->getFont($fontId);
                continue;
            } elseif ('Q' == $command['o']) {
                $currentFont = $clippedFont;
            } elseif ('q' == $command['o']) {
                $clippedFont = $currentFont;
            }
        }

        return $extractedRawData;
    }

    /**
     * Gets just the Text commands that are involved in text positions and
     * Text Matrix (Tm)
     *
     * It extract just the PDF commands that are involved with text positions, and
     * the Text Matrix (Tm). These are: BT, ET, TL, Td, TD, Tm, T*, Tj, ', ", and TJ
     *
     * @param array $extractedDecodedRawData The data extracted by extractDecodeRawData.
     *                                       If it is null, the method extractDecodeRawData is called.
     *
     * @return array An array with the text command of the page
     */
    public function getDataCommands($extractedDecodedRawData = null)
    {
        if (!isset($extractedDecodedRawData) || !$extractedDecodedRawData) {
            $extractedDecodedRawData = $this->extractDecodedRawData();
        }
        $extractedData = array();
        foreach ($extractedDecodedRawData as $command) {
            switch ($command['o']) {
                /*
                 * BT
                 * Begin a text object, inicializind the Tm and Tlm to identity matrix
                 */
                case 'BT':
                    $extractedData[] = $command;
                    break;

                /*
                 * ET
                 * End a text object, discarding the text matrix
                 */
                case 'ET':
                    $extractedData[] = $command;
                    break;

                /*
                 * leading TL
                 * Set the text leading, Tl, to leading. Tl is used by the T*, ' and " operators.
                 * Initial value: 0
                 */
                case 'TL':
                    $extractedData[] = $command;
                    break;

                /*
                 * tx ty Td
                 * Move to the start of the next line, offset form the start of the
                 * current line by tx, ty.
                 */
                case 'Td':
                    $extractedData[] = $command;
                    break;

                /*
                 * tx ty TD
                 * Move to the start of the next line, offset form the start of the
                 * current line by tx, ty. As a side effect, this operator set the leading
                 * parameter in the text state. This operator has the same effect as the
                 * code:
                 * -ty TL
                 * tx ty Td
                 */
                case 'TD':
                    $extractedData[] = $command;
                    break;

                /*
                 * a b c d e f Tm
                 * Set the text matrix, Tm, and the text line matrix, Tlm. The operands are
                 * all numbers, and the initial value for Tm and Tlm is the identity matrix
                 * [1 0 0 1 0 0]
                 */
                case 'Tm':
                    $extractedData[] = $command;
                    break;

                /*
                 * T*
                 * Move to the start of the next line. This operator has the same effect
                 * as the code:
                 * 0 Tl Td
                 * Where Tl is the current leading parameter in the text state.
                 */
                case 'T*':
                    $extractedData[] = $command;
                    break;

                /*
                 * string Tj
                 * Show a Text String
                 */
                case 'Tj':
                    $extractedData[] = $command;
                    break;

                /*
                 * string '
                 * Move to the next line and show a text string. This operator has the
                 * same effect as the code:
                 * T*
                 * string Tj
                 */
                case "'":
                    $extractedData[] = $command;
                    break;

                /*
                 * aw ac string "
                 * Move to the next lkine and show a text string, using aw as the word
                 * spacing and ac as the character spacing. This operator has the same
                 * effect as the code:
                 * aw Tw
                 * ac Tc
                 * string '
                 * Tw set the word spacing, Tw, to wordSpace.
                 * Tc Set the character spacing, Tc, to charsSpace.
                 */
                case '"':
                    $extractedData[] = $command;
                    break;

                case 'Tf':
                case 'TF':
                    if ($this->_config->getDataTmFontInfoHasToBeIncluded()) {
                        $extractedData[] = $command;
                    }
                    break;

                /*
                 * array TJ
                 * Show one or more text strings allow individual glyph positioning.
                 * Each lement of array con be a string or a number. If the element is
                 * a string, this operator shows the string. If it is a number, the
                 * operator adjust the text position by that amount; that is, it translates
                 * the text matrix, Tm. This amount is substracted form the current
                 * horizontal or vertical coordinate, depending on the writing mode.
                 * in the default coordinate system, a positive adjustment has the effect
                 * of moving the next glyph painted either to the left or down by the given
                 * amount.
                 */
                case 'TJ':
                    $extractedData[] = $command;
                    break;
                default:
            }
        }

        return $extractedData;
    }

    /**
     * Gets the Text Matrix of the text in the page
     *
     * Return an array where every item is an array where the first item is the
     * Text Matrix (Tm) and the second is a string with the text data.  The Text matrix
     * is an array of 6 numbers. The last 2 numbers are the coordinates X and Y of the
     * text. The first 4 numbers has to be with Scalation, Rotation and Skew of the text.
	 * 
	array(a,b,c,d,e,f):
	
	a is Scale_x
	b is Shear_x
	c is Shear_y
	d is Scale_y
	e is offset x
	f is offset y
     *
     * @param array $dataCommands the data extracted by getDataCommands
     *                            if null getDataCommands is called
     *
     * @return array an array with the data of the page including the Tm information
     *               of any text in the page
     */
    public function getDataTm($dataCommands = null)
    {
        if (!isset($dataCommands) || !$dataCommands) {
            $dataCommands = $this->getDataCommands();
        }

        /*
         * At the beginning of a text object Tm is the identity matrix
         */
        $defaultTm = array('1', '0', '0', '1', '0', '0');

        /*
         *  Set the text leading used by T*, ' and " operators
         */
        $defaultTl = 0;

        /*
         *  Set default values for font data
         */
        $defaultFontId = -1;
        $defaultFontSize = 0;

        /*
         * Setting where are the X and Y coordinates in the matrix (Tm)
         */
        $x = 4;
        $y = 5;
        $Tx = 0;
        $Ty = 0;

        $Tm = $defaultTm;
        $Tl = $defaultTl;
        $fontId = $defaultFontId;
        $fontSize = $defaultFontSize;

        $extractedTexts = $this->getTextArray();
        $extractedData = array();
        foreach ($dataCommands as $command) {
            $currentText = $extractedTexts[count($extractedData)];
            switch ($command['o']) {
                /*
                 * BT
                 * Begin a text object, inicializind the Tm and Tlm to identity matrix
                 */
                case 'BT':
                    $Tm = $defaultTm;
                    $Tl = $defaultTl; //review this.
                    $Tx = 0;
                    $Ty = 0;
                    $fontId = $defaultFontId;
                    $fontSize = $defaultFontSize;
					
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "recv BT ... fontId=$fontId, fontSize=$fontSize!");
					
                    break;

                /*
                 * ET
                 * End a text object, discarding the text matrix
                 */
                case 'ET':
                    $Tm = $defaultTm;
                    $Tl = $defaultTl;  //review this
                    $Tx = 0;
                    $Ty = 0;
                    $fontId = $defaultFontId;
                    $fontSize = $defaultFontSize;
					
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "recv ET ...++++++++++++++++++++++++");
					
                    break;

                /*
                 * leading TL
                 * Set the text leading, Tl, to leading. Tl is used by the T*, ' and " operators.
                 * Initial value: 0
                 */
                case 'TL':
                    $Tl = (float) $command['c'];
                    break;

                /*
                 * tx ty Td
                 * Move to the start of the next line, offset form the start of the
                 * current line by tx, ty.
                 */
                case 'Td':
                    $coord = explode(' ', $command['c']);
                    $Tx += (float) $coord[0];
                    $Ty += (float) $coord[1];
                    $Tm[$x] = (string) $Tx;
                    $Tm[$y] = (string) $Ty;
                    break;

                /*
                 * tx ty TD
                 * Move to the start of the next line, offset form the start of the
                 * current line by tx, ty. As a side effect, this operator set the leading
                 * parameter in the text state. This operator has the same effect as the
                 * code:
                 * -ty TL
                 * tx ty Td
                 */
                case 'TD':
                    $coord = explode(' ', $command['c']);
                    $Tl = (float) $coord[1];
                    $Tx += (float) $coord[0];
                    $Ty -= (float) $coord[1];
                    $Tm[$x] = (string) $Tx;
                    $Tm[$y] = (string) $Ty;
                    break;

                /*
                 * a b c d e f Tm
                 * Set the text matrix, Tm, and the text line matrix, Tlm. The operands are
                 * all numbers, and the initial value for Tm and Tlm is the identity matrix
                 * [1 0 0 1 0 0]
                 */
                case 'Tm':
                    $Tm = explode(' ', $command['c']);
                    $Tx = (float) $Tm[$x];
                    $Ty = (float) $Tm[$y];
                    break;

                /*
                 * T*
                 * Move to the start of the next line. This operator has the same effect
                 * as the code:
                 * 0 Tl Td
                 * Where Tl is the current leading parameter in the text state.
                 */
                case 'T*':
                    $Ty -= $Tl;
                    $Tm[$y] = (string) $Ty;
                    break;

                /*
                 * string Tj
                 * Show a Text String
                 */
                case 'Tj':
                    $data = array($Tm, $currentText);
                    if ($this->_config->getDataTmFontInfoHasToBeIncluded()) {
                        $data[] = $fontId;
                        $data[] = $fontSize;
                    }
                    $extractedData[] = $data;
                    break;

                /*
                 * string '
                 * Move to the next line and show a text string. This operator has the
                 * same effect as the code:
                 * T*
                 * string Tj
                 */
                case "'":
                    $Ty -= $Tl;
                    $Tm[$y] = (string) $Ty;
                    $extractedData[] = array($Tm, $currentText);
                    break;

                /*
                 * aw ac string "
                 * Move to the next line and show a text string, using aw as the word
                 * spacing and ac as the character spacing. This operator has the same
                 * effect as the code:
                 * aw Tw
                 * ac Tc
                 * string '
                 * Tw set the word spacing, Tw, to wordSpace.
                 * Tc Set the character spacing, Tc, to charsSpace.
                 */
                case '"':
                    $data = explode(' ', $currentText);
                    $Ty -= $Tl;
                    $Tm[$y] = (string) $Ty;
                    $extractedData[] = array($Tm, $data[2]); //Verify
                    break;

                case 'Tf':
                    /*
                     * From PDF 1.0 specification, page 106:
                     *     fontname size Tf Set font and size
                     *     Sets the text font and text size in the graphics state. There is no default value for
                     *     either fontname or size; they must be selected using Tf before drawing any text.
                     *     fontname is a resource name. size is a number expressed in text space units.
                     *
                     * Source: https://ia902503.us.archive.org/10/items/pdfy-0vt8s-egqFwDl7L2/PDF%20Reference%201.0.pdf
                     * Introduced with https://github.com/smalot/pdfparser/pull/516
                     */
                    list($fontId, $fontSize) = explode(' ', $command['c'], 2);
                    break;

                /*
                 * array TJ
                 * Show one or more text strings allow individual glyph positioning.
                 * Each lement of array con be a string or a number. If the element is
                 * a string, this operator shows the string. If it is a number, the
                 * operator adjust the text position by that amount; that is, it translates
                 * the text matrix, Tm. This amount is substracted form the current
                 * horizontal or vertical coordinate, depending on the writing mode.
                 * in the default coordinate system, a positive adjustment has the effect
                 * of moving the next glyph painted either to the left or down by the given
                 * amount.
                 */
                case 'TJ':
                    $data = array($Tm, $currentText);
                    if ($this->_config->getDataTmFontInfoHasToBeIncluded()) {
                        $data[] = $fontId;
                        $data[] = $fontSize;
                    }
                    $extractedData[] = $data;
                    break;
                default:
            }
        }
        $this->_dataTm = $extractedData;

        return $extractedData;
    }

    /**
     * Gets text data that are around the given coordinates (X,Y)
     *
     * If the text is in near the given coordinates (X,Y) (or the TM info),
     * the text is returned.  The extractedData return by getDataTm, could be use to see
     * where is the coordinates of a given text, using the TM info for it.
     *
     * @param float $x      The X value of the coordinate to search for. if null
     *                      just the Y value is considered (same Row)
     * @param float $y      The Y value of the coordinate to search for
     *                      just the X value is considered (same column)
     * @param float $xError The value less or more to consider an X to be "near"
     * @param float $yError The value less or more to consider an Y to be "near"
     *
     * @return array An array of text that are near the given coordinates. If no text
     *               "near" the x,y coordinate, an empty array is returned. If Both, x
     *               and y coordinates are null, null is returned.
     */
    public function getTextXY($x = null, $y = null, $xError = 0, $yError = 0)
    {
		if (!isset($this->_dataTm) || !$this->_dataTm) {
            $this->getDataTm();
        }

        if (null !== $x) {
            $x = (float) $x;
        }

        if (null !== $y) {
            $y = (float) $y;
        }

        if (null === $x && null === $y) {
            return array();
        }

        $xError = (float) $xError;
        $yError = (float) $yError;

        $extractedData = array();
		foreach ($this->_dataTm as $item) {
            $tm = $item[0];
            $xTm = (float) $tm[4];
            $yTm = (float) $tm[5];
            $text = $item[1];
            if (null === $y) {
                if (($xTm >= ($x - $xError)) &&
                    ($xTm <= ($x + $xError))) {
                    $extractedData[] = array($tm, $text);
                    continue;
                }
            }
            if (null === $x) {
                if (($yTm >= ($y - $yError)) &&
                    ($yTm <= ($y + $yError))) {
                    $extractedData[] = array($tm, $text);
                    continue;
                }
            }
            if (($xTm >= ($x - $xError)) &&
                ($xTm <= ($x + $xError)) &&
                ($yTm >= ($y - $yError)) &&
                ($yTm <= ($y + $yError))) {
                $extractedData[] = array($tm, $text);
                continue;
            }
        }

        return $extractedData;
    }
}
