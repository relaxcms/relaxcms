<?php

require_once(RPATH_PHPPDF.DS.'font/FontType0.php');
require_once(RPATH_PHPPDF.DS.'font/FontType1.php');
require_once(RPATH_PHPPDF.DS.'font/FontType3.php');
require_once(RPATH_PHPPDF.DS.'font/FontTrueType.php');
require_once(RPATH_PHPPDF.DS.'font/FontCIDFontType0.php');
require_once(RPATH_PHPPDF.DS.'font/FontCIDFontType2.php');


/**
 * Class Font
 */
class Font extends PDFObject
{
    const MISSING = '?';

    /**
     * @var array
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $tableSizes = null;

    /**
     * Caches results from uchr.
     *
     * @var array
     */
    private static $uchrCache = array();

    /**
     * In some PDF-files encoding could be referenced by object id but object itself does not contain
     * `/Type /Encoding` in its dictionary. These objects wouldn't be initialized as Encoding in
     * \Smalot\PdfParser\PDFObject::factory() during file parsing (they would be just PDFObject).
     *
     * Therefore, we create an instance of Encoding from them during decoding and cache this value in this property.
     *
     * @var Encoding
     *
     * @see https://github.com/smalot/pdfparser/pull/500
     */
    private $initializedEncodingByPdfObject;

    public function init()
    {
		// Load translate table.
        $this->loadTranslateTable();
    }

    public function getName()
    {
        return $this->has('BaseFont') ? (string) $this->get('BaseFont') : '[Unknown]';
    }

    public function getType()
    {
        return (string) $this->_header->get('Subtype');
    }

    public function getDetails($deep = true)
    {
        $details = array();

        $details['Name'] = $this->getName();
        $details['Type'] = $this->getType();
        $details['Encoding'] = ($this->has('Encoding') ? (string) $this->get('Encoding') : 'Ansi');

        $details += parent::getDetails($deep);

        return $details;
    }

    /**
     * @return string|bool
     */
    public function translateChar($char, $use_default = true)
    {
        $dec = hexdec(bin2hex($char));
		if (array_key_exists($dec, $this->table)) {
            return $this->table[$dec];
        }

        // fallback for decoding single-byte ANSI characters that are not in the lookup table
        $fallbackDecoded = $char;
        if (
            strlen($char) < 2
            && $this->has('Encoding')
            && $this->get('Encoding') instanceof Encoding
        ) {
            try {
				$encoding = $this->get('Encoding')->__toString();
				//var_dump($encoding);
                if (WinAnsiEncoding::className === $encoding) {
                    $fallbackDecoded = self::uchr($dec);
                }
            } catch (EncodingNotFoundException $e) {
                // Encoding->getEncodingClass() throws EncodingNotFoundException when BaseEncoding doesn't exists
                // See table 5.11 on PDF 1.5 specs for more info
            }
        }

        return $use_default ? self::MISSING : $fallbackDecoded;
    }

    /**
     * Convert unicode character code to "utf-8" encoded string.
     */
    public static function uchr($code)
    {
        if (!isset(self::$uchrCache[$code])) {
            // html_entity_decode() will not work with UTF-16 or UTF-32 char entities,
            // therefore, we use mb_convert_encoding() instead
            self::$uchrCache[$code] = mb_convert_encoding("&#{$code};", 'UTF-8', 'HTML-ENTITIES');
        }

        return self::$uchrCache[$code];
    }

