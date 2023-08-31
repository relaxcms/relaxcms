<?php


/**
 * Class ElementHexa
 */
class ElementHexa extends ElementString
{
    /**
     * @return bool|ElementHexa|ElementDate
     */
    public static function parse($content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*\<(?P<name>[A-F0-9]+)\>/is', $content, $match)) {
            $name = $match['name'];
            $offset += strpos($content, '<'.$name) + strlen($name) + 2; // 1 for '>'
            // repackage string as standard
            $name = '('.self::decode($name).')';
            $element = ElementDate::parse($name, $pdf);

            if (!$element) {
                $element = ElementString::parse($name, $pdf);
            }

            return $element;
        }

        return false;
    }

    public static function decode( $value)
    {
        $text = '';
        $length = strlen($value);

        if ('00' === substr($value, 0, 2)) {
            for ($i = 0; $i < $length; $i += 4) {
                $hex = substr($value, $i, 4);
                $text .= '&#'.str_pad(hexdec($hex), 4, '0', STR_PAD_LEFT).';';
            }
        } else {
            for ($i = 0; $i < $length; $i += 2) {
                $hex = substr($value, $i, 2);
                $text .= chr(hexdec($hex));
            }
        }

        $text = html_entity_decode($text, ENT_NOQUOTES, 'UTF-8');

        return $text;
    }
}
