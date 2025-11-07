<?php

namespace ACPT\Core\Models\Meta;

use ACPT\Constants\Logic;
use ACPT\Constants\Operator;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Abstracts\AbstractModel;
use ACPT\Utils\PHP\JSON;

class MetaBoxVisibilityModel extends AbstractModel implements \JsonSerializable
{
    const TYPES = [
        'POST_ID',
        'TERM_ID',
        'USER_ID',
        'OPTION_PAGE',
        'TAXONOMY',
        'USER',
    ];

    /**
     * @var MetaBoxModel
     */
    private MetaBoxModel $metaBox;

    /**
     * @var array
     */
    private array $type = [];

    /**
     * @var string
     */
    private string $operator;

    /**
     * @var string
     */
    private string $value;

	/**
	 * @var string|null
	 */
    private ?string $logic = null;

    /**
     * @var int
     */
    private int $sort;

	/**
	 * @var bool
	 */
    private bool $backEnd;

	/**
	 * @var bool
	 */
    private bool $frontEnd;

	/**
	 * MetaFieldVisibilityModel constructor.
	 *
	 * @param string $id
	 * @param MetaBoxModel $metaBox
	 * @param array $type
	 * @param string $operator
	 * @param string $value
	 * @param int $sort
	 * @param string|null $logic
	 * @param bool $backEnd
	 * @param bool $frontEnd
	 *
	 * @throws \Exception
	 */
    public function __construct(
	    string $id,
        MetaBoxModel $metaBox,
	    array $type,
	    string $operator,
	    string $value,
        int $sort,
        ?string $logic = null,
	    bool $backEnd = true,
	    bool $frontEnd = true
    )
    {
        parent::__construct($id);
        $this->setType($type);
        $this->setOperator($operator);
        $this->setLogic($logic);
        $this->metaBox = $metaBox;
        $this->value   = $value;
        $this->sort = $sort;
        $this->backEnd = $backEnd;
        $this->frontEnd = $frontEnd;
    }

    /**
     * @return MetaBoxModel
     */
    public function getMetaBox() {
        return $this->metaBox;
    }

    /**
     * @param $logic
     *
     * @throws \Exception
     */
    private function setLogic($logic)
    {
        if(!in_array($logic, Logic::ALLOWED_VALUES)){
            throw new \Exception($logic . ' is not a valid logic');
        }

        $this->logic = $logic;
    }

    /**
     * @param array $type
     * @example ["type" => "TAXONOMY", "value" => 3]
     *
     * @throws \Exception
     */
    private function setType(array $type)
    {
        if(!isset($type['type'])){
            throw new \Exception('Type is not a valid type');
        }

        if(!in_array($type['type'], self::TYPES)){
            throw new \Exception($type . ' is not a valid type');
        }

        $this->type = $type;
    }

    /**
     * @param $operator
     *
     * @throws \Exception
     */
    private function setOperator($operator)
    {
        if(!in_array($operator, Operator::ALLOWED_VALUES)){
            throw new \Exception($operator . ' is not a valid operator');
        }

        $this->operator = $operator;
    }

    /**
     * @return array
     */
    public function getType()
    {
        return $this->type;
    }

	/**
	 * Needed by UI
	 *
	 * @return string
	 */
    public function getTypeForUI()
    {
    	if($this->type['value'] instanceof MetaFieldModel){
		    $this->type['value'] = $this->type['value']->getId();
	    }

		return JSON::arrayToEscapedJson($this->type);
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getLogic()
    {
        return $this->logic;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

	/**
	 * @return bool
	 */
	public function isBackEnd(): bool
	{
		return $this->backEnd;
	}

	/**
	 * @return bool
	 */
	public function isFrontEnd(): bool
	{
		return $this->frontEnd;
	}

	#[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'boxId' => $this->getMetaBox()->getId(),
            'type' => $this->getTypeForUI(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
            'logic' => $this->getLogic(),
            'sort' => (int)$this->sort,
            'backEnd' => $this->isBackEnd(),
            'frontEnd' => $this->isFrontEnd(),
        ];
    }

    /**
     * @param MetaBoxModel $duplicateFrom
     *
     * @return MetaBoxVisibilityModel
     */
	public function duplicateFrom( MetaBoxModel $duplicateFrom ): MetaBoxVisibilityModel
	{
		$duplicate          = clone $this;
		$duplicate->id      = Uuid::v4();
		$duplicate->metaBox = $duplicateFrom;

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
			'metaBox' => [
				'required' => false,
				'type' => 'object',
				'instanceOf' => MetaBoxModel::class
			],
			'boxId' => [
				'required' => false,
				'type' => 'string',
			],
			'fieldId' => [
				'required' => false,
				'type' => 'string',
			],
			'type' => [
				'required' => true,
				'type' => 'array',
			],
			'operator' => [
				'required' => true,
				'type' => 'string',
				'enum' => Operator::ALLOWED_VALUES,
			],
			'value' => [
				'required' => true,
				'type' => 'string|integer',
			],
			'logic' => [
				'required' => false,
				'type' => 'string',
				'enum' => [
					Logic::BLANK,
					Logic::AND,
					Logic::OR,
				],
			],
			'sort' => [
				'required' => false,
				'type' => 'string|integer',
			],
			'backEnd' => [
				'required' => false,
				'type' => 'boolean',
			],
			'frontEnd' => [
				'required' => false,
				'type' => 'boolean',
			],
		];
	}
}