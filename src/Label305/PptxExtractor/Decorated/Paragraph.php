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
     * @var int
     */
    protected $nextTagIdentifier = 0;

    /**
     * Convenience constructor for the user of the API
     * Strings with <br> <b> <i> <u> <sub> <sup< <mark> and <font> tags are supported.
     * @param $html string
     * @param Paragraph|null $originalParagraph
     * @return Paragraph
     */
    public static function paragraphWithHTML(string $html, ?Paragraph $originalParagraph = null)
    {
        $html = "<html>" . strip_tags($html, '<br /><br><b><strong><em><i><u><mark><sub><sup><font>') . "</html>";
        $html = str_replace("<br>", "<br />", $html);
        $html = str_replace("&nbsp;", " ", $html);
        $htmlDom = new DOMDocument;
        @$htmlDom->loadXml(preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', html_entity_decode($html)));


        $paragraph = new Paragraph();
        if ($htmlDom->documentElement !== null) {
            $paragraph->fillWithHTMLDom($htmlDom->documentElement, $originalParagraph);
        }
        return $paragraph;
    }

    /**
     * Recursive method to fill paragraph from HTML data
     *
     * @param DOMNode $node
     * @param Paragraph|null $originalParagraph
     * @param bool $bold
     * @param bool $italic
     * @param bool $underline
     * @param bool $highlight
     * @param bool $superscript
     * @param bool $subscript
     */
    public function fillWithHTMLDom(
        DOMNode $node,
        ?Paragraph $originalParagraph = null,
        bool $bold = false,
        bool $italic = false,
        bool $underline = false,
        bool $highlight = false,
        bool $superscript = false,
        bool $subscript = false
    ) {
        if ($node instanceof DOMText) {

            $originalStyle = null;
            if ($originalParagraph !== null) {
                $originalStyle = $this->getOriginalStyle($node, $originalParagraph);
            }
            $this[] = new TextRun($node->nodeValue, $bold, $italic, $underline, $highlight, $superscript, $subscript, $originalStyle);
            $this->nextTagIdentifier++;

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
                if ($node->nodeName == 'sub') {
                    $subscript = true;
                }
                
                foreach ($node->childNodes as $key => $child) {
                    $this->fillWithHTMLDom($child, $originalParagraph, $bold, $italic, $underline, $highlight, $superscript, $subscript);
                }
            }
        }
    }

    /**
     * @param DOMText $node
     * @param Paragraph $originalParagraph
     * @return Style|null
     */
    private function getOriginalStyle(DOMText $node, Paragraph $originalParagraph)
    {
        $originalStyle = null;
        if (array_key_exists($this->nextTagIdentifier, $originalParagraph)) {
            // Sometimes we extract a single space, but in the Paragraph the space is at the beginning of the sentence
            $startsWithSpace = strlen($node->nodeValue) > strlen(ltrim($node->nodeValue));
            if ($startsWithSpace && strlen(ltrim($originalParagraph[$this->nextTagIdentifier]->text)) === 0) {
                // When the current paragraph has no lengt it may be the space at the beginning
                $this->nextTagIdentifier++;
                // Return the next paragraph style
                if (array_key_exists($this->nextTagIdentifier, $originalParagraph)) {
                    $originalStyle = $originalParagraph[$this->nextTagIdentifier]->style;
                }
            } else {
                $originalStyle = $originalParagraph[$this->nextTagIdentifier]->style;
            }
        }
        return $originalStyle;
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

            $textRun = $this[$i];

            $openBold = false;
            if ($textRun->bold && !$boldIsActive) {
                $boldIsActive = true;
                $openBold = true;
            }

            $openItalic = false;
            if ($textRun->italic && !$italicIsActive) {
                $italicIsActive = true;
                $openItalic = true;
            }

            $openUnderline = false;
            if ($textRun->underline && !$underlineIsActive) {
                $underlineIsActive = true;
                $openUnderline = true;
            }

            $openHighlight = false;
            if ($textRun->highlight && !$highlightActive) {
                $highlightActive = true;
                $openHighlight = true;
            }

            $openSuperscript = false;
            if ($textRun->superscript && !$superscriptActive) {
                $superscriptActive = true;
                $openSuperscript = true;
            }

            $openSubscript = false;
            if ($textRun->subscript && !$subscriptActive) {
                $subscriptActive = true;
                $openSubscript = true;
            }

            $nextTextRun = ($i + 1 < count($this)) ? $this[$i + 1] : null;
            $closeBold = false;
            if ($nextTextRun === null || (!$nextTextRun->bold && $boldIsActive)) {
                $boldIsActive = false;
                $closeBold = true;
            }

            $closeItalic = false;
            if ($nextTextRun === null || (!$nextTextRun->italic && $italicIsActive)) {
                $italicIsActive = false;
                $closeItalic = true;
            }

            $closeUnderline = false;
            if ($nextTextRun === null || (!$nextTextRun->underline && $underlineIsActive)) {
                $underlineIsActive = false;
                $closeUnderline = true;
            }

            $closeHighlight = false;
            if ($nextTextRun === null || (!$nextTextRun->highlight && $highlightActive)) {
                $highlightActive = false;
                $closeHighlight = true;
            }

            $closeSuperscript = false;
            if ($nextTextRun === null || (!$nextTextRun->superscript && $superscriptActive)) {
                $superscriptActive = false;
                $closeSuperscript = true;
            }

            $closeSubscript = false;
            if ($nextTextRun === null || (!$nextTextRun->subscript && $subscriptActive)) {
                $subscriptActive = false;
                $closeSubscript = true;
            }

            $result .= $textRun->toHTML($openBold, $openItalic, $openUnderline, $openHighlight, $openSuperscript,
                $openSubscript, $closeBold, $closeItalic, $closeUnderline, $closeHighlight, $closeSuperscript,
                $closeSubscript);
        }

        return $result;
    }
}
