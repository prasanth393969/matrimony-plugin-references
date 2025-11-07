<?php

namespace ACPT\Core\JSON;

use ACPT\Core\Models\DynamicBlock\DynamicBlockControlModel;

class DynamicBlockControlSchema extends AbstractJSONSchema
{
    /**
     * @inheritDoc
     */
    function toArray()
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'id' => [
                    'type' => 'string',
                    'format' => 'uuid',
                    'readOnly' => true,
                ],
                'name' => [
                    'type' => 'string',
                ],
                'label' => [
                    'type' => 'string',
                ],
                'type' => [
                    'type' => 'string',
                    'example' => DynamicBlockControlModel::TEXT_TYPE,
                    'enum' => [
                        DynamicBlockControlModel::CHECKBOX_TYPE,
                        DynamicBlockControlModel::RADIO_TYPE,
                        DynamicBlockControlModel::SELECT_TYPE,
                        DynamicBlockControlModel::SELECT_MULTI_TYPE,
                        DynamicBlockControlModel::TOGGLE_TYPE,
                        DynamicBlockControlModel::TEXT_TYPE,
                        DynamicBlockControlModel::TEXTAREA_TYPE,
                        DynamicBlockControlModel::NUMBER_TYPE,
                        DynamicBlockControlModel::RANGE_TYPE,
                        DynamicBlockControlModel::EMAIL_TYPE,
                        DynamicBlockControlModel::PHONE_TYPE,
                    ]
                ],
                'default' => [
                    'type' => 'string',
                    'nullable' => true,
                ],
                'description' => [
                    'type' => 'string',
                    'nullable' => true,
                ],
                'settings' => [
                    'type' => 'array',
                ],
                "sort" => [
                    'type' => 'integer',
                    'example' => 1,
                    'readOnly' => true,
                ],
            ],
            'required' => [
                'name'
            ],
        ];
    }
}