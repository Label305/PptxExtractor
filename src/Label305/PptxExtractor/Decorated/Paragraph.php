<?php

namespace Label305\PptxExtractor\Decorated;

use ArrayObject;
use DOMDocument;
use DOMNode;
use DOMText;

/**
 * Class TextGroup
 * @package Label305\PptxExtractor\Decorated
 *
 * Represents a list of <a:p> objects in the docx format. Does not contain
 * <w:p> data. That data is preserved in the extracted document.
 */
class Paragraph extends ArrayObject
{

    /**
     * Convenience constructor for the user of the API
     * Strings with <br> <b> <i> <u> <sub> <sup< and <mark> tags are supported.
     * @param $html string
     * @return Paragraph
     */
    public static function paragraphWithHTML($html)
    {
        $html = "<html>" . strip_tags($html, '<br /><br><b><strong><em><i><u><mark><sub><sup>') . "</html>";
        $html = str_replace("<br>", "<br />", $html);
        $html = str_replace("&nbsp;", " ", $html);
        $htmlDom = new DOMDocument;
        @$htmlDom->loadXml(preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', html_entity_decode($html)));

        $paragraph = new Paragraph();
        if ($htmlDom->documentElement !== null) {
            $paragraph->fillWithHTMLDom($htmlDom->documentElement);
        }

        return $paragraph;
    }

    /**
     * Recursive method to fill paragraph from HTML data
     *
     * @param DOMNode $node
     * @param int $br
     * @param bool $bold
     * @param bool $italic
     * @param bool $underline
     * @param bool $superscript
     * @param bool $subscript
     */
    public function fillWithHTMLDom(
        DOMNode $node,
        $br = 0,
        $bold = false,
        $italic = false,
        $underline = false,
        $superscript = false,
        $subscript = false
    ) {
        if ($node instanceof DOMText) {

            $this[] = new TextRun($node->nodeValue);

        } else {
            if ($node->childNodes !== null) {

//                if ($node->nodeName == 'b' || $node->nodeName == 'strong') {
//                    $bold = true;
//                }
//                if ($node->nodeName == 'i' || $node->nodeName == 'em') {
//                    $italic = true;
//                }
//                if ($node->nodeName == 'u') {
//                    $underline = true;
//                }
//                if ($node->nodeName == 'sup') {
//                    $superscript = true;
//                }
//                if ($node->nodeName == 'sub') {
//                    $subscript = true;
//                }

                foreach ($node->childNodes as $child) {
                    $this->fillWithHTMLDom($child, $br, $bold, $italic, $underline, $superscript, $subscript);
                }
            }
        }
    }

    /**
     * Give me a paragraph HTML
     *
     * @return string
     */
    public function toHTML()
    {
        $result = '';
        for ($i = 0; $i < count($this); $i++) {
            $text = $this[$i];
            $result .= $text->toHTML();
        }

        return $result;
    }
}
