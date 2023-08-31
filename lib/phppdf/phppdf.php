<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define('RPATH_PHPPDF', dirname(__FILE__) );

require_once(RPATH_PHPPDF.DS.'Config.php');
require_once(RPATH_PHPPDF.DS.'Element.php');
require_once(RPATH_PHPPDF.DS.'Header.php');
require_once(RPATH_PHPPDF.DS.'PDFObject.php');

require_once(RPATH_PHPPDF.DS.'xobject/Form.php');
require_once(RPATH_PHPPDF.DS.'xobject/Image.php');

require_once(RPATH_PHPPDF.DS.'Page.php');
require_once(RPATH_PHPPDF.DS.'Pages.php');
require_once(RPATH_PHPPDF.DS.'Encoding.php');
require_once(RPATH_PHPPDF.DS.'Font.php');

require_once(RPATH_PHPPDF.DS.'rawdata/FilterHelper.php');
require_once(RPATH_PHPPDF.DS.'rawdata/RawDataParser.php');


class PHPPDF
{
	/**
	 * @var PDFObject[]
	 */
	protected $_objects = array();
	
	/**
	 * @var array
	 */
	protected $_dictionary = array();
	
	/**
	 * @var Header
	 */
	protected $_trailer = null;
	
	/**
	 * @var array
	 */
	protected $_details = null;
	
	public function __construct($filename, $options=array())
	{
		$this->_trailer = new Header(array(), $this);
		
		$this->_options = $options;
		$this->_filename = $filename;
		$this->_config = new Config();
		$this->_rawDataParser = new RawDataParser($options, $this->config);
		
		$this->_init();
	}
	
	
	protected function _init()
	{
		$this->parseFile($this->_filename);
	}
	
	
	public function parseFile($filename)
	{
		$content = s_read($filename); //file_get_contents($filename);
		/*
		 * 2018/06/20 @doganoo as multiple times a
		 * users have complained that the parseFile()
		 * method dies silently, it is an better option
		 * to remove the error control operator (@) and
		 * let the users know that the method throws an exception
		 * by adding @throws tag to PHPDoc.
		 *
		 * See here for an example: https://github.com/smalot/pdfparser/issues/204
		 */
		return $this->parseContent($content);
	}
	
	public function parseContent($rawdata)
	{
		/*try {
			// parse PDF data
			$pdf = new TCPDF_PARSER($rawdata, $cfg);
		} catch (Exception $e) {
			$err = $e->getMessage();
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, $err);
			return false;
		}*/
		// Create structure from raw data.
		//list($xref, $data) = $pdf->getParsedData();;
		try {
			list($xref, $data) = $this->_rawDataParser->parseData($rawdata);
		} catch (Exception $e) {
			$err = $e->getMessage();
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, $err);
			return false;
		}
		
		if (isset($xref['trailer']['encrypt'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'Secured pdf file are currently not supported.');
			return false;
		}
		if (empty($data)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 
					'Object list not found. Possible secured file.');
			return false;
		}
		
		
		//var_dump($xref);
		//var_dump($data);exit;
		
		$this->_objects = array();		
		foreach ($data as $id => $structure) {
			$this->parseObject($id, $structure);
			unset($data[$id]);
		}
		
		$this->parseTrailer($xref['trailer']);
		$this->init();
		
