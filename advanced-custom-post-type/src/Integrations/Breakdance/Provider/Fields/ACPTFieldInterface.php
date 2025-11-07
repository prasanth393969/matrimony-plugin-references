<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Core\Models\Meta\MetaFieldModel;

interface ACPTFieldInterface
{
    /**
     * ACPTFieldInterface constructor.
     * @param MetaFieldModel $fieldModel
     * @param null $belongsTo
     * @param null $find
     * @param int $count
     */
	public function __construct(MetaFieldModel $fieldModel, $belongsTo = null, $find = null, $count = 1);
}