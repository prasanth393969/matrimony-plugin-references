<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

class AddressField extends AbstractField
{
    /**
     * @inheritDoc
     */
    protected function options(): array
    {
        return [
                'format' => [
                        'type'    => 'select',
                        'label'   => __( 'Format', ACPT_PLUGIN_NAME ),
                        'default' => 'address',
                        'options' => $this->addressFormatOptions(),
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

        $format = $options['format'] ?? "address";

        switch ($format){
            case "city":
                if(!isset($value['city'])){
                    return null;
                }

                return $value['city'];

            case "country":
                if(!isset($value['country'])){
                    return null;
                }

                return $value['country'];

            case "coordinates":
                if(!isset($value['lat'])){
                    return null;
                }

                if(!isset($value['lng'])){
                    return null;
                }

                return $value['lat'].", ".$value['lng'];

            default:
            case "address":
                if(!isset($value['address'])){
                    return null;
                }

                return $before . $value['address'] . $after;
        }

        return null;
    }
}
