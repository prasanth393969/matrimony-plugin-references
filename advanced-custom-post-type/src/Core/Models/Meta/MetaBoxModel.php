<?php

namespace ACPT\Core\Models\Meta;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Abstracts\AbstractModel;
use ACPT\Core\Repository\MetaRepository;

class MetaBoxModel extends AbstractModel implements \JsonSerializable
{
	/**
	 * @var MetaGroupModel
	 */
	private MetaGroupModel $group;

	/**
	 * @var string
	 */
	private string $name;

	/**
	 * @var string
	 */
	private ?string $label = null;

    /**
     * @var array
     */
    private array $settings = [];

	/**
	 * @var int
	 */
	private int $sort;

	/**
	 * @var MetaFieldModel[]
	 */
	private array $fields = [];

    /**
     * @var MetaBoxVisibilityModel[]
     */
    private array $visibilityConditions = [];

	/**
	 * MetaBox constructor.
	 *
	 * @param string $id
	 * @param MetaGroupModel $group
	 * @param string $name
	 * @param int $sort
	 * @param string|null $label
	 */
	public function __construct(
		string $id,
		MetaGroupModel $group,
		string $name,
		int $sort,
		string $label = null
	) {
		parent::__construct($id);
		$this->group = $group;
		$this->name = $name;
		$this->label = $label;
		$this->sort = $sort;
		$this->fields = [];
		$this->settings = [];
        $this->visibilityConditions = [];
	}

	/**
	 * @param MetaGroupModel $group
	 */
	public function changeGroup( MetaGroupModel $group )
	{
		$this->group = $group;
	}

	/**
	 * @return MetaGroupModel
	 */
	public function getGroup(): MetaGroupModel
	{
		return $this->group;
	}

	/**
	 * @param string $name
	 */
	public function changeName( $name )
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
	public function getLabel(): ?string
	{
		return $this->label;
	}

    /**
     * @return string
     */
    public function getDbName()
    {
        return Strings::toDBFormat($this->getName());
    }

	/**
	 * @return string
	 */
	public function getUiName(): string
	{
		if($this->getLabel()){
			return $this->getLabel();
		}

		return $this->getName();
	}

	/**
	 * @param string $label
	 */
	public function changeLabel( $label )
	{
		$this->label = $label;
	}

	/**
	 * @param $sort
	 */
	public function changeSort($sort)
	{
		$this->sort = $sort;
	}

	/**
	 * @return int
	 */
	public function getSort()
	{
		return $this->sort;
	}

	/**
	 * @return MetaFieldModel[]
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @param $index
	 * @param MetaFieldModel $fieldModel
	 */
	public function setField($index, MetaFieldModel $fieldModel): void
	{
		$this->fields[$index] = $fieldModel;
	}

	/**
	 * @param MetaFieldModel $field
	 *
	 * @return bool
	 */
	public function hasField(MetaFieldModel $field): bool
	{
		return $this->existsInCollection($field->getId(), $this->fields);
	}

	/**
	 * @param $fieldId
	 * @param MetaFieldModel[] $fields
	 *
	 * @return MetaFieldModel|null
	 */
	public function findAFieldById($fieldId, $fields = null): ?MetaFieldModel
	{
		$fields = $fields ?? $this->getFields();

		foreach($fields as $field) {
			if($field->getId() === $fieldId){
				return $field;
			}

			if ($field->hasChildren()){
				$nestedId = $this->findAFieldById($fieldId, $field->getChildren());

				if($nestedId !== null){
					return $nestedId;
				}
			}

			// @TODO to be fixed in 2.0.14 beta3
			foreach ($field->getBlocks() as $block){
				foreach ($block->getFields() as $field){
					$nestedId = $this->findAFieldById($field->getId(), $block->getFields());

					if($nestedId !== null){
						return $nestedId;
					}
				}
			}
		}

		return null;
	}

	/**
	 * @param $fieldName
	 * @param null $fields
	 */
	public function removeAField($fieldName, &$fields = null)
	{
		$fields = $fields ?? $this->getFields();

		foreach($fields as $fieldIndex => $field) {
			if($field->getName() === $fieldName){
				unset($fields[$fieldIndex]);
			}

			foreach ($field->getChildren() as $child){
				$fields = $field->getChildren();
				$this->removeAField($child->getName(), $fields);
			}

			foreach ($field->getBlocks() as $block){
				foreach ($block->getFields() as $field){
					$fields = $block->getFields();
					$this->removeAField($field->getName(), $fields);
				}
			}
		}

		$this->fields = $fields;
	}

	/**
	 * @param MetaFieldModel $field
	 */
	public function addField(MetaFieldModel $field)
	{
		if(!$this->existsInCollection($field->getId(), $this->fields)){
			$this->fields[] = $field;
		}
	}

	/**
	 * @param MetaFieldModel $field
	 */
	public function removeField(MetaFieldModel $field)
	{
		$this->fields = $this->removeFromCollection($field->getId(), $this->fields);
	}

    /**
     * @param $settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getSetting($key)
    {
        return $this->settings[$key] ?? null;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        if($this->getSetting("context")){
            return $this->getSetting("context");
        }

        return $this->getGroup()->getContext() !== null ? $this->getGroup()->getContext() : 'advanced';
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        if($this->getSetting("priority")){
            return $this->getSetting("priority");
        }

        return $this->getGroup()->getPriority() !== null ? $this->getGroup()->getPriority() : 'default';
    }

    /**
     * @param MetaBoxVisibilityModel $condition
     */
    public function addVisibilityCondition(MetaBoxVisibilityModel $condition)
    {
        if(!$this->existsInCollection($condition->getId(), $this->visibilityConditions)){
            $this->visibilityConditions[] = $condition;
        }
    }

