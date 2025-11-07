<?php

namespace ACPT\Core\Generators\Comment;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\AbstractGenerator;
use ACPT\Core\Generators\Meta\Fields\AbstractField;
use ACPT\Core\Models\Meta\MetaFieldModel;

class CommentMetaFieldGenerator extends AbstractGenerator
{
	/**
	 * @var MetaFieldModel
	 */
	private MetaFieldModel $fieldModel;

    /**
     * @var null
     */
    private $commentId;

    /**
     * AttachmentMetaFieldGenerator constructor.
     *
     * @param MetaFieldModel $fieldModel
     * @param null           $commentId
     */
	public function __construct(MetaFieldModel $fieldModel, $commentId = null)
	{
		$this->fieldModel = $fieldModel;
        $this->commentId = $commentId;
    }

    /**
     * @param string $for
     *
     * @return string|null
     */
	public function render($for = "frontEnd")
	{
		$field = $this->getCommentMetaField();

		if($field){
			$render = $field->render();

			if($for === "backEnd"){
			    return $render;
            }

			return preg_replace('/width: \d+\%\;|width: \d+\%/', '', $render);
		}

		return null;
	}

	/**
	 * @param null $commentId
	 *
	 * @return AbstractField|null
	 */
	public function getCommentMetaField()
	{
		$notAllowed = [
			MetaFieldModel::EMBED_TYPE,
			MetaFieldModel::REPEATER_TYPE,
			MetaFieldModel::FLEXIBLE_CONTENT_TYPE,
			MetaFieldModel::LIST_TYPE,
			MetaFieldModel::POST_TYPE,
			MetaFieldModel::IMAGE_TYPE,
			MetaFieldModel::GALLERY_TYPE,
			MetaFieldModel::VIDEO_TYPE,
		];

		if(in_array($this->fieldModel->getType(), $notAllowed)){
			return null;
		}

		$className = 'ACPT\\Core\\Generators\\Meta\\Fields\\'.$this->fieldModel->getType().'Field';
		$commentId = $this->commentId ? $this->commentId : 0;

		if(class_exists($className)){
			/** @var AbstractField $instance */
			$instance = new $className($this->fieldModel, MetaTypes::COMMENT, $commentId);

			return $instance;
		}

		return null;
	}
}
