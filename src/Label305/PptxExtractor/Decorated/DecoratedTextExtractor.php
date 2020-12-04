<?php

namespace Label305\PptxExtractor\Decorated;


use DOMElement;
use DOMNode;
use DOMText;
use Label305\PptxExtractor\Decorated\Extractors\RNodeTextExtractor;
use Label305\PptxExtractor\PptxFileException;
use Label305\PptxExtractor\PptxHandler;
use Label305\PptxExtractor\PptxParsingException;
use Label305\PptxExtractor\Extractor;


class DecoratedTextExtractor extends PptxHandler implements Extractor {

    /**
     * @var int
     */
    protected $nextTagIdentifier;

    /**
     * @param $originalFilePath
     * @param $mappingFileSaveLocationPath
     * @return array
     * @throws PptxParsingException
     * @throws PptxFileException
     */
    public function extractStringsAndCreateMappingFile(string $originalFilePath, string $mappingFileSaveLocationPath): array
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

    protected function replaceAndMapValues(DOMNode $node) {
        $result = [];

        if ($node instanceof DOMElement && $node->nodeName == "a:p") {
            $this->replaceAndMapValuesForParagraph($node, $result);

        } else {
            if ($node->childNodes !== null) {
                foreach ($node->childNodes as $child) {
                    $result = array_merge(
                        $result,
                        $this->replaceAndMapValues($child)
                    );
                }
            }
        }

        return $result;
    }

    /**
     * @param DOMNode $DOMNode
     * @param $result
     * @return array
     */
    protected function replaceAndMapValuesForParagraph(DOMNode $DOMNode, &$result)
    {
        $firstTextChild = null;
        $otherNodes = [];
        $parts = new Paragraph();

        if ($DOMNode->childNodes !== null) {
            foreach ($DOMNode->childNodes as $DOMNodeChild) {

                if ($DOMNodeChild instanceof DOMElement && $DOMNodeChild->nodeName === "a:t") {
                    $parts[] = [new TextRun($DOMNodeChild->nodeValue)];
                    $firstTextChild = $DOMNodeChild;

                } elseif ($DOMNodeChild instanceof DOMElement && in_array($DOMNodeChild->nodeName, ["a:r", "a:fld"])) {
                    // Parse results
                    $sharedStringParts = (new RNodeTextExtractor())->extract($DOMNodeChild);
                    if (count($sharedStringParts) !== 0) {
                        foreach ($sharedStringParts as $sharedStringPart) {
                            $parts[] = $sharedStringPart;
                        }
                        if ($firstTextChild === null) {
                            $firstTextChild = $DOMNodeChild;
                        } else {
                            $otherNodes[] = $DOMNodeChild;
                        }
                    }

                } elseif ($DOMNodeChild instanceof DOMElement) {
                    $this->replaceAndMapValuesForParagraph($DOMNodeChild, $result);
                }
            }

            if ($firstTextChild !== null) {
                $replacementNode = new DOMText();
                $replacementNode->nodeValue = "%" . $this->nextTagIdentifier . "%";
                $DOMNode->replaceChild($replacementNode, $firstTextChild);

                foreach ($otherNodes as $otherNode) {
                    $DOMNode->removeChild($otherNode);
                }

                $result[$this->nextTagIdentifier] = $parts;
                $this->nextTagIdentifier++;
            }
        }

        return $result;
    }
}
