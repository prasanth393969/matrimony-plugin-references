<?php

namespace ACPT\Utils\Wordpress;

use ACPT\Core\Helper\Strings;

class DynamicBlock
{
    /**
     * This is the list of all the block attributes.
     * Extra attributes are the additional controls
     *
     * @param array $extraAttributes
     * @return array
     */
    public static function attributes($extraAttributes = [])
    {
        return array_merge([
            "anchor" => [
                "type" => 'string',
                "default" => null
            ],
            "align" => [
                "type" => 'string',
                "default" => null
            ],
            "backgroundColor" => [
                "type" => 'string',
                "default" => null
            ],
            "fontSize" => [
                "type" => 'string',
                "default" => null
            ],
            "textColor" => [
                "type" => 'string',
                "default" => null
            ],
            "style" => [
                "type" => 'object',
                "default" => []
            ]

        ], $extraAttributes);
    }

    /**
     * Render a block injecting CSS rules
     *
     * @param $string
     * @param array $cssRules
     * @return string
     */
    public static function render($string, $cssRules = [])
    {
        $id = 'acpt_dynamic_block_'. Strings::generateRandomId();

        $div = "<div id='".$id."'>";
        $div .= $string;
        $div .= "</div>";

        self::enqueueInlineCSS($id, self::generateCSSFromAttributes($id, $cssRules));

        return $div;
    }

    /**
     * @param $id
     * @param $content
     */
    private static function enqueueInlineCSS($id, $content)
    {
        if(empty($content)){
            return;
        }

        wp_register_style($id, '', [], ACPT_PLUGIN_VERSION, true );
        wp_enqueue_style($id);
        wp_add_inline_style( $id, $content);
    }

