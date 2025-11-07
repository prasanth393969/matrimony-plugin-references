<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

class ListField extends AbstractField
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
                'default' => 'ul',
                'options' => $this->listRenderingOptions(),
            ],
            'separator' => [
                'type'  => 'text',
                'default' => ',',
                'label' => __( 'List item separator', ACPT_PLUGIN_NAME ),
                'help'  => __( 'Sets the list separator if you are rendering it as text.' ),
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

        $values = $rawValue['value'];
        $before = $rawValue['before'];
        $after = $rawValue['after'];

        $render = $options['render'] ?? "ul";
        $separator = $options['separator'] ?? ",";

        if($render === "ul"){
            $return = '<ul>';

            foreach ($values as $value){
                $return = '<li>'.$before . $value. $after.'</li>';
            }

            $return .= '</ul>';

            return $return;
        }

        if($render === "ol"){
            $return = '<ol>';

            foreach ($values as $value){
                $return = '<li>'.$before . $value. $after.'</li>';
            }

            $return .= '</ol>';

            return $return;
        }

        return $before . implode($values, $separator) . $after;
    }
}