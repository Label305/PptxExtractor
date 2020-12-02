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
     * @param bool $bold
     * @param bool $italic
     * @param bool $underline
     * @param bool $highlight
     * @param bool $superscript
     * @param bool $subscript
     */
    public function fillWithHTMLDom(
        DOMNode $node,
        bool $bold = false,
        bool $italic = false,
        bool $underline = false,
        bool $highlight = false,
        bool $superscript = false,
        bool $subscript = false
    ) {
        if ($node instanceof DOMText) {
            $this[] = new TextRun($node->nodeValue, $bold, $italic, $underline, $highlight, $superscript, $subscript);

        } else {
            if ($node->childNodes !== null) {

                if ($node->nodeName == 'b' || $node->nodeName == 'strong') {
                    $bold = true;
                }
                if ($node->nodeName == 'i' || $node->nodeName == 'em') {
                    $italic = true;
                }
                if ($node->nodeName == 'u') {
                    $underline = true;
                }
                if ($node->nodeName == 'mark') {
                    $highlight = true;
                }
                if ($node->nodeName == 'sup') {
                    $superscript = true;
                }
                if ($node->nodeName == 'sub') {
                    $subscript = true;
                }
                foreach ($node->childNodes as $child) {
                    $this->fillWithHTMLDom($child, $bold, $italic, $underline, $highlight, $superscript, $subscript);
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

        $boldIsActive = false;
        $italicIsActive = false;
        $underlineIsActive = false;
        $highlightActive = false;
        $superscriptActive = false;
        $subscriptActive = false;

        for ($i = 0; $i < count($this); $i++) {

            $sentence = $this[$i];

            $openBold = false;
            if ($sentence->bold && !$boldIsActive) {
                $boldIsActive = true;
                $openBold = true;
            }

            $openItalic = false;
            if ($sentence->italic && !$italicIsActive) {
                $italicIsActive = true;
                $openItalic = true;
            }

            $openUnderline = false;
            if ($sentence->underline && !$underlineIsActive) {
                $underlineIsActive = true;
                $openUnderline = true;
            }

            $openHighlight = false;
            if ($sentence->highlight && !$highlightActive) {
                $highlightActive = true;
                $openHighlight = true;
            }

            $openSuperscript = false;
            if ($sentence->superscript && !$superscriptActive) {
                $superscriptActive = true;
                $openSuperscript = true;
            }

            $openSubscript = false;
            if ($sentence->subscript && !$subscriptActive) {
                $subscriptActive = true;
                $openSubscript = true;
            }

            $nextSentence = ($i + 1 < count($this)) ? $this[$i + 1] : null;
            $closeBold = false;
            if ($nextSentence === null || (!$nextSentence->bold && $boldIsActive)) {
                $boldIsActive = false;
                $closeBold = true;
            }

            $closeItalic = false;
            if ($nextSentence === null || (!$nextSentence->italic && $italicIsActive)) {
                $italicIsActive = false;
                $closeItalic = true;
            }

            $closeUnderline = false;
            if ($nextSentence === null || (!$nextSentence->underline && $underlineIsActive)) {
                $underlineIsActive = false;
                $closeUnderline = true;
            }

            $closeHighlight = false;
            if ($nextSentence === null || (!$nextSentence->highlight && $highlightActive)) {
                $highlightActive = false;
                $closeHighlight = true;
            }

            $closeSuperscript = false;
            if ($nextSentence === null || (!$nextSentence->superscript && $superscriptActive)) {
                $superscriptActive = false;
                $closeSuperscript = true;
            }

            $closeSubscript = false;
            if ($nextSentence === null || (!$nextSentence->subscript && $subscriptActive)) {
                $subscriptActive = false;
                $closeSubscript = true;
            }

            $result .= $sentence->toHTML($openBold, $openItalic, $openUnderline, $openHighlight, $openSuperscript,
                $openSubscript, $closeBold, $closeItalic, $closeUnderline, $closeHighlight, $closeSuperscript,
                $closeSubscript);
        }

        return $result;
    }
}
