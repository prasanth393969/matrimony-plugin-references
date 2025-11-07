<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\PHP\Date;

class DateField extends AbstractField
{
    /**
     * @inheritDoc
     */
    protected function options(): array
    {
        return [
            'format' => [
                'type'    => 'select',
                'label'   => __( 'Date format', ACPT_PLUGIN_NAME ),
                'default' => 'text',
                'options' => $this->dateOptions(),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function render($rawValue, $options = [])
    {
        try {
            $dateFormat = $this->getDefaultDateFormat($options['format']);

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

            return $before . Date::format($dateFormat, $value['object']) . $after;
        } catch (\Exception $exception) {

            do_action("acpt/error", $exception);

            return null;
        }
    }

    /**
     * @param null $format
     *
     * @return mixed|string|void|null
     */
    protected function getDefaultDateFormat($format = null)
    {
        if($format !== null and Date::isDateFormatValid($format)){
            return $format;
        }

        if($this->fieldModel !== null and $this->fieldModel->getAdvancedOption('date_format') !== null){
            return $this->fieldModel->getAdvancedOption('date_format');
        }

        if(!empty(get_option('date_format'))){
            return get_option('date_format');
        }

        return "Y-m-d";
    }

    /**
     * @param null $format
     *
     * @return mixed|string|void|null
     */
    protected function getDefaultTimeFormat($format = null)
    {
        if($format !== null and Date::isDateFormatValid($format)){
            return $format;
        }

        if($this->fieldModel !== null and $this->fieldModel->getAdvancedOption('time_format') !== null){
            return $this->fieldModel->getAdvancedOption('time_format');
        }

        if(!empty(get_option('time_format'))){
            return get_option('time_format');
        }

        return "H:i";
    }
}