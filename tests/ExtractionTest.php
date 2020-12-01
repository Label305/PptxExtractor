<?php

use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;
use Label305\PptxExtractor\Decorated\DecoratedTextExtractor;
use Label305\PptxExtractor\Decorated\DecoratedTextInjector;

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

    public function test_markup() {

        $extractor = new DecoratedTextExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/markup-presentation.pptx', __DIR__. '/fixtures/markup-presentation-extracted.pptx');
        $this->assertEquals("Test", $mapping[0][0]->text);
        $this->assertEquals("slide ", $mapping[0][2]->text);
        $this->assertEquals("Italic", $mapping[0][3]->text);
        $this->assertEquals("Underline", $mapping[0][4]->text);
        $this->assertEquals("List item 1", $mapping[2][0]->text);
        $this->assertEquals("List item 2", $mapping[3][0]->text);

        $mapping[0][0]->text = 'Vertaald';
        $mapping[0][2]->text = 'sheet';
        $mapping[0][3]->text = 'Schuingedrukt';
        $mapping[0][4]->text = 'Onderlijnd';
        $mapping[2][0]->text = 'Lijst item 1';
        $mapping[3][0]->text = 'Lijst item 2';

        $injector = new DecoratedTextInjector();
        $injector->injectMappingAndCreateNewFile($mapping, __DIR__. '/fixtures/markup-presentation-extracted.pptx', __DIR__. '/fixtures/markup-presentation-injected.pptx');

        $otherExtractor = new DecoratedTextExtractor();
        $otherMapping = $otherExtractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/markup-presentation-injected.pptx', __DIR__. '/fixtures/markup-presentation-injected-extracted.pptx');

        $this->assertEquals('Vertaald', $otherMapping[0][0]->text);
        $this->assertEquals('sheet', $otherMapping[0][2]->text);
        $this->assertEquals('Schuingedrukt', $otherMapping[0][3]->text);
        $this->assertEquals('Onderlijnd', $otherMapping[0][4]->text);
        $this->assertEquals('Lijst item 1', $otherMapping[2][0]->text);
        $this->assertEquals('Lijst item 2', $otherMapping[3][0]->text);

        unlink(__DIR__.'/fixtures/markup-presentation-extracted.pptx');
        unlink(__DIR__.'/fixtures/markup-presentation-injected-extracted.pptx');
        unlink(__DIR__.'/fixtures/markup-presentation-injected.pptx');
    }
    
}