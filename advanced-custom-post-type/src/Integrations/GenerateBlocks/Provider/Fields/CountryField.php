<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\PHP\Country;

class CountryField extends DateField
{
    /**
     * @inheritDoc
     */
    protected function options(): array
    {
        return [
            'render' => [
                'type'    => 'select',
                'label'   => __( 'Render as', ACPT_PLUGIN_NAME ),
                'default' => 'country',
                'options' => $this->countryFormatOptions(),
            ],
        ];
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

        if(!isset($value['value'])){
            return null;
        }

        $countryIsoCode = $value['country'];

        if(empty($countryIsoCode)){
            return null;
        }

        $render = $options['render'] ?? 'country';

        if($render === 'flag' and !empty($countryIsoCode)){
            return Country::getFlag($countryIsoCode);
        }

        if($render === 'full' and !empty($countryIsoCode)){
            return $before . Country::fullFormat($countryIsoCode, $value['value']) . $after;
        }

        return $before . $value['value'] . $after;
    }
}