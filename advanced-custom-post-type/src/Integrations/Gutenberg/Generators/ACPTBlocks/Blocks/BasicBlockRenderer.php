<?php

namespace ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Blocks;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Currencies;
use ACPT\Core\Helper\Lengths;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Weights;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Audio;
use ACPT\Utils\PHP\Barcode;
use ACPT\Utils\PHP\Country;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\PHP\Email;
use ACPT\Utils\PHP\ImageSlider;
use ACPT\Utils\PHP\Maps;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\PHP\QRCode;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\WPAttachment;
use ACPT\Utils\Wordpress\WPUtils;

class BasicBlockRenderer
{
    /**
     * @param $attributes
     * @param $content
     *
     * @return string
     */
    public function render($attributes, $content)
    {
        if(isset($attributes['field'])){

            $closingDiv = '</div>';
            $openingDiv = str_replace($closingDiv, '', $content);

            $field = json_decode($attributes['field'], true);
            $className = $attributes['className'] ?? null;
            $align = $attributes['align'] ?? 'left';
            $prefix = $attributes['prefix'] ?? null;
            $suffix = $attributes['suffix'] ?? null;

            if(!is_array($field) or empty($field)){
                return null;
            }

            $rawData = $prefix;
            $rawData .= $this->getRawData($field, $attributes);
            $rawData .= $suffix;

            if($rawData === null){
                return null;
            }

            return $openingDiv.'<p style="text-align: '.$align.';" class="'.$className.'">'.$rawData.'</p>'.$closingDiv;
        }

        return null;
    }

