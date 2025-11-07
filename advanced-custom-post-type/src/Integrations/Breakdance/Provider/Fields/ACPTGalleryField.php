<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\GalleryData;
use Breakdance\DynamicData\GalleryField;
use Breakdance\DynamicData\ImageData;

class ACPTGalleryField extends GalleryField implements ACPTFieldInterface
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
     * ACPTGalleryField constructor.
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
     * @return array
     */
    public function controls()
    {
        return [
            \Breakdance\Elements\control('sort', Translator::translate('Sorting'), [
                'type' => 'dropdown',
                'layout' => 'vertical',
                'items' => [
                    ['text' => Translator::translate('Ascendant'), 'value' => 'asc'],
                    ['text' => Translator::translate('Descendant'), 'value' => 'desc'],
                    ['text' => Translator::translate('Random'), 'value' => 'rand'],
                ]
            ]),
        ];
    }

	/**
	 * @param mixed $attributes
	 *
	 * @return GalleryData
	 * @throws \Exception
	 */
	public function handler($attributes): GalleryData
	{
        $this->fieldModel->setBelongsToLabel($this->belongsTo);
        $this->fieldModel->setFindLabel($this->find);
		$attachmentIds = ACPTField::getValue($this->fieldModel, $attributes);

		$gallery = new GalleryData();

        if(empty($attachmentIds)){
            return $gallery;
        }

        if(!is_array($attachmentIds)){
            return $gallery;
        }

		// sort
        $sort = $attributes['sort'] ?? 'asc';

        if($sort === 'desc'){
            $attachmentIds = array_reverse($attachmentIds);
        }

        if($sort === 'rand'){
            shuffle($attachmentIds);
        }

		$images = [];

		foreach ($attachmentIds as $attachmentId){
			$images[] = ImageData::fromAttachmentId($attachmentId);
		}

		$gallery->images = $images;

		return $gallery;
	}
}