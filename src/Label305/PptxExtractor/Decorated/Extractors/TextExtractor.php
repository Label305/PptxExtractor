<?php

namespace Label305\PptxExtractor\Decorated\Extractors;

use DOMElement;
use Label305\PptxExtractor\Decorated\TextRun;

interface TextExtractor {

    /**
     * @param DOMElement $DOMElement
     * The result is the array which contains te sentences
     * @return TextRun[]
     */
    public function extract(DOMElement $DOMElement);
}