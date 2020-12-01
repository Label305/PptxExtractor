<?php

namespace Label305\PptxExtractor;

use DirectoryIterator;
use DOMDocument;

abstract class PptxHandler extends ZipHandler {

    /**
     * Defaults to sys_get_temp_dir()
     *
     * @var string the tmp dir location
     */
    protected $temporaryDirectory;

    /**
     * Sets the temporary directory to the system
     */
    function __construct()
    {
        $this->setTemporaryDirectory(sys_get_temp_dir());
    }

    /**
     * @return string|null
     */
    public function getTemporaryDirectory(): ?string
    {
        return $this->temporaryDirectory;
    }

    /**
     * @param string|null $temporaryDirectory
     * @return $this
     */
    public function setTemporaryDirectory(?string $temporaryDirectory)
    {
        $this->temporaryDirectory = $temporaryDirectory;
        return $this;
    }

    /**
     * Extract file
     * @param string $filePath
     * @return array
     * @throws PptxFileException
     * @throws PptxParsingException
     * @returns array With "slide" key, "dom" and "archive" key both are paths. "document" points to the ppt/slide.xml (or slide1.xml, slide2.xml)
     * and "archive" points to the root of the archive. "dom" is the DOMDocument object for the slide.xml.
     */
    protected function prepareDocumentForReading(string $filePath)
    {
        //Make sure we have a complete and correct path
        $filePath = realpath($filePath) ?: $filePath;

        $tempPath = $this->temporaryDirectory . DIRECTORY_SEPARATOR . uniqid();

        if (file_exists($tempPath)) {
            $this->rmdirRecursive($tempPath);
        }
        mkdir($tempPath);

        // Open the zip
        $this->openZip($filePath, $tempPath);

        // Find slides
        $documentLocations = [];
        $slidesPath = $tempPath . DIRECTORY_SEPARATOR . 'ppt' . DIRECTORY_SEPARATOR . 'slides';
        foreach (new DirectoryIterator($slidesPath) as $fileInfo) {
            if ($fileInfo->getType() === 'file' && strpos($fileInfo->getFilename(), 'slide') !== -1) {
                $documentLocations[] = $slidesPath . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
            }
        }

        sort($documentLocations);

        // Prepare slides for reading
        $extractedDocuments = [];
        foreach ($documentLocations as $documentLocation) {
            $documentXmlContents = file_get_contents($documentLocation);
            $dom = new DOMDocument();
            $loadXMLResult = $dom->loadXML($documentXmlContents, LIBXML_NOERROR | LIBXML_NOWARNING);

            if (!$loadXMLResult || !($dom instanceof DOMDocument)) {
                throw new PptxParsingException( 'Could not parse XML document' );
            }

            $extractedDocuments[] = [
                "dom" => $dom,
                "document" => $documentLocation,
                "archive" => $tempPath
            ];
        }

        return $extractedDocuments;
    }

    /**
     * @param array $preparedSlides
     * @param string $saveLocation
     * @throws PptxFileException
     */
    protected function saveDocument(array $preparedSlides, string $saveLocation)
    {
        foreach ($preparedSlides as $preparedSlide) {
            if (!array_key_exists('archive', $preparedSlide)) {
                throw new PptxFileException('The prepared slides array should contain an "archive" key with the path to the whole archive');
            }
            if (!array_key_exists('document', $preparedSlide)) {
                throw new PptxFileException('The prepared slides array should contain a "document" key with the path to the ppt/slide.xml');
            }
            if (!array_key_exists('dom', $preparedSlide)) {
                throw new PptxFileException('The prepared slides array should contain a "dom" key with the parsed DOM from the ppt/slide.xml');
            }
        }

        $archiveLocation = null;
        foreach ($preparedSlides as $preparedSlide) {
            if(!file_exists($preparedSlide['archive'])) {
                throw new PptxFileException( 'Archive should exist: ' . $preparedSlide['archive']);
            }
            if(!file_exists($preparedSlide['document'])) {
                throw new PptxFileException( 'Archive should exist: ' . $preparedSlide['document']);
            }
            if ($archiveLocation === null) {
                $archiveLocation = $preparedSlide['archive'];
            }
        }


        foreach ($preparedSlides as $preparedSlide) {
            $newDocumentXMLContents = $preparedSlide['dom']->saveXml();
            file_put_contents($preparedSlide['document'], $newDocumentXMLContents);
        }

        $this->buildZip($saveLocation, $archiveLocation);
    }

    /**
     * Helper to remove tmp dir
     *
     * @param $dir
     * @return bool
     */
    protected function rmdirRecursive($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach($files as $file) {
            (is_dir("$dir/$file")) ? rmdirRecursive("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

}
