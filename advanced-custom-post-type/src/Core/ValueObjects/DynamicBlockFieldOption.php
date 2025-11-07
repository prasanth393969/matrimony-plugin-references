<?php

namespace ACPT\Core\ValueObjects;

class DynamicBlockFieldOption implements \JsonSerializable
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $value;

    /**
     * @var bool
     */
    private $isDefault;

    /**
     * DynamicBlockFieldOption constructor.
     * @param $label
     * @param $value
     * @param bool $isDefault
     */
    public function __construct(
        $label,
        $value,
        $isDefault = false
    )
    {
        $this->label = $label;
        $this->value = $value;
        $this->isDefault = $isDefault;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'label' => $this->getLabel(),
            'value' => $this->getValue(),
            'isDefault' => $this->isDefault(),
        ];
    }
}