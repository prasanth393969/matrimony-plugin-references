<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\PHP\Date;

class DateRangeField extends AbstractField
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
            'date_separator' => [
                'type'  => 'text',
                'default' => ' - ',
                'label' => __( 'Date separator', ACPT_PLUGIN_NAME ),
                'help'  => __( 'Sets the date separator.' ),
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
            $dateSeparator = $options['date_separator'] ?? " - ";

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

            if(!isset($value['value'])){
                return null;
            }

            $values = $value['value'];
            $dateTimeObjects = $value['object'];

            if(!is_array($values)){
                return null;
            }

            if(count($values) !== 2){
                return null;
            }

            if(!is_array($dateTimeObjects)){
                return null;
            }

            if(count($dateTimeObjects) !== 2){
                return null;
            }

            if(!$dateTimeObjects[0] instanceof \DateTime){
                return null;
            }

            if(!$dateTimeObjects[1] instanceof \DateTime){
                return null;
            }

            $start = Date::format($dateFormat, $dateTimeObjects[0]);
            $end = Date::format($dateFormat, $dateTimeObjects[1]);

            return $before . $start . $dateSeparator . $end . $after;
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