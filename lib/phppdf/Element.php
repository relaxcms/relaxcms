<?php

require_once(RPATH_PHPPDF.DS.'element/ElementString.php');
require_once(RPATH_PHPPDF.DS.'element/ElementBoolean.php');
require_once(RPATH_PHPPDF.DS.'element/ElementArray.php');
require_once(RPATH_PHPPDF.DS.'element/ElementDate.php');
require_once(RPATH_PHPPDF.DS.'element/ElementMissing.php');
require_once(RPATH_PHPPDF.DS.'element/ElementName.php');
require_once(RPATH_PHPPDF.DS.'element/ElementXRef.php');
require_once(RPATH_PHPPDF.DS.'element/ElementNumeric.php');
require_once(RPATH_PHPPDF.DS.'element/ElementHexa.php');
require_once(RPATH_PHPPDF.DS.'element/ElementNull.php');
require_once(RPATH_PHPPDF.DS.'element/ElementStruct.php');

/**
 * Class Element
 */
class Element
{
	/**
	 * @var Document
	 */
	protected $_pdf = null;
	
	protected $_value = null;
	
	public function __construct($value, $pdf = null)
	{
		$this->_value = $value;
		$this->_pdf = $pdf;
	}
	
	public function init()
	{
	}
	
	public function equals($value)
	{
		return $value == $this->_value;
	}
	
	public function contains($value)
	{
		if (is_array($this->_value)) {
			/** @var Element $val */
			foreach ($this->_value as $val) {
				if ($val->equals($value)) {
					return true;
				}
			}
			
			return false;
		}
		
		return $this->equals($value);
	}
	
	public function getContent()
	{
		return $this->_value;
	}
	
	public function __toString()
	{
		return (string) ($this->_value);
	}
	
	public static function parse($content, $pdf = null, &$position = 0)
	{
		$args = func_get_args();
		$only_values = isset($args[3]) ? $args[3] : false;
		$content = trim($content);
		$values = array();
		
		do {
			$old_position = $position;
			
			if (!$only_values) {
				if (!preg_match('/^\s*(?P<name>\/[A-Z0-9\._]+)(?P<value>.*)/si', 
							substr($content, $position), $match)) {
					break;
				} else {
					$name = ltrim($match['name'], '/');
					$value = $match['value'];
					$position = strpos($content, $value, $position + strlen($match['name']));
				}
			} else {
				$name = count($values);
				$value = substr($content, $position);
			}
			
			if ($element = ElementName::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementXRef::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementNumeric::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementStruct::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementBoolean::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementNull::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementDate::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementString::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementHexa::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} elseif ($element = ElementArray::parse($value, $pdf, $position)) {
				$values[$name] = $element;
			} else {
				$position = $old_position;
				break;
			}
		} while ($position < strlen($content));
		
		return $values;
	}
}
