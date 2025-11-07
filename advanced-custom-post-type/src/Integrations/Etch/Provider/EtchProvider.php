<?php

namespace ACPT\Integrations\Etch\Provider;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Utils\Wordpress\WPAttachment;

class EtchProvider
{
    /**
     * @var array
     */
    private array $data;

    /**
     * @var string
     */
    private string $objectType;

    /**
     * @var string
     */
    private string $objectDisc;

    /**
     * @var int
     */
    private int $objectId;

    /**
     *
     * @var string|null
     */
    private ?string $taxonomy;

    /**
     * EtchProvider constructor.
     *
     * @param array       $data
     * @param string      $objectType
     * @param int         $objectId
     * @param string|null $taxonomy
     *
     * @throws \Exception
     */
    public function __construct( array $data, string $objectType, int $objectId, ?string $taxonomy = null)
    {
        $this->data = $data;
        $this->objectType = $objectType;
        $this->setObjectDisc($objectType);
        $this->objectId = $objectId;
        $this->taxonomy = $taxonomy;
    }

    /**
     * $objectType can be:
     *
     * - post
     * - term
     * - user
     * - option
     *
     * @param $objectType
     *
     * @throws \Exception
     */
    private function setObjectDisc($objectType)
    {
        $allowed = [
            'post',
            'term',
            'user',
            'option',
        ];

        if(!in_array($objectType, $allowed)){
            throw new \Exception($objectType . " is not allowed");
        }

        switch ($objectType){

            default:
            case "post";
                $objectDisc = 'post_id';
                break;

            case "term";
                $objectDisc = 'term_id';
                break;

            case "user";
                $objectDisc = 'user_id';
                break;

            case "option";
                $objectDisc = 'option_page';
                break;
        }

        $this->objectDisc = $objectDisc;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function fields()
    {
        //
        // 1. Option page fields
        //
        // Example: {options.acpt.{option_page_name}_field_name}
        if($this->objectType === "option"){
            $pages = OptionPageRepository::getAllSlugs();
            $fields = [];

            foreach ($pages as $page){
                $fieldValues = get_acpt_fields([
                    'option_page' => $page,
                    'format' => 'complete',
                    'with_context' => true,
                    'return' => 'object',
                ]);

                if(!empty($fieldValues)){
                    foreach ($fieldValues as $fieldValue){
                        $fields[$fieldValue->db_name] = $fieldValue->value;
                    }
                }
            }

            return $fields;
        }

        //
        // 2. Other fields
        //
        // Example: {meta.acpt.field_name}
        $fieldValues = get_acpt_fields([
            $this->objectDisc =>  $this->objectId,
            'format' => 'complete',
            'with_context' => true,
            'return' => 'object',
        ]);

        $fields = [];

        if(!empty($fieldValues)){
            foreach ($fieldValues as $fieldValue){
                $fields[$fieldValue->db_name] = $fieldValue->value;
            }
        }

        return $fields;
    }
}