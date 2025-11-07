<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\PHP\Date;

class DateTimeField extends DateField
{
    /**
     * @inheritDoc
     */
    protected function options(): array
    {
        return [
            'date_format' => [
                'type'    => 'select',
                'label'   => __( 'Date format', ACPT_PLUGIN_NAME ),
                'default' => 'text',
                'options' => $this->dateOptions(),
            ],
            'time_format' => [
                'type'    => 'select',
                'label'   => __( 'Time format', ACPT_PLUGIN_NAME ),
                'default' => 'text',
                'options' => $this->timeOptions(),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function render($rawValue, $options = [])
    {
        try {
            $dateFormat = $this->getDefaultDateFormat($options['date_format']);
            $timeFormat = $this->getDefaultTimeFormat($options['time_format']);
            $dateTimeFormat = $dateFormat . ' ' . $timeFormat;

            if(!is_array($rawValue)){
                return null;
            }

            if(empty($rawValue['value'])){
                return null;
            }

            $value = $rawValue['value'];
            $before = $rawValue['before'];
            $after = $rawValue['after'];

            if(!isset($value['object'])){
                return null;
            }

            if(!$value['object'] instanceof \DateTime){
                return null;
            }

            return $before . Date::format($dateTimeFormat, $value['object']) . $after;
        } catch (\Exception $exception) {

            do_action("acpt/error", $exception);

            return null;
        }
    }
}