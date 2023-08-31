<?php

/**
 * Class ElementBoolean
 */
class ElementBoolean extends Element
{
    /**
     * @param string|bool $value
     */
    public function __construct($value)
    {
        parent::__construct(('true' == strtolower($value) || true === $value), null);
    }

    public function __toString()
    {
        return $this->_value ? 'true' : 'false';
    }

    public function equals($value)
    {
        return $this->getContent() === $value;
    }

    /**
     * @return bool|ElementBoolean
     */
    public static function parse($content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*(?P<value>true|false)/is', $content, $match)) {
            $value = $match['value'];
            $offset += strpos($content, $value) + strlen($value);

            return new self($value);
        }

        return false;
    }
}
