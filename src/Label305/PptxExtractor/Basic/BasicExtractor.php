<?php

namespace Label305\PptxExtractor\Basic;


use DOMDocument;
use DOMNode;
use DOMText;
use Label305\PptxExtractor\PptxFileException;
use Label305\PptxExtractor\PptxHandler;
use Label305\PptxExtractor\PptxParsingException;
use Label305\PptxExtractor\Extractor;


class BasicExtractor extends PptxHandler implements Extractor {

    /**
     * @var int
     */
    protected $nextTagIdentifier;

    /**
     * @param $originalFilePath
     * @param $mappingFileSaveLocationPath
     * @throws PptxParsingException
     * @throws PptxFileException
     */
    public function extractStringsAndCreateMappingFile($originalFilePath, $mappingFileSaveLocationPath)
    {
        $preparedSlides = $this->prepareDocumentForReading($originalFilePath);

        $mappings = [];
        $this->nextTagIdentifier = 0;
        foreach ($preparedSlides as $key => $prepared) {
            $mappings[] = $this->replaceAndMapValues($prepared['dom']->documentElement);
        }

        $this->saveDocument($preparedSlides, $mappingFileSaveLocationPath);

        $result = [];
        foreach ($mappings as $map) {
            foreach ($map as $item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Override this method to make a more complex replace and mapping
     *
     * @param DOMNode $node
     * @return array returns the mapping array
     */
    protected function replaceAndMapValues(DOMNode $node)
    {
        $result = [];

        if ($node instanceof DOMText) {
            $result[$this->nextTagIdentifier] = $node->nodeValue;
            $node->nodeValue = "%".$this->nextTagIdentifier."%";
            $this->nextTagIdentifier++;
        }

        if ($node->childNodes !== null) {
            foreach ($node->childNodes as $child) {
                $result = array_merge(
                    $result,
                    $this->replaceAndMapValues($child)
                );
            }
        }

        return $result;
    }

}