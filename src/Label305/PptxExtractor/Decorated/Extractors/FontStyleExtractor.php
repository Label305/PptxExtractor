<?php

namespace Label305\PptxExtractor\Decorated\Extractors;

use DOMElement;
use Label305\PptxExtractor\Decorated\Style\FontStyle;

class FontStyleExtractor {

    /**
     * @param DOMElement $DOMElement
     * The result is the array which contains te sentences
     * @return FontStyle
     */
    public function extract(DOMElement $DOMElement) {

        $typeface = $DOMElement->getAttribute('typeface');
        $panose = $DOMElement->getAttribute('panose');
        $pitchFamily = $DOMElement->getAttribute('pitchFamily');
        $charset = $DOMElement->getAttribute('charset');

        $fontStyle = new FontStyle();
        $fontStyle->typeface = $typeface !== null && $typeface !== "" ? $typeface : null;
        $fontStyle->panose = $panose !== null && $panose !== "" ? $panose : null;
        $fontStyle->pitchFamily = $pitchFamily !== null && $pitchFamily !== "" ? $pitchFamily : null;
        $fontStyle->charset = $charset !== null && $charset !== "" ? $charset : null;

        return $fontStyle;
    }
}