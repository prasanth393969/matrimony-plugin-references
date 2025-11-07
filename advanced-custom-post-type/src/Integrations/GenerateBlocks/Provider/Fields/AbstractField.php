<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Phone;
use GenerateBlocks_Dynamic_Tag_Callbacks;
use GenerateBlocks_Dynamic_Tags;
use GenerateBlocks_Register_Dynamic_Tag;

abstract class AbstractField
{
    /**
     * @var BelongModel
     */
    protected BelongModel $belong;

    /**
     * @var MetaFieldModel
     */
    protected MetaFieldModel $fieldModel;

    /**
     * AbstractField constructor.
     *
     * @param BelongModel    $belong
     * @param MetaFieldModel $fieldModel
     */
    public function __construct(BelongModel $belong, MetaFieldModel $fieldModel)
    {
        $this->belong = $belong;
        $this->fieldModel = $fieldModel;
    }

    /**
     * Register the field
     */
    public function register()
    {
        new GenerateBlocks_Register_Dynamic_Tag(
            [
                'title'       => $this->title(),
                'tag'         => $this->tag(),
                'type'        => 'ACPT',
                'box'         => $this->fieldModel->getBox()->getName(),
                'field'       => $this->fieldModel->getName(),
                'belongsTo'   => $this->belong->getBelongsTo(),
                'find'        => $this->belong->getFind(),
                'supports'    => $this->supports(),
                'options'     => $this->options(),
                'description' => $this->description(),
                'return'      => [ $this, 'renderField' ],
            ]
        );
    }

    /**
     * @return string
     */
    private function title()
    {
        return  __( '['.$this->belong->getFind().'] - ' . $this->fieldModel->getUiName(), ACPT_PLUGIN_NAME );
    }

    /**
     * @return string
     */
    private function tag()
    {
        return 'acpt_' . $this->fieldModel->getDbName();
    }

    /**
     * @return array
     */
    private function supports()
    {
        return [];
    }

    /**
     * @return string|void
     */
    private function description()
    {
        return __( $this->fieldModel->getDescription(), ACPT_PLUGIN_NAME );
    }

    /**
     * @return array
     */
    protected abstract function options(): array;

    /**
     * Render the ACPT field
     *
     * @param $options
     * @param $block
     * @param $instance
     *
     * @return string
     */
    public function renderField( $options, $block, $instance )
    {
        switch ($this->fieldModel->getBelongsToLabel()){
            default:
                $fallbackType = 'post';
                break;

            case MetaTypes::TAXONOMY:
            case BelongsTo::TERM_ID:
                $fallbackType = 'term';
                break;

            case MetaTypes::USER:
            case BelongsTo::USER_ID:
                $fallbackType = 'user';
                break;

            case MetaTypes::OPTION_PAGE:
                $fallbackType = 'option_page';
                break;
        }

        if($fallbackType === 'option_page'){
            $id = $this->fieldModel->getFindLabel();
        } else {
            $id = GenerateBlocks_Dynamic_Tags::get_id( $options, $fallbackType, $instance );
        }

        if (!$id) {
            return GenerateBlocks_Dynamic_Tag_Callbacks::output( '', $options, $instance );
        }

        $tagDetails = GenerateBlocks_Register_Dynamic_Tag::get_tag_details( $options['tag_name'] );

        $this->fieldModel->setFindLabel($tagDetails['find']);
        $this->fieldModel->setBelongsToLabel($tagDetails['belongsTo']);

        $rawValue = $this->getRawValue($id, $tagDetails);

        if(empty($rawValue)){
            return GenerateBlocks_Dynamic_Tag_Callbacks::output( '', $options, $instance );
        }

        if(!isset($rawValue['value'])){
            return GenerateBlocks_Dynamic_Tag_Callbacks::output( '', $options, $instance );
        }

        $output = $this->render($rawValue, $options);

        return GenerateBlocks_Dynamic_Tag_Callbacks::output( $output, $options, $instance );
    }

