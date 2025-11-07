<?php

namespace ACPT\Core\JSON;

use ACPT\Utils\Data\Normalizer;

abstract class AbstractJSONSchema
{
    /**
     * @return array
     */
    abstract function toArray();

    /**
     * @return \stdClass
     */
    public function toObject()
    {
        return Normalizer::arrayToObject(static::toArray());
    }
}
