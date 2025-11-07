<?php

namespace ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Blocks;

use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Currencies;
use ACPT\Core\Helper\Lengths;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Weights;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Arrays;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\PHP\Email;
use ACPT\Utils\PHP\QRCode;
use ACPT\Utils\Wordpress\Users;
use ACPT\Utils\Wordpress\WPAttachment;
use ACPT\Utils\Wordpress\WPUtils;

class RepeaterLoopBlockRenderer extends AbstractBlockRenderer
{
    /**
     * @param $attributes
     * @param $content
     *
     * @return string
     */
    public function render($attributes, $content)
    {
        $rawValue = $this->rawValue($attributes);

        if(empty($rawValue)){
            return null;
        }

        if($rawValue->type === MetaFieldModel::REPEATER_TYPE){
            return $this->renderRepeaterField($rawValue, $content);
        }

        if($rawValue->type === MetaFieldModel::FLEXIBLE_CONTENT_TYPE){
            $block = json_decode($attributes['block'], true);

            return $this->renderFlexibleField($rawValue, $block, $content);
        }
    }

    /**
     * @param $rawValue
     * @param $content
     *
     * @return string|null
     */
    private function renderRepeaterField($rawValue, $content)
    {
        if(empty($rawValue->children)){
            return null;
        }

        $return = '';
        $values = $rawValue->value;

        if(!empty($values)){
            foreach ($values as $index => $value){

                $s = $content;

                foreach ($value as $key => $v){
                    $regex = '/{{([^<>!{}]+?|)'.$key.'([^<>!{}]+?|)}}/iu';
                    preg_match_all($regex, $content, $matches);

                    $field = array_filter($rawValue->children, function ($child) use ($key) {
                        return $child->name === $key;
                    });

                    $field = Arrays::reindex($field);

                    if(count($field) === 1 and isset($matches[0][0])){
                        $matchedKey = $matches[0][0];
                        $args = $this->extractArgs($matchedKey);
                        $s = $this->replaceContent($matchedKey, $v, $field[0], $s, $args);
                    }
                }

                $return .= $s;
            }
        }

        return $return;
    }