    /**
     * @param $id
     * @param $tagDetails
     *
     * @return array
     */
    private function getRawValue($id, $tagDetails)
    {
        $a = null;
        $b = null;

        switch ($tagDetails['belongsTo']){
            case MetaTypes::CUSTOM_POST_TYPE:
            case BelongsTo::POST_ID:
            case BelongsTo::POST_CAT:
            case BelongsTo::POST_TAX:
            case BelongsTo::POST_TEMPLATE:
                $a = 'post_id';
                $b = $id;
                break;

            case MetaTypes::TAXONOMY:
            case BelongsTo::TERM_ID:
                $a = 'term_id';
                $b = $id;
                break;

            case MetaTypes::OPTION_PAGE:
                $a = 'option_page';
                $b = $this->fieldModel->getFindLabel() ?? 'test';
                break;
        }

        return get_acpt_field([
            $a => $b,
            'box_name' => $tagDetails['box'],
            'field_name' => $tagDetails['field'],
            'return' => 'raw',
            'with_context' => true,
        ]);
    }

    /**
     * @param array $rawValue
     * @param array $options
     *
     * @return mixed
     */
    protected abstract function render(array $rawValue, array $options = []);

    /**
     * @return array
     */
    protected function phoneNumberOptions()
    {
        return [
            [
                'value' => Phone::FORMAT_E164,
                'label' => __( Phone::FORMAT_E164, ACPT_PLUGIN_NAME ),
            ],
            [
                'value' => Phone::FORMAT_INTERNATIONAL,
                'label' => __( Phone::FORMAT_INTERNATIONAL, ACPT_PLUGIN_NAME ),
            ],
            [
                'value' => Phone::FORMAT_NATIONAL,
                'label' => __( Phone::FORMAT_NATIONAL, ACPT_PLUGIN_NAME ),
            ],
        ];
    }

    /**
     * @return array
     */
    protected function renderOptions()
    {
        return [
            [
                'value' => 'text',
                'label' => __( 'Text', ACPT_PLUGIN_NAME ),
            ],
            [
                'value' => 'html',
                'label' => __( 'HTML', ACPT_PLUGIN_NAME ),
            ],
        ];
    }

    /**
     * @return array
     */
    protected function urlRenderOptions()
    {
        return [
            [
                'value' => 'url',
                'label' => __( 'URL', ACPT_PLUGIN_NAME ),
            ],
            [
                'value' => 'label',
                'label' => __( 'Label', ACPT_PLUGIN_NAME ),
            ],
            [
                'value' => 'html',
                'label' => __( 'HTML', ACPT_PLUGIN_NAME ),
            ],
        ];
    }

