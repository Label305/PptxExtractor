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
    public $baseline;

    /**
     * @var string|null
     */
    public $sz;

    /**
     * @var ColorStyle|null
     */
    public $solidFill;

    /**
     * @var ColorStyle|null
     */
    public $highlight;

    /**
     * @var FontStyle|null
     */
    public $latin;

    /**
     * @var FontStyle|null
     */
    public $cs;

    function __construct(
        ?string $lang = null,
        ?string $underline = null,
        ?string $baseline = null,
        ?string $sz = null,
        ?ColorStyle $solidFill = null,
        ?ColorStyle $highlight = null,
        ?FontStyle $latin = null,
        ?FontStyle $cs = null
    ) {
        $this->lang = $lang;
        $this->underline = $underline;
        $this->baseline = $baseline;
        $this->sz = $sz;
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
        if ($this->solidFill !== null) {
            $value .= $this->getColorStyleXml($this->solidFill, 'solidFill');
        }
        if ($this->highlight !== null) {
            $value .= $this->getColorStyleXml($this->highlight, 'highlight');
        }
        if ($this->latin !== null) {
            $value .= $this->getFontStyle($this->latin, 'latin');
        }
        if ($this->cs !== null) {
            $value .= $this->getFontStyle($this->cs, 'cs');
        }

        return $value;
    }

    /**
     * @param FontStyle $fontStyle
     * @param string $tagName
     * @return string
     */
    private function getFontStyle(FontStyle $fontStyle, string $tagName)
    {
        $value = '';
        $value .= '<a:' . $tagName . ' typeface="' . $fontStyle->typeface . '"';
        if ($fontStyle->panose !== null && $fontStyle->panose !== "") {
            $value .= ' panose="' . $fontStyle->panose . '"';
        }
        if ($fontStyle->pitchFamily !== null && $fontStyle->pitchFamily !== "") {
            $value .= ' pitchFamily="' . $fontStyle->pitchFamily . '"';
        }
        if ($fontStyle->charset !== null && $fontStyle->charset !== "") {
            $value .= ' charset="' . $fontStyle->charset . '"';
        }
        $value .= ' />';

        return $value;
    }

    /**
     * @param ColorStyle $colorStyle
     * @param string $tagName
     * @return string
     */
    private function getColorStyleXml(ColorStyle $colorStyle, string $tagName)
    {
        $value = '';
        $value .= '<a:' . $tagName . '>';
        if ($colorStyle->schemeClr !== null && $colorStyle->schemeClr !== "") {
            $value .= '<a:schemeClr val="' . $colorStyle->schemeClr . '">';
            if ($colorStyle->schemeClrLumMod !== null && $colorStyle->schemeClrLumMod !== "") {
                $value .= '<a:lumMod val="' . $colorStyle->schemeClrLumMod . '"/>';
            }
            $value .= '</a:schemeClr>';

        } elseif ($colorStyle->srgbClr !== null && $colorStyle->srgbClr !== "") {
            $value .= '<a:srgbClr val="' . $colorStyle->srgbClr . '"/>';
        }
        $value .= '</a:' . $tagName . '>';

        return $value;
    }
}