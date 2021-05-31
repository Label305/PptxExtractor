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
     * @var string|null
     */
    private $direction;

    /**
     * @param string|null $direction
     * @throws \Exception
     */
    public function setDirection(string $direction) {
        if (!in_array($direction, ['ltr', 'rtl'])) {
            throw new \Exception('Direction should be ltr or rtl');
        }
        $this->direction = $direction;
    }

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

                if ($this->direction !== null) {
                    $this->addParagraphDirection($parent);
                }

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

    private function addParagraphDirection(DOMNode $parent)
    {
        $fragment = $parent->ownerDocument->createDocumentFragment();
        $direction = null;
        if ($this->direction === 'ltr') {
            $direction = 'l';
        } elseif ($this->direction === 'rtl') {
            $direction = 'r';
        }
        if ($direction !== null) {
            foreach ($parent->childNodes as $childNode) {
                if ($childNode->nodeName === 'a:pPr') {
                    $parent->removeChild($childNode);
                }
            }
            $fragment->appendXML('<a:pPr algn="' . $direction . '" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" />');
            $parent->appendChild($fragment);
        }
    }
}