    /**
     * @param $field
     * @param array $attributes
     *
     * @return mixed|string|null
     */
    private function getRawData($field, $attributes = [])
    {
        $findValue = null;

        // try to calculate $find and $findValue
        if(isset($field['belongsTo']) and $field['belongsTo'] === MetaTypes::OPTION_PAGE){
            $findValue = $field['find'];
            $find = 'option_page';
        } elseif(isset($field['belongsTo']) and $field['belongsTo'] === MetaTypes::TAXONOMY){
            $find = 'term_id';
            $termId = null;

            // Front-end rendering
            $queriedObject = get_queried_object();
            if($queriedObject instanceof \WP_Term){
                $termId = $queriedObject->term_id;
            }

            // try to calculate $termId from HTTP_REFERER (AJAX request)
            if($termId === null){
                $referer = $_SERVER['HTTP_REFERER'];
                $parsedReferer = parse_url($referer);
                parse_str(  $parsedReferer['query'], $parsedRefererArray );

                $prefix = wp_get_theme()->get_stylesheet()."//".$field['find']."-";
                $taxonomySlug = str_replace($prefix, "", $parsedRefererArray['postId']);

                $term = get_term_by('slug', $taxonomySlug, $field['find'] );
                $termId = $term->term_id;
            }

            $findValue = (isset($attributes['postId']) and $attributes['postId'] < 99999999999999999) ? $attributes['postId'] : $termId;
        } else {
            global $post;

            $find = 'post_id';
            $findValue = (isset($attributes['postId']) and $attributes['postId'] < 99999999999999999) ? $attributes['postId'] : $post->ID;
        }

        // static preview if no context is available
        if(empty($findValue)){
            return '{acpt_'.$field['box'].'_'.$field['field'].'}';
        }

        if(isset($field['block_name'])){
            $args = [
                $find => $findValue,
                'box_name' => $field['box'],
                'field_name' => $field['field'],
                'parent_field_name' => $field['parent_field'],
                'index' => $field['index'],
                'block_name' => $field['block_name'],
                'block_index' => $field['block_index'],
                'with_context' => true,
            ];

            $rawData = get_acpt_block_child_field($args);
        } elseif(isset($field['parent_field'])){
            $args = [
                $find => $findValue,
                'box_name' => $field['box'],
                'field_name' => $field['field'],
                'parent_field_name' => $field['parent_field'],
                'index' => $field['index'],
                'with_context' => true,
            ];

            $rawData = get_acpt_child_field($args);
        } else {
            $args = [
                $find => $findValue,
                'box_name' => $field['box'],
                'field_name' => $field['field'],
                'with_context' => true,
            ];

            $rawData = get_acpt_field($args);
        }

        if(!is_acpt_field_visible($args)){
            return null;
        }

        if(empty($rawData)){
            return null;
        }

        if(!isset($rawData['value'])){
            return null;
        }

        $value = $rawData['value'];
        $after = $rawData['after'] ?? null;
        $before = $rawData['before'] ?? null;

        switch ($field['type']){

            // ADDRESS_TYPE
            case MetaFieldModel::ADDRESS_TYPE:

                if(!isset($value['address']) and empty($value['address'])){
                    return null;
                }

                if(!isset($value['lat']) and empty($value['lat'])){
                    return null;
                }

                if(!isset($value['lng']) and empty($value['lng'])){
                    return null;
                }

                $display = !empty($attributes['display']) ? $attributes['display'] : 'text';
                $zoom = !empty($attributes['zoom']) ? $attributes['zoom'] : 14;
                $width = !empty($attributes['width']) ? $attributes['width'] : "100%";
                $height = !empty($attributes['height']) ? $attributes['height'] : 500;

                if($display === 'text'){
                    return $value['address'];
                }

                return $before . Maps::render($width, $height, $value['address'], $zoom, $value['lat'], $value['lng'] ) . $after;

            // ADDRESS_MULTI_TYPE
            case MetaFieldModel::ADDRESS_MULTI_TYPE:

                if(empty($value)){
                    return null;
                }

                $display = !empty($attributes['display']) ? $attributes['display'] : 'text';
                $zoom = !empty($attributes['zoom']) ? $attributes['zoom'] : 14;
                $width = !empty($attributes['width']) ? $attributes['width'] : "100%";
                $height = !empty($attributes['height']) ? $attributes['height'] : 500;

                if($display === 'text'){

                    $addresses = [];
                    foreach ($value as $datum){
                        $addresses[] = $datum['address'];
                    }

                    return implode(', ', $addresses);
                }

                return $before . Maps::renderMulti($width, $height, $zoom, $value, true) . $after;

            // AUDIO_TYPE
            case MetaFieldModel::AUDIO_TYPE:

                // for fields nested in a repeater
                if(is_string($value)){
                    $value = WPAttachment::fromUrl($value);
                }

                if(!$value instanceof WPAttachment){
                    return null;
                }

                // preview in editor
                if(is_admin() or wp_doing_ajax() or (isset($_REQUEST['context']) and $_REQUEST['context'] === 'edit')){
                    return $before . $value->render() . $after;
                }

                // custom_audio_player
                $customPlayer = (isset($field['advanced_options'][33]) and isset($field['advanced_options'][33]['value']) and $field['advanced_options'][33]['value'] == 1) ? true : false;

                // Accepts 'light' or 'dark'.
                $style = $attributes['audioStyle'] ?? "light";

                return $before . Audio::single($value, $customPlayer, $style) . $after;

            // AUDIO_MULTI_TYPE
            case MetaFieldModel::AUDIO_MULTI_TYPE:

                if(!is_array($value)){
                    return null;
                }

                // preview in editor
                if(is_admin() or wp_doing_ajax() or (isset($_REQUEST['context']) and $_REQUEST['context'] === 'edit')){
                    $preview = "<div style='display: flex; gap: 10px; flex-direction: column'>";

                    foreach ($value as $attachment){

                        // for fields nested in a repeater
                        if(is_string($attachment)){
                            $attachment = WPAttachment::fromUrl($attachment);
                        }

                        if($attachment instanceof WPAttachment){
                            $preview .= $before . $attachment->render() . $after;
                        }
                    }

                    $preview .= "</div>";

                    return $preview;
                }

                // custom_audio_player
                $customPlayer = (isset($field['advanced_options'][33]) and isset($field['advanced_options'][33]['value']) and $field['advanced_options'][33]['value'] == 1) ? true : false;

                // Accepts 'light' or 'dark'.
                $style = $attributes['audioStyle'] ?? "light";

                // for fields nested in a repeater
                foreach ($value as $index => $attachment){
                    if(is_string($attachment)){
                        $value[$index] = WPAttachment::fromUrl($attachment);
                    }
                }

                return Audio::playlist($value, $customPlayer, $style);

            // COUNTRY_TYPE
            case MetaFieldModel::COUNTRY_TYPE:

                if(is_array($value) and !empty($value) and isset($value['value'])){
                    $display = $attributes['display'] ?? "text";

                    if($display === 'flag' and isset($value['country'])){
                        return Country::getFlag($value['country']);
                    }

                    if($display === 'full' and isset($value['country'])){
                        return Country::fullFormat($value['country'], $value['value']);
                    }

                    return $before . $value['value'] . $after;
                }

                break;

            // CURRENCY_TYPE
            case MetaFieldModel::CURRENCY_TYPE:

                if(!isset($value['amount']) or empty($value['amount'])){
                    return null;
                }

                if(!isset($value['unit'])){
                    return null;
                }

                $decimalPoints = $attributes['uomFormatDecimalPoints'] ?? 0;

                if($decimalPoints < 0){
                    $decimalPoints = 0;
                }

                $decimalSeparator = $attributes['uomFormatDecimalSeparator'] ?? ".";
                $thousandsSeparator = $attributes['uomFormatThousandsSeparator'] ?? ",";
                $currencyFormat = $attributes['uomFormat'] ?? "full";
                $currencyPosition = $attributes['uomPosition'] ?? "after";

                $amount = $rawData['amount'];
                $unit = $rawData['unit'];

                $amount = number_format($amount, (int)$decimalPoints, $decimalSeparator, $thousandsSeparator);
                $unit = ($currencyFormat === 'abbreviation') ? Currencies::getSymbol($unit) : $unit;

                if($currencyPosition === 'none'){
                    if($amount === null){
                        return null;
                    }

                    return $before . $amount . $after;
                }

                if($currencyPosition === 'prepend'){
                    if($unit === null or $amount === null){
                        return null;
                    }

                    return $before . $unit . ' ' . $amount . $after;
                }

                if($unit === null or $amount === null){
                    return null;
                }

                return $before . $amount . ' ' . $unit . $after;

            // DATE_RANGE_TYPE
            case MetaFieldModel::DATE_RANGE_TYPE:

                if(!isset($value['value'])){
                    return null;
                }

                if(!isset($value['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $value['object'];
                $val = $value['value'];

                if(is_array($val) and !empty($val) and count($val) === 2){

                    if( !empty($attributes['dateFormat']) and Date::isDateFormatValid($attributes['dateFormat']) ){
                        if(is_array($dateTimeObject) and !empty($dateTimeObject) and count($dateTimeObject) === 2){

                            if(!$dateTimeObject[0] instanceof \DateTime){
                                return null;
                            }

                            if(!$dateTimeObject[1] instanceof \DateTime){
                                return null;
                            }

                            $value  = $before;
                            $value .= Date::format($attributes['dateFormat'], $dateTimeObject[0]);
                            $value .= ' - ';
                            $value .= Date::format($attributes['dateFormat'], $dateTimeObject[1]);
                            $value .= $after;

                            return $value;
                        }
                    }

                    $value  = $before;
                    $value -= $value[0];
                    $value .= ' - ';
                    $value .= $value[1];
                    $value .= $after;

                    return $value;
                }

                break;

            // DATE_TIME_TYPE
            case MetaFieldModel::DATE_TIME_TYPE:

                if(!isset($value['value'])){
                    return null;
                }

                if(!isset($value['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $value['object'];
                $val = $value['value'];

                if(
                    !empty($attributes['dateFormat']) and
                    !empty($attributes['timeFormat']) and
                    Date::isDateFormatValid($attributes['dateFormat']) and
                    Date::isDateFormatValid($attributes['timeFormat'])
                ){
                    $dateFormat = $attributes['dateFormat'];
                    $timeFormat = $attributes['timeFormat'];
                    $format = $dateFormat . ' ' . $timeFormat;

                    return $before . Date::format($format, $dateTimeObject) . $after;
                }

                return $before . $val . $after;

            // DATE_TYPE
            case MetaFieldModel::DATE_TYPE:

                if(!isset($value['value'])){
                    return null;
                }

                if(!isset($value['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $value['object'];
                $val = $value['value'];

                if( !empty($attributes['dateFormat']) and Date::isDateFormatValid($attributes['dateFormat']) ){
                    return $before . Date::format($attributes['dateFormat'], $dateTimeObject) . $after;
                }

                return $before . $val . $after;

            // EMAIL_TYPE
            case MetaFieldModel::EMAIL_TYPE:

                if(!is_string($value)){
                    return null;
                }

                if(isset($attributes['display']) and $attributes['display'] === 'link' and $value !== null){
                    return '<a href="mailto:'.Email::sanitize($value).'">'.$before . $value . $after.'</a>';
                }

                return $before . $value . $after;

            // EMBED_TYPE
            case MetaFieldModel::EMBED_TYPE:

                $width = !empty($attributes['width']) ? $attributes['width'] : 180;
                $height = !empty($attributes['height']) ? $attributes['height'] : 135;

                $shortCode = (new \WP_Embed())->shortcode([
                    'width' => $width,
                    'height' => $height,
                ], $value);

                if(!$shortCode){
                    return null;
                }

                return $before . $shortCode . $after;

            // FILE_TYPE
            case MetaFieldModel::FILE_TYPE:

                if(!isset($value['file'])){
                    return null;
                }

                if(!$value['file'] instanceof WPAttachment){
                    return null;
                }

                $label = $before;
                $label .= (!empty($rawData['label'])) ? $rawData['label'] : $value['file']->getTitle();
                $label .= $after;

                return '<a href="'.$value['file']->getSrc().'" target="_blank">'.$label.'</a>';

            // CURRENCY_TYPE
            case MetaFieldModel::GALLERY_TYPE:

                add_action( 'wp_enqueue_scripts', [new BasicBlockRenderer(), 'enqueueStyle'] );

                if(!is_array($value)){
                    return null;
                }

                if(empty($value)){
                    return null;
                }

                $sort = !empty($attributes['sort']) ? $attributes['sort'] : 'asc';
                $display = !empty($attributes['display']) ? $attributes['display'] : 'mosaic';
                $gap = !empty($attributes['gap']) ? $attributes['gap'] : 20;
                $elementsPerRow = !empty($attributes['elements']) ? $attributes['elements'] : 3;

                if($sort === 'desc'){
                    $value = array_reverse($value);
                }

                if($sort === 'rand'){
                    shuffle($value);
                }

                // Carousel
                if($display === 'carousel'){
                    $carousel = '<div class="acpt-gallery carousel per-row-'.$elementsPerRow.'">';

                    for ($i = 1; $i <= count($rawData); $i++){
                        $carousel .= '<input type="radio" name="slides" '. ($i === 1 ? 'checked="checked"' : '') .' id="slide-'.$i.'">';
                    }

                    $carousel .= '<ul class="carousel__slides">';

                    /** @var WPAttachment $image */
                    foreach ($value as $index => $image){
                        if(!$image instanceof WPAttachment){
                            return null;
                        }

                        $marginLeft = $index*100;

                        $carousel .= ' <li class="carousel__slide" style="--slide-margin: -'.$marginLeft.'%;" >'.$this->renderImage('carousel', $image, $attributes).'</li>';
                    }

                    $carousel .= '</ul>';
                    $carousel .= '<ul class="carousel__thumbnails">';

                    /** @var WPAttachment $image */
                    foreach ($value as $index => $image){
                        if(!$image instanceof WPAttachment){
                            return null;
                        }

                        $borderRadius = !empty($attributes['borderRadius']) ? $attributes['borderRadius'] : null;
                        $imgStyle = '';

                        if($borderRadius !== null){
                            $imgStyle .= 'border-radius: '.$borderRadius['top'].' '.$borderRadius['right'].' '.$borderRadius['bottom'].' '.$borderRadius['left'].';';
                        }

                        $carousel .= '<li>
                    		<label for="slide-'.($index+1).'">
                    		    '.$image->render([
                                    'style' => $imgStyle,
                                    'size'  => 'medium'
                                ]).'
                    		</label>
                		</li>';
                    }

                    $carousel .= '</ul>';
                    $carousel .= '</div>';

                    return $carousel;
                }

                // Masonry
                if($display === 'masonry'){
                    $masonry = '<div style="column-gap:'.$gap.'px;" class="acpt-gallery masonry per-row-'.$elementsPerRow.'">';

                    /** @var WPAttachment $image */
                    foreach ($value as $image){
                        if(!$image instanceof WPAttachment){
                            return null;
                        }

                        $masonry .= $this->renderImage('masonry', $image, $attributes);
                    }

                    $masonry .= '</div>';

                    return $masonry;
                }

                // Mosaic
                $mosaic = '<div style="gap:'.$gap.'px;" class="acpt-gallery mosaic per-row-'.$elementsPerRow.'">';

                /** @var WPAttachment $image */
                foreach ($value as $image){
                    if(!$image instanceof WPAttachment){
                        return null;
                    }

                    $mosaic .= $this->renderImage('mosaic', $image, $attributes);
                }

                $mosaic .= '</div>';

                return $mosaic;

            // ICON_TYPE
            case MetaFieldModel::ICON_TYPE:

                $fontSize = $attributes['fontSize'] ?? null;
                $color = $attributes['color'] ?? null;

                $styles = '';

                if($color !== null){
                    $styles .= 'color: ' . $color . ';';
                }

                if($fontSize !== null and is_numeric($fontSize)){
                    $styles .= 'font-size: ' . $fontSize . 'px;';
                }

                return '<span style="'.$styles.'">' . $before . $value . $after . '</span>';

            // IMAGE_SLIDER_TYPE
            case MetaFieldModel::IMAGE_SLIDER_TYPE:

                if(!is_array($value)){
                    return null;
                }

                if(count($value) > 2){
                    return null;
                }

                $defaultPercent = (isset($field['advanced_options'][41]) and !empty($field['advanced_options'][41])) ? $field['advanced_options'][41]['value'] : 50;

                return ImageSlider::render($value, $defaultPercent);

            // IMAGE_TYPE
            case MetaFieldModel::IMAGE_TYPE:

                if(!$value instanceof WPAttachment){
                    return null;
                }

                return $before . $this->renderImage('single', $value, $attributes) . $after;

            // LENGTH_TYPE
            case MetaFieldModel::LENGTH_TYPE:

                if(!isset($value['length']) or empty($value['length'])){
                    return null;
                }

                if(!isset($value['unit'])){
                    return null;
                }

                $decimalPoints = $attributes['uomFormatDecimalPoints'] ?? 0;

                if($decimalPoints < 0){
                    $decimalPoints = 0;
                }

                $decimalSeparator = $attributes['uomFormatDecimalSeparator'] ?? ".";
                $thousandsSeparator = $attributes['uomFormatThousandsSeparator'] ?? ",";
                $currencyFormat = $attributes['uomFormat'] ?? "full";
                $currencyPosition = $attributes['uomPosition'] ?? "after";

                $length = $value['length'];
                $unit = $value['unit'];

                $length = number_format($length, (int)$decimalPoints, $decimalSeparator, $thousandsSeparator);
                $unit = ($currencyFormat === 'abbreviation') ? Lengths::getSymbol($unit) : $unit;

                if($currencyPosition === 'none'){
                    if($length === null){
                        return null;
                    }

                    return $before . $length . $after;
                }

                if($currencyPosition === 'prepend'){
                    if($unit === null or $length === null){
                        return null;
                    }

                    return $before . $unit . ' ' . $length . $after;
                }

                if($unit === null or $length === null){
                    return null;
                }

                return $before . $length . ' ' . $unit . $after;

            // CURRENCY_TYPE
            case MetaFieldModel::LIST_TYPE:
                $display = !empty($attributes['display']) ? $attributes['display'] : 'ul';

                if($display === 'ul'){
                    $ul = '<ul>';

                    foreach ($value as $item){
                        $ul .= '<li>'.$before . $item . $after.'</li>';
                    }

                    $ul .= '</ul>';

                    return $ul;
                }

                if($display === 'ol'){
                    $ol = '<ol>';

                    foreach ($value as $item){
                        $ol .= '<li>'.$before . $item . $after.'</li>';
                    }

                    $ol .= '</ol>';

                    return $ol;
                }

                if(!is_array($value)){
                    return null;
                }

                return implode(", ", $value);

            // PHONE_TYPE
            case MetaFieldModel::PHONE_TYPE:

                $format = isset($attributes['phoneFormat']) ? $attributes['phoneFormat'] : Phone::FORMAT_E164;
                $val = $value['value'];
                $dial = $value['dial'];
                $phone = Phone::format($val, $dial, $format);

                if(isset($attributes['display']) and $attributes['display'] === 'link'){
                    return '<a href="'.Phone::format($phone, null, Phone::FORMAT_RFC3966).'" target="_blank">'. $before . $phone . $after .'</a>';
                }

                return $phone;

            // RATING_TYPE
            case MetaFieldModel::RATING_TYPE:

                $display = $attributes['display'] ?? 'star';
                $color = $attributes['color'] ?? null;
                $styles = '';

                if($color !== null){
                    $styles .= 'color: ' . $color . ';';
                }

                $rating = ($display === 'star') ? Strings::renderStars($value) : Strings::renderRatingAsString($value);

                return '<span style="'.$styles.'">' . $before . $rating . $after . '</span>';

            // SELECT_MULTI_TYPE
            case MetaFieldModel::CHECKBOX_TYPE:
            case MetaFieldModel::SELECT_MULTI_TYPE:
                $display = $attributes['display'] ?? 'list';

                if($display === 'list'){
                    $ul = '<ul>';

                    foreach ($value as $item){
                        $ul .= '<li>'.$before . $item . $after.'</li>';
                    }

                    $ul .= '</ul>';

                    return $ul;
                }

                if(!is_array($value)){
                    return null;
                }

                return implode(", ", $value);

            // TEXTAREA_TYPE
            case MetaFieldModel::TEXTAREA_TYPE:

                if(!is_string($value)){
                    return null;
                }

                return $before . WPUtils::renderShortCode($value, true) . $after;

            // TIME_TYPE
            case MetaFieldModel::TIME_TYPE:

                if(!isset($value['value'])){
                    return null;
                }

                if(!isset($value['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $value['object'];
                $val = $value['value'];

                if( !empty($attributes['timeFormat']) and Date::isDateFormatValid($attributes['timeFormat']) ){
                    return  $before . Date::format($attributes['timeFormat'], $dateTimeObject) . $after;
                }

                return $before . $val . $after;

            // BARCODE_TYPE
            case MetaFieldModel::BARCODE_TYPE:
                return $before . Barcode::render($value) . $after;

            // QR_CODE_TYPE
            case MetaFieldModel::QR_CODE_TYPE:
                return $before . QRCode::render($value) . $after;

            // URL_TYPE
            case MetaFieldModel::URL_TYPE:

                if(!isset($value['url'])){
                    return null;
                }

                $url = $value['url'];
                $label = $value['label'] ?? $url;
                $target = $attributes['target'] ?? '_self';
                $gradient = $attributes['gradient'] ?? null;
                $textColor = $attributes['textColor'] ?? null;
                $backgroundColor = $attributes['backgroundColor'] ?? null;
                $border = $attributes['border'] ?? [];
                $borderRadius = $attributes['borderRadius'] ?? [];
                $padding = $attributes['padding'] ?? [];

                $style = '';

                if($backgroundColor !== null){
                    $style .= 'background-color: '.$backgroundColor.';';
                }

                if($gradient !== null){
                    $style .= 'background-image: '.$gradient.';';
                }

                if($textColor !== null){
                    $style .= 'color: '.$textColor.';';
                }

                if($border !== null){
                    $borderWidth = $border['width'] ?? '1px';
                    $borderColor = $border['color'] ?? 'inherit';
                    $borderStyle = $border['style'] ?? 'solid';

                    $style .= 'border: '.$borderWidth.' '.$borderStyle.' '.$borderColor.';';
                }

                if(!empty($padding)){
                    $style .= 'padding: '.implode(' ', $padding).';';
                }

                if(!empty($borderRadius)){
                    $style .= 'border-radius: '.implode(' ', $borderRadius).';';
                }

                if($url === null){
                    return null;
                }

                if(!is_string($url)){
                    return null;
                }

                return '<a style="'.$style.'" href="'.Url::sanitize($url).'" target="'.$target.'">'.$before . $label . $after .'</a>';

            // VIDEO_TYPE
            case MetaFieldModel::VIDEO_TYPE:

                if(!$value instanceof WPAttachment){
                    return null;
                }

                $width = $attributes['width'] ?? "100%";
                $height = $attributes['height'] ?? null;

                return $before . $value->render([
                    'w' => $width,
                    'h' => $height,
                ]) . $after;

            // WEIGHT_TYPE
            case MetaFieldModel::WEIGHT_TYPE:

                if(!isset($value['weight']) or empty($value['weight'])){
                    return null;
                }

                if(!isset($value['unit'])){
                    return null;
                }

                $decimalPoints = $attributes['uomFormatDecimalPoints'] ?? 0;

                if($decimalPoints < 0){
                    $decimalPoints = 0;
                }

                $decimalSeparator = $attributes['uomFormatDecimalSeparator'] ?? ".";
                $thousandsSeparator = $attributes['uomFormatThousandsSeparator'] ?? ",";
                $currencyFormat = $attributes['uomFormat'] ?? "full";
                $currencyPosition = $attributes['uomPosition'] ?? "after";

                $weight = $value['weight'];
                $unit = $value['unit'];

                $weight = number_format($weight, (int)$decimalPoints, $decimalSeparator, $thousandsSeparator);
                $unit = ($currencyFormat === 'abbreviation') ? Weights::getSymbol($unit) : $unit;

                if($currencyPosition === 'none'){
                    if($weight === null){
                        return null;
                    }

                    return $before . $weight . $after;
                }

                if($currencyPosition === 'prepend'){
                    if($unit === null or $weight === null){
                        return null;
                    }

                    return $before .$unit . ' ' . $weight . $after;
                }

                if($unit === null or $weight === null){
                    return null;
                }

                return $before . $weight . ' ' . $unit . $after;

            default:

                if(is_string($value)){
                    return $before . do_shortcode($value) . $after;
                }

                return null;
        }
    }

    /**
     * @param string $display
     * @param WPAttachment $image
     * @param $attributes
     *
     * @return string
     */
    private function renderImage($display, WPAttachment $image, $attributes)
    {
        $gap = !empty($attributes['gap']) ? $attributes['gap'] : 20;
        $width = $attributes['width'] ?? null;
        $height = $attributes['height'] ?? null;
        $border = !empty($attributes['border']) ? $attributes['border'] : null;
        $borderRadius = !empty($attributes['borderRadius']) ? $attributes['borderRadius'] : null;
        $imgStyle = '';
        $figureStyle = '';

        if($border !== null){
            $imgStyle .= 'border: '.$border['width'].' '. ((isset($border['style'])) ? $border['style'] : 'solid').' '.$border['color'].';';
        }

        if($borderRadius !== null){
            $imgStyle .= 'border-radius: '.$borderRadius['top'].' '.$borderRadius['right'].' '.$borderRadius['bottom'].' '.$borderRadius['left'].';';
        }

        if($display === 'masonry'){
            $figureStyle .= "margin: 0 0 ".$gap."px;";
        }

        return '<figure style="'.$figureStyle.'">
                '.$image->render([
                    'w' => $width,
                    'h' => $height,
                    'style' => $imgStyle,
                ]).'
            </figure>';
    }

    /**
     * Enqueue CSS scripts
     */
    public function enqueueStyle()
    {
        wp_register_style( 'gallery-css', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/gallery.css' : 'advanced-custom-post-type/assets/static/css/gallery.min.css'), [], ACPT_PLUGIN_VERSION );
        wp_enqueue_style( 'gallery-css' );
    }
}