<?php

use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;
use Label305\PptxExtractor\Decorated\DecoratedTextExtractor;
use Label305\PptxExtractor\Decorated\DecoratedTextInjector;
use Label305\PptxExtractor\Decorated\Paragraph;
use Label305\PptxExtractor\Decorated\Style;
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

    public function test_markupFldTag() {

        $extractor = new DecoratedTextExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/a-fld-test.pptx', __DIR__. '/fixtures/a-fld-test-extracted.pptx');

        $this->assertEquals("VOORSTEL VOOR WERKWIJZE VOLGENDE FASE:", $mapping[0][0]->text);
        $this->assertEquals("1", $mapping[1][0]->text);

        $mapping[0][0]->text = 'PROPOSAL FOR PROCEDURE FOR NEXT PHASE';
        $mapping[1][0]->text = '2';

        $injector = new DecoratedTextInjector();
        $injector->injectMappingAndCreateNewFile($mapping, __DIR__. '/fixtures/a-fld-test-extracted.pptx', __DIR__. '/fixtures/a-fld-test-injected.pptx');

        $otherExtractor = new DecoratedTextExtractor();
        $otherMapping = $otherExtractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/a-fld-test-injected.pptx', __DIR__. '/fixtures/a-fld-test-injected-extracted.pptx');

        $this->assertEquals('PROPOSAL FOR PROCEDURE FOR NEXT PHASE', $otherMapping[0][0]->text);
        $this->assertEquals('2', $otherMapping[1][0]->text);

        unlink(__DIR__.'/fixtures/a-fld-test-extracted.pptx');
        unlink(__DIR__.'/fixtures/a-fld-test-injected-extracted.pptx');
        unlink(__DIR__.'/fixtures/a-fld-test-injected.pptx');
    }

    public function test_zs() {

        $extractor = new DecoratedTextExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/sz-test.pptx', __DIR__. '/fixtures/sz-test-extracted.pptx');
        $this->assertEquals("WBSO", $mapping[0][0]->text);
        $this->assertEquals("Research & Development (Promotion) Act", $mapping[1][0]->text);

        $mapping[0][0]->text = 'WBSO';
        $mapping[1][0]->text = 'Wet ter bevordering van onderzoek en ontwikkeling';

        $injector = new DecoratedTextInjector();
        $injector->injectMappingAndCreateNewFile($mapping, __DIR__. '/fixtures/sz-test-extracted.pptx', __DIR__. '/fixtures/sz-test-injected.pptx');

        $otherExtractor = new DecoratedTextExtractor();
        $otherMapping = $otherExtractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/sz-test-injected.pptx', __DIR__. '/fixtures/sz-test-injected-extracted.pptx');

        $this->assertEquals('WBSO', $otherMapping[0][0]->text);
        $this->assertEquals('Wet ter bevordering van onderzoek en ontwikkeling', $otherMapping[1][0]->text);

        unlink(__DIR__.'/fixtures/sz-test-extracted.pptx');
        unlink(__DIR__.'/fixtures/sz-test-injected-extracted.pptx');
        unlink(__DIR__.'/fixtures/sz-test-injected.pptx');
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
        $paragraph[] = new TextRun(' and ');
        $paragraph[] = new TextRun('font style' , false, false, false, false, false, false, new Style());

        $this->assertEquals('This is a test with <strong>bold</strong> and <em>italic</em> and <u>underline</u> and <mark>highlight</mark> and <sup>superscript</sup> and <sub>subscript</sub> and font style', $paragraph->toHTML());
    }

    public function test_paragraph_fillWithHTMLDom()
    {
        $html = 'This is a test with <strong>bold</strong> and <em>italic</em> and <u>underline</u> and <mark>highlight</mark> and <sup>superscript</sup> and <sub>subscript</sub> and <font>font style</font>';
        $html = "<html>" . $html . "</html>";

        $htmlDom = new DOMDocument;
        @$htmlDom->loadXml($html);

        $paragraph = new Paragraph();
        $paragraph->fillWithHTMLDom($htmlDom->documentElement, null);

        $this->assertEquals('This is a test with ', $paragraph[0]->text);
        $this->assertEquals('bold', $paragraph[1]->text);
        $this->assertTrue($paragraph[1]->bold);
        $this->assertEquals(' and ', $paragraph[2]->text);
        $this->assertEquals('italic', $paragraph[3]->text);
        $this->assertTrue($paragraph[3]->italic);
        $this->assertEquals(' and ', $paragraph[4]->text);
        $this->assertEquals('underline', $paragraph[5]->text);
        $this->assertTrue($paragraph[5]->underline);
        $this->assertEquals(' and ', $paragraph[6]->text);
        $this->assertEquals('highlight', $paragraph[7]->text);
        $this->assertTrue($paragraph[7]->highlight);
        $this->assertEquals(' and ', $paragraph[8]->text);
        $this->assertEquals('superscript', $paragraph[9]->text);
        $this->assertTrue($paragraph[9]->superscript);
        $this->assertEquals(' and ', $paragraph[10]->text);
        $this->assertEquals('subscript', $paragraph[11]->text);
        $this->assertTrue($paragraph[11]->subscript);
        $this->assertEquals(' and ', $paragraph[12]->text);
        $this->assertEquals('font style', $paragraph[13]->text);
    }

    public function test_paragraphWithHTML()
    {
        $extractor = new DecoratedTextExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/markup-presentation.pptx', __DIR__. '/fixtures/markup-presentation-extracted.pptx');

        $translations = [
            '<mark>Nieuwe</mark> pagi<i>n</i>a <i>Schuingedrukt</i><u>Onderlijnd</u>',
            'Lijst item 1',
            'Lijst item 2',
        ];

        foreach ($translations as $key => $translation) {
            $mapping[$key] = Paragraph::paragraphWithHTML($translation, $mapping[$key]);
        }

        $injector = new DecoratedTextInjector();
        $injector->injectMappingAndCreateNewFile($mapping, __DIR__. '/fixtures/markup-presentation-extracted.pptx', __DIR__. '/fixtures/markup-presentation-injected.pptx');

        $otherExtractor = new DecoratedTextExtractor();
        $otherMapping = $otherExtractor->extractStringsAndCreateMappingFile(__DIR__. '/fixtures/markup-presentation-injected.pptx', __DIR__. '/fixtures/markup-presentation-injected-extracted.pptx');

        $this->assertEquals('Nieuwe', $otherMapping[0][0]->text);
        $this->assertEquals(' pagi', $otherMapping[0][1]->text);
        $this->assertEquals('n', $otherMapping[0][2]->text);
        $this->assertEquals('a ', $otherMapping[0][3]->text);
        $this->assertEquals('Schuingedrukt', $otherMapping[0][4]->text);
        $this->assertEquals('Onderlijnd', $otherMapping[0][5]->text);
        $this->assertEquals('Lijst item 1', $otherMapping[1][0]->text);
        $this->assertEquals('Lijst item 2', $otherMapping[2][0]->text);

        unlink(__DIR__.'/fixtures/markup-presentation-extracted.pptx');
        unlink(__DIR__.'/fixtures/markup-presentation-injected-extracted.pptx');
        unlink(__DIR__.'/fixtures/markup-presentation-injected.pptx');
    }
    
}