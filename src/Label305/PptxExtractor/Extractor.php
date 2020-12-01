<?php

namespace Label305\PptxExtractor;

interface Extractor {

    /**
     * @param string $originalFilePath
     * @param string $mappingFileSaveLocationPath
     * @throws PptxParsingException
     * @throws PptxFileException
     * @return array The mapping of all the strings
     */
    public function extractStringsAndCreateMappingFile(string $originalFilePath, string $mappingFileSaveLocationPath): array;

}