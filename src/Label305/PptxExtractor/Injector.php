<?php namespace Label305\PptxExtractor;


interface Injector {

    /**
     * @param $mapping
     * @param $fileToInjectLocationPath
     * @param $saveLocationPath
     * @throws PptxParsingException
     * @throws PptxFileException
     * @return void
     */
    public function injectMappingAndCreateNewFile($mapping, $fileToInjectLocationPath, $saveLocationPath);

}