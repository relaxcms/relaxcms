<?php


/**
 * Class ElementName
 */
class ElementName extends Element
{
    public function __construct($value)
    {
        parent::__construct($value, null);
    }

    public function equals($value)
    {
        return $value == $this->_value;
    }

    /**
     * @return bool|ElementName
     */
    public static function parse($content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*\/([A-Z0-9\-\+,#\.]+)/is', $content, $match)) {
            $name = $match[1];
            $offset += strpos($content, $name) + strlen($name);
            $name = Font::decodeEntities($name);

            return new self($name);
        }

        return false;
    }
}
