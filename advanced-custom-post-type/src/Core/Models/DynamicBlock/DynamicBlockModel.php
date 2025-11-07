<?php

namespace ACPT\Core\Models\DynamicBlock;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Abstracts\AbstractModel;
use ACPT\Core\Repository\DynamicBlockRepository;

/**
 * Class DatasetModelItem
 * @package ACPT\Core\Models\Meta
 */
class DynamicBlockModel extends AbstractModel implements \JsonSerializable
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $category;

    /**
     * @var array
     */
    private $icon;

    /**
     * @var ?string
     */
    private $css = null;

    /**
     * @var DynamicBlockControlModel[]
     */
    private $controls = [];

    /**
     * @var string|null
     */
    private $callback;

    /**
     * @var array
     */
    private $keywords = [];

    /**
     * @var array
     */
    private $postTypes = [];

    /**
     * @var array
     */
    private $supports = [];

    /**
     * DynamicBlockModel constructor.
     * @param $id
     * @param string $title
     * @param string $name
     * @param string $category
     * @param $icon
     * @param string|null $css
     * @param string|null $callback
     * @param array $keywords
     * @param array $postTypes
     * @param array $supports
     */
    public function __construct($id,
        string $title,
        string $name,
        string $category,
        $icon,
        ?string $css = null,
        ?string $callback = null,
        $keywords = [],
        $postTypes = [],
        $supports = []
    )
    {
        parent::__construct($id);
        $this->title = $title;
        $this->name = $name;
        $this->category = $category;
        $this->setIcon($icon);
        $this->css = $css;
        $this->callback = $callback;
        $this->controls = [];
        $this->keywords = $keywords ?? [];
        $this->postTypes = $postTypes ?? [];
        $this->supports = $supports ?? [];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param $name
     */
    private function changeName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param $icon
     */
    public function setIcon($icon): void
    {
        if(empty($icon)){
            throw new \DomainException("Icon array is empty");
        }

        if(is_string($icon)){
            $this->icon = str_replace("dashicons-", "", $icon);
        }

        if(is_array($icon)){
            $neededKeys = [
                'background',
                'foreground',
                'src',
            ];

            $check = array_diff(array_keys($icon), $neededKeys);

            if(!empty($check)){
                throw new \DomainException("Icon array is not well formed");
            }

            $icon['src'] = str_replace("dashicons-", "", $icon['src']);

            $this->icon = $icon;
        }
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getIconSrc(): string
    {
        return $this->icon['src'];
    }

    /**
     * @return mixed|string
     */
    public function generateIconSrc()
    {
        $iconSrc = (is_string($this->getIcon())) ? $this->getIcon() : $this->getIcon()['src'];

        if(Strings::contains("<svg", $iconSrc)){

            $svg ="el('svg', { width: 20, height: 20 }, ";
            $paths = Strings::extractNodesFromTag($iconSrc, 'svg');

            foreach ($paths as $tag => $attrs){
                if(!empty($attrs)){
                    $svg .= "el('".$tag."', ".str_replace("\"","'", json_encode($attrs))." )";

                    if($attrs !== end($paths)){
                        $svg .= ", ";
                    }
                }
            }

            $svg .= ");";

            return $svg;
        }

        return "`".str_replace("dashicons:","", $iconSrc)."`";
    }

    /**
     * Used by DynamicBlockGenerator
     *
     * @return string
     */
    public function iconCodeString()
    {
        if(is_string($this->getIcon())){
            return "icon: icon,";
        }

        return "icon: {
            src: icon,
            background: `".$this->getIcon()['background']."`,
            foreground: `".$this->getIcon()['foreground']."`
        },";
    }

    /**
     * @return string|null
     */
    public function getCallback(): ?string
    {
        return $this->callback;
    }

    /**
     * @return array|null
     */
    public function getKeywords(): ?array
    {
        return $this->keywords;
    }

    /**
     * @return array|null
     */
    public function getPostTypes(): ?array
    {
        return $this->postTypes;
    }
    /**
     * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
     *
     * @return array|null
     */
    public function getSupports(): ?array
    {
        return $this->supports;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasSupportFor($key): bool
    {
        return isset($this->supports[$key]) and $this->supports[$key] !== null;
    }

    /**
     * Used by DynamicBlockGenerator
     *
     * @return string
     */
    public function supportsCodeString()
    {
        $supportArray = [];

        if($this->hasSupportFor('anchor')){
            $supportArray[] = "anchor: " . Strings::trueOfFalse($this->getSupports()['anchor']);
        }

        if($this->hasSupportFor('align')){
            $align = (!empty($this->getSupports()['align'])) ? "['" . implode("','", $this->getSupports()['align']) . "']" : 'false';
            $supportArray[] = "align: ". $align;
        }

        if($this->hasSupportFor('alignWide')){
            $supportArray[] = "alignWide: " . Strings::trueOfFalse($this->getSupports()['alignWide']);
        }

        if($this->hasSupportFor('background')){

            $a = (isset($this->supports['background']['backgroundImage']) and $this->supports['background']['backgroundImage'] === true) ? 'true': 'false';
            $b = (isset($this->supports['background']['backgroundSize']) and $this->supports['background']['backgroundSize'] === true) ? 'true': 'false';

            $supportArray[] = "background: {
                backgroundImage:".$a.",
                backgroundSize: ".$b."
            }";
        }

        if($this->hasSupportFor('color')){

            $a = (isset($this->supports['color']['background']) and $this->supports['color']['background'] === true) ? 'true': 'false';
            $b = (isset($this->supports['color']['button']) and $this->supports['color']['button'] === true) ? 'true': 'false';
            $c = (isset($this->supports['color']['enableContrastChecker']) and $this->supports['color']['enableContrastChecker'] === true) ? 'true': 'false';
            $d = (isset($this->supports['color']['gradients']) and $this->supports['color']['gradients'] === true) ? 'true': 'false';
            $e = (isset($this->supports['color']['heading']) and $this->supports['color']['heading'] === true) ? 'true': 'false';
            $f = (isset($this->supports['color']['link']) and $this->supports['color']['link'] === true) ? 'true': 'false';
            $g = (isset($this->supports['color']['text']) and $this->supports['color']['text'] === true) ? 'true': 'false';

            $supportArray[] = "color: {
                background:".$a.",
                button: ".$b.",
                enableContrastChecker: ".$c.",
                gradients: ".$d.",
                heading: ".$e.",
                link: ".$f.",
                text: ".$g."
            }";
        }

        if($this->hasSupportFor('dimensions')){

            $a = (isset($this->supports['dimensions']['aspectRatio']) and $this->supports['dimensions']['aspectRatio'] === true) ? 'true': 'false';
            $b = (isset($this->supports['dimensions']['minHeight']) and $this->supports['dimensions']['minHeight'] === true) ? 'true': 'false';

            $supportArray[] = "dimensions: {
                aspectRatio:".$a.",
                minHeight: ".$b."
            }";
        }

        if($this->hasSupportFor('filter')){
            $supportArray[] = "filter: {
                duotone: ".Strings::trueOfFalse($this->getSupports()['filter']['duotone'])."
            }";
        }

        if($this->hasSupportFor('html')){
            $supportArray[] = "html: " . Strings::trueOfFalse($this->getSupports()['html']);
        }

        if($this->hasSupportFor('lock')){
            $supportArray[] = "lock: " . Strings::trueOfFalse($this->getSupports()['lock']);
        }

        if($this->hasSupportFor('multiple')){
            $supportArray[] = "multiple: " . Strings::trueOfFalse($this->getSupports()['multiple']);
        }

        if($this->hasSupportFor('position')){
            $supportArray[] = "position: {
                sticky: ".Strings::trueOfFalse($this->getSupports()['position']['sticky'])."
            }";
        }

        if($this->hasSupportFor('renaming')){
            $supportArray[] = "renaming: " . Strings::trueOfFalse($this->getSupports()['renaming']);
        }

        if($this->hasSupportFor('reusable')){
            $supportArray[] = "reusable: " . Strings::trueOfFalse($this->getSupports()['reusable']);
        }

        if($this->hasSupportFor('shadow')){
            $supportArray[] = "shadow: ". Strings::trueOfFalse($this->getSupports()['shadow']);
        }

        if($this->hasSupportFor('spacing')){

            $margin = (!empty($this->getSupports()['spacing']['margin'])) ? "['" . implode("','", $this->getSupports()['spacing']['margin']) . "']" : 'false';
            $padding = (!empty($this->getSupports()['spacing']['padding'])) ? "['" . implode("','", $this->getSupports()['spacing']['padding']) . "']" : 'false';
            $blockGap = (!empty($this->getSupports()['spacing']['blockGap'])) ? "['" . implode("','", $this->getSupports()['spacing']['blockGap']) . "']" : 'false';

            $supportArray[] = "spacing: {
                margin: ".$margin.",
                padding: ".$padding.",
                blockGap: ".$blockGap.",
            }";
        }

        if($this->hasSupportFor('typography')){

            $a = (isset($this->supports['typography']) and isset($this->supports['typography']['fontSize']) and $this->supports['typography']['fontSize'] === true) ? 'true': 'false';
            $b = (isset($this->supports['typography']) and isset($this->supports['typography']['lineHeight']) and $this->supports['typography']['lineHeight'] === true) ? 'true': 'false';
            $c = (isset($this->supports['typography']) and isset($this->supports['typography']['textAlign']) and $this->supports['typography']['textAlign'] === true) ? 'true': 'false';

            $supportArray[] = "typography: {
                fontSize:".$a.",
                lineHeight: ".$b.",
                textAlign: ".$c."
            }";
        }

        if(empty($supportArray)){
            return '{}';
        }

        return "{". implode(", ", $supportArray) . "}";
    }

    /**
     * @return string|null
     */
    public function getCSS(): ?string
    {
        return $this->css;
    }

    /**
     * @return string|null
     */
    public function getScriptName(): string
    {
        return Strings::toDBFormat($this->name).'_block_js';
    }

    /**
     * @return string|null
     */
    public function getStyleName(): string
    {
        return Strings::toDBFormat($this->name).'_block_css';
    }

    /**
     * @return string|null
     */
    public function getBlockName(): string
    {
        return 'acpt-dynamic-blocks/'.Strings::toDBFormat($this->name);
    }

    /**
     * @param DynamicBlockControlModel $field
     */
    public function addControl(DynamicBlockControlModel $field)
    {
        if(!$this->existsInCollection($field->getId(), $this->controls)){
            $this->controls[] = $field;
        }
    }

    /**
     * @param DynamicBlockControlModel $field
     */
    public function removeControl(DynamicBlockControlModel $field)
    {
        $this->controls = $this->removeFromCollection($field->getId(), $this->controls);
    }

    /**
     * Clear all options
     */
    public function clearControls()
    {
        $this->controls = [];
    }

    /**
     * @return array
     */
    public function getControlsArray()
    {
        if(empty($this->controls)){
            return [];
        }

        $fields = [];

        foreach ($this->controls as $field){

            $fieldArray = [
                'type' => $field->returnType(),
                'default' => $field->getDefault() ?? '',
            ];

            $fields[$field->getName()] = $fieldArray;
        }

        return $fields;
    }

    /**
     * @return DynamicBlockControlModel[]
     */
    public function getControls()
    {
        return $this->controls;
    }

    /**
     * @return string
     */
    public function controlsToJSElements()
    {
        $elements = [];

        foreach ($this->controls as $field){
            $elements[] = $field->transformToJSElement();
        }

        return implode(",", $elements);
    }

    /**
     * @return DynamicBlockModel
     */
    public function duplicate(): DynamicBlockModel
    {
        $duplicate = clone $this;
        $duplicate->id = Uuid::v4();
        $duplicate->changeName(Strings::getTheFirstAvailableName($duplicate->getName(), DynamicBlockRepository::getNames()));

        $fields = $duplicate->getControls();
        $duplicate->controls = [];

        foreach ($fields as $fieldModel){
            $duplicate->controls[] = $fieldModel->duplicateFrom($duplicate);
        }

        return $duplicate;
    }

    /**
     * @inheritDoc
     */
    public static function validationRules(): array
    {
        return [
            'id' => [
                'required' => false,
                'type' => 'string',
            ],
            'title' => [
                'required' => true,
                'type' => 'string',
            ],
            'name' => [
                'required' => true,
                'type' => 'string',
            ],
            'category' => [
                'required' => true,
                'type' => 'string',
            ],
            'icon' => [
                'required' => true,
                'type' => 'string|array',
            ],
            'keywords' => [
                'required' => false,
                'type' => 'array',
            ],
            'postTypes' => [
                'required' => false,
                'type' => 'array',
            ],
            'supports' => [
                'required' => false,
                'type' => 'array',
            ],
            'callback' => [
                'required' => false,
                'type' => 'string',
            ],
            'css' => [
                'required' => false,
                'type' => 'string',
            ],
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'name' => $this->getName(),
            'scriptName' => $this->getScriptName(),
            'blockName' => $this->getBlockName(),
            'category' => $this->getCategory(),
            'icon' => $this->getIcon(),
            'callback' => $this->getCallback(),
            'css' => $this->getCSS(),
            'controls' => $this->getControls(),
            'keywords' => $this->getKeywords(),
            'postTypes' => $this->getPostTypes(),
            'supports' => $this->getSupports(),
        ];
    }
}