    /**
     * @param $rawValue
     * @param $block
     * @param $content
     *
     * @return string
     */
    private function renderFlexibleField($rawValue, $block, $content)
    {
        if(empty($rawValue->blocks)){
            return null;
        }

        if(empty($rawValue->value)){
            return null;
        }

        $return = '';
        $blockName = $block['name'];
        $values = $rawValue->value['blocks'];

        if(!empty($values)){
            foreach ($values as $b){
                if(isset($b[$blockName]) and count($b[$blockName]) > 0){

                    $bl = $b[$blockName];
                    $len = count($bl[array_keys($bl)[0]]);

                    for($i = 0; $i < $len; $i++){
                        $s = $content;

                        foreach ($b[$blockName] as $key => $val){

                            if(isset($val[$i])){
                                $v = $val[$i];
                                $regex = '/{{([^<>!{}]+?|)'.$key.'([^<>!{}]+?|)}}/iu';
                                preg_match_all($regex, $content, $matches);

                                $fetchedBlock = array_filter($rawValue->blocks, function ($child) use ($block) {
                                    return $child->name === $block['name'];
                                });

                                $fetchedBlock = Arrays::reindex($fetchedBlock);

                                if(!empty($fetchedBlock) and count($fetchedBlock) === 1){
                                    $field = array_filter($fetchedBlock[0]->fields, function ($child) use ($key) {
                                        return $child->name === $key;
                                    });

                                    $field = Arrays::reindex($field);

                                    if(count($field) === 1 and isset($matches[0][0])){
                                        $matchedKey = $matches[0][0];
                                        $args = $this->extractArgs($matchedKey);
                                        $s = $this->replaceContent($matchedKey, $v, (object)$field[0], $s, $args);
                                    }
                                }
                            }
                        }

                        $return .= $s;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    private function extractArgs($key)
    {
        $args = explode(":", str_replace(["{{", "}}"], "", $key));
        unset($args[0]);

        return Arrays::reindex($args);
    }

    /**
     * @param string    $key
     * @param array     $value
     * @param \stdClass $field
     * @param string    $content
     * @param array     $args
     *
     * @return string
     */
    private function replaceContent($key, $value, $field, $content, $args = [])
    {
        $after    = $value['after'] ?? null;
        $before   = $value['before'] ?? null;
        $rawValue = $value['value'] ?? null;

        if(empty($rawValue)){
            return $content;
        }

        $realValue = $this->realValue($field->type, $rawValue, $args, $before, $after);

        return str_replace($key, $realValue, $content);
    }

    /**
     * @param       $fieldType
     * @param       $rawValue
     * @param array $args
     * @param null  $before
     * @param null  $after
     *
     * @return string|null
     */
    private function realValue($fieldType, $rawValue, $args = [], $before = null, $after = null)
    {
        switch ($fieldType){

            // ADDRESS_TYPE
            case MetaFieldModel::ADDRESS_TYPE:

                if(is_array($rawValue) and  isset($rawValue['address'])){
                    $value = $rawValue['address'];

                    if(!empty($args)){
                        foreach ($args as $arg){
                            switch ($arg){
                                case "lat":
                                    if(isset($rawValue['lat'])){
                                        return $rawValue['lat'];
                                    }

                                    return null;

                                case "lng":
                                    if(isset($raw_acpt_value['lng'])){
                                        return $rawValue['lng'];
                                    }

                                    return null;

                                case "city":
                                    if(isset($raw_acpt_value['city'])){
                                        return $rawValue['city'];
                                    }

                                    return null;

                                case "country":
                                    if(isset($raw_acpt_value['country'])){
                                        return $rawValue['country'];
                                    }

                                    return null;
                            }
                        }
                    }

                    return $value;
                }

                return null;

            // @TODO case MetaFieldModel::ADDRESS_MULTI_TYPE:

            // COUNTRY_TYPE
            case MetaFieldModel::COUNTRY_TYPE:

                if(is_array($rawValue) and isset($rawValue['value'])){
                    return $rawValue['value'];
                }

                return null;

            // CURRENCY_TYPE
            case MetaFieldModel::CURRENCY_TYPE:
                if(
                    is_array($rawValue) and
                    isset($rawValue['amount']) and
                    isset($rawValue['unit'])
                ){
                    $value = $rawValue['amount']['value'];
                    $after = $rawValue['amount']['after'];
                    $before = $rawValue['amount']['before'];
                    $unit = $rawValue['unit'];

                    return $before.$this->renderAmount($value, $unit,'currency', $args).$after;
                }

                return null;

            // DATE_TYPE
            case MetaFieldModel::DATE_TYPE:

                if(is_string($rawValue)){
                    $value = $rawValue;

                    if($rawValue !== null and is_string($rawValue) and $rawValue !== ''){

                        if(!empty($args)){
                            $format = null;
                            foreach ($args as $arg){
                                if(Date::isDateFormatValid($arg)){
                                    $format = $arg;
                                    break;
                                }
                            }

                            if($format){
                                return  $before. Date::format($format, $value) . $after;
                            }
                        }
                    }

                    return $value;
                }

                return null;

            // DATE_RANGE_TYPE
            case MetaFieldModel::DATE_RANGE_TYPE:

                if(is_array($rawValue) and !empty($rawValue) and count($rawValue) === 2){

                    $format = null;
                    $from = $rawValue[0];
                    $to = $rawValue[1];

                    if(!empty($args)){
                        foreach ($args as $arg){
                            if(Date::isDateFormatValid($arg)){
                                $format = $arg;
                                break;
                            }
                        }

                        if($format !== null){
                            $from = $before. Date::format($format, $from) . $after;
                            $to = $before. Date::format($format, $to) . $after;
                        }
                    }

                    $value = $from;
                    $value .= ' - ';
                    $value .= $to;

                    return $value;
                }

                break;

            // DATE_TIME_TYPE
            case MetaFieldModel::DATE_TIME_TYPE:

                if(is_string($rawValue)){
                    $value = $rawValue;

                    if($rawValue !== null and is_string($rawValue) and $rawValue !== ''){

                        if(!empty($args)){

                            $dateFormat = $args[0];
                            $format = $dateFormat;
                            unset($args[0]);

                            if(!empty($args)){
                                $timeFormat = implode(":", $args);
                                $format .= " " . $timeFormat;
                            }

                            if(!empty($format) and Date::isDateFormatValid($format)){
                                return $before . Date::format($format, $value) . $after;
                            }
                        }
                    }

                    return $value;
                }

                return null;

            // EDITOR_TYPE
            case MetaFieldModel::EDITOR_TYPE:

                if(!is_string($rawValue)){
                    return null;
                }

                $value = WPUtils::removeEmptyParagraphs($rawValue);
                $value = WPUtils::renderShortCode($value);

                return $value;

            // EMAIL_TYPE
            case MetaFieldModel::EMAIL_TYPE:

                if(!is_string($rawValue)){
                    return null;
                }

                if(!empty($args) and $args[0] === 'url'){
                    return 'mailto:'.Email::sanitize($rawValue);
                }

                if(!empty($args) and $args[0] === 'link'){
                    return '<a href="mailto:'.Email::sanitize($rawValue).'">'.$before.$rawValue.$after.'</a>';
                }

                return $rawValue;

            // @TODO case MetaFieldModel::GALLERY_TYPE:

            // AUDIO_TYPE
            // IMAGE_TYPE
            // FILE_TYPE
            // VIDEO_TYPE
            case MetaFieldModel::AUDIO_TYPE:
            case MetaFieldModel::IMAGE_TYPE:
            case MetaFieldModel::FILE_TYPE:
            case MetaFieldModel::VIDEO_TYPE:

                if(!$rawValue instanceof WPAttachment){
                    return null;
                }

                if(!empty($args) and $args[0] === 'url'){
                    return $rawValue->getSrc();
                }

                if(!empty($args) and $args[0] === 'id'){
                    return $rawValue->getId();
                }

                return $rawValue->render();

            // LENGTH_TYPE
            case MetaFieldModel::LENGTH_TYPE:

                if(
                    is_array($rawValue) and
                    isset($rawValue['length']) and
                    isset($rawValue['unit'])
                ){
                    $value = $rawValue['length']['value'];
                    $after = $rawValue['length']['after'];
                    $before = $rawValue['length']['before'];
                    $unit = $rawValue['unit'];

                    return $before.$this->renderAmount($value, $unit,'length', $args).$after;
                }

                return null;

            // CHECKBOX_TYPE
            // SELECT_MULTI_TYPE
            // LIST_TYPE
            case MetaFieldModel::LIST_TYPE:
            case MetaFieldModel::SELECT_MULTI_TYPE:
            case MetaFieldModel::CHECKBOX_TYPE:
                return $this->renderList($rawValue, $args);

            // NUMBER_TYPE
            case MetaFieldModel::NUMBER_TYPE:

                if(!is_numeric($rawValue)){
                    return null;
                }

                $value = $rawValue;
                $decimals = (isset($args[0]) ? (int)$args[0] : null);
                $decimal_point = (isset($args[1]) ? $args[1] : null);
                $separator = (isset($args[2]) ? $args[2] : null);

                if($decimals !== null and $decimal_point === null and $separator === null){
                    $value = number_format($rawValue, $decimals, ".", "");
                } elseif($decimals !== null and $decimal_point and $separator === null){
                    $value = number_format($rawValue, $decimals, $decimal_point, "");
                } elseif($decimals !== null and $decimal_point and $separator){
                    $value = number_format($rawValue, $decimals, $decimal_point, $separator);
                }

                return $value;

            // POST_OBJECT_TYPE
            case MetaFieldModel::POST_OBJECT_TYPE:

                if(!is_scalar($rawValue)){
                    return null;
                }

                $post = get_post((int)$rawValue);

                if(!$post instanceof \WP_Post){
                    return null;
                }

                if(!empty($args) and $args[0] === 'id'){
                    return $rawValue;
                }

                if(!empty($args) and $args[0] === 'title'){
                    return $post->post_title;
                }

                return "<a href='".get_permalink((int)$rawValue)."'>".$post->post_title."</a>";

            // POST_OBJECT_MULTI_TYPE
            case MetaFieldModel::POST_OBJECT_MULTI_TYPE:

                if(!is_array($rawValue)){
                    return null;
                }

                $posts = [];

                foreach ($rawValue as $pid){
                    $post = get_post((int)$pid);

                    if(!$post instanceof \WP_Post){
                        return null;
                    }

                    if(!empty($args) and $args[0] === 'id'){
                        $posts[] = $pid;
                    } elseif(!empty($args) and $args[0] === 'title'){
                        $posts[] = $post->post_title;
                    } else {
                        $posts[] = "<a href='".get_permalink((int)$pid)."'>".$post->post_title."</a>";
                    }
                }

                return $this->renderList($posts, ["string"]);

            // QR_CODE_TYPE
            case MetaFieldModel::QR_CODE_TYPE:

                if(!is_array($rawValue)){
                    return null;
                }

                if(!isset($rawValue['url'])){
                    return null;
                }

                if(!isset($rawValue['value'])){
                    return null;
                }

                if(!isset($rawValue['value']['img'])){
                    return null;
                }

                if(!empty($args) and $args[0] === 'link'){
                    return $rawValue['url'];
                }

                return $value = QRCode::render($rawValue);

            // RATING_TYPE
            case MetaFieldModel::RATING_TYPE:

                if(!empty($rawValue) and is_numeric($rawValue)){
                    return ($rawValue/2) . "/5";
                }

                return null;

            // TABLE_TYPE
            case MetaFieldModel::TABLE_TYPE:
                if(is_string($rawValue) and Strings::isJson($rawValue)){
                    $generator = new TableFieldGenerator($rawValue);
                    return $generator->generate();
                }

                return null;

            // TERM_OBJECT_TYPE
            case MetaFieldModel::TERM_OBJECT_TYPE:

                if(!is_scalar($rawValue)){
                    return null;
                }

                $term = get_term((int)$rawValue);

                if(!$term instanceof \WP_Term){
                    return null;
                }

                if(!empty($args) and $args[0] === 'id'){
                    return $rawValue;
                }

                if(!empty($args) and $args[0] === 'name'){
                    return $term->name;
                }

                return "<a href='".get_term_link((int)$rawValue)."'>".$term->name."</a>";

            // TERM_OBJECT_MULTI_TYPE
            case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:

                if(!is_array($rawValue)){
                    return null;
                }

                $terms = [];

                foreach ($rawValue as $tid){
                    $term = get_term((int)$tid);

                    if(!$term instanceof \WP_Term){
                        return null;
                    }

                    if(!empty($args) and $args[0] === 'id'){
                        $terms[] = $tid;
                    } elseif(!empty($args) and $args[0] === 'name'){
                        $terms[] = $term->name;
                    } else {
                        $terms[] = "<a href='".get_term_link((int)$tid)."'>".$term->name."</a>";
                    }
                }

                return $this->renderList($terms, ["string"]);

            // TEXTAREA_TYPE
            case MetaFieldModel::TEXTAREA_TYPE:

                if(is_string($rawValue)){
                    return WPUtils::renderShortCode($rawValue, true);
                }

                return null;

            // TIME_TYPE
            case MetaFieldModel::TIME_TYPE:

                if(is_string($rawValue)){
                    $value = $rawValue;

                    if(!empty($args)){
                        $format = implode(":", $args);
                        if(!empty($format) and Date::isDateFormatValid($format)){
                            return $before. Date::format($format, $value) . $after;
                        }
                    }

                    return $value;
                }

                return null;

            // USER_TYPE
            case MetaFieldModel::USER_TYPE:

                if(!is_scalar($rawValue)){
                    return null;
                }

                $user = get_user($rawValue);

                if(!$user instanceof \WP_User){
                    return null;
                }

                if(!empty($args) and $args[0] === 'id'){
                    return $rawValue;
                }

                if(!empty($args) and $args[0] === 'name'){
                    return Users::getUserLabel($user);
                }

                return "<a href='".get_author_posts_url((int)$rawValue)."'>".Users::getUserLabel($user)."</a>";

            // USER_MULTI_TYPE
            case MetaFieldModel::USER_MULTI_TYPE:

                if(!is_array($rawValue)){
                    return null;
                }

                $users = [];

                foreach ($rawValue as $uid){
                    $user = get_user($uid);

                    if(!$user instanceof \WP_User){
                        return null;
                    }

                    if(!empty($args) and $args[0] === 'id'){
                        $users[] = $uid;
                    } elseif(!empty($args) and $args[0] === 'name'){
                        $users[] = Users::getUserLabel($user);
                    } else {
                        $users[] = "<a href='".get_author_posts_url((int)$uid)."'>".Users::getUserLabel($user)."</a>";
                    }
                }

                return $this->renderList($users, ["string"]);

            // WEIGHT_TYPE
            case MetaFieldModel::WEIGHT_TYPE:
                if(
                    is_array($rawValue) and
                    isset($rawValue['weight']) and
                    isset($rawValue['unit'])
                ){
                    $value = $rawValue['weight']['value'];
                    $after = $rawValue['weight']['after'];
                    $before = $rawValue['weight']['before'];
                    $unit = $rawValue['unit'];

                    return $before.$this->renderAmount($value, $unit,'weight', $args).$after;
                }

                return null;

            // DEFAULT
            default:
                if(!is_scalar($rawValue)){
                    return null;
                }

                return $before.$rawValue.$after;
        }
    }

    /**
     * @param $rawValue
     * @param array $args
     *
     * @return string|null
     */
    private function renderList( $rawValue, $args = [])
    {
        if(!is_array($rawValue)){
            return null;
        }

        if(isset($args[0]) and is_numeric($args[0]) and isset($rawValue[(int)$args[0]])){
            return $rawValue[(int)$args[0]];
        }

        if(isset($args[0]) and $args[0] === 'string'){
            $separator = (isset($args[1])) ? $args[1] : ",";

            return implode($separator, $rawValue);
        }

        if(isset($args[0]) and $args[0] === 'ol'){
            $classes = (isset($args[1])) ? $args[1] : null;
            $value = '<ol>';

            foreach ( $rawValue as $item){
                $value .= '<li class="'.$classes.'">' . $item . '</li>';
            }

            $value .= '</ol>';

            return $value;
        }

        $classes = (isset($args[0]) and $args[0] === 'li' and isset($args[1])) ? $args[1] : null;
        $value = '<ul>';

        foreach ( $rawValue as $item){
            $value .= '<li class="'.$classes.'">' . $item . '</li>';
        }

        $value .= '</ul>';

        return $value;
    }

    /**
     * @param $amount
     * @param $unit
     * @param $context
     * @param array $args
     *
     * @return string|null
     */
    private function renderAmount($amount, $unit, $context, $args = [])
    {
        if(!is_numeric($amount)){
            return null;
        }

        $format = (!empty($args) and isset($args[0])) ? $args[0] : 'full';
        $decimalSeparator = (!empty($args) and isset($args[1])) ? $args[1] : ".";
        $thousandsSeparator = (!empty($args) and isset($args[2])) ? $args[2] : ",";
        $position = (!empty($args) and isset($args[3])) ? $args[3] : "after";
        $amount = number_format($amount, 2, $decimalSeparator, $thousandsSeparator);

        if($format === 'value'){
            return $amount;
        }

        if($format === 'symbol'){
            switch ($context){
                case "currency":
                    $unit = Currencies::getSymbol($unit);
                    break;

                case "length":
                    $unit = Lengths::getSymbol($unit);
                    break;

                case "weight":
                    $unit = Weights::getSymbol($unit);
                    break;
            }
        }

        if($position === 'before'){
            return $unit .' '. $amount;
        }

        return $amount . ' ' . $unit;
    }
}