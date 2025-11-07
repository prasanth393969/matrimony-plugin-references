<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

class PostObjectField extends AbstractField
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
                'default' => 'title',
                'options' => $this->objectRenderingOptions("post"),
            ]
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

        if(!is_numeric($value)){
            return null;
        }

        $value = (int)$value;
        $render = $options['render'] ?? "title";

        if($render === "id"){
            return $value;
        }

        if($render === "link"){
            $title = get_the_title($value);
            $link = get_the_permalink($value);

            return '<a href="'.$link.'" target="_blank">'.$title.'</a>';
        }

        return get_the_title($value);
    }
}