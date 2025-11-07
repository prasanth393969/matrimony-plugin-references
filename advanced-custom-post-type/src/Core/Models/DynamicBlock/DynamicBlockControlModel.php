<?php

namespace ACPT\Core\Models\DynamicBlock;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Abstracts\AbstractModel;
use ACPT\Core\Repository\DynamicBlockRepository;
use ACPT\Core\ValueObjects\DynamicBlockFieldOption;

/**
 * Class DatasetModelItem
 * @package ACPT\Core\Models\Meta
 */
class DynamicBlockControlModel extends AbstractModel implements \JsonSerializable
{
    /**
     * Possible return types
     * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/
     */
    const NULL_RETURN = 'null';
    const BOOLEAN_RETURN = 'boolean';
    const OBJECT_RETURN = 'object';
    const ARRAY_RETURN = 'array';
    const STRING_RETURN = 'string';
    const INTEGER_RETURN = 'integer';

    /**
     * Field types
     */
    const CHECKBOX_TYPE = 'Checkbox';
    const EMAIL_TYPE = 'Email';
    const NUMBER_TYPE = 'Number';
    const PHONE_TYPE = 'Phone';
    const RADIO_TYPE = 'Radio';
    const RANGE_TYPE = 'Range';
    const SELECT_TYPE = 'Select';
    const SELECT_MULTI_TYPE = 'SelectMulti';
    const TEXT_TYPE = 'Text';
    const TEXTAREA_TYPE = 'Textarea';
    const TOGGLE_TYPE = 'Toggle';

    /**
     * @var DynamicBlockModel
     */
    private DynamicBlockModel $block;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var null|string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @var ?mixed
     */
    private $default = null;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var int
     */
    private $sort;

    /**
     * DynamicBlockField constructor.
     * @param $id
     * @param DynamicBlockModel $block
     * @param string $name
     * @param string $label
     * @param string $type
     * @param string|null $description
     * @param string|null $default
     * @param array $settings
     */
    public function __construct($id, DynamicBlockModel $block, string $name, string $label, string $type, int $sort, ?string $description = null, $default = null, $settings = [])
    {
        parent::__construct($id);
        $this->block = $block;
        $this->name = Strings::toDBFormat($name);
        $this->label = $label;
        $this->setType($type);
        $this->default = $default;
        $this->description = $description;
        $this->sort = $sort;
        $this->options = [];
        $this->settings = $settings ?? [];
    }

    /**
     * @return DynamicBlockModel
     */
    public function getBlock(): DynamicBlockModel
    {
        return $this->block;
    }