    /**
     * @param $fieldType
     *
     * @return array
     */
    protected function mediaRenderOptions($fieldType)
    {
        switch ($fieldType){

            // Audio
            case AudioField::class:
                return [
                    [
                        'value' => 'src',
                        'label' => __( 'File URL', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'id',
                        'label' => __( 'Attachment ID', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'title',
                        'label' => __( 'Title', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'caption',
                        'label' => __( 'Caption text', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'description',
                        'label' => __( 'Description', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'artist',
                        'label' => __( 'Artist', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'album',
                        'label' => __( 'Album', ACPT_PLUGIN_NAME ),
                    ],
                ];

            // Image
            case ImageField::class:
                return [
                    [
                        'value' => 'src',
                        'label' => __( 'Source URL', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'id',
                        'label' => __( 'Attachment ID', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'alt',
                        'label' => __( 'Alternative text', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'title',
                        'label' => __( 'Title', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'caption',
                        'label' => __( 'Caption text', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'description',
                        'label' => __( 'Description', ACPT_PLUGIN_NAME ),
                    ],
                ];

            // File
            // Video
            case FileField::class:
            case VideoField::class:
                return [
                    [
                        'value' => 'src',
                        'label' => __( 'Source URL', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'id',
                        'label' => __( 'Attachment ID', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'title',
                        'label' => __( 'Title', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'caption',
                        'label' => __( 'Caption text', ACPT_PLUGIN_NAME ),
                    ],
                    [
                        'value' => 'description',
                        'label' => __( 'Description', ACPT_PLUGIN_NAME ),
                    ],
                ];
        }
    }

    /**
     * @return array
     */
    protected function targetOptions()
    {
        return [
            [
                    'value' => '_blank',
                    'label' => __( 'Opens in a new window or tab', ACPT_PLUGIN_NAME ),
            ],
            [
                    'value' => '_self',
                    'label' => __( 'Opens in the full body of the window', ACPT_PLUGIN_NAME ),
            ],
            [
                    'value' => '_parent',
                    'label' => __( 'Opens in the parent frame', ACPT_PLUGIN_NAME ),
            ],
            [
                    'value' => '_top',
                    'label' => __( 'Opens in the same frame as it was clicked', ACPT_PLUGIN_NAME ),
            ],
        ];
    }

    /**
     * @return array
     */
    protected function uomPositionOptions()
    {
        return [
                [
                        "value" =>"after",
                        "label" => "After value"
                ],
                [
                        "value" =>"before",
                        "label" => "Before value"
                ],
                [
                        "value" =>"hide",
                        "label" => "Hide"
                ],
        ];
    }

    /**
     * @return array
     */
    protected function addressFormatOptions()
    {
        return [
                [
                        "value" => "address",
                        "label" => "Address"
                ],
                [
                        "value" => "country",
                        "label" => "Country"
                ],
                [
                        "value" => "city",
                        "label" => "City"
                ],
                [
                        "value" => "coordinates",
                        "label" => "Coordinates"
                ],
        ];
    }

    /**
     * @return array
     */
    protected function countryFormatOptions()
    {
        return [
                [
                        "value" =>"country",
                        "label" => "Only country"
                ],
                [
                        "value" =>"flag",
                        "label" => "Only flag"
                ],
                [
                        "value" =>"full",
                        "label" => "Full (country+flag)"
                ],
        ];
    }

    /**
     * @return array
     */
    protected function timeOptions()
    {
        return [
            [
                "value" =>"H:i",
                "label" => "H:i (ex. 21:18)"
            ],
            [
                "value" =>"g:i a",
                "label" => "g:i a (ex. 9:18 pm)"
            ],
            [
                "value" =>"g:i A",
                "label" => "g:i A (ex. 9:18 PM)"
            ],
        ];
    }

    /**
     * @return array
     */
    protected function dateOptions()
    {
       return [
           [
                   "value" => "d-M-y",
                   "label" => "dd-mmm-yy (ex. 28-OCT-90)"
           ],
           [
                   "value" => "d-M-Y",
                   "label" => "dd-mmm-yyyy (ex. 28-OCT-1990)"
           ],
           [
                   "value" => "d M y",
                   "label" => "mmm yy (ex. 28 OCT 90)"
           ],
           [
                   "value" => "d M Y",
                   "label" => "mmm yyyy (ex. 28 OCT 1990)"
           ],
           [
                   "value" => "d/m/Y",
                   "label" => "dd/mm/yy (ex. 28/10/90)"
           ],
           [
                   "value" => "d/m/Y",
                   "label" => "dd/mm/yyyy (ex. 28/10/1990)"
           ],
           [
                   "value" => "m/d/y",
                   "label" => "mm/dd/yy (ex. 10/28/90)"
           ],
           [
                   "value" => "m/d/Y",
                   "label" => "mm/dd/yyyy (ex. 10/28/1990)"
           ],
           [
                   "value" => "d.m.Y",
                   "label" => "dd.mm.yy (ex. 28.10.90)"
           ],
           [
                   "value" => "d.m.Y",
                   "label" => "dd.mm.yyyy (ex. 28.10.1990)"
           ],
       ];
    }

    /**
     * @param $object
     *
     * @return array
     */
    protected function objectRenderingOptions($object)
    {
        if($object === 'user'){
            return [
                [
                    "value" => "title",
                    "label" => "Name"
                ],
                [
                    "value" => "id",
                    "label" => "ID"
                ],
            ];
        }

        return [
            [
                    "value" => "title",
                    "label" => "Title"
            ],
            [
                    "value" => "link",
                    "label" => "Link"
            ],
            [
                    "value" => "id",
                    "label" => "ID"
            ],
        ];
    }

    /**
     * @return array
     */
    protected function listRenderingOptions()
    {
        return [
            [
                'value' => 'text',
                'label' => __( 'Text', ACPT_PLUGIN_NAME ),
            ],
            [
                'value' => 'ul',
                'label' => __( 'HTML Unordered list', ACPT_PLUGIN_NAME ),
            ],
            [
                'value' => 'ol',
                'label' => __( 'HTML Ordered list', ACPT_PLUGIN_NAME ),
            ],
        ];
    }
}