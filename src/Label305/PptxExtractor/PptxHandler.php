<?php

namespace Label305\PptxExtractor;

use DirectoryIterator;
use DOMDocument;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

abstract class PptxHandler {

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
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return $this->temporaryDirectory;
    }

    /**
     * @param string $temporaryDirectory
     * @return $this
     */
    public function setTemporaryDirectory($temporaryDirectory)
    {
        $this->temporaryDirectory = $temporaryDirectory;
        return $this;
    }

    /**
     * Extract file
     * @param $filePath
     * @throws PptxFileException
     * @throws PptxParsingException
     * @returns array With "document" key, "dom" and "archive" key both are paths. "slide" points to the slide.xml
     * and "archive" points to the root of the archive. "dom" is the DOMDocument object for the document.xml.
     */
    protected function prepareDocumentForReading($filePath)
    {
        //Make sure we have a complete and correct path
        $filePath = realpath($filePath) ?: $filePath;

        $temp = $this->temporaryDirectory . DIRECTORY_SEPARATOR . uniqid();

        if (file_exists($temp)) {
            $this->rmdirRecursive($temp);
        }
        mkdir($temp);

        $zip = new ZipArchive;
        $opened = $zip->open($filePath);
        if ($opened !== TRUE) {
            throw new PptxFileException( 'Could not open zip archive ' . $filePath . '[' . $opened . ']' );
        }
        $zip->extractTo($temp);
        $zip->close();

        $slideLocations = [];
        $slidesPath = $temp . DIRECTORY_SEPARATOR . 'ppt' . DIRECTORY_SEPARATOR . 'slides';
        foreach (new DirectoryIterator($slidesPath) as $fileInfo) {
            if ($fileInfo->getType() === 'file' && strpos($fileInfo->getFilename(), 'slide') !== -1) {
                $slideLocations[] = $slidesPath . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
            }
        }

        sort($slideLocations);

        $extractedSlides = [];
        foreach ($slideLocations as $slideLocation) {
            $documentXmlContents = file_get_contents($slideLocation);
            $dom = new DOMDocument();
            $loadXMLResult = $dom->loadXML($documentXmlContents, LIBXML_NOERROR | LIBXML_NOWARNING);

            if (!$loadXMLResult || !($dom instanceof DOMDocument)) {
                throw new PptxParsingException( 'Could not parse XML document' );
            }

            $extractedSlides[] = [
                "dom" => $dom,
                "slide" => $slideLocation,
                "archive" => $temp
            ];
        }

        return $extractedSlides;
    }

    /**
     * @param $preparedSlides
     * @param $archiveLocation
     * @param $saveLocation
     * @throws PptxFileException
     */
    protected function saveDocument($preparedSlides, $saveLocation)
    {
        $archiveLocation = null;
        foreach ($preparedSlides as $preparedSlide) {
            if(!file_exists($preparedSlide['archive'])) {
                throw new PptxFileException( 'Archive should exist: ' . $preparedSlide['archive']);
            }
            if(!file_exists($preparedSlide['slide'])) {
                throw new PptxFileException( 'Archive should exist: ' . $preparedSlide['slide']);
            }
            if ($archiveLocation === null) {
                $archiveLocation = $preparedSlide['archive'];
            }
        }


        foreach ($preparedSlides as $preparedSlide) {
            $newDocumentXMLContents = $preparedSlide['dom']->saveXml();
            file_put_contents($preparedSlide['slide'], $newDocumentXMLContents);
        }


        //Create a pptx file again
        $zip = new ZipArchive;

        $opened = $zip->open($saveLocation, ZIPARCHIVE::CREATE | ZipArchive::OVERWRITE);
        if ($opened !== true) {
            throw new PptxFileException( 'Cannot open zip: ' . $saveLocation . ' [' . $opened . ']' );
        }

        // Create recursive directory iterator
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($archiveLocation), RecursiveIteratorIterator::LEAVES_ONLY);

        foreach($files as $name => $file) {

            $filePath = $file->getRealPath();

            if (in_array($file->getFilename(), array('.', '..'))) {
                continue;
            }

            if (!file_exists($filePath)) {
                throw new PptxFileException( 'File does not exists: ' . $file->getPathname() );
            } else {
                if (!is_readable($filePath)) {
                    throw new PptxFileException( 'File is not readable: ' . $file->getPathname() );
                } else {
                    if (!$zip->addFile($filePath, substr($file->getPathname(), strlen($archiveLocation) + 1))) {
                        throw new PptxFileException( 'Error adding file: ' . $file->getPathname() );
                    }
                }
            }
        }
        if (!$zip->close()) {
            throw new PptxFileException( 'Could not create zip file' );
        }
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
