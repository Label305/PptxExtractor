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
     * @var bool If sentence is bold or not
     */
    public $bold;

    /**
     * @var bool If sentence is italic or not
     */
    public $italic;

    /**
     * @var bool If sentence is underlined or not
     */
    public $underline;

    /**
     * @var bool If sentence is highlighted or not
     */
    public $highlight;

    /**
     * @var bool If sentence is superscript or not
     */
    public $superscript;

    /**
     * @var bool If sentence is subscriot or not
     */
    public $subscript;

    /**
     * @var Style|null
     */
    public $style;


    function __construct(
        string $text,
        bool $bold = false,
        bool $italic = false,
        bool $underline = false,
        bool $highlight = false,
        bool $superscript = false,
        bool $subscript = false,
        ?Style $style = null
    ) {
        $this->text = $text;
        $this->bold = $bold;
        $this->italic = $italic;
        $this->underline = $underline;
        $this->highlight = $highlight;
        $this->superscript = $superscript;
        $this->subscript = $subscript;
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
        if ($this->hasMarkup()) {
            $value .= '<a:rPr';
            if ($this->style !== null && !empty($this->style->lang)) {
                $value .= ' lang="' . $this->style->lang . '"';
            }
            if ($this->style !== null && !empty($this->style->baseline)) {
                $value .= ' baseline="' . $this->style->baseline . '"';
            }
            if ($this->style !== null && !empty($this->style->sz)) {
                $value .= ' sz="' . $this->style->sz . '"';
            }
            if ($this->bold) {
                $value .= ' b="1"';
            }
            if ($this->italic) {
                $value .= ' i="1"';
            }
            if ($this->underline) {
                if ($this->style !== null && !empty($this->style->underline)) {
                    $value .= ' u="' . $this->style->underline . '"';
                } else {
                    $value .= ' u="sng"';
                }
            }
            $value .= ' dirty="0">';
            if ($this->style !== null) {
                $value .= $this->style->toPptxXML();
            }
            $value .= '</a:rPr>';
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
     * @param bool $firstWrappedInBold
     * @param bool $firstWrappedInItalic
     * @param bool $firstWrappedInUnderline
     * @param bool $firstWrappedInHighlight
     * @param bool $firstWrappedInSuperscript
     * @param bool $firstWrappedInSubscript
     * @param bool $firstWrappedInStyle
     * @param bool $lastWrappedInBold
     * @param bool $lastWrappedInItalic
     * @param bool $lastWrappedInUnderline
     * @param bool $lastWrappedInHighlight
     * @param bool $lastWrappedInSuperscript
     * @param bool $lastWrappedInSubscript
     * @param bool $lastWrappedInStyle
     * @return string HTML string
     */
    public function toHTML(
        $firstWrappedInBold = true,
        $firstWrappedInItalic = true,
        $firstWrappedInUnderline = true,
        $firstWrappedInHighlight = true,
        $firstWrappedInSuperscript = true,
        $firstWrappedInSubscript = true,
        $firstWrappedInStyle = true,
        $lastWrappedInBold = true,
        $lastWrappedInItalic = true,
        $lastWrappedInUnderline = true,
        $lastWrappedInHighlight = true,
        $lastWrappedInSuperscript = true,
        $lastWrappedInSubscript = true,
        $lastWrappedInStyle = true
    ) {
        $value = '';

        if ($this->highlight && $firstWrappedInHighlight) {
            $value .= "<mark>";
        }
        if ($this->bold && $firstWrappedInBold) {
            $value .= "<strong>";
        }
        if ($this->italic && $firstWrappedInItalic) {
            $value .= "<em>";
        }
        if ($this->underline && $firstWrappedInUnderline) {
            $value .= "<u>";
        }
        if ($this->subscript && $firstWrappedInSubscript) {
            $value .= "<sub>";
        }
        if ($this->superscript && $firstWrappedInSuperscript) {
            $value .= "<sup>";
        }
        if ($this->style !== null && $firstWrappedInStyle) {
            $value .= "<font>";
        }

        $value .= htmlentities($this->text);

        if ($this->style !== null && $lastWrappedInStyle) {
            $value .= "</font>";
        }
        if ($this->superscript && $lastWrappedInSuperscript) {
            $value .= "</sup>";
        }
        if ($this->subscript && $lastWrappedInSubscript) {
            $value .= "</sub>";
        }
        if ($this->underline && $lastWrappedInUnderline) {
            $value .= "</u>";
        }
        if ($this->italic && $lastWrappedInItalic) {
            $value .= "</em>";
        }
        if ($this->bold && $lastWrappedInBold) {
            $value .= "</strong>";
        }
        if ($this->highlight && $lastWrappedInHighlight) {
            $value .= "</mark>";
        }

        return $value;
    }

    private function hasMarkup()
    {
        if ($this->style !== null) {
            return true;
        }

        return $this->bold !== null ||
            $this->italic !== null ||
            $this->underline !== null ||
            $this->highlight !== null ||
            $this->superscript !== null ||
            $this->subscript !== null;
    }
}