    /**
     * @param MetaBoxVisibilityModel $condition
     */
    public function removeVisibilityCondition(MetaBoxVisibilityModel $condition)
    {
        $this->visibilityConditions = $this->removeFromCollection($condition->getId(), $this->visibilityConditions);
    }

    /**
     * Clear all visibility conditions
     */
    public function clearVisibilityConditions()
    {
        $this->visibilityConditions = [];
    }

    /**
     * @return MetaBoxVisibilityModel[]
     */
    public function getVisibilityConditions(): array
    {
        return $this->visibilityConditions;
    }

    /**
     * @return bool
     */
    public function hasVisibilityConditions(): bool
    {
        return count($this->visibilityConditions) > 0;
    }

	/**
	 * @return MetaBoxModel
	 */
	public function duplicate(): MetaBoxModel
	{
		$duplicate = clone $this;
		$duplicate->id = Uuid::v4();
		$duplicatedFields = $duplicate->getFields();
		$duplicate->fields = [];

		foreach ($duplicatedFields as $field){
			$duplicatedFieldModel = $field->duplicateFrom($duplicate);
			$duplicate->addField($duplicatedFieldModel);
		}

		return $duplicate;
	}

    /**
     * @param MetaGroupModel $groupModel
     * @return MetaBoxModel
     */
    public function duplicateFrom(MetaGroupModel $groupModel): MetaBoxModel
    {
        $duplicate = clone $this;
        $duplicate->id = Uuid::v4();
        $duplicate->group = $groupModel;
        $duplicate->changeName(Strings::getTheFirstAvailableName($duplicate->getName(), MetaRepository::getBoxNames()));
        $duplicatedFields = $duplicate->getFields();
        $duplicate->fields = [];
        $duplicate->visibilityConditions = [];
        $duplicatedVisibilityConditions = $duplicate->getVisibilityConditions();

        foreach ($duplicatedFields as $field){
            $duplicatedFieldModel = $field->duplicateFrom($duplicate);
            $duplicate->addField($duplicatedFieldModel);
        }

        foreach ($duplicatedVisibilityConditions as $condition){
            $visibilityConditionModel = $condition->duplicateFrom($duplicate);
            $duplicate->addVisibilityCondition($visibilityConditionModel);
        }

        return $duplicate;
    }

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'label' => $this->getLabel(),
			'UIName' => $this->getUiName(),
			'groupId' => $this->getGroup()->getId(),
			'settings' => $this->getSettings(),
			'sort' => $this->getSort(),
			'fields' => $this->getFields(),
            'visibilityConditions' => $this->getVisibilityConditions(),
		];
	}

	/**
	 * @param string $format
	 *
	 * @return array
	 */
	public function arrayRepresentation(string $format = 'full'): array
	{
		if($format === 'mini'){
			return [
				'id' => $this->getId(),
				"name" => $this->getName(),
				"label" => $this->getLabel(),
				"UIName" => $this->getUIName(),
				'groupId' => $this->getGroup()->getId(),
                'settings' => $this->getSettings(),
				"sort" => (int)$this->getSort(),
				"count" => count($this->getFields()),
			];
		}

		if($format === 'full'){

			$fieldsArray = [];
            $visibilityConditionsArray = [];

			foreach ($this->getFields() as $fieldModel){
				$fieldsArray[] = $fieldModel->arrayRepresentation($format);
			}

            foreach ($this->getVisibilityConditions() as $visibilityCondition){
                $visibilityConditionsArray[] = [
                    'id' => $visibilityCondition->getId(),
                    'type' => $visibilityCondition->getType(),
                    'operator' => $visibilityCondition->getOperator(),
                    'value' => $visibilityCondition->getValue(),
                    'logic' => $visibilityCondition->getLogic(),
                    'sort' => (int)$visibilityCondition->getSort(),
                ];
            }

			return [
				"id" => $this->getId(),
				"name" => $this->getName(),
				"label" => $this->getLabel(),
				"UIName" => $this->getUIName(),
				'groupId' => $this->getGroup()->getId(),
                'settings' => $this->getSettings(),
				"sort" => (int)$this->getSort(),
                'visibilityConditions' => $visibilityConditionsArray,
				"fields" => $fieldsArray
			];
		}
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
			'group' => [
				'required' => true,
				'type' => 'object',
				'instanceOf' => MetaGroupModel::class,
			],
			'groupId' => [
				'required' => false,
				'type' => 'string',
			],
			'name' => [
				'required' => true,
				'type' => 'string',
			],
			'new_name' => [
				'required' => false,
				'type' => 'string',
			],
			'UIName' => [
				'required' => false,
				'type' => 'string',
			],
			'label' => [
				'required' => false,
				'type' => 'string',
			],
			'sort' => [
				'required' => false,
				'type' => 'string|integer',
			],
			'fields' => [
				'required' => false,
				'type' => 'array',
			],
            'visibilityConditions' => [
                    'required' => false,
                    'type' => 'array',
            ],
            'settings' => [
                'required' => false,
                'type' => 'object|array',
            ],
		];
	}
}