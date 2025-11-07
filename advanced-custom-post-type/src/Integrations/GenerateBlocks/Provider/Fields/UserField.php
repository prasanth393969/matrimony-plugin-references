<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Utils\Wordpress\Users;

class UserField extends AbstractField
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
                'options' => $this->objectRenderingOptions("user"),
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

        $user = get_user($value);

        if(!$user instanceof \WP_User){
            return null;
        }

        return Users::getUserLabel($user);
    }
}