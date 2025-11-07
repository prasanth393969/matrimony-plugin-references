<?php

namespace ACPT\Core\JSON;

class DynamicBlockSchema extends AbstractJSONSchema
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
                'title' => [
                    'type' => 'string',
                ],
                'category' => [
                    'type' => 'string',
                ],
                'icon' => [
                    'type' => 'string',
                ],
                'css' => [
                    'type' => 'string',
                    'nullable' => true,
                ],
                'callback' => [
                    'type' => 'string',
                    'nullable' => true,
                ],
                'keywords' => [
                    'type' => 'array',
                    'nullable' => true,
                ],
                'postTypes' => [
                    'type' => 'array',
                    'nullable' => true,
                ],
                'controls' => [
                    'type' => 'array',
                    'items' => (new DynamicBlockControlSchema())->toArray(),
                ],
            ],
            'required' => [
                'title',
                'name',
                'category',
                'icon',
                'postTypes',
                'controls',
            ]
        ];
    }
}