<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Core\Models\Meta\MetaFieldModel;
use Breakdance\DynamicData\ImageData;
use Breakdance\DynamicData\ImageField;

class ACPTImageField extends ImageField implements ACPTFieldInterface
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
     * ACPTImageField constructor.
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
	 * @param mixed $attributes
	 *
	 * @return ImageData
	 * @throws \Exception
	 */
	public function handler($attributes): ImageData
	{
        $this->fieldModel->setBelongsToLabel($this->belongsTo);
        $this->fieldModel->setFindLabel($this->find);
		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if(empty($value)){
			return ImageData::emptyImage();
		}

		return ImageData::fromAttachmentId($value);
	}
}