<?php

/**
 * Class ElementXRef
 */
class ElementXRef extends Element
{
    public function getId()
    {
        return $this->getContent();
    }

    public function getObject()
    {
        return $this->_pdf->getObjectById($this->getId());
    }

    public function equals($value)
    {
        /**
         * In case $value is a number and $this->value is a string like 5_0
         *
         * Without this if-clause code like:
         *
         *      $element = new ElementXRef('5_0');
         *      $this->assertTrue($element->equals(5));
         *
         * would fail (= 5_0 and 5 are not equal in PHP 8.0+).
         */
        if (
            true === is_numeric($value)
            && true === is_string($this->getContent())
            && 1 === preg_match('/[0-9]+\_[0-9]+/', $this->getContent(), $matches)
        ) {
            return (float) ($this->getContent()) == $value;
        }

        $id = ($value instanceof self) ? $value->getId() : $value;

        return $this->getId() == $id;
    }

    public function __toString()
    {
        return '#Obj#'.$this->getId();
    }

    /**
     * @return bool|ElementXRef
     */
	public static function parse( $content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*(?P<id>[0-9]+\s+[0-9]+\s+R)/s', $content, $match)) {
            $id = $match['id'];
            $offset += strpos($content, $id) + strlen($id);
            $id = str_replace(' ', '_', rtrim($id, ' R'));

			return new self($id, $pdf);
        }

        return false;
    }
}
