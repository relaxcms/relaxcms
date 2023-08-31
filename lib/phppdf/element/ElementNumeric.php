<?php

/**
 * Class ElementNumeric
 */
class ElementNumeric extends Element
{
    public function __construct($value)
    {
        parent::__construct((float) $value, null);
    }

    /**
     * @return bool|ElementNumeric
     */
	public static function parse($content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*(?P<value>\-?[0-9\.]+)/s', $content, $match)) {
            $value = $match['value'];
            $offset += strpos($content, $value) + strlen($value);

            return new self($value);
        }

        return false;
    }
}
