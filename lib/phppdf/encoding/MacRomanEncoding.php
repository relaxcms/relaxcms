<?php

/**
 * Class MacRomanEncoding
 */
class MacRomanEncoding extends AbstractEncoding
{
    public function getTranslations()
    {
        $encoding =
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef '.
          'space exclam quotedbl numbersign dollar percent ampersand quotesingle '.
          'parenleft parenright asterisk plus comma minus period slash '.
          'zero one two three four five six seven '.
          'eight nine colon semicolon less equal greater question '.
          'at A B C D E F G '.
          'H I J K L M N O '.
          'P Q R S T U V W '.
          'X Y Z bracketleft backslash bracketright asciicircum underscore '.
          'grave a b c d e f g '.
          'h i j k l m n o '.
          'p q r s t u v w '.
          'x y z braceleft bar braceright asciitilde .notdef '.
          'Adieresis Aring Ccedilla Eacute Ntilde Odieresis Udieresis aacute '.
          'agrave acircumflex adieresis atilde aring ccedilla eacute egrave '.
          'ecircumflex edieresis iacute igrave icircumflex idieresis ntilde oacute '.
          'ograve ocircumflex odieresis otilde uacute ugrave ucircumflex udieresis '.
          'dagger degree cent sterling section bullet paragraph germandbls '.
          'registered copyright trademark acute dieresis notequal AE Oslash '.
          'infinity plusminus lessequal greaterequal yen mu partialdiff summation '.
          'Pi pi integral ordfeminine ordmasculine Omega ae oslash '.
          'questiondown exclamdown logicalnot radical florin approxequal delta guillemotleft '.
          'guillemotright ellipsis space Agrave Atilde Otilde OE oe '.
          'endash emdash quotedblleft quotedblright quoteleft quoteright divide lozenge '.
          'ydieresis Ydieresis fraction currency guilsinglleft guilsinglright fi fl '.
          'daggerdbl periodcentered quotesinglbase quotedblbase perthousand Acircumflex Ecircumflex Aacute '.
          'Edieresis Egrave Iacute Icircumflex Idieresis Igrave Oacute Ocircumflex '.
          'heart Ograve Uacute Ucircumflex Ugrave dotlessi circumflex tilde '.
          'macron breve dotaccent ring cedilla hungarumlaut ogonek caron';

        return explode(' ', $encoding);
    }
}
