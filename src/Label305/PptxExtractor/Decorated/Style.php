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
     * @return bool
     * Prevent setting font tags when only language tag is found.
     */
    public function isEmpty(): bool
    {
        $hasLang = $this->lang === null || $this->lang === "";
        $hasOther =
            ($this->underline === null || $this->underline === "") &&
            ($this->baseline === null || $this->baseline === "") &&
            ($this->sz === null || $this->sz === "") &&
            ($this->solidFill === null || $this->solidFill === "") &&
            ($this->highlight === null || $this->highlight === "") &&
            ($this->latin === null || $this->latin === "") &&
            ($this->cs === null || $this->cs === "");

        if ($hasLang && !$hasOther) {
            return false;
        }

        return $hasOther;
    }

    /**
     * To docx xml string
     *
     * @return string
     */
    public function toPptxXML(): string
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
    private function getFontStyle(FontStyle $fontStyle, string $tagName): string
    {
        $properties = [
            'typeface',
            'panose',
            'pitchFamily',
            'charset'
        ];

        $value = '<a:' . $tagName;
        foreach ($properties as $property) {
            if ($fontStyle->$property !== null && $fontStyle->$property !== "") {
                $value .= ' ' . $property . '="' . $fontStyle->$property . '"';
            }
        }
        $value .= ' />';

        return $value;
    }

    /**
     * @param ColorStyle $colorStyle
     * @param string $tagName
     * @return string
     */
    private function getColorStyleXml(ColorStyle $colorStyle, string $tagName): string
    {
        $properties = [
            'schemeClr',
            'srgbClr',
            'scrgbClr',
            'prstClr',
            'hslClr',
            'sysClr',
        ];

        $lumModRendered = false;
        $lumOffRendered = false;

        $value = '<a:' . $tagName . '>';
        foreach ($properties as $property) {
            if ($colorStyle->$property !== null && $colorStyle->$property !== "") {
                $value .= '<a:' . $property . ' val="' . $colorStyle->$property . '">';
                if (!$lumModRendered && $colorStyle->lumMod !== null && $colorStyle->lumMod !== "") {
                    $value .= '<a:lumMod val="' . $colorStyle->lumMod . '"/>';
                    $lumModRendered = true;
                }
                if (!$lumOffRendered && $colorStyle->lumOff !== null && $colorStyle->lumOff !== "") {
                    $value .= '<a:lumOff val="' . $colorStyle->lumOff . '"/>';
                    $lumOffRendered = true;
                }
                $value .= '</a:' . $property . '>';
            }
        }
        $value .= '</a:' . $tagName . '>';

        return $value;
    }
}