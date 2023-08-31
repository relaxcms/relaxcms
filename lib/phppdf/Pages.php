<?php

/**
 * Class Pages
 */
class Pages extends PDFObject
{
    /**
     * @todo Objects other than Pages or Page might need to be treated specifically in order to get Page objects out of them,
     *
     * @see https://github.com/smalot/pdfparser/issues/331
     */
    public function getPages($deep = false)
    {
        if (!$this->has('Kids')) {
            return array();
        }

        /** @var ElementArray $kidsElement */
        $kidsElement = $this->get('Kids');

        if (!$deep) {
            return $kidsElement->getContent();
        }

        $kids = $kidsElement->getContent();
		$pages = array();

        foreach ($kids as $kid) {
            if ($kid instanceof self) {
                $pages = array_merge($pages, $kid->getPages(true));
            } elseif ($kid instanceof Page) {
                $pages[] = $kid;
            }
        }

        return $pages;
    }
}
