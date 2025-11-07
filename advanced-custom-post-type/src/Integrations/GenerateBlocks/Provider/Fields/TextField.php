<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\Wordpress\WPUtils;

class TextField extends AbstractField
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

        return $before . WPUtils::renderShortCode($value) . $after;
    }
}