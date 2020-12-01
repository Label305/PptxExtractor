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
        $style = null;
        $result = [];

        foreach ($DOMElement->childNodes as $rChild) {
            $this->parseChildRNode($rChild, $result, $text, $style);
        }

        return $result;
    }

    /**
     * @param $rChild
     * @param array $result
     * @param string|null $text
     * @param Style|null $style
     */
    private function parseChildRNode(
        $rChild,
        array &$result,
        ?string &$text,
        ?Style &$style
    ) {
        $lang = null;
        $underline = null;
        $bold = null;
        $italic = null;
        $baseline = null;

        if ($rChild instanceof DOMElement) {
            switch ($rChild->nodeName) {
                case "a:rPr" :
                    $solidFill = null;
                    $highlight = null;
                    $latin = null;
                    $cs = null;
                    $hasStyle = false;

                    foreach ($rChild->childNodes as $propertyNode) {
                        if ($propertyNode instanceof DOMElement) {
                            $this->parseStyle(
                                $propertyNode,
                                $solidFill,
                                $highlight,
                                $latin,
                                $cs,
                                $hasStyle
                            );
                        }
                    }
                    if ($hasStyle) {
                        $style = new Style(
                            $rChild->getAttribute('lang'),
                            $rChild->getAttribute('u'),
                            $rChild->getAttribute('b'),
                            $rChild->getAttribute('i'),
                            $rChild->getAttribute('baseline'),
                            $solidFill,
                            $highlight,
                            $latin,
                            $cs
                        );
                    }
                    break;

                case "a:t" :
                    $text = $rChild->nodeValue;
                    break;
            }


            if ($text !== null && strlen($text) !== 0) {
                $result[] = new TextRun($text, $style);

                // Reset
                $style = null;
                $text = null;
            }
        }
    }

    /**
     * @param DOMElement $propertyNode
     * @param ColorStyle|null $solidFill
     * @param ColorStyle|null $highlight
     * @param FontStyle|null $latin
     * @param FontStyle|null $cs
     * @param bool $hasStyle
     */
    private function parseStyle(
        DOMElement $propertyNode,
        ?ColorStyle &$solidFill,
        ?ColorStyle &$highlight,
        ?FontStyle &$latin,
        ?FontStyle &$cs,
        bool &$hasStyle
    ) {
        if (in_array($propertyNode->nodeName,  ["a:solidFill", "a:highlight"])) {
            $colorStyle = new ColorStyle();
            if ($propertyNode->childNodes !== null) {
                foreach ($propertyNode->childNodes as $colorNode) {
                    if ($colorNode instanceof DOMElement && $colorNode->nodeName === "a:schemeClr") {
                        $colorStyle->schemeClr = $colorNode->getAttribute('val');
                        if ($colorNode->childNodes !== null) {
                            foreach ($colorNode->childNodes as $schemeClrChildNode) {
                                if ($schemeClrChildNode instanceof DOMElement && $schemeClrChildNode->nodeName === "a:lumMod") {
                                    $colorStyle->schemeClrLumMod = $schemeClrChildNode->getAttribute('val');
                                }
                            }
                        }
                    } elseif ($colorNode instanceof DOMElement && $colorNode->nodeName === "a:srgbClr") {
                        $colorStyle->srgbClr = $colorNode->getAttribute('val');
                    }
                }
                if ($propertyNode->nodeName === "a:solidFill") {
                    $solidFill = $colorStyle;
                } elseif ($propertyNode->nodeName === "a:solidFill") {
                    $highlight = $colorStyle;
                }
                $hasStyle = true;
            }

        } elseif (in_array($propertyNode->nodeName,  ["a:latin", "a:cs"])) {
            $fontStyle = new FontStyle();
            $fontStyle->typeface = $propertyNode->getAttribute('typeface');
            $fontStyle->panose = $propertyNode->getAttribute('panose');
            $fontStyle->pitchFamily = $propertyNode->getAttribute('pitchFamily');
            $fontStyle->charset = $propertyNode->getAttribute('charset');

            if ($propertyNode->nodeName === "a:latin") {
                $latin = $fontStyle;
            } elseif ($propertyNode->nodeName === "a:cs") {
                $cs = $fontStyle;
            }
            $hasStyle = true;
        }
    }
}