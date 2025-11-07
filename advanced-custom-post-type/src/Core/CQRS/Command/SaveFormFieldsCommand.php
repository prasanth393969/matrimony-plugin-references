<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Validation\ValidationRuleModel;
use ACPT\Core\Repository\FormRepository;
use ACPT\Core\Repository\MetaRepository;

class SaveFormFieldsCommand implements CommandInterface, LogFormatInterface
{
	/**
	 * @var string
	 */
	private string $id;

	/**
	 * @var array
	 */
	private array $data;

	/**
	 * SaveFormFieldsCommand constructor.
	 *
	 * @param string $id
	 * @param array $data
	 */
	public function __construct($id, array $data)
	{
		$this->id = $id;
		$this->data = $data;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function execute(): string
	{
		$formModel = FormRepository::getById($this->id);

		if($formModel === null){
			throw new \Exception("Form with id ".$this->id." does not exists");
		}

		$formModel->resetFields();

		foreach ($this->data as $fieldIndex => $field){

			$metaFieldModel = null;
			if(isset($field['metaFieldId']) and $field['metaFieldId'] !== null){
				$metaFieldModel = MetaRepository::getMetaFieldById($field['metaFieldId']);
			}

			$isRequired = $field['required'] ?? $field['is_required'] ?? $field['isRequired'];

			$fieldModel = FormFieldModel::hydrateFromArray([
				'id' => $field['id'],
				'metaField' => $metaFieldModel,
				'group' => $field['group'],
				'key' => $field['key'],
				'name' => $field['name'],
				'label' => $field['label'],
				'type' => $field['type'],
				'description' => $field['description'],
				'isRequired' => (bool)$isRequired,
				'extra' => $field['extra'],
				'settings' => $field['settings'],
				'sort' => ($fieldIndex+1),
			]);

			if(isset($field['rules']) and !empty($field['rules'])){
				foreach ($field['rules'] as $ruleIndex => $rule){

					$validationRuleModel = ValidationRuleModel::hydrateFromArray([
						'id' => $rule['id'],
						'condition' => $rule['condition'],
						'value' => $rule['value'],
						'message' => $rule['message'],
						'sort' => ($ruleIndex+1),
					]);

					$fieldModel->addValidationRule($validationRuleModel);
				}
			}

			$formModel->addField($fieldModel);
		}

		FormRepository::save($formModel);

        do_action("acpt/form_fields/save", $this, $formModel);

		return $formModel->getId();
	}

    /**
     * @inheritDoc
     */
    public function logFormat(): array
    {
        return [
            "class"  => SaveFormFieldsCommand::class,
            'data' => $this->data
        ];
    }
}