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
    public function extract(DOMElement $DOMElement): ColorStyle {
        
        $properties = [
            'schemeClr' => $schemeClr = null,
            'lumMod' => $lumMod = null,
            'lumOff' => $lumOff = null,
            'srgbClr' => $srgbClr = null,
            'prstClr' => $prstClr = null,
            'scrgbClr' => $scrgbClr = null,
            'hslClr' => $hslClr = null,
            'sysClr' => $sysClr = null
        ];
        
        if ($DOMElement->childNodes !== null) {
            foreach ($DOMElement->childNodes as $colorNode) {
                if ($colorNode instanceof DOMElement ) {

                    $propertyName = str_replace('a:', '', $colorNode->nodeName);

                    if (in_array($propertyName, array_keys($properties))) {
                        $$propertyName = $colorNode->getAttribute('val');
                        if ($$propertyName !== null && $$propertyName !== "" && $colorNode->childNodes !== null) {
                            foreach ($colorNode->childNodes as $schemeClrChildNode) {
                                if ($schemeClrChildNode instanceof DOMElement && $schemeClrChildNode->nodeName === "a:lumMod") {
                                    $lumMod = $schemeClrChildNode->getAttribute('val');
                                } elseif ($schemeClrChildNode instanceof DOMElement && $schemeClrChildNode->nodeName === "a:lumOff") {
                                    $lumOff = $schemeClrChildNode->getAttribute('val');
                                }
                            }
                        }
                    }
                }
            }
        }

        $colorStyle = new ColorStyle();
        $colorStyle->schemeClr = $schemeClr !== null && $schemeClr !== "" ? $schemeClr : null;
        $colorStyle->lumMod = $lumMod !== null && $lumMod !== "" ? $lumMod : null;
        $colorStyle->lumOff = $lumOff !== null && $lumOff !== "" ? $lumOff : null;
        $colorStyle->srgbClr = $srgbClr !== null && $srgbClr !== "" ? $srgbClr : null;
        $colorStyle->scrgbClr = $scrgbClr !== null && $scrgbClr !== "" ? $scrgbClr : null;
        $colorStyle->prstClr = $prstClr !== null && $prstClr !== "" ? $prstClr : null;
        $colorStyle->hslClr = $hslClr !== null && $hslClr !== "" ? $hslClr : null;
        $colorStyle->sysClr = $sysClr !== null && $sysClr !== "" ? $sysClr : null;

        return $colorStyle;
    }
}