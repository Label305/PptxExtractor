<?php

use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;
use Label305\PptxExtractor\Decorated\DecoratedTextExtractor;
use Label305\PptxExtractor\Decorated\DecoratedTextInjector;
use Label305\PptxExtractor\Decorated\Paragraph;
use Label305\PptxExtractor\Decorated\TextRun;

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
        $this->assertEquals("sli", $mapping[0][2]->text);
        $this->assertEquals("d", $mapping[0][3]->text);
        $this->assertEquals("e ", $mapping[0][4]->text);
        $this->assertEquals("Italic", $mapping[0][5]->text);
        $this->assertEquals("Underline", $mapping[0][6]->text);
        $this->assertEquals("List item 1", $mapping[2][0]->text);
        $this->assertEquals("List item 2", $mapping[3][0]->text);

        $mapping[0][0]->text = 'Vertaald';
        $mapping[0][2]->text = 'she';
        $mapping[0][3]->text = 'e';
        $mapping[0][4]->text = 't ';
        $mapping[0][5]->text = 'Schuingedrukt';
        $mapping[0][6]->text = 'Onderlijnd';
        $mapping[2][0]->text = 'Lijst item 1';
        $mapping[3][0]->text = 'Lijst item 2';

        $injector = new DecoratedTextInjector();
        $injector->injectMappingAndCreateNewFile($mapping, __DIR__. '/fixtures/markup-presentation-extracted.pptx', __DIR__. '/fixtures/markup-presentation-injected.pptx');

        $otherExtractor = new DecoratedTextExtractor();
        $otherMapping = $otherExtractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/markup-presentation-injected.pptx', __DIR__. '/fixtures/markup-presentation-injected-extracted.pptx');

        $this->assertEquals('Vertaald', $otherMapping[0][0]->text);
        $this->assertEquals('she', $otherMapping[0][2]->text);
        $this->assertEquals('e', $otherMapping[0][3]->text);
        $this->assertEquals('t ', $otherMapping[0][4]->text);
        $this->assertEquals('Schuingedrukt', $otherMapping[0][5]->text);
        $this->assertEquals('Onderlijnd', $otherMapping[0][6]->text);
        $this->assertEquals('Lijst item 1', $otherMapping[2][0]->text);
        $this->assertEquals('Lijst item 2', $otherMapping[3][0]->text);

        unlink(__DIR__.'/fixtures/markup-presentation-extracted.pptx');
        unlink(__DIR__.'/fixtures/markup-presentation-injected-extracted.pptx');
        unlink(__DIR__.'/fixtures/markup-presentation-injected.pptx');
    }

    public function test_paragraph_toHtml()
    {
        $paragraph = new Paragraph();
        $paragraph[] = new TextRun('This is a test with ');
        $paragraph[] = new TextRun('bold' , true);
        $paragraph[] = new TextRun(' and ');
        $paragraph[] = new TextRun('italic' , false, true);
        $paragraph[] = new TextRun(' and ');
        $paragraph[] = new TextRun('underline' , false, false, true);
        $paragraph[] = new TextRun(' and ');
        $paragraph[] = new TextRun('highlight' , false, false, false, true);
        $paragraph[] = new TextRun(' and ');
        $paragraph[] = new TextRun('superscript' , false, false, false, false, true);
        $paragraph[] = new TextRun(' and ');
        $paragraph[] = new TextRun('subscript' , false, false, false, false, false, true);

        $this->assertEquals('This is a test with <strong>bold</strong> and <em>italic</em> and <u>underline</u> and <mark>highlight</mark> and <sup>superscript</sup> and <sub>subscript</sub>', $paragraph->toHTML());
    }

    public function test_paragraph_fillWithHTMLDom()
    {
        $html = 'This is a test with <strong>bold</strong> and <em>italic</em> and <u>underline</u> and <mark>highlight</mark> and <sup>superscript</sup> and <sub>subscript</sub>';
        $html = "<html>" . $html . "</html>";

        $htmlDom = new DOMDocument;
        @$htmlDom->loadXml($html);

        $sharedString = new Paragraph();
        $sharedString->fillWithHTMLDom($htmlDom->documentElement);

        $this->assertEquals('This is a test with ', $sharedString[0]->text);
        $this->assertEquals('bold', $sharedString[1]->text);
        $this->assertTrue($sharedString[1]->bold);
        $this->assertEquals(' and ', $sharedString[2]->text);
        $this->assertEquals('italic', $sharedString[3]->text);
        $this->assertTrue($sharedString[3]->italic);
        $this->assertEquals(' and ', $sharedString[4]->text);
        $this->assertEquals('underline', $sharedString[5]->text);
        $this->assertTrue($sharedString[5]->underline);
        $this->assertEquals(' and ', $sharedString[6]->text);
        $this->assertEquals('highlight', $sharedString[7]->text);
        $this->assertTrue($sharedString[7]->highlight);
        $this->assertEquals(' and ', $sharedString[8]->text);
        $this->assertEquals('superscript', $sharedString[9]->text);
        $this->assertTrue($sharedString[9]->superscript);
        $this->assertEquals(' and ', $sharedString[10]->text);
        $this->assertEquals('subscript', $sharedString[11]->text);
        $this->assertTrue($sharedString[11]->subscript);
    }

    
}