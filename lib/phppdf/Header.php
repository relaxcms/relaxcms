<?php


/**
 * Class Header
 */
class Header
{
	/**
	 * @var Document
	 */
	protected $_pdf = null;
	
	/**
	 * @var Element[]
	 */
	protected $_elements = array();
	
	/**
	 * @param Element[] $elements list of elements
	 * @param Document  $document document
	 */
	public function __construct($elements = array(), $pdf = null)
	{
		$this->_elements = $elements;
		$this->_pdf = $pdf;
	}
	
	public function init()
	{
		foreach ($this->_elements as $element) {
			if ($element instanceof Element) {
				$element->init();
			}
		}
	}
	
	/**
	 * Returns all elements.
	 */
	public function getElements()
	{
		foreach ($this->_elements as $name => $element) {
			$this->resolveXRef($name);
		}
		
		return $this->_elements;
	}
	
	/**
	 * Used only for debug.
	 */
	public function getElementTypes()
	{
		$types = array();
		
		foreach ($this->_elements as $key => $element) {
			$types[$key] = get_class($element);
		}
		
		return $types;
	}
	
	public function getDetails($deep = true)
	{
		$values = array();
		$elements = $this->getElements();
		
		foreach ($elements as $key => $element) {
			if ($element instanceof self && $deep) {
				$values[$key] = $element->getDetails($deep);
			} elseif ($element instanceof PDFObject && $deep) {
				$values[$key] = $element->getDetails(false);
			} elseif ($element instanceof ElementArray) {
				if ($deep) {
					$values[$key] = $element->getDetails();
				}
			} elseif ($element instanceof Element) {
				$values[$key] = (string) $element;
			}
		}
		
		return $values;
	}
	
	/**
	 * Indicate if an element name is available in header.
	 *
	 * @param string $name the name of the element
	 */
	public function has($name)
	{
		return array_key_exists($name, $this->_elements);
	}
	
	/**
	 * @return Element|PDFObject
	 */
	public function get($name)
	{
		if (array_key_exists($name, $this->_elements) 
				&& $element = $this->resolveXRef($name)) {
			return $element;
		}
		
		return new ElementMissing();
	}
	
	/**
	 * Resolve XRef to object.
	 *
	 * @return Element|PDFObject
	 *
	 * @throws \Exception
	 */
	protected function resolveXRef($name)
	{
		if (($obj = $this->_elements[$name]) instanceof ElementXRef && null !== $this->_pdf) {
			/** @var ElementXRef $obj */
			$object = $this->_pdf->getObjectById($obj->getId());
			if (null === $object) {
				return new ElementMissing();
			}
			
			// Update elements list for future calls.
			$this->_elements[$name] = $object;
		}
		
		return $this->_elements[$name];
	}
	
	/**
	 * @param string   $content  The content to parse
	 * @param Document $document The document
	 * @param int      $position The new position of the cursor after parsing
	 */
	public static function parse($content, $pdf, &$position = 0)
	{
		/* @var Header $header */
		if ('<<' == substr(trim($content), 0, 2)) {
			$header = ElementStruct::parse($content, $pdf, $position);
		} else {
			$elements = ElementArray::parse($content, $pdf, $position);
			if ($elements) {
				$header = new self($elements->getRawContent(), null);
			}
		}
		
		if ($header) {
			return $header;
		}
		
		// Build an empty header.
		return new self(array(), $pdf);
	}
}
