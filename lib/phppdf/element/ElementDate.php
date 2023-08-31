<?php


/**
 * Class ElementDate
 */
class ElementDate extends ElementString
{
    /**
     * @var array
     */
    protected static $_formats = array(
        4 => 'Y',
        6 => 'Ym',
        8 => 'Ymd',
        10 => 'YmdH',
        12 => 'YmdHi',
        14 => 'YmdHis',
        15 => 'YmdHise',
        17 => 'YmdHisO',
        18 => 'YmdHisO',
        19 => 'YmdHisO',
    );

    /**
     * @var string
     */
    protected $_format = 'c';

   
    public function __construct($value)
    {
        if (!($value instanceof DateTime)) {
            throw new Exception('DateTime required.'); // FIXME: Sometimes strings are passed to this function
        }

        parent::__construct($value);
    }

    public function setFormat(string $format)
    {
        $this->_format = $format;
    }

    public function equals($value)
    {
        if ($value instanceof DateTime) {
            $timestamp = $value->getTimeStamp();
        } else {
            $timestamp = strtotime($value);
        }

        return $timestamp == $this->_value->getTimeStamp();
    }

    public function __toString()
    {
		return (string) ($this->_value->format($this->_format));
    }

    /**
     * @return bool|ElementDate
     */
    public static function parse($content,  $pdf = null,  &$offset = 0)
    {
        if (preg_match('/^\s*\(D\:(?P<name>.*?)\)/s', $content, $match)) {
            $name = $match['name'];
            $name = str_replace("'", '', $name);
            $date = false;

            // Smallest format : Y
            // Full format     : YmdHisP
            if (preg_match('/^\d{4}(\d{2}(\d{2}(\d{2}(\d{2}(\d{2}(Z(\d{2,4})?|[\+-]?\d{2}(\d{2})?)?)?)?)?)?)?$/', $name)) {
                if ($pos = strpos($name, 'Z')) {
                    $name = substr($name, 0, $pos + 1);
                } elseif (18 == strlen($name) && preg_match('/[^\+-]0000$/', $name)) {
                    $name = substr($name, 0, -4).'+0000';
                }

                $format = self::$_formats[strlen($name)];
                $date = DateTime::createFromFormat($format, $name, new DateTimeZone('UTC'));
            } else {
                // special cases
                if (preg_match('/^\d{1,2}-\d{1,2}-\d{4},?\s+\d{2}:\d{2}:\d{2}[\+-]\d{4}$/', $name)) {
                    $name = str_replace(',', '', $name);
                    $format = 'n-j-Y H:i:sO';
                    $date = DateTime::createFromFormat($format, $name, new DateTimeZone('UTC'));
                }
            }

            if (!$date) {
                return false;
            }

            $offset += strpos($content, '(D:') + strlen($match['name']) + 4; // 1 for '(D:' and ')'

            return new self($date);
        }

        return false;
    }
}
