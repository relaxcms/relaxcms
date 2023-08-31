<?php


require_once(RPATH_PHPPDF.DS.'encoding/AbstractEncoding.php');
require_once(RPATH_PHPPDF.DS.'encoding/WinAnsiEncoding.php');
require_once(RPATH_PHPPDF.DS.'encoding/StandardEncoding.php');
require_once(RPATH_PHPPDF.DS.'encoding/PostScriptGlyphs.php');
require_once(RPATH_PHPPDF.DS.'encoding/MacRomanEncoding.php');
require_once(RPATH_PHPPDF.DS.'encoding/ISOLatin9Encoding.php');
require_once(RPATH_PHPPDF.DS.'encoding/EncodingLocator.php');



/**
 * Class Encoding
 */
class Encoding extends PDFObject
{
    /**
     * @var array
     */
    protected $encoding;

    /**
     * @var array
     */
    protected $differences;

    /**
     * @var array
     */
    protected $mapping;

    public function init()
    {
        $this->mapping = array();
        $this->differences = array();
        $this->encoding = array();
		
		if ($this->has('BaseEncoding')) {
			$t = EncodingLocator::getEncoding($this->getEncodingClass());
            $this->encoding = $t->getTranslations();

            // Build table including differences.
            $differences = $this->get('Differences')->getContent();
            $code = 0;

            if (!is_array($differences)) {
                return;
            }

            foreach ($differences as $difference) {
                /** @var ElementNumeric $difference */
                if ($difference instanceof ElementNumeric) {
                    $code = $difference->getContent();
                    continue;
                }

                // ElementName
                $this->differences[$code] = $difference;
                if (is_object($difference)) {
                    $this->differences[$code] = $difference->getContent();
                }

                // For the next char.
                ++$code;
            }

            $this->mapping = $this->encoding;
            foreach ($this->differences as $code => $difference) {
                /* @var string $difference */
                $this->mapping[$code] = $difference;
            }
        }
    }

    public function getDetails($deep = true)
    {
        $details = array();

        $details['BaseEncoding'] = ($this->has('BaseEncoding') ? (string) $this->get('BaseEncoding') : 'Ansi');
        $details['Differences'] = ($this->has('Differences') ? (string) $this->get('Differences') : '');

        $details += parent::getDetails($deep);

        return $details;
    }

    public function translateChar($dec)
    {
        if (isset($this->mapping[$dec])) {
            $dec = $this->mapping[$dec];
        }

        return PostScriptGlyphs::getCodePoint($dec);
    }

    /**
     * Returns encoding class name if available or empty string (only prior PHP 7.4).
     *
     * @throws \Exception On PHP 7.4+ an exception is thrown if encoding class doesn't exist.
     */
    public function __toString()
    {
        try {
            return $this->getEncodingClass();
        } catch (Exception $e) {
            // prior to PHP 7.4 toString has to return an empty string.
            if (version_compare(PHP_VERSION, '7.4.0', '<')) {
                return '';
            }
            throw $e;
        }
    }

    /**
     * @throws EncodingNotFoundException
     */
    protected function getEncodingClass()
    {
        // Load reference table charset.
        $baseEncoding = preg_replace('/[^A-Z0-9]/is', '', 
		$this->get('BaseEncoding')->getContent());
        $className = 'Encoding'.$baseEncoding;
		var_dump($className);exit;
		
        if (!class_exists($className)) {
            throw new EncodingNotFoundException('Missing encoding data for: "'.$baseEncoding.'".');
        }

        return $className;
    }
}
