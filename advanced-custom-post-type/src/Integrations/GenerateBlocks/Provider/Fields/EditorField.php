<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\Wordpress\WPUtils;

class EditorField extends AbstractField
{
    /**
     * @inheritDoc
     */
    protected function options(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function render($rawValue, $options = [])
    {
        if(!is_array($rawValue)){
            return null;
        }

        if(empty($rawValue['value'])){
            return null;
        }

        $value = $rawValue['value'];
        $before = $rawValue['before'];
        $after = $rawValue['after'];

        $content = WPUtils::renderShortCode($value);

        if($content === null){
            return null;
        }

        $replacementMap = [
                '<p>['    => '[',
                ']</p>'   => ']',
                ']<br />' => ']'
        ];

        return $before . strtr( $content, $replacementMap ) . $after;
    }
}