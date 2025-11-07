<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\PHP\Phone;

class PhoneField extends AbstractField
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
                'default' => 'text',
                'options' => $this->renderOptions(),
            ],
            'format' => [
                'type'    => 'select',
                'label'   => __( 'Phone number', ACPT_PLUGIN_NAME ),
                'default' => Phone::FORMAT_E164,
                'options' => $this->phoneNumberOptions()
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function render($rawValue, $options = [])
    {
        $format = $options['format'] ?? Phone::FORMAT_E164;

        if(!is_array($rawValue)){
            return null;
        }

        if(empty($rawValue['value'])){
            return null;
        }

        $value = $rawValue['value'];
        $before = $rawValue['before'];
        $after = $rawValue['after'];

        $val = $value['value'];
        $dial = $value['dial'] ?? null;

        $phone = $before . Phone::format($val, $dial, $format) . $after;

        if(isset($options['render']) and $options['render'] === "html"){
            return '<a href="'.Phone::format($val, $dial, Phone::FORMAT_RFC3966).'" target="_blank">'.$phone.'</a>';
        }

        return $phone;
    }
}