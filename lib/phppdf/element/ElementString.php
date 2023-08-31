<?php

/**
 * Class ElementString
 */
class ElementString extends Element
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
     * @return bool|ElementString
     */
    public static function parse($content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*\((?P<name>.*)/s', $content, $match)) {
            $name = $match['name'];

            // Find next ')' not escaped.
            $cur_start_text = $start_search_end = 0;
            while (false !== ($cur_start_pos = strpos($name, ')', $start_search_end))) {
                $cur_extract = substr($name, $cur_start_text, $cur_start_pos - $cur_start_text);
                preg_match('/(?P<escape>[\\\]*)$/s', $cur_extract, $match);
                if (!(strlen($match['escape']) % 2)) {
                    break;
                }
                $start_search_end = $cur_start_pos + 1;
            }

            // Extract string.
            $name = substr($name, 0, (int) $cur_start_pos);
            $offset += strpos($content, '(') + $cur_start_pos + 2; // 2 for '(' and ')'
            $name = str_replace(
                array('\\\\', '\\ ', '\\/', '\(', '\)', '\n', '\r', '\t'),
					array('\\',   ' ',   '/',   '(',  ')',  "\n", "\r", "\t"),
                $name
            );

            // Decode string.
            $name = Font::decodeOctal($name);
            $name = Font::decodeEntities($name);
            $name = Font::decodeHexadecimal($name, false);
            $name = Font::decodeUnicode($name);

            return new self($name);
        }

        return false;
    }
}
