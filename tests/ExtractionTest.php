<?php

use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;

class ExtractionTest extends TestCase {

    public function test() {

        $extractor = new BasicExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(__DIR__.'/fixtures/styled-presentation.pptx', __DIR__.'/fixtures/styled-presentation-extracted.pptx');

        $this->assertEquals("Test slide", $mapping[0]);
        $this->assertEquals("Test subtitle", $mapping[1]);

        $mapping[0] = "Vertaalde slide";

        $injector = new BasicInjector();
        $injector->injectMappingAndCreateNewFile($mapping, __DIR__. '/fixtures/styled-presentation-extracted.pptx', __DIR__. '/fixtures/styled-presentation-injected.pptx');

        $otherExtractor = new BasicExtractor();
        $otherMapping = $otherExtractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/styled-presentation-injected.pptx', __DIR__. '/fixtures/styled-presentation-injected-extracted.pptx');

        $this->assertEquals("Vertaalde slide", $otherMapping[0]);

        unlink(__DIR__.'/fixtures/styled-presentation-extracted.pptx');
        unlink(__DIR__.'/fixtures/styled-presentation-injected-extracted.pptx');
        unlink(__DIR__.'/fixtures/styled-presentation-injected.pptx');
    }

    public function test_multipleSlides() {

        $extractor = new BasicExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/styled-presentation-multiple.pptx', __DIR__. '/fixtures/styled-presentation-multiple-extracted.pptx');

        $this->assertEquals("Slide 1 title", $mapping[0]);
        $this->assertEquals("Slide 1 subtitle", $mapping[1]);
        $this->assertEquals("Slide 2 title", $mapping[2]);
        $this->assertEquals("Slide 2 subtitle", $mapping[3]);

        $mapping[0] = "Vertaalde titel slide 1";
        $mapping[2] = "Vertaalde titel slide 2";

        $injector = new BasicInjector();
        $injector->injectMappingAndCreateNewFile($mapping, __DIR__. '/fixtures/styled-presentation-multiple-extracted.pptx', __DIR__. '/fixtures/styled-presentation-multiple-injected.pptx');

        $otherExtractor = new BasicExtractor();
        $otherMapping = $otherExtractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/styled-presentation-multiple-injected.pptx', __DIR__. '/fixtures/styled-presentation-multiple-injected-extracted.pptx');

        $this->assertEquals("Vertaalde titel slide 1", $otherMapping[0]);
        $this->assertEquals("Vertaalde titel slide 2", $otherMapping[2]);

        unlink(__DIR__.'/fixtures/styled-presentation-multiple-extracted.pptx');
        unlink(__DIR__.'/fixtures/styled-presentation-multiple-injected-extracted.pptx');
        unlink(__DIR__.'/fixtures/styled-presentation-multiple-injected.pptx');
    }
    
}