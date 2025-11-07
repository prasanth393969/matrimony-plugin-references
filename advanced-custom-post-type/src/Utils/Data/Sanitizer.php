<?php

namespace ACPT\Utils\Data;

use ACPT\Constants\HTMLTag;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\JSON;

class Sanitizer
{
	/**
	 * Sanitize post type data before saving
	 *
	 * @param $type
	 * @param $rawData
	 * @param $allowHtml
	 * @param $allowDangerousContent
	 *
	 * @return mixed
	 */
	public static function sanitizeRawData($type, $rawData, $allowHtml = false, $allowDangerousContent = false)
	{
	    // Allow HTML in Text and Textarea fields
	    if($allowHtml){
            return htmlspecialchars(Sanitizer::customWpKses( $rawData ), ENT_QUOTES, 'UTF-8');
        }

        // Allow dangerous content
        if($allowDangerousContent){
            return $rawData;
        }

        switch ($type){

			case MetaFieldModel::ICON_TYPE:
				return Sanitizer::sanitizeSVG($rawData);

			case MetaFieldModel::EMAIL_TYPE:
				return sanitize_email($rawData);

            case MetaFieldModel::IMAGE_TYPE:
            case MetaFieldModel::AUDIO_TYPE:
            case MetaFieldModel::QR_CODE_TYPE:
			case MetaFieldModel::URL_TYPE:
				return esc_url_raw($rawData);

			case MetaFieldModel::TEXTAREA_TYPE:
				return stripslashes_deep(sanitize_textarea_field($rawData));

			case FormFieldModel::WORDPRESS_USER_BIO:
			case FormFieldModel::WORDPRESS_POST_CONTENT:
			case FormFieldModel::WORDPRESS_POST_EXCERPT:
			case FormFieldModel::WORDPRESS_TERM_DESCRIPTION:
			case MetaFieldModel::EDITOR_TYPE:
			case MetaFieldModel::HTML_TYPE:
				return Sanitizer::customWpKses( $rawData );

			case is_array($rawData):
			case MetaFieldModel::AUDIO_MULTI_TYPE:
			case MetaFieldModel::IMAGE_SLIDER_TYPE:
			case MetaFieldModel::GALLERY_TYPE:
			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:
			case MetaFieldModel::LIST_TYPE:
			case MetaFieldModel::USER_MULTI_TYPE:
				return Sanitizer::recursiveSanitizeRawData($rawData);
				break;

			default:
				return Sanitizer::sanitizeDefault($rawData);
		}
	}

