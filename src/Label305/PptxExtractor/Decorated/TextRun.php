<?php

namespace Label305\PptxExtractor\Decorated;

/**
 * Class Text
 * @package Label305\PptxExtractor\Decorated
 *
 * Represents a <a:r> object in the docx format.
 */
class TextRun {

    /**
     * @var string
     */
    public $text;

    /**
     * @var Style|null
     */
    public $style;


    function __construct($text, $style = null)
    {
        $this->text = $text;
        $this->style = $style;
    }

    /**
     * To docx xml string
     *
     * @return string
     */
    public function toPptxXML()
    {
        $value = '<a:r xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">';
        if ($this->style !== null) {
            $value .= $this->style->toPptxXML();
        }
        $value .= '<a:t>' . htmlentities($this->text, ENT_XML1) . "</a:t>";
        $value .= '</a:r>';

        return $value;
    }

    /**
     * Convert to HTML
     *
     * To prevent duplicate tags (e.g. <strong) and allow wrapping you can use the parameters. If they are set to false
     * a tag will not be opened or closed.
     *
     * @return string HTML string
     */
    public function toHTML()
    {
        return htmlentities($this->text);
    }
}