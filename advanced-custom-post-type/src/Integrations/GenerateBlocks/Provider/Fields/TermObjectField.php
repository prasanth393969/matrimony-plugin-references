<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

class TermObjectField extends AbstractField
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
                'options' => $this->objectRenderingOptions("term"),
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
            return $rawValue;
        }

        $term = get_term($value);

        if(!$term instanceof \WP_Term){
            return null;
        }

        if($render === "link"){
            $link = get_term_link($term);

            return '<a href="'.$link.'" target="_blank">'.$term->name.'</a>';
        }

        return $term->name;
    }
}