    /**
     * @param $array
     *
     * @return mixed
     */
    public static function recursiveSanitizeRawData($array)
    {
    	if(is_string($array)){
    		return sanitize_text_field($array);
	    }

        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = self::recursiveSanitizeRawData($value);
            } elseif(Strings::contains('</svg>', $value) or Strings::contains('&lt;/svg&gt;', $value)){
	            $value = self::sanitizeSVG($value);
            } elseif(\is_string($value)){
                $value = self::sanitizeHTML($value);
                $value = self::rebuildPHP($value);
            } elseif(\is_bool($value)) {
                $value = (bool)( $value );
            } elseif (\is_null($value)){
                $value = null;
            }
        }

        return $array;
    }

	/**
	 * Improved wp_kses() with support for SVG and iframe
	 *
	 * @param $string
	 *
	 * @return string
	 */
    private static function customWpKses($string)
    {
        if(empty($string)){
            return $string;
        }

	    $svgArgs = [
		    'svg'   => [
                'aria-hidden'     => true,
                'aria-labelledby' => true,
			    'class'           => true,
			    'id'              => true,
			    'role'            => true,
                'style'           => true,
			    'xmlns'           => true,
			    'width'           => true,
			    'height'          => true,
			    'version'         => true,
			    'viewBox'         => true,
			    'viewbox'         => true,
			    'xmlns:svg'       => true
		    ],
            'iframe' => [
                'id'                => true,
                'title'             => true,
                'frameborder'       => true,
                'width'             => true,
                'height'            => true,
                'style'             => true,
                'src'               => true,
                'type'              => true,
                'allowscriptaccess' => true,
                'allowfullscreen'   => true,
                'allownetworking'   => true,
                'scrolling'         => true,
            ],
		    'g'     => [
		        'fill'  => true
            ],
            'line'      => [
                'id'        => true,
                'x1'        => true,
                'y1'        => true,
                'x2'        => true,
                'y2'        => true,
                'style'     => true,
            ],
            'polyline' => [
                'id'        => true,
                'points'    => true,
                'style'     => true,
            ],
		    'path'  => [
			    'd'     => true,
			    'id'    => true,
			    'fill'  => true,
			    'name'  => true,
			    'style' => true,
		    ],
            'circle' => [
                'cx'    => true,
                'cy'    => true,
                'id'    => true,
                'r'     => true,
                'style' => true,
            ],
	    ];

	    // Allow fill CSS attribute
	    // @see https://wordpress.stackexchange.com/questions/173526/why-is-wp-kses-not-keeping-style-attributes-as-expected/195433#195433
        add_filter( 'safe_style_css', function( $styles ) {

            if(!in_array('fill', $styles)){
                $styles[] = 'fill';
            }

            if(!in_array('transform', $styles)){
                $styles[] = 'transform';
            }

            return $styles;
        } );

	    $ksesDefaults = wp_kses_allowed_html( 'post' );
	    $allowedTags = array_merge( $ksesDefaults, $svgArgs );

	    // Allow xlink attributes for <a> tags
	    $allowedTags['a']['xlink:title' ] = true;
	    $allowedTags['a']['xlink:type' ] = true;
	    $allowedTags['a']['xlink:href' ] = true;

	    return wp_kses( $string, $allowedTags );
    }

	/**
	 * @param $svg
	 *
	 * @return mixed
	 */
    private static function sanitizeSVG($svg)
    {
    	return self::escapeField($svg);
    }

    /**
     * @param $string
     *
     * @return string
     */
    private static function sanitizeHTML($string)
    {
        // Fix &entity\n;
        $string = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $string);
        $string = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $string);
        $string = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $string);
        $string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $string = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $string);

        // Remove javascript: and vbscript: protocols
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $string);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $string);
        $string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $string);
        $string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $string);

        // Remove namespaced elements (we do not need them)
        $string = preg_replace('#</*\w+:\w[^>]*+>#i', '', $string);

        do {
            // Remove really unwanted tags
            $old_data = $string;
            $string   = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $string);
        }

        while ( $old_data !== $string);

        return $string;
    }

    /**
     * @param $value
     *
     * @return string|string[]
     */
    private static function rebuildPHP($value)
    {
        preg_match_all('/&lt;\?php(.*?)\?&gt;/iu', $value, $phpMatches);

        if(empty($phpMatches[0])){
            return $value;
        }

        foreach ($phpMatches[0] as $match){
            $value = str_replace($match, str_replace(['&lt;','&gt;'], ['<','>'], $match), $value);
        }

        return $value;
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public static function escapeField($field)
    {
        if(empty($field)){
            return $field;
        }

        return wp_kses($field, HTMLTag::ALLOWED_FORMATS);
    }

    /**
     * @param $base64
     * @return string
     */
    public static function sanitizeBase64Image($base64)
    {
        if(Strings::isValidBase64Image($base64)){
            return $base64;
        }

        return '';
    }

    /**
     * @param $rawData
     * @return mixed
     */
    public static function sanitizeDefault($rawData)
    {
        if(JSON::isValid($rawData)){
            $rawData = json_decode($rawData, true);

            if(isset($rawData['img'])){
                $rawData['img'] = Sanitizer::sanitizeBase64Image($rawData['img']);
            }

            $rawData = json_encode($rawData);
        }

        return stripslashes_deep(sanitize_text_field($rawData));
    }
}