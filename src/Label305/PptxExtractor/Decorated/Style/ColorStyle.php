<?php

namespace Label305\PptxExtractor\Decorated\Style;

/**
 * Class ColorStyle
 * @package Label305\PptxExtractor\Decorated\Style
 *
 * Represents the style contents of a <a:highlight> or <a:solidFill> object in the docx format.
 */
class ColorStyle {

    /**
     * @var string|null
     */
    public $schemeClr;

    /**
     * @var string|null
     */
    public $schemeClrLumMod;

    /**
     * @var string|null
     */
    public $srgbClr;

}