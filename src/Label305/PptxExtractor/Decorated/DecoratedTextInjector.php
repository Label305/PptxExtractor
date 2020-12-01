<?php

namespace Label305\PptxExtractor\Decorated;

use DOMNode;
use DOMText;
use Label305\PptxExtractor\Injector;
use Label305\PptxExtractor\PptxFileException;
use Label305\PptxExtractor\PptxHandler;
use Label305\PptxExtractor\PptxParsingException;

class DecoratedTextInjector extends PptxHandler implements Injector {

    /**
     * @param array $mapping
     * @param string $fileToInjectLocationPath
     * @param string $saveLocationPath
     * @throws PptxParsingException
     * @throws PptxFileException
     * @return void
     */
    public function injectMappingAndCreateNewFile(array $mapping, string $fileToInjectLocationPath, string $saveLocationPath)
    {
        $preparedSlides = $this->prepareDocumentForReading($fileToInjectLocationPath);

        foreach ($preparedSlides as $key => $prepared) {
            $this->assignMappedValues($prepared['dom']->documentElement, $mapping);
        }

        $this->saveDocument($preparedSlides, $saveLocationPath);
    }

    /**
     * @param DOMNode $node
     * @param array $mapping
     */
    protected function assignMappedValues(DOMNode $node, array $mapping)
    {
        if ($node instanceof DOMText) {
            $results = [];
            preg_match("/%[0-9]*%/", $node->nodeValue, $results);

            if (count($results) > 0) {
                $key = trim($results[0], '%');

                $parent = $node->parentNode;
                foreach ($mapping[$key] as $text) {
                    $fragment = $parent->ownerDocument->createDocumentFragment();
                    $fragment->appendXML($text->toPptxXML());
                    $parent->insertBefore($fragment, $node);
                }
                $parent->removeChild($node);
            }
        }

        if ($node->childNodes !== null) {
            foreach ($node->childNodes as $child) {
                $this->assignMappedValues($child, $mapping);
            }
        }
    }
}