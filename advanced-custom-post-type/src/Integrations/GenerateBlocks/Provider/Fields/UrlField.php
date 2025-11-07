<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

class UrlField extends AbstractField
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
                'default' => 'url',
                'options' => $this->urlRenderOptions(),
            ],
            'target' => [
                'type'    => 'select',
                'label'   => __( 'Link target', ACPT_PLUGIN_NAME ),
                'default' => '_blank',
                'options' => $this->targetOptions(),
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

        if(!isset($value['url'])){
            return null;
        }

        $label = isset($value['label']) ? $before . $value['label'] . $after : $value['url'];
        $render = $options['render'] ?? "html";
        $target = $options['target'] ?? "_blank";

        if($render === "label"){
            return $label;
        }

        if($render === "url"){
            return $value['url'];
        }

        return '<a href="'.$value['url'].'" target="'.$target.'">'.$label.'</a>';
    }
}