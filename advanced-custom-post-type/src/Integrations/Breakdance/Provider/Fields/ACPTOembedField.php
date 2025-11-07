<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Core\Models\Meta\MetaFieldModel;
use Breakdance\DynamicData\OembedData;
use Breakdance\DynamicData\OembedField;

class ACPTOembedField extends OembedField implements ACPTFieldInterface
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
     * ACPTOembedField constructor.
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
	 * @return OembedData
	 * @throws \Exception
	 */
	public function handler($attributes): OembedData
	{
        $this->fieldModel->setBelongsToLabel($this->belongsTo);
        $this->fieldModel->setFindLabel($this->find);
		$resource = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $resource['after'] ?? null;
        $before = $resource['before'] ?? null;
        $resource = $resource['value'] ?? null;

		if(!is_string($resource) or $resource === null){
			return new OembedData();
		}

		return OembedData::fromOembedUrl($resource);
	}
}