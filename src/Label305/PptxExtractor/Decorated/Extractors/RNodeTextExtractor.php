<?php

namespace Label305\PptxExtractor\Decorated\Extractors;

use DOMElement;
use Label305\PptxExtractor\Decorated\Style;
use Label305\PptxExtractor\Decorated\Style\ColorStyle;
use Label305\PptxExtractor\Decorated\Style\FontStyle;
use Label305\PptxExtractor\Decorated\TextRun;

class RNodeTextExtractor implements TextExtractor {

    /**
     * @param DOMElement $DOMElement
     * The result is the array which contains te sentences
     * @return TextRun[]
     */
    public function extract(DOMElement $DOMElement) {

        $text = null;
        $bold = false;
        $italic = false;
        $underline = false;
        $highLight = false;
        $superscript = false;
        $subscript = false;
        $style = null;
        $result = [];

        foreach ($DOMElement->childNodes as $rChild) {
            $this->parseChildRNode($rChild, $result, $text, $bold, $italic, $underline, $highLight, $superscript, $subscript, $style);
        }

        return $result;
    }

    /**
     * @param $rChild
     * @param array $result
     * @param string|null $text
     * @param bool|null $bold
     * @param bool|null $italic
     * @param bool|null $underline
     * @param bool|null $highLight
     * @param bool|null $superscript
     * @param bool|null $subscript
     * @param Style|null $style
     */
    private function parseChildRNode(
        $rChild,
        array &$result,
        ?string &$text,
        ?bool &$bold,
        ?bool &$italic,
        ?bool &$underline,
        ?bool &$highLight,
        ?bool &$superscript,
        ?bool &$subscript,
        ?Style &$style
    ) {
        if ($rChild instanceof DOMElement) {
            switch ($rChild->nodeName) {
                case "a:rPr" :
                    $this->parseRPRNode($rChild, $bold, $italic, $underline, $highLight, $superscript, $subscript, $style);
                    break;

                case "a:t" :
                    $text = $rChild->nodeValue;
                    break;
            }

            if ($text !== null && strlen($text) !== 0) {
                $result[] = new TextRun($text, $bold, $italic, $underline, $highLight, $superscript, $subscript, $style);

                // Reset
                $style = null;
                $text = null;
            }
        }
    }

    private function parseRPRNode(
        DOMElement $rChild,
        ?bool &$bold,
        ?bool &$italic,
        ?bool &$underline,
        ?bool &$highLight,
        ?bool &$superscript,
        ?bool &$subscript,
        ?Style &$style
    ) {
        $solidFillStyle = null;
        $highlightStyle = null;
        $latinStyle = null;
        $csStyle = null;
        $hasStyle = false;

        foreach ($rChild->childNodes as $propertyNode) {
            if ($propertyNode instanceof DOMElement) {
                $this->parseStyle($propertyNode, $solidFillStyle, $highlightStyle, $latinStyle, $csStyle, $hasStyle);
            }
        }

        if ($hasStyle) {
            $lang = $rChild->getAttribute('lang');
            $underlineStyle = $rChild->getAttribute('u');
            $baseline = $rChild->getAttribute('baseline');

            $style = new Style(
                !empty($lang) ? $lang : null,
                !empty($underlineStyle) ? $underlineStyle : null,
                !empty($baseline) ? $baseline : null,
                $solidFillStyle,
                $highlightStyle,
                $latinStyle,
                $csStyle
            );
        }

        $bold = !empty($rChild->getAttribute('b'));
        $italic = !empty($rChild->getAttribute('i'));
        $underline = !empty($rChild->getAttribute('u'));
        $highLight = $highlightStyle !== null;
        $superscript = !empty($baseline) && (int) $baseline > 0;
        $subscript = !empty($baseline) && (int) $baseline < 0;
    }

    /**
     * @param DOMElement $propertyNode
     * @param ColorStyle|null $solidFillStyle
     * @param ColorStyle|null $highlightStyle
     * @param FontStyle|null $latinStyle
     * @param FontStyle|null $csStyle
     * @param bool $hasStyle
     */
    private function parseStyle(
        DOMElement $propertyNode,
        ?ColorStyle &$solidFillStyle,
        ?ColorStyle &$highlightStyle,
        ?FontStyle &$latinStyle,
        ?FontStyle &$csStyle,
        bool &$hasStyle
    ) {
        switch ($propertyNode->nodeName) {
            case "a:solidFill" :
                $solidFillStyle = (new ColorStyleExtractor())->extract($propertyNode);
                $hasStyle = true;
                break;

            case "a:highlight" :
                $highlightStyle = (new ColorStyleExtractor())->extract($propertyNode);
                $hasStyle = true;
                break;

            case "a:latin" :
                $latinStyle = (new FontStyleExtractor())->extract($propertyNode);
                $hasStyle = true;
                break;

            case "a:cs" :
                $csStyle = (new FontStyleExtractor())->extract($propertyNode);
                $hasStyle = true;
                break;
        }
    }
}