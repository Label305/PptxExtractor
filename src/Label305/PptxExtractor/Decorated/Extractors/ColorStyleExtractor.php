<?php

namespace Label305\PptxExtractor\Decorated\Extractors;

use DOMElement;
use Label305\PptxExtractor\Decorated\Style\ColorStyle;

class ColorStyleExtractor {

    /**
     * @param DOMElement $DOMElement
     * The result is the array which contains te sentences
     * @return ColorStyle
     */
    public function extract(DOMElement $DOMElement) {
        
        $schemeClrVal = null;
        $lumModVal = null;
        $srgbClr = null;
        if ($DOMElement->childNodes !== null) {
            foreach ($DOMElement->childNodes as $colorNode) {
                if ($colorNode instanceof DOMElement && $colorNode->nodeName === "a:schemeClr") {
                    $schemeClrVal = $colorNode->getAttribute('val');
                    if ($schemeClrVal !== null && $schemeClrVal !== "" && $colorNode->childNodes !== null) {
                        foreach ($colorNode->childNodes as $schemeClrChildNode) {
                            if ($schemeClrChildNode instanceof DOMElement && $schemeClrChildNode->nodeName === "a:lumMod") {
                                $lumModVal = $schemeClrChildNode->getAttribute('val');
                            }
                        }
                    }
                } elseif ($colorNode instanceof DOMElement && $colorNode->nodeName === "a:srgbClr") {
                    $srgbClr = $colorNode->getAttribute('val');
                }
            }
        }

        $colorStyle = new ColorStyle();
        $colorStyle->schemeClr = $schemeClrVal !== null && $schemeClrVal !== "" ? $schemeClrVal : null;
        $colorStyle->schemeClrLumMod = $lumModVal !== null && $lumModVal !== "" ? $lumModVal : null;
        $colorStyle->srgbClr = $srgbClr !== null && $srgbClr !== "" ? $srgbClr : null;

        return $colorStyle;
    }
}