    /**
     * Init internal chars translation table by ToUnicode CMap.
     */
    public function loadTranslateTable()
    {
        if (null !== $this->table) {
            return $this->table;
        }

        $this->table = array();
        $this->tableSizes = array(
            'from' => 1,
            'to' => 1,
        );

        if ($this->has('ToUnicode')) {
            $content = $this->get('ToUnicode')->getContent();
			$matches = array();

            // Support for multiple spacerange sections
            if (preg_match_all('/begincodespacerange(?P<sections>.*?)endcodespacerange/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
					$regexp = '/<(?P<from>[0-9A-F]+)> *<(?P<to>[0-9A-F]+)>[ \r\n]+/is';
					/*1 begincodespacerange
					<0000><FFFF>
					endcodespacerange
					72 beginbfchar
					<0001><0020>
					*/
                    preg_match_all($regexp, $section, $matches);
					//var_dump($matches);
					
					//2-2(2字节对2字节)
					$tableSizes = array(
                        'from' => max(1, strlen(current($matches['from'])) / 2),
                        'to' => max(1, strlen(current($matches['to'])) / 2),
                    );
					//var_dump($tableSizes);
					
					$this->tableSizes = $tableSizes;
					

                    break;
                }
            }

            // Support for multiple bfchar sections
            if (preg_match_all('/beginbfchar(?P<sections>.*?)endbfchar/s', $content, $matches)) {
				
				foreach ($matches['sections'] as $section) {
                    $regexp = '/<(?P<from>[0-9A-F]+)>+<(?P<to>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches2);
					//var_dump($matches2);
					
					$this->tableSizes['from'] = max(1, strlen(current($matches2['from'])) / 2);

					foreach ($matches2['from'] as $key => $from) {
                        $parts = preg_split(
                            '/([0-9A-F]{4})/i',
								$matches2['to'][$key],
                            0,
                            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                        );
                        $text = '';
                        foreach ($parts as $part) {
                            $text .= self::uchr(hexdec($part));
                        }
                        $this->table[hexdec($from)] = $text;
                    }
                }
            }

            // Support for multiple bfrange sections
            if (preg_match_all('/beginbfrange(?P<sections>.*?)endbfrange/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    // Support for : <srcCode1> <srcCode2> <dstString>
                    $regexp = '/<(?P<from>[0-9A-F]+)> *<(?P<to>[0-9A-F]+)> *<(?P<offset>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $char_to = hexdec($matches['to'][$key]);
                        $offset = hexdec($matches['offset'][$key]);

                        for ($char = $char_from; $char <= $char_to; ++$char) {
                            $this->table[$char] = self::uchr($char - $char_from + $offset);
                        }
                    }

                    // Support for : <srcCode1> <srcCodeN> [<dstString1> <dstString2> ... <dstStringN>]
                    // Some PDF file has 2-byte Unicode values on new lines > added \r\n
                    $regexp = '/<(?P<from>[0-9A-F]+)> *<(?P<to>[0-9A-F]+)> *\[(?P<strings>[\r\n<>0-9A-F ]+)\][ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $strings = array();

                        preg_match_all('/<(?P<string>[0-9A-F]+)> */is', $matches['strings'][$key], $strings);

                        foreach ($strings['string'] as $position => $string) {
                            $parts = preg_split(
                                '/([0-9A-F]{4})/i',
                                $string,
                                0,
                                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                            );
                            $text = '';
                            foreach ($parts as $part) {
                                $text .= self::uchr(hexdec($part));
                            }
                            $this->table[$char_from + $position] = $text;
                        }
                    }
                }
            }
        }

        return $this->table;
    }

    /**
     * Set custom char translation table where:
     * - key - integer character code;
     * - value - "utf-8" encoded value;
     *
     * @return void
     */
    public function setTable(array $table)
    {
        $this->table = $table;
    }

    /**
     * Calculate text width with data from header 'Widths'. If width of character is not found then character is added to missing array.
     */
    public function calculateTextWidth(string $text, array &$missing = null)
    {
        $index_map = array_flip($this->table);
        $details = $this->getDetails();
        $widths = $details['Widths'];

        // Widths array is zero indexed but table is not. We must map them based on FirstChar and LastChar
        $width_map = array_flip(range($details['FirstChar'], $details['LastChar']));

        $width = null;
        $missing = array();
        $textLength = mb_strlen($text);
        for ($i = 0; $i < $textLength; ++$i) {
            $char = mb_substr($text, $i, 1);
            if (
                !array_key_exists($char, $index_map)
                || !array_key_exists($index_map[$char], $width_map)
                || !array_key_exists($width_map[$index_map[$char]], $widths)
            ) {
                $missing[] = $char;
                continue;
            }
            $width_index = $width_map[$index_map[$char]];
            $width += $widths[$width_index];
        }

        return $width;
    }

    /**
     * Decode hexadecimal encoded string. If $add_braces is true result value would be wrapped by parentheses.
     */
    public static function decodeHexadecimal($hexa, $add_braces = false)
    {
        // Special shortcut for XML content.
        if (false !== stripos($hexa, '<?xml')) {
            return $hexa;
        }

        $text = '';
        $parts = preg_split('/(<[a-f0-9]+>)/si', $hexa, -1, 
			PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match('/^<.*>$/s', $part) && false === stripos($part, '<?xml')) {
                // strip line breaks
                $part = preg_replace("/[\r\n]/", '', $part);
                $part = trim($part, '<>');
                if ($add_braces) {
                    $text .= '(';
                }

                $part = pack('H*', $part);
                $text .= ($add_braces ? preg_replace('/\\\/s', '\\\\\\', $part) : $part);

                if ($add_braces) {
                    $text .= ')';
                }
            } else {
                $text .= $part;
            }
        }

        return $text;
    }

    /**
     * Decode string with octal-decoded chunks.
     */
    public static function decodeOctal($text)
    {
        $parts = preg_split('/(\\\\[0-7]{3})/s', $text, -1, 
			PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $text = '';

        foreach ($parts as $part) {
            if (preg_match('/^\\\\[0-7]{3}$/', $part)) {
                $text .= chr(octdec(trim($part, '\\')));
            } else {
                $text .= $part;
            }
        }

        return $text;
    }

    /**
     * Decode string with html entity encoded chars.
     */
    public static function decodeEntities($text)
    {
        $parts = preg_split('/(#\d{2})/s', $text, -1, 
			PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $text = '';

        foreach ($parts as $part) {
            if (preg_match('/^#\d{2}$/', $part)) {
                $text .= chr(hexdec(trim($part, '#')));
            } else {
                $text .= $part;
            }
        }

        return $text;
    }

    /**
     * Check if given string is Unicode text (by BOM);
     * If true - decode to "utf-8" encoded string.
     * Otherwise - return text as is.
     *
     * @todo Rename in next major release to make the name correspond to reality (for ex. decodeIfUnicode())
     */
    public static function decodeUnicode($text)
    {
        if (preg_match('/^\xFE\xFF/i', $text)) {
            // Strip U+FEFF byte order marker.
            $decode = substr($text, 2);
            $text = '';
            $length = strlen($decode);

            for ($i = 0; $i < $length; $i += 2) {
                $text .= self::uchr(hexdec(bin2hex(substr($decode, $i, 2))));
            }
        }

        return $text;
    }

    /**
     * @todo Deprecated, use $this->_config->getFontSpaceLimit() instead.
     */
    protected function getFontSpaceLimit()
    {
        return $this->_config->getFontSpaceLimit();
    }

    /**
     * Decode text by commands array.
     */
    public function decodeText($commands)
    {
		$word_position = 0;
        $words = array();
        $font_space = $this->getFontSpaceLimit();

        foreach ($commands as $command) {
 /*
 array(3) {
  ["t"]=>
  string(1) "("
  ["o"]=>
  string(2) "Tj"
  ["c"]=>
  string(23) "T\b�\te皬oN鰃\t朠QlS
}
string(23) "T\b�\te皬oN鰃\t朠QlS? */

			switch ($command[PDFObject::TYPE]) {
                case 'n':
                    if ((float) (trim($command[PDFObject::COMMAND])) < $font_space) {
                        $word_position = count($words);
                    }
                    continue 2;//跳过，看下一条命令
                case '<':
                    // Decode hexadecimal.
                    $text = self::decodeHexadecimal('<'.$command[PDFObject::COMMAND].'>');
                    break;

                default:
                    // Decode octal (if necessary).
                    $text = self::decodeOctal($command[PDFObject::COMMAND]);
            }
			

            // replace escaped chars
            $text = str_replace(
					array('\\\\', '\(', '\)', '\n', '\r', '\t', '\f', '\ ', '\b'),
					array('\\', '(', ')', "\n", "\r", "\t", "\f", ' ', chr(8)),
                $text
            );
						
            // add content to result string
            if (isset($words[$word_position])) {
                $words[$word_position] .= $text;
            } else {
                $words[$word_position] = $text;
            }
        }

        foreach ($words as &$word) {
            $word = $this->decodeContent($word);
        }

        return implode(' ', $words);
    }

    /**
     * Decode given $text to "utf-8" encoded string.
     *
     * @param bool $unicode This parameter is deprecated and might be removed in a future release
     */
    public function decodeContent($text, &$unicode = null)
    {
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN decodeContent ... ");
		if ($this->has('ToUnicode')) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "ToUnicode ... ");
            $res = $this->decodeContentByToUnicodeCMapOrDescendantFonts($text);
        } else if ($this->has('Encoding')) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "Encoding ... ");
			$res = $this->decodeContentByEncoding($text);
			if (null === $res) {
				$res = $this->decodeContentByAutodetectIfNecessary($text);
			} 
		} else {
			$res = $this->decodeContentByAutodetectIfNecessary($text);
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return$res;
    }

    /**
     * First try to decode $text by ToUnicode CMap.
     * If char translation not found in ToUnicode CMap tries:
     *  - If DescendantFonts exists tries to decode char by one of that fonts.
     *      - If have no success to decode by DescendantFonts interpret $text as a string with "Windows-1252" encoding.
     *  - If DescendantFonts does not exist just return "?" as decoded char.
     *
     * @todo Seems this is invalid algorithm that do not follow pdf-format specification. Must be rewritten.
     */
    private function decodeContentByToUnicodeCMapOrDescendantFonts($text)
    {
        $bytes = $this->tableSizes['from'];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN decodeContentByToUnicodeCMapOrDescendantFonts bytes=".$bytes,  $this->tableSizes);
		
        if ($bytes) {
            $result = '';
            $length = strlen($text);

            for ($i=0; $i < $length; $i += $bytes) {
                $char = substr($text, $i, $bytes);

                if (false !== ($decoded = $this->translateChar($char, false))) {
                    $char = $decoded;
                } elseif ($this->has('DescendantFonts')) {
                    if ($this->get('DescendantFonts') instanceof PDFObject) {
                        $fonts = $this->get('DescendantFonts')->getHeader()->getElements();
                    } else {
                        $fonts = $this->get('DescendantFonts')->getContent();
                    }
                    $decoded = false;

                    foreach ($fonts as $font) {
                        if ($font instanceof self) {
                            if (false !== ($decoded = $font->translateChar($char, false))) {
                                $decoded = mb_convert_encoding($decoded, 'UTF-8', 'Windows-1252');
                                break;
                            }
                        }
                    }

                    if (false !== $decoded) {
                        $char = $decoded;
                    } else {
                        $char = mb_convert_encoding($char, 'UTF-8', 'Windows-1252');
                    }
                } else {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'MISSING, char='.$char);
					
                    $char = self::MISSING;
                }

                $result .= $char;
            }

            $text = $result;
        }

        return $text;
    }

    /**
     * Decode content by any type of Encoding (dictionary's item) instance.
     */
    private function decodeContentByEncoding($text)
    {
		$encoding = $this->get('Encoding');
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN decodeContentByEncoding....encoding=".$encoding->getContent());
		
		// When Encoding referenced by object id (/Encoding 520 0 R) but object itself does not contain `/Type /Encoding` in it's dictionary.
        if ($encoding instanceof PDFObject) {
            $encoding = $this->getInitializedEncodingByPdfObject($encoding);
        }

        // When Encoding referenced by object id (/Encoding 520 0 R) but object itself contains `/Type /Encoding` in it's dictionary.
        if ($encoding instanceof Encoding) {
            return $this->decodeContentByEncodingEncoding($text, $encoding);
        }

        // When Encoding is just string (/Encoding /WinAnsiEncoding)
        if ($encoding instanceof Element) { //todo: ElementString class must by used?
            return $this->decodeContentByEncodingElement($text, $encoding);
        }

        // don't double-encode strings already in UTF-8
        if (!mb_check_encoding($text, 'UTF-8')) {
            return mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        }

        return $text;
    }

    /**
     * Returns already created or create a new one if not created before Encoding instance by PDFObject instance.
     */
    private function getInitializedEncodingByPdfObject($PDFObject)
    {
        if (!$this->initializedEncodingByPdfObject) {
            $this->initializedEncodingByPdfObject = $this->createInitializedEncodingByPdfObject($PDFObject);
        }

        return $this->initializedEncodingByPdfObject;
    }

    /**
     * Decode content when $encoding (given by $this->get('Encoding')) is instance of Encoding.
     */
    private function decodeContentByEncodingEncoding($text, Encoding $encoding)
    {
        $result = '';
        $length = strlen($text);

        for ($i = 0; $i < $length; ++$i) {
            $dec_av = hexdec(bin2hex($text[$i]));
            $dec_ap = $encoding->translateChar($dec_av);
            $result .= self::uchr($dec_ap?$dec_av:null);
        }

        return $result;
    }

	function ucs2_to_utf8($h)
	{
		if (!is_string($h)) return null;
		$r='';
		for ($a=0; $a<strlen($h); $a+=4) { 
		$r.=chr(hexdec($h{$a}.$h{($a+1)}.$h{($a+2)}.$h{($a+3)})); 
		}
		return $r;
	}
	
    /**
     * Decode content when $encoding (given by $this->get('Encoding')) is instance of Element.
     */
    private function decodeContentByEncodingElement($text, Element $encoding)
    {
		
        $pdfEncodingName = $encoding->getContent();
		
        // mb_convert_encoding does not support MacRoman/macintosh,
        // so we use iconv() here
        $iconvEncodingName = $this->getIconvEncodingNameOrNullByPdfEncodingName($pdfEncodingName);
		
		
		//UniGB-UCS2-H
		//if ($pdfEncodingName == 'UniGB-UCS2-H') {
			
			//$de = mb_detect_encoding($text);
			
			//$res = mb_convert_encoding($text, 'UTF-8', 'UCS2');
			//$res2 = mb_convert_encoding($res, 'UTF-8', 'UCS2');
			
			//$res3 = iconv('UCS-2BE', 'UTF-8', $text);
			//$detail = $this->getDetails();
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$pdfEncodingName='.$pdfEncodingName.',$iconvEncodingName='.$iconvEncodingName.',res='.$res.',res3='.$res3.',de='.$de, $detail);
		
			//return $res;	
		//} else {
			//$res = mb_detect_encoding($text);
			//var_dump($res);
			
			//$res = mb_convert_encoding($text, 'UTF-8', 'CP1252');
			
			//$detail = $this->getDetails();
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$pdfEncodingName='.$pdfEncodingName.',$iconvEncodingName='.$iconvEncodingName.',res='.$res, $detail);
		//}
        return $iconvEncodingName ? iconv($iconvEncodingName, 'UTF-8', $text) : null;
    }

    /**
     * Convert PDF encoding name to iconv-known encoding name.
     */
    private function getIconvEncodingNameOrNullByPdfEncodingName($pdfEncodingName)
    {
		$pdfToIconvEncodingNameMap = array(
				'StandardEncoding' => 'ISO-8859-1',
				'MacRomanEncoding' => 'MACINTOSH',
				'WinAnsiEncoding' => 'CP1252',
				'UniGB-UCS2-H'=>'UCS-2BE',
				);

        return array_key_exists($pdfEncodingName, $pdfToIconvEncodingNameMap)
            ? $pdfToIconvEncodingNameMap[$pdfEncodingName]
            : null;
    }

    /**
     * If string seems like "utf-8" encoded string do nothing and just return given string as is.
     * Otherwise, interpret string as "Window-1252" encoded string.
     *
     * @return string|false
     */
    private function decodeContentByAutodetectIfNecessary($text)
    {
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        return mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        //todo: Why exactly `Windows-1252` used?
    }

    /**
     * Create Encoding instance by PDFObject instance and init it.
     */
    private function createInitializedEncodingByPdfObject($PDFObject)
    {
        $encoding = $this->createEncodingByPdfObject($PDFObject);
        $encoding->init();

        return $encoding;
    }

    /**
     * Create Encoding instance by PDFObject instance (without init).
     */
    private function createEncodingByPdfObject($PDFObject)
    {
		$pdf = $PDFObject->getDocument();
        $header = $PDFObject->getHeader();
        $content = $PDFObject->getContent();
        $config = $PDFObject->getConfig();

        return new Encoding($pdf, $header, $content, $config);
    }
}
