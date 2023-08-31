<?php


/**
 * Class ElementStruct
 */
class ElementStruct extends Element
{
    /**
     * @return false|Header
     */
	public static function parse($content, $pdf = null, &$offset = 0)
    {
        if (preg_match('/^\s*<<(?P<struct>.*)/is', $content)) {
            preg_match_all('/(.*?)(<<|>>)/s', trim($content), $matches);

            $level = 0;
            $sub = '';
            foreach ($matches[0] as $part) {
                $sub .= $part;
                $level += (false !== strpos($part, '<<') ? 1 : -1);
                if ($level <= 0) {
                    break;
                }
            }

            $offset += strpos($content, '<<') + strlen(rtrim($sub));

            // Removes '<<' and '>>'.
            $sub = trim((string) preg_replace('/^\s*<<(.*)>>\s*$/s', '\\1', $sub));

            $position = 0;
			$elements = Element::parse($sub, $pdf, $position);

			return new Header($elements, $pdf);
        }

        return false;
    }
}