    /**
     * @param string $id
     * @param array $cssRules
     * @return string
     */
    private static function generateCSSFromAttributes($id, $cssRules = [])
    {
        $css = '';

        if(empty($cssRules)){
            return $css;
        }

        $styles = [];
        $buttonStyles = [];
        $linkStyles = [];
        $linkHoverStyles = [];
        $headingStyles = [];

        // align
        if(isset($cssRules['align']) and !empty($cssRules['align'])){
            $styles[] = self::textAlign($cssRules['align']);
        }

        // backgroundColor
        if(isset($cssRules['backgroundColor']) and !empty($cssRules['backgroundColor'])){
            $styles[] = 'background-color: '.self::color( $cssRules['backgroundColor']);
        }

        // fontSize
        if(isset($cssRules['fontSize']) and !empty($cssRules['fontSize'])){
            $styles[] = 'font-size: '.$cssRules['fontSize'];
        }

        // textColor
        if(isset($cssRules['textColor']) and !empty($cssRules['textColor'])){
            $styles[] = 'color: '.self::color( $cssRules['textColor']);
        }

        // style
        if(isset($cssRules['style']) and !empty($cssRules['style'])){
            $style = $cssRules['style'];

            // background
            if(isset($style['background']) and !empty($style['background'])){

                $background = $style['background'];

                if(isset($background['backgroundImage']) and !empty($background['backgroundImage'])){

                    $backgroundImage = $background['backgroundImage'];

                    if(isset($backgroundImage['url']) and !empty($backgroundImage['url'])){
                        $styles[] = 'background-image: url('.$backgroundImage['url'].')';
                    }

                    if(isset($backgroundImage['backgroundSize']) and !empty($backgroundImage['backgroundSize'])){
                        $styles[] = 'background-size: '.$backgroundImage['backgroundSize'];
                    }
                }
            }

            // color
            if(isset($style['color']) and !empty($style['color'])){

                $color = $style['color'];

                if(isset($color['background']) and !empty($color['background'])){
                    $styles[] = 'background-color: '.self::color($color['background']);
                }

                if(isset($color['gradient']) and !empty($color['gradient'])){
                    $styles[] = 'background: '.$color['gradient'];
                }

                if(isset($color['text']) and !empty($color['text'])){
                    $styles[] = 'color: '.self::color($color['text']);
                }
            }

            // dimensions
            if(isset($style['dimensions']) and !empty($style['dimensions'])){
                $dimensions = $style['dimensions'];

                if(isset($dimensions['aspectRatio']) and !empty($dimensions['aspectRatio'])){
                    $styles[] = 'aspect-ratio: '.$dimensions['aspectRatio'];
                }

                if(isset($dimensions['minHeight']) and !empty($dimensions['minHeight'])){
                    $styles[] = 'min-height: '.$dimensions['minHeight'];
                }
            }

            // position

            // spacing
            if(isset($style['spacing']) and !empty($style['spacing'])){
                $spacing = $style['spacing'];

                // margin
                if(isset($spacing['margin']) and !empty($spacing['margin'])){
                    $margin = $spacing['margin'];

                    if(isset($margin['bottom']) and !empty($margin['bottom'])){
                        $styles[] = 'margin-bottom: '.self::length($margin['bottom']);
                    }

                    if(isset($margin['top']) and !empty($margin['top'])){
                        $styles[] = 'margin-top: '.self::length($margin['top']);
                    }

                    if(isset($margin['left']) and !empty($margin['left'])){
                        $styles[] = 'margin-left: '.self::length($margin['left']);
                    }

                    if(isset($margin['right']) and !empty($margin['right'])){
                        $styles[] = 'margin-right: '.self::length($margin['right']);
                    }
                }

                // padding
                if(isset($spacing['padding']) and !empty($spacing['padding'])){
                    $padding = $spacing['padding'];

                    if(isset($padding['bottom']) and !empty($padding['bottom'])){
                        $styles[] = 'padding-bottom: '.self::length($padding['bottom']);
                    }

                    if(isset($padding['top']) and !empty($padding['top'])){
                        $styles[] = 'padding-top: '.self::length($padding['top']);
                    }

                    if(isset($padding['left']) and !empty($padding['left'])){
                        $styles[] = 'padding-left: '.self::length($padding['left']);
                    }

                    if(isset($padding['right']) and !empty($padding['right'])){
                        $styles[] = 'padding-right: '.self::length($padding['right']);
                    }
                }

                // blockGap
                if(isset($spacing['blockGap']) and !empty($spacing['blockGap'])){
                    $gap = $spacing['blockGap'];

                    if(isset($gap['top']) and !empty($gap['top'])){
                        $styles[] = 'gap-row: '.self::length($gap['top']);
                    }

                    if(isset($gap['left']) and !empty($gap['left'])){
                        $styles[] = 'gap-column: '.self::length($gap['left']);
                    }
                }
            }

            // typography
            if(isset($style['typography']) and !empty($style['typography'])){

                $typography = $style['typography'];

                // fontSize
                if(isset($typography['fontSize']) and !empty($typography['fontSize'])){
                    $styles[] = 'font-size: '. $typography['fontSize'];
                }

                // lineHeight
                if(isset($typography['lineHeight']) and !empty($typography['lineHeight'])){
                    $styles[] = 'line-height: '. $typography['lineHeight'];
                }
            }

            // elements
            if(isset($style['elements']) and !empty($style['elements'])){
                $elements = $style['elements'];

                // button
                if(isset($elements['button']) and !empty($elements['button'])){
                    $button = $elements['button'];

                    if(isset($button['color']) and !empty($button['color'])){
                        $color = $button['color'];

                        if(isset($color['background']) and !empty($color['background'])){
                            $buttonStyles[] = 'background-color: ' . self::color($color['background']);
                        }

                        if(isset($color['gradient']) and !empty($color['gradient'])){
                            $buttonStyles[] = 'background: ' . $color['gradient'];
                        }

                        if(isset($color['text']) and !empty($color['text'])){
                            $buttonStyles[] = 'color: ' . self::color($color['text']);
                        }
                    }
                }

                // heading
                if(isset($elements['heading']) and !empty($elements['heading'])){
                    $heading = $elements['heading'];

                    if(isset($heading['color']) and !empty($heading['color'])){
                        $color = $heading['color'];

                        if(isset($color['text']) and !empty($color['text'])){
                            $headingStyles[] = 'color: ' . self::color($color['text']);
                        }
                    }
                }

                // link
                if(isset($elements['link']) and !empty($elements['link'])){
                    $link = $elements['link'];

                    if(isset($link['color']) and !empty($link['color'])){
                        $color = $link['color'];

                        if(isset($color['text']) and !empty($color['text'])){
                            $linkStyles[] = 'color: ' . self::color($color['text']);
                        }
                    }

                    if(isset($link[':hover']) and !empty($link[':hover'])){
                        $hover = $link[':hover'];

                        if(isset($hover['color']) and !empty($hover['color'])){
                            $color = $link['color'];

                            if(isset($color['text']) and !empty($color['text'])){
                                $linkHoverStyles[] = 'color: ' . self::color($color['text']);
                            }
                        }
                    }
                }
            }
        }



        // main
        if(!empty($styles)){
            $css .= "#".$id." {".implode(";", $styles)."}";
        }

        // titles
        if(!empty($headingStyles)){
            $css .= "#".$id." h1 {".implode(";", $headingStyles)."}";
            $css .= "#".$id." h2 {".implode(";", $headingStyles)."}";
            $css .= "#".$id." h3 {".implode(";", $headingStyles)."}";
            $css .= "#".$id." h4 {".implode(";", $headingStyles)."}";
            $css .= "#".$id." h5 {".implode(";", $headingStyles)."}";
            $css .= "#".$id." h6 {".implode(";", $headingStyles)."}";
        }

        // links
        if(!empty($linkStyles)){
            $css .= "#".$id." a {".implode(";", $linkStyles)."}";
        }

        if(!empty($linkHoverStyles)){
            $css .= "#".$id." a:hover {".implode(";", $linkHoverStyles)."}";
        }

        // buttons
        if(!empty($buttonStyles)){
            $css .= "#".$id." button {".implode(";", $buttonStyles)."}";
        }

        return $css;
    }

    /**
     * @param $align
     * @return string
     */
    private static function textAlign($align)
    {
        if($align === 'full'){
            return 'max-width: none';
        }

        if($align === 'wide'){
            return 'max-width: 1340px';
        }

        return 'text-align: '. $align;
    }

    /**
     * @param $color
     * @return string
     */
    private static function color($color)
    {
        if(empty($color)){
            return null;
        }

        if(Strings::isHexadecimalString($color)){
            return $color;
        }

        if(Strings::contains("var", $color)){
            $color = explode("|", $color);

            return "var(--wp--preset--color--".end($color).")";
        }

        return "var(--wp--preset--color--".$color.")";
    }

    /**
     * @param $value
     * @return string|null
     */
    private static function length($value)
    {
        if(empty($value)){
            return null;
        }

        if(Strings::contains("var", $value)){
            $value = explode("|", $value);

            return "var(--wp--preset--spacing--".end($value).")";
        }

        return $value;
    }
}