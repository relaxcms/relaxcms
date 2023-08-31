<?php

/**
 * Class ElementArray
 */
class ElementArray extends Element
{
    public function __construct($value, $pdf = null)
    {
        parent::__construct($value, $pdf);
    }

    public function getContent()
    {
        foreach ($this->_value as $name => $element) {
            $this->resolveXRef($name);
        }

        return parent::getContent();
    }

    public function getRawContent()
    {
        return $this->_value;
    }

    public function getDetails($deep = true)
    {
        $values = array();
        $elements = $this->getContent();

        foreach ($elements as $key => $element) {
            if ($element instanceof Header && $deep) {
                $values[$key] = $element->getDetails($deep);
            } elseif ($element instanceof PDFObject && $deep) {
                $values[$key] = $element->getDetails(false);
            } elseif ($element instanceof self) {
                if ($deep) {
                    $values[$key] = $element->getDetails();
                }
            } elseif ($element instanceof Element && !($element instanceof self)) {
                $values[$key] = $element->getContent();
            }
        }

        return $values;
    }

    public function __toString()
    {
        return implode(',', $this->_value);
    }

    /**
     * @return Element|PDFObject
     */
    protected function resolveXRef( $name)
    {
        if (($obj = $this->_value[$name]) instanceof ElementXRef) {
            /** @var ElementXRef $obj */
            $obj = $this->_pdf->getObjectById($obj->getId());
            $this->_value[$name] = $obj;
        }

        return $this->_value[$name];
    }

    /**
     * @todo: These methods return mixed and mismatched types throughout the hierarchy
     *
     * @return bool|ElementArray
     */
    public static function parse($content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*\[(?P<array>.*)/is', $content, $match)) {
            preg_match_all('/(.*?)(\[|\])/s', trim($content), $matches);

            $level = 0;
            $sub = '';
            foreach ($matches[0] as $part) {
                $sub .= $part;
                $level += (false !== strpos($part, '[') ? 1 : -1);
                if ($level <= 0) {
                    break;
                }
            }

            // Removes 1 level [ and ].
            $sub = substr(trim($sub), 1, -1);
            $sub_offset = 0;
            $values = Element::parse($sub, $pdf, $sub_offset, true);

            $offset += strpos($content, '[') + 1;
            // Find next ']' position
            $offset += strlen($sub) + 1;

            return new self($values, $pdf);
        }

        return false;
    }
}