    /**
     * @param $name
     */
    private function changeName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array
     */
    private function allowedTypes()
    {
        return [
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
        ];
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        if(!in_array($type, $this->allowedTypes())){
            throw new \DomainException($type . ' is not a valid field type for this meta box field');
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function returnType(): string
    {
        switch ($this->type){

            case DynamicBlockControlModel::CHECKBOX_TYPE:
            case DynamicBlockControlModel::TOGGLE_TYPE:
                return '['.self::BOOLEAN_RETURN.', '.self::NULL_RETURN.']';

            case DynamicBlockControlModel::RANGE_TYPE:
            case DynamicBlockControlModel::NUMBER_TYPE:
                return '['.self::INTEGER_RETURN.', '.self::NULL_RETURN.']';

            case DynamicBlockControlModel::RADIO_TYPE:
            case DynamicBlockControlModel::SELECT_TYPE:
            case DynamicBlockControlModel::TEXT_TYPE:
            case DynamicBlockControlModel::TEXTAREA_TYPE:
            case DynamicBlockControlModel::EMAIL_TYPE:
            case DynamicBlockControlModel::PHONE_TYPE:
                return '['.self::STRING_RETURN.', '.self::NULL_RETURN.']';

            case DynamicBlockControlModel::SELECT_MULTI_TYPE:
                return '['.self::ARRAY_RETURN.', '.self::NULL_RETURN.']';
        }

        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @return bool
     */
    public function hasOptions()
    {
        return count($this->options) > 0;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        try {
            foreach ($options as $option){

                $option = (array)$option;
                $value  = $option['value'] ?? null;
                $label  = $option['label'] ?? null;
                $isDefault = $option['isDefault'] ?? false;

                if(is_string($label) and is_string($value) and is_bool($isDefault)){
                    $option = new DynamicBlockFieldOption($label, $value, $isDefault);
                    $this->setOption($option);
                }
            }
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
    }

    /**
     * @param DynamicBlockFieldOption $option
     */
    public function setOption(DynamicBlockFieldOption $option)
    {
        $this->options[] = $option;
    }

    /**
     * @return DynamicBlockFieldOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return (array)$this->settings;
    }

    /**
     * This function generate the JSON needed by registerBlockType JS function
     *
     * @return string
     */
    public function transformToJSElement()
    {
        $el = 'el(';

        switch ($this->getType()){
            case DynamicBlockControlModel::CHECKBOX_TYPE:
                $el .= 'Fields.CheckboxControl';
                break;

            case DynamicBlockControlModel::RADIO_TYPE:
                $el .= 'Fields.RadioControl';
                break;

            case DynamicBlockControlModel::RANGE_TYPE:
                $el .= 'Fields.RangeControl';
                break;

            case DynamicBlockControlModel::SELECT_TYPE:
            case DynamicBlockControlModel::SELECT_MULTI_TYPE:
                $el .= 'Fields.SelectControl';
                break;

            case DynamicBlockControlModel::EMAIL_TYPE:
            case DynamicBlockControlModel::NUMBER_TYPE:
            case DynamicBlockControlModel::PHONE_TYPE:
            case DynamicBlockControlModel::TEXT_TYPE:
                $el .= 'Fields.TextControl';
                break;

            case DynamicBlockControlModel::TEXTAREA_TYPE:
                $el .= 'Fields.TextareaControl';
                break;

            case DynamicBlockControlModel::TOGGLE_TYPE:
                $el .= 'Fields.ToggleControl';
                break;
        }

        $el .= ',{';
        $el .= "label: '".$this->getLabel()."',";

        if($this->getType() === DynamicBlockControlModel::TOGGLE_TYPE or $this->getType() === DynamicBlockControlModel::CHECKBOX_TYPE){
            $el .= "checked: props.attributes.".$this->getName().",";
        } elseif($this->getType() === DynamicBlockControlModel::RADIO_TYPE){
            $el .= "selected: props.attributes.".$this->getName().",";
        } else {
            $el .= "value: props.attributes.".$this->getName().",";
        }

        if($this->getDescription() !== null){
            $el .= "help: '".$this->getDescription()."',";
        }

        if($this->hasOptions()){
            $el .= 'options: [';

            foreach ($this->getOptions() as $option){
                $el .= '{label: "'.$option->getLabel().'", value: "'.$option->getValue().'"},';
            }

            $el .= '],';
        }

        if($this->getType() === DynamicBlockControlModel::RANGE_TYPE or $this->getType() === DynamicBlockControlModel::NUMBER_TYPE){
            if(isset($this->getSettings()['min']) and !empty($this->getSettings()['min'])){
                $el .= "min: ".$this->getSettings()['min'].",";
            }

            if(isset($this->getSettings()['max']) and !empty($this->getSettings()['max'])){
                $el .= "max: ".$this->getSettings()['max'].",";
            }

            if(isset($this->getSettings()['step']) and !empty($this->getSettings()['step'])){
                $el .= "step: ".$this->getSettings()['step'].",";
            }
        }

        if($this->getType() === DynamicBlockControlModel::SELECT_MULTI_TYPE){
            $el .= "multiple: true,";
        }

        if($this->getType() === DynamicBlockControlModel::EMAIL_TYPE){
            $el .= "type: 'email',";
        }

        if($this->getType() === DynamicBlockControlModel::PHONE_TYPE){
            $el .= "type: 'tel',";
        }

        if($this->getType() === DynamicBlockControlModel::NUMBER_TYPE){
            $el .= "type: 'number',";
        }

        if($this->getType() === DynamicBlockControlModel::TEXTAREA_TYPE and isset($this->getSettings()['rows']) and !empty($this->getSettings()['rows'])){
            $el .= "rows: ".$this->getSettings()['rows'].",";
        }

        $el .= "onChange: function(e){
            props.setAttributes({ '".$this->getName()."': e });
        }";
        $el .= '})';

        return $el;
    }

    /**
     * @inheritDoc
     */
    public static function validationRules(): array
    {
        return [
            'id' => [
                'required' => false,
                'type' => 'string',
            ],
            'block' => [
                'required' => false,
                'type' => 'object',
                'instanceOf' => DynamicBlockModel::class,
            ],
            'label' => [
                'required' => true,
                'type' => 'string',
            ],
            'name' => [
                'required' => true,
                'type' => 'string',
            ],
            'type' => [
                'required' => true,
                'type' => 'string',
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
                'required' => false,
                'type' => 'array|boolean|string|integer',
            ],
            'description' => [
                'required' => false,
                'type' => 'string',
            ],
            'sort' => [
                'required' => true,
                'type' => 'string|integer',
            ],
            'settings' => [
                'required' => false,
                'type' => 'object|array',
            ]
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'default' => $this->getDefault() ?? null,
            'description' => $this->getDescription() ?? null,
            'sort' => $this->getSort(),
            'settings' => $this->getSettings() ?? [],
            'options' => $this->getOptions() ?? [],
        ];
    }

    /**
     * @param DynamicBlockModel $block
     * @return DynamicBlockControlModel
     */
    public function duplicateFrom(DynamicBlockModel $block)
    {
        $duplicate = clone $this;
        $duplicate->id = Uuid::v4();
        $duplicate->changeName(Strings::getTheFirstAvailableName($duplicate->getName(), DynamicBlockRepository::getControlNames()));
        $duplicate->block = $block;

        return $duplicate;
    }
}
