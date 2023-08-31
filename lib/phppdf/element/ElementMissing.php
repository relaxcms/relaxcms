<?php


/**
 * Class ElementMissing
 */
class ElementMissing extends Element
{
    public function __construct()
    {
        parent::__construct(null, null);
    }

    public function equals($value)
    {
        return false;
    }

    public function contains($value)
    {
        return false;
    }

    public function getContent()
    {
        return false;
    }

    public function __toString()
    {
        return '';
    }
}
