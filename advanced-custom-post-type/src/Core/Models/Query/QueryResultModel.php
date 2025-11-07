<?php

namespace ACPT\Core\Models\Query;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\WooCommerceProductDataRepository;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\Data\Normalizer;

class QueryResultModel implements \JsonSerializable
{
    /**
     * @var array
     */
    private $post;

    /**
     * @var array
     */
    private $user;

    /**
     * @var array
     */
    private $meta;

    /**
     * QueryResultModel constructor.
     *
     * @param $object
     *
     * @throws \Exception
     */
    private function __construct($object)
    {
        if($object instanceof \WP_User){
            $this->user = Normalizer::objectToArray($object);
        } elseif($object instanceof \WP_Post){
            $this->post = Normalizer::objectToArray($object);
        }

        $this->setMeta();
    }

    /**
     * @param \WP_Post $post
     *
     * @return QueryResultModel
     * @throws \Exception
     */
    public static function forPost(\WP_Post $post)
    {
        return new self($post);
    }

    /**
     * @param \WP_User $user
     *
     * @return QueryResultModel
     * @throws \Exception
     */
    public static function forUser(\WP_User $user)
    {
        return new self($user);
    }

    /**
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @throws \Exception
     */
    private function setMeta()
    {
        $metaGroups = [];
        $meta = [
            'meta' => [],
        ];

        if(!empty($this->post)){
            $pid = $this->post['ID'];
            $pid = (int)$pid;
            $postType = $this->post['post_type'];

            $belongsTo = MetaTypes::CUSTOM_POST_TYPE;
            $find = $pid;

            $metaGroups = MetaRepository::get([
                'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
                'find' => $postType,
                'clonedFields' => true,
            ]);

        } elseif (!empty($this->user)){

            $belongsTo = MetaTypes::USER;
            $find = (int)$this->user['ID'];

            $metaGroups = MetaRepository::get([
                'belongsTo' => MetaTypes::USER,
                'clonedFields' => true,
            ]);
        }

        if(empty($metaGroups)){
            return $meta;
        }

        if(empty($belongsTo)){
            return $meta;
        }

        if(empty($find)){
            return $meta;
        }

        foreach ($metaGroups as $groupModel){
	        foreach ($groupModel->getBoxes() as $metaBox){

		        $metaFields = [];

		        foreach ($metaBox->getFields() as $field){

			        $options = [];

			        foreach ($field->getOptions() as $option){
				        $options[] = [
					        'label' => $option->getLabel(),
					        'value' => $option->getValue(),
				        ];
			        }

			        $metaFields[] = [
				        "name" => $field->getName(),
				        "type" => $field->getType(),
				        "options" => $options,
				        "value" => Meta::fetch($find, $belongsTo, $field->getDbName(), true),
				        "default" => $field->getDefaultValue(),
				        "required" => $field->isRequired() === '1',
				        "showInAdmin" => $field->isShowInArchive() === '1',
				        "advancedOptions" => $field->getAdvancedOptions() ,
			        ];
		        }

		        $meta['meta'][] = [
			        "meta_box" => $metaBox->getName(),
			        "meta_fields" => $metaFields,
		        ];
	        }
        }

        if( isset($postType) and $postType === 'product' and class_exists( 'woocommerce' )  ){
            $meta['wc_product_data'] = [];
            $productData = WooCommerceProductDataRepository::get();

            foreach ($productData as $productDatum) {

                $productDataFields = [];

                foreach ($productDatum->getFields() as $field){

                    $options = [];

                    foreach ($field->getOptions() as $option){
                        $options[] = [
                                'label' => $option->getLabel(),
                                'value' => $option->getValue(),
                        ];
                    }

                    $productDataFields[] = [
                            'name' => $field->getName(),
                            'type' => $field->getType(),
                            "options" => $options,
                            'value' => Meta::fetch($pid, MetaTypes::CUSTOM_POST_TYPE, $field->getDbName(), true),
                            'default' => $field->getDefaultValue(),
                            'required' => $field->isRequired() === '1',
                    ];
                }

                $meta['wc_product_data'][] = [
                        'name' => $productDatum->getName(),
                        'icon' => $productDatum->getIcon(),
                        'visibility' => $productDatum->getVisibility(),
                        'fields' => $productDataFields,
                ];
            }
        }

        $this->meta = $meta;
    }

    #[\ReturnTypeWillChange]
    public function getMeta()
    {
        return $this->meta;
    }

	#[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_merge($this->getPost(), $this->getMeta());
    }
}