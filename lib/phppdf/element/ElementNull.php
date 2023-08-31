<?php


/**
 * Class ElementNull
 */
class ElementNull extends Element
{
    public function __construct()
    {
        parent::__construct(null, null);
    }

    public function __toString()
    {
        return 'null';
    }

    public function equals($value)
    {
        return $this->getContent() === $value;
    }

    /**
     * @return bool|ElementNull
     */
    public static function parse($content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*(null)/s', $content, $match)) {
            $offset += strpos($content, 'null') + strlen('null');

            return new self();
        }

        return false;
    }
}
