<?php

namespace Label305\PptxExtractor\Decorated;

use Label305\PptxExtractor\Decorated\Style\FontStyle;
use Label305\PptxExtractor\Decorated\Style\ColorStyle;

/**
 * Class Sentence
 * @package Label305\PptxExtractor\Decorated
 *
 * Represents the style contents of a <a:rPr> object in the docx format.
 */
class Style {

    /**
     * @var string|null
     */
    public $lang;

    /**
     * @var string|null
     */
    public $underline;

    /**
     * @var string|null
     */
    public $bold;

    /**
     * @var string|null
     */
    public $italic;

    /**
     * @var string|null
     */
    public $baseline;

    /**
     * @var ColorStyle|null
     */
    public $solidFill;

    /**
     * @var ColorStyle|null
     */
    private $highlight;

    /**
     * @var FontStyle|null
     */
    private $latin;

    /**
     * @var FontStyle|null
     */
    private $cs;

    function __construct(
        ?string $lang = null,
        ?string $underline = null,
        ?string $bold = null,
        ?string $italic = null,
        ?string $baseline = null,
        ?ColorStyle $solidFill = null,
        ?ColorStyle $highlight = null,
        ?FontStyle $latin = null,
        ?FontStyle $cs = null
    ) {
        $this->lang = $lang;
        $this->underline = $underline;
        $this->bold = $bold;
        $this->italic = $italic;
        $this->baseline = $baseline;
        $this->solidFill = $solidFill;
        $this->highlight = $highlight;
        $this->latin = $latin;
        $this->cs = $cs;
    }

    /**
     * To docx xml string
     *
     * @return string
     */
    public function toPptxXML()
    {
        $value = '';
        $value .= $this->openArPr();
        if ($this->latin !== null) {
            $value .= '<a:latin typeface="' . $this->latin->typeface . '" panose="' . $this->latin->panose . '" pitchFamily="' . $this->latin->pitchFamily . '" charset="' . $this->latin->charset . '"/>';
        }
        if ($this->cs !== null) {
            $value .= '<a:cs typeface="' . $this->cs->typeface . '" panose="' . $this->cs->panose . '" pitchFamily="' . $this->cs->pitchFamily . '" charset="' . $this->cs->charset . '"/>';
        }
        if ($this->solidFill !== null) {
            $value .= '<a:solidFill>';
            $value .= $this->getColorStyleXml($this->solidFill);
            $value .= '</a:solidFill>';
        }
        if ($this->highlight !== null) {
            $value .= '<a:highlight>';
            $value .= $this->getColorStyleXml($this->highlight);
            $value .= '</a:highlight>';
        }

        $value .= '</a:rPr>';

        return $value;
    }

    /**
     * @return string
     */
    private function openArPr()
    {
        $value = '';
        $value .= '<a:rPr ';
        if ($this->lang !== null) {
            $value .= ' lang="' . $this->lang . '"';
        }
        if ($this->bold !== null) {
            $value .= ' b="' . $this->bold . '"';
        }
        if ($this->underline !== null) {
            $value .= ' u="' . $this->underline . '"';
        }
        if ($this->italic !== null) {
            $value .= ' i="' . $this->italic . '"';
        }
        if ($this->baseline !== null) {
            $value .= ' baseline="' . $this->baseline . '"';
        }
        $value .= ' dirty="0">';

        return $value;
    }

    /**
     * @param ColorStyle $colorStyle
     * @return string
     */
    private function getColorStyleXml(ColorStyle $colorStyle)
    {
        $value = '';
        $value .= '<a:schemeClr val="' . $colorStyle->schemeClr . '">';
        if ($colorStyle->schemeClrLumMod !== null) {
            $value .= '<a:lumMod val="' . $colorStyle->schemeClrLumMod . '"/>';
        } elseif ($colorStyle->srgbClr !== null) {
            $value .= '<a:srgbClr val="' . $colorStyle->srgbClr . '"/>';
        }
        $value .= '</a:schemeClr>';
        return $value;
    }
}