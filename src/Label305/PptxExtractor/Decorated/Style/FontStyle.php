<?php

namespace Label305\PptxExtractor\Decorated\Style;

/**
 * Class FontStyle
 * @package Label305\PptxExtractor\Decorated\Style
 *
 * Represents the style contents of a <a:latin> or <a:cs> object in the docx format.
 */
class FontStyle {

    /**
     * @var string|null
     */
    public $typeface;

    /**
     * @var string|null
     */
    public $panose;

    /**
     * @var string|null
     */
    public $pitchFamily;

    /**
     * @var string|null
     */
    public $charset;
}