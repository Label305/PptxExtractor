<?php

namespace Label305\PptxExtractor\Basic;

use DOMNode;
use DOMText;
use Label305\PptxExtractor\PptxFileException;
use Label305\PptxExtractor\PptxHandler;
use Label305\PptxExtractor\PptxParsingException;
use Label305\PptxExtractor\Injector;

class BasicInjector extends PptxHandler implements Injector {

    /**
     * @param $mapping
     * @param $fileToInjectLocationPath
     * @param $saveLocationPath
     * @throws PptxFileException
     * @throws PptxParsingException
     * @return void
     */
    public function injectMappingAndCreateNewFile($mapping, $fileToInjectLocationPath, $saveLocationPath)
    {
        $preparedSlides = $this->prepareDocumentForReading($fileToInjectLocationPath);

        foreach ($preparedSlides as $key => $prepared) {
            $this->assignMappedValues($prepared['dom']->documentElement, $mapping);
        }

        $this->saveDocument($preparedSlides, $saveLocationPath);
    }

    /**
     * @param DOMNode $node
     * @param $mapping
     */
    protected function assignMappedValues(DOMNode $node, $mapping)
    {
        if ($node instanceof DOMText) {
            $results = [];
            preg_match("/%[0-9]*%/", $node->nodeValue, $results);

            if (count($results) > 0) {
                $key = trim($results[0], '%');
                $node->nodeValue = $mapping[$key];
            }
        }

        if ($node->childNodes !== null) {
            foreach ($node->childNodes as $child) {
                $this->assignMappedValues($child, $mapping);
            }
        }
    }
}