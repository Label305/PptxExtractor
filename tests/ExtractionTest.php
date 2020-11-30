<?php

use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;

class ExtractionTest extends TestCase {

    public function testTagMappingBasicExtractorWithSimpleDocument() {

        $extractor = new BasicExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(__DIR__.'/fixtures/styled-presentation.pptx', __DIR__.'/fixtures/styled-presentation-extracted.pptx');

        $this->assertEquals("Test slide", $mapping[0]);
        $this->assertEquals("Test subtitle", $mapping[1]);

        $mapping[0] = "Vertaalde slide";

        $injector = new BasicInjector();
        $injector->injectMappingAndCreateNewFile($mapping, __DIR__.'/fixtures/styled-presentation-extracted.pptx', __DIR__.'/fixtures/styled-presentation-injected.pptx');

        $otherExtractor = new BasicExtractor();
        $otherMapping = $otherExtractor->extractStringsAndCreateMappingFile(__DIR__.'/fixtures/styled-presentation-injected.pptx', __DIR__.'/fixtures/styled-presentation-injected-extracted.pptx');

        $this->assertEquals("Vertaalde slide", $otherMapping[0]);

        unlink(__DIR__.'/fixtures/styled-presentation-extracted.pptx');
        unlink(__DIR__.'/fixtures/styled-presentation-injected-extracted.pptx');
        unlink(__DIR__.'/fixtures/styled-presentation-injected.pptx');
    }
    
}