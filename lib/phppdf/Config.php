<?php

class Config
{
	private $fontSpaceLimit = -50;
	
	/**
	 * @var string
	 */
	private $horizontalOffset = ' ';
	
	/**
	 * Represents: (NUL, HT, LF, FF, CR, SP)
	 *
	 * @var string
	 */
	private $pdfWhitespaces = "\0\t\n\f\r ";
	
	/**
	 * Represents: (NUL, HT, LF, FF, CR, SP)
	 *
	 * @var string
	 */
	private $pdfWhitespacesRegex = '[\0\t\n\f\r ]';
	
	/**
	 * Whether to retain raw image data as content or discard it to save memory
	 *
	 * @var bool
	 */
	private $retainImageContent = true;
	
	/**
	 * Memory limit to use when de-compressing files, in bytes.
	 *
	 * @var int
	 */
	private $decodeMemoryLimit = 0;
	
	/**
	 * Whether to include font id and size in dataTm array
	 *
	 * @var bool
	 */
	private $dataTmFontInfoHasToBeIncluded = false;
	
	public function getFontSpaceLimit()
	{
		return $this->fontSpaceLimit;
	}
	
	public function setFontSpaceLimit($value)
	{
		$this->fontSpaceLimit = $value;
	}
	
	public function getHorizontalOffset()
	{
		return $this->horizontalOffset;
	}
	
	public function setHorizontalOffset($value)
	{
		$this->horizontalOffset = $value;
	}
	
	public function getPdfWhitespaces()
	{
		return $this->pdfWhitespaces;
	}
	
	public function setPdfWhitespaces(string $pdfWhitespaces)
	{
		$this->pdfWhitespaces = $pdfWhitespaces;
	}
	
	public function getPdfWhitespacesRegex()
	{
		return $this->pdfWhitespacesRegex;
	}
	
	public function setPdfWhitespacesRegex(string $pdfWhitespacesRegex)
	{
		$this->pdfWhitespacesRegex = $pdfWhitespacesRegex;
	}
	
	public function getRetainImageContent()
	{
		return $this->retainImageContent;
	}
	
	public function setRetainImageContent(bool $retainImageContent)
	{
		$this->retainImageContent = $retainImageContent;
	}
	
	public function getDecodeMemoryLimit()
	{
		return $this->decodeMemoryLimit;
	}
	
	public function setDecodeMemoryLimit(int $decodeMemoryLimit)
	{
		$this->decodeMemoryLimit = $decodeMemoryLimit;
	}
	
	public function getDataTmFontInfoHasToBeIncluded()
	{
		return $this->dataTmFontInfoHasToBeIncluded;
	}
	
	public function setDataTmFontInfoHasToBeIncluded(bool $dataTmFontInfoHasToBeIncluded)
	{
		$this->dataTmFontInfoHasToBeIncluded = $dataTmFontInfoHasToBeIncluded;
	}
}
