<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use Breakdance\DynamicData\StringData;
use Breakdance\DynamicData\StringField;

class ACPTTableField extends StringField implements ACPTFieldInterface
{
	/**
	 * @var MetaFieldModel
	 */
	protected MetaFieldModel $fieldModel;

    /**
     * @var null
     */
    protected $belongsTo;

    /**
     * @var null
     */
    protected $find;

    /**
     * @var int
     */
    protected $count;

    /**
     * ACPTTableField constructor.
     * @param MetaFieldModel $fieldModel
     * @param null $belongsTo
     * @param null $find
     * @param int $count
     */
	public function __construct(MetaFieldModel $fieldModel, $belongsTo = null, $find = null, $count = 1)
	{
		$this->fieldModel = $fieldModel;
        $this->belongsTo = $belongsTo;
        $this->find = $find;
        $this->count = $count;
    }

	/**
	 * @return string
	 */
	public function label()
	{
		return ACPTField::label($this->fieldModel);
	}

	/**
	 * @return string
	 */
	public function category()
	{
		return ACPTField::category();
	}

	/**
	 *@return string
	 */
	public function subcategory()
	{
        return ACPTField::subcategory($this->fieldModel, $this->find);
	}

	/**
	 * @return string
	 */
	public function slug()
	{
        $baseSlug = ACPTField::slug($this->fieldModel);

        if($this->count > 1){
            $baseSlug .= "_".$this->count;
        }

        return $baseSlug;
	}

	/**
	 * @inheritDoc
	 */
	public function returnTypes()
	{
		return ['string'];
	}

	/**
	 * @param mixed $attributes
	 *
	 * @return StringData
	 * @throws \Exception
	 */
	public function handler($attributes): StringData
	{
        $this->fieldModel->setBelongsToLabel($this->belongsTo);
        $this->fieldModel->setFindLabel($this->find);
		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if(is_string($value) and Strings::isJson($value)){
			$generator = new TableFieldGenerator($value);
			$value = $generator->generate();

			return StringData::fromString($before . $value . $after);
		}

		return StringData::emptyString();
	}
}