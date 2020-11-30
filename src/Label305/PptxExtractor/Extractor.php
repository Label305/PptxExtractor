<?php

namespace Label305\PptxExtractor;

interface Extractor {

    /**
     * @param $originalFilePath
     * @param $mappingFileSaveLocationPath
     * @throws PptxParsingException
     * @throws PptxFileException
     * @return array The mapping of all the strings
     */
    public function extractStringsAndCreateMappingFile($originalFilePath, $mappingFileSaveLocationPath);

}