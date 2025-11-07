<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\Data\Sanitizer;
use ACPT\Utils\Wordpress\Posts;

class CreatePostAndLinkToARelationalFieldCommand implements CommandInterface
{
    private $value;
    private MetaFieldModel $metaField;
    private $entityType;
    private $entityValue;
    private $entityId;
    private $savedValues;

    /**
     * CreatePostAndLinkToARelationalFieldCommand constructor.
     * @param MetaFieldModel $metaField
     * @param $newPostId
     * @param $entityType
     * @param $entityValue
     * @param $entityId
     * @param $savedValues
     */
    public function __construct(MetaFieldModel $metaField, $newPostId, $entityType, $entityValue, $entityId, $savedValues = null)
    {
        $this->metaField = $metaField;
        $this->value = $newPostId;
        $this->entityType = $entityType;
        $this->entityValue = $entityValue;
        $this->entityId = $entityId;
        $this->savedValues = $savedValues;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $postId = wp_insert_post([
                'post_title' =>  $this->value,
                'post_content' =>  '',
                'post_type' =>  $this->entityValue,
                'post_status' => 'publish',
            ]);

            if (is_wp_error($postId)) {
                return false;
            }

            $idName = $this->metaField->getDbName();
            $fieldType = $this->metaField->getType();

            $newValues = [$postId];

            if($fieldType  === MetaFieldModel::POST_TYPE or $fieldType === MetaFieldModel::POST_OBJECT_MULTI_TYPE){
                $newValues = array_merge($newValues, explode(",", $this->savedValues));
            }

            if($fieldType === MetaFieldModel::POST_TYPE){
                $command = new HandleRelationsCommand($this->metaField, $newValues, $this->entityId, $this->entityType);
                $command->execute();
            }

            Meta::save($this->entityId, $this->entityType, $idName, Sanitizer::sanitizeRawData($fieldType, $newValues));
            Meta::save($this->entityId, $this->entityType, $idName.'_id', $this->metaField->getId());
            Meta::save($this->entityId, $this->entityType, $idName.'_type', $fieldType);

            Posts::invalidCache();

            return true;
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return false;
        }
    }
}