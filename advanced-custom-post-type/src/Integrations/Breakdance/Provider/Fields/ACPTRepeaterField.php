<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use Breakdance\DynamicData\DynamicDataController;
use Breakdance\DynamicData\LoopController;
use Breakdance\DynamicData\RepeaterData;
use Breakdance\DynamicData\RepeaterField;

class ACPTRepeaterField extends RepeaterField implements ACPTFieldInterface
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
	 * @var LoopController
	 */
	private LoopController $loop;

	/**
	 * @var int
	 */
	private $count;

    /**
     * @var int
     */
    private int $index;

    /**
     * ACPTRepeaterField constructor.
     * @param MetaFieldModel $fieldModel
     * @param null $belongsTo
     * @param null $find
     * @param int $count
     */
	public function __construct(MetaFieldModel $fieldModel, $belongsTo = null, $find = null, $count = 1)
	{
		$this->fieldModel = $fieldModel;
		$this->loop = \Breakdance\DynamicData\LoopController::getInstance($fieldModel->getId());
		$this->index = 0;
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
	 * Is this never called?
	 *
	 * @param mixed $attributes
	 *
	 * @return RepeaterData
	 * @throws \Exception
	 */
	public function handler($attributes): RepeaterData
	{
		return RepeaterData::fromArray([]);
	}

	/**
	 * @param null $postId
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function hasSubFields( $postId = null )
	{
		$fieldLoop = $this->loop->get();

		switch ($this->fieldModel->getType()){

			// Handle Repeater fields
			case MetaFieldModel::REPEATER_TYPE:

				if($this->fieldModel->getBox()->getGroup()->belongsToPostType()){

					if($postId === null){
						$postId = get_the_ID();
					}

					if($postId === null){
						return null;
					}

					$nestedValues = get_acpt_field([
						'post_id' => $postId,
						'box_name' => $this->fieldModel->getBox()->getName(),
						'field_name' => $this->fieldModel->getName(),
                        'return' => 'raw',
					]);

				} elseif($this->fieldModel->getBox()->getGroup()->belongsToTaxonomy()){

				    $term = get_queried_object();

                    if(!$term instanceof \WP_Term){
                        return null;
                    }

                    $nestedValues = get_acpt_field([
                        'term_id' => $term->term_id,
                        'box_name' => $this->fieldModel->getBox()->getName(),
                        'field_name' => $this->fieldModel->getName(),
                        'return' => 'raw',
                    ]);

                } elseif($this->fieldModel->getBox()->getGroup()->belongsTo(MetaTypes::OPTION_PAGE)){
					$nestedValues = get_acpt_field([
						'option_page' => $this->fieldModel->getFindLabel() ?? 'test',
						'box_name' => $this->fieldModel->getBox()->getName(),
						'field_name' => $this->fieldModel->getName(),
                        'return' => 'raw',
					]);
				}

				if(empty($nestedValues)){
					return false;
				}

				$maxLoops = count($nestedValues) - 1;

				if(isset($fieldLoop['index']) and $fieldLoop['index'] !== null and $maxLoops <= $fieldLoop['index']){
					$this->index = 0;
					$this->loop->reset();

					return false;
				}

				$this->loop->set([
					'field' => $this->fieldModel,
					'index' => $this->index,
				]);

				$this->index++;

				return true;
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function setSubFieldIndex( $index )
	{
		$blockLoop = $this->loop->get();

		return $blockLoop['index'];
	}

	/**
	 * @return \Breakdance\DynamicData\Field|RepeaterField|false|null
	 * @throws \Exception
	 */
	public function parentField()
	{
		$parentFieldModel =  $this->fieldModel->getParentField();

		if($parentFieldModel === null){
			return null;
		}

        $parentFieldModel->setBelongsToLabel($this->belongsTo);
        $parentFieldModel->setFindLabel($this->find);

		return DynamicDataController::getInstance()->getField(ACPTField::slug($parentFieldModel));
	}
}