		return $this;
	}
	
	
	protected function parseTrailer($structure)
	{
		$trailer = array();
		
		foreach ($structure as $name => $values) {
			$name = ucfirst($name);
			
			if (is_numeric($values)) {
				$trailer[$name] = new ElementNumeric($values);
			} elseif (is_array($values)) {
				$value = $this->parseTrailer($values, null);
				$trailer[$name] = new ElementArray($value, null);
			} elseif (false !== strpos($values, '_')) {
				$trailer[$name] = new ElementXRef($values, $this);
			} else {
				$trailer[$name] = $this->parseHeaderElement('(', $values, $this);
			}
		}
		
		$this->_trailer = new Header($trailer, $this);
		
		return true;
	}
	
	
	protected function parseObject($id, array $structure)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "id=$id", $structure);
		$header = new Header(array(), $this);
		$content = '';
		
		foreach ($structure as $position => $part) {
			if (is_int($part)) {
				$part = array(null, null);
			}
			switch ($part[0]) {
				case '[':
					$elements = array();
					
					foreach ($part[1] as $sub_element) {
						$sub_type = $sub_element[0];
						$sub_value = $sub_element[1];
						$elements[] = $this->parseHeaderElement($sub_type, $sub_value);
					}
					
					$header = new Header($elements, $this);
					break;
				
				case '<<':
					$header = $this->parseHeader($part[1]);
					break;
				
				case 'stream':
					$content = isset($part[3][0]) ? $part[3][0] : $part[1];
					//
					/*$dir = RPATH_CACHE.DS.'pdfdebug';
					s_mkdir($dir);
					$file = $dir.DS.md5($content);
					s_write($file, $content);*/
					
					if ($header->get('Type')->equals('ObjStm')) {
						$match = array();
						// Split xrefs and contents.
						preg_match('/^((\d+\s+\d+\s*)*)(.*)$/s', $content, $match);
						$content = $match[3];
						
						// Extract xrefs.
						$xrefs = preg_split(
								'/(\d+\s+\d+\s*)/s',
								$match[1],
								-1,
								PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
								);
						$table = array();
						
						foreach ($xrefs as $xref) {
							list($id, $position) = preg_split("/\s+/", trim($xref));
							$table[$position] = $id;
						}
						
						ksort($table);
						
						$ids = array_values($table);
						$positions = array_keys($table);
						
						foreach ($positions as $index => $position) {
							$id = $ids[$index].'_0';
							$next_position = isset($positions[$index + 1]) ? $positions[$index + 1] : strlen($content);
							$sub_content = substr($content, $position, (int) $next_position - (int) $position);
							
							$sub_header = Header::parse($sub_content, $this);
							$object = PDFObject::factory($this, $sub_header, '', $this->_config);
							$this->_objects[$id] = $object;
						}
						
						// It is not necessary to store this content.
						
						return;
					}
					break;
				
				default:
					if ('null' != $part) {
						$element = $this->parseHeaderElement($part[0], $part[1]);
						
						if ($element) {
							$header = new Header(array($element), $this);
						}
					}
					break;
			}
		}
		
		if (!isset($this->_objects[$id])) {
			$this->_objects[$id] = PDFObject::factory($this, $header, $content, $this->_config);
		}
	}
	
	/**
	 * @throws Exception
	 */
	protected function parseHeader($structure)
	{
		$elements = array();
		$count = count($structure);
		
		for ($position = 0; $position < $count; $position += 2) {
			$name = $structure[$position][1];
			$type = $structure[$position + 1][0];
			$value = $structure[$position + 1][1];
			
			$elements[$name] = $this->parseHeaderElement($type, $value);
		}
		
		return new Header($elements, $this);
	}
	
	/**
	 * @param string|array $value
	 *
	 * @return Element|Header|null
	 *
	 * @throws Exception
	 */
	protected function parseHeaderElement($type, $value)
	{
		switch ($type) {
			case '<<':
			case '>>':
				$header = $this->parseHeader($value);
				PDFObject::factory($this, $header, null, $this->_config);
				
				return $header;
			
			case 'numeric':
				return new ElementNumeric($value);
			
			case 'boolean':
				return new ElementBoolean($value);
			
			case 'null':
				return new ElementNull();
			
			case '(':
				if ($date = ElementDate::parse('('.$value.')', $this)) {
					return $date;
				}
				
				return ElementString::parse('('.$value.')', $this);
			
			case '<':
				return $this->parseHeaderElement('(', ElementHexa::decode($value), $this);
			
			case '/':
				return ElementName::parse('/'.$value, $this);
			
			case 'ojbref': // old mistake in tcpdf parser
			case 'objref':
				return new ElementXRef($value, $this);
			
			case '[':
				$values = array();
				
				if (is_array($value)) {
					foreach ($value as $sub_element) {
						$sub_type = $sub_element[0];
						$sub_value = $sub_element[1];
						$values[] = $this->parseHeaderElement($sub_type, $sub_value);
					}
				}
				
				return new ElementArray($values, $this);
			
			case 'endstream':
			case 'obj': //I don't know what it means but got my project fixed.
			case '':
				// Nothing to do with.
				return null;
			
			default:
				throw new Exception('Invalid type: "'.$type.'".');
		}
	}
	
	
	public function init()
	{
		$this->buildDictionary();
		
		$this->buildDetails();
		
		// Propagate init to objects.
		foreach ($this->_objects as $key=>$object) {
			$object->getHeader()->init();
			$object->init();
			
			$type = $object->getHeader()->get('Type')->getContent();
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "init id '$key', type '$type'");
		}
	}
	
	/**
	 * Build dictionary based on type header field.
	 */
	protected function buildDictionary()
	{
		// Build dictionary.
		$this->_dictionary = array();
		
		foreach ($this->_objects as $id => $object) {
			// Cache objects by type and subtype
			$type = $object->getHeader()->get('Type')->getContent();
			
			if (null != $type) {
				if (!isset($this->_dictionary[$type])) {
					$this->_dictionary[$type] = array(
							'all' => array(),
							'subtype' => array(),
							);
				}
				
				$this->_dictionary[$type]['all'][$id] = $object;
				
				$subtype = $object->getHeader()->get('Subtype')->getContent();
				if (null != $subtype) {
					if (!isset($this->_dictionary[$type]['subtype'][$subtype])) {
						$this->_dictionary[$type]['subtype'][$subtype] = array();
					}
					$this->_dictionary[$type]['subtype'][$subtype][$id] = $object;
				}
			}
		}
	}
	
	/**
	 * Build details array.
	 */
	protected function buildDetails()
	{
		// Build details array.
		$details = array();
		
		// Extract document info
		if ($this->_trailer->has('Info')) {
			/** @var PDFObject $info */
			$info = $this->_trailer->get('Info');
			// This could be an ElementMissing object, so we need to check for
			// the getHeader method first.
			if (null !== $info && method_exists($info, 'getHeader')) {
				$details = $info->getHeader()->getDetails();
			}
		}
		
		// Retrieve the page count
		try {
			$pages = $this->getPages();
			$details['Pages'] = count($pages);
		} catch (Exception $e) {
			$details['Pages'] = 0;
		}
		
		$this->_details = $details;
	}
	
	public function getDictionary()
	{
		return $this->_dictionary;
	}
	
	/**
	 * @return PDFObject[]
	 */
	public function getObjects()
	{
		return $this->_objects;
	}
	
	/**
	 * @return PDFObject|Font|Page|Element|null
	 */
	public function getObjectById($id)
	{
		if (isset($this->_objects[$id])) {
			return $this->_objects[$id];
		}
		
		return null;
	}
	
	public function hasObjectsByType($type, $subtype = null)
	{
		return 0 < count($this->getObjectsByType($type, $subtype));
	}
	
	public function getObjectsByType($type, $subtype = null)
	{
		if (!isset($this->_dictionary[$type])) {
			return array();
		}
		
		if (null != $subtype) {
			if (!isset($this->_dictionary[$type]['subtype'][$subtype])) {
				return array();
			}
			
			return $this->_dictionary[$type]['subtype'][$subtype];
		}
		
		return $this->_dictionary[$type]['all'];
	}
	
	/**
	 * @return Font[]
	 */
	public function getFonts()
	{
		return $this->getObjectsByType('Font');
	}
	
	public function getFirstFont()
	{
		$fonts = $this->getFonts();
		if (array() === $fonts) {
			return null;
		}
		
		return reset($fonts);
	}
	
	/**
	 * @return Page[]
	 *
	 * @throws \Exception
	 */
	public function getPages()
	{
		if ($this->hasObjectsByType('Catalog')) {
			// Search for catalog to list pages.
			$catalogues = $this->getObjectsByType('Catalog');
			$catalogue = reset($catalogues);//reset() 函数将内部指针指向数组中的第一个元素，并输出。
			
			/** @var Pages $object */
			$object = $catalogue->get('Pages');
			if (method_exists($object, 'getPages')) {
				return $object->getPages(true);
			}
		}
		
		if ($this->hasObjectsByType('Pages')) {
			// Search for pages to list kids.
			$pages = array();
			
			/** @var Pages[] $objects */
			$objects = $this->getObjectsByType('Pages');
			foreach ($objects as $object) {
				$pages = array_merge($pages, $object->getPages(true));
			}
			
			return $pages;
		}
		
		if ($this->hasObjectsByType('Page')) {
			// Search for 'page' (unordered pages).
			$pages = $this->getObjectsByType('Page');
			
			return array_values($pages);
		}
		
		return array();
	}
	
	public function getText()
	{
		$texts = array();
		$pages = $this->getPages();
		
		foreach ($pages as $key => $page) {
			/**
			 * In some cases, the $page variable may be null.
			 */
			if (null === $page) {
				continue;
			}
			if ($text = trim($page->getText())) {
				$texts[] = $text;
			}
		}
		
		
		return implode("\n\n", $texts);
	}
	
	public function getTrailer()
	{
		return $this->_trailer;
	}
	
	
	public function getDetails()
	{
		return $this->_details;
	}
}
