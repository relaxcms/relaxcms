<?php


/**
 * Class ISOLatin9Encoding
 */
class ISOLatin9Encoding extends AbstractEncoding
{
    public function getTranslations()
    {
        $encoding =
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          'space exclam quotedbl numbersign dollar percent ampersand quoteright '.
          'parenleft parenright asterisk plus comma minus period slash zero one '.
          'two three four five six seven eight nine colon semicolon less equal '.
          'greater question at A B C D E F G H I J K L M N O P Q R S T U V W X '.
          'Y Z bracketleft backslash bracketright asciicircum underscore '.
          'quoteleft a b c d e f g h i j k l m n o p q r s t u v w x y z '.
          'braceleft bar braceright asciitilde .notdef .notdef .notdef .notdef '.
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          '.notdef .notdef .notdef .notdef .notdef dotlessi grave acute '.
          'circumflex tilde macron breve dotaccent dieresis .notdef ring '.
          'cedilla .notdef hungarumlaut ogonek caron space exclamdown cent '.
          'sterling Euro yen Scaron section scaron copyright '.
          'ordfeminine guillemotleft logicalnot hyphen registered macron degree '.
          'plusminus twosuperior threesuperior Zcaron mu paragraph '.
          'periodcentered zcaron onesuperior ordmasculine guillemotright '.
          'OE oe Ydieresis questiondown Agrave Aacute '.
          'Acircumflex Atilde Adieresis Aring AE Ccedilla Egrave Eacute '.
          'Ecircumflex Edieresis Igrave Iacute Icircumflex Idieresis Eth Ntilde '.
          'Ograve Oacute Ocircumflex Otilde Odieresis multiply Oslash Ugrave '.
          'Uacute Ucircumflex Udieresis Yacute Thorn germandbls agrave aacute '.
          'acircumflex atilde adieresis aring ae ccedilla egrave eacute '.
          'ecircumflex edieresis igrave iacute icircumflex idieresis eth ntilde '.
          'ograve oacute ocircumflex otilde odieresis divide oslash ugrave '.
          'uacute ucircumflex udieresis yacute thorn ydieresis';

        return explode(' ', $encoding);
    }
}
