<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Data\Meta;

abstract class AbstractMetaFieldValueCommand
{
	/**
	 * @var MetaFieldModel
	 */
	protected MetaFieldModel $fieldModel;

	/**
	 * @var string
	 */
	protected $belongsTo;

	/**
	 * @var string
	 */
	protected $location;

	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var array
	 */
	protected $args;

    /**
     * @var bool
     */
    protected bool $withContext;

    /**
     * @var string
     */
    protected $return;

    /**
	 * SaveMetaFieldValueCommand constructor.
	 *
	 * @param MetaFieldModel $fieldModel
	 * @param array $args
	 *
	 * @throws \Exception
	 */
	public function __construct(MetaFieldModel $fieldModel, $args = [])
	{
		$this->buildFromArgs($args);
		$this->args = $args;
		$this->fieldModel = $fieldModel;

		// override find and belongsTo
        $this->fieldModel->setBelongsToLabel($this->belongsTo);
        $this->fieldModel->setFindLabel($this->location);

        $this->withContext = (isset($this->args['with_context']) and $this->args['with_context'] == true) ? true : false;
        $this->return = (isset($this->args['return']) and !empty($this->args['return'])) ? $this->args['return'] : 'object';
	}

	/**
	 * @param $args
	 *
	 * @throws \Exception
	 */
	protected function buildFromArgs($args)
	{
		if(isset($args['post_id'])){
			$this->belongsTo = MetaTypes::CUSTOM_POST_TYPE;
			$this->location = $args['post_id'];
			$this->identifier = 'post_id';
		} elseif (isset($args['term_id'])){
			$this->belongsTo = MetaTypes::TAXONOMY;
			$this->location = $args['term_id'];
			$this->identifier = 'term_id';
		} elseif (isset($args['user_id'])){
			$this->belongsTo = MetaTypes::USER;
			$this->location = $args['user_id'];
			$this->identifier = 'user_id';
		} elseif (isset($args['comment_id'])){
			$this->belongsTo = MetaTypes::COMMENT;
			$this->location = $args['comment_id'];
			$this->identifier = 'comment_id';
		} elseif (isset($args['option_page'])){
			$this->belongsTo = MetaTypes::OPTION_PAGE;
			$this->location = $args['option_page'];
			$this->identifier = 'option_page';
		}

		if($this->belongsTo === null and $this->location === null){
			throw new \Exception("BelongsTo and location are null. Invalid args provided");
		}
	}

	/**
	 * @param $key
	 * @param null $location
	 *
	 * @return mixed|void
	 */
	protected function get($key, $location = null)
	{
        $loc = $location ? $location : $this->location;
        $fetched = Meta::fetch($loc, $this->belongsTo, $key);

        if($fetched === null){
            $key = $loc.'_'.$key;

            return Meta::fetch($loc, $this->belongsTo, $key);
        }

        return $fetched;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|void
	 */
	protected function getData($key = '')
	{
		return $this->get($this->fieldModel->getDbName().$key);
	}

	/**
	 * @param $key
	 * @param $value
	 * @param null $location
	 *
	 * @return bool|int|\WP_Error
	 */
	protected function set($key, $value, $location = null)
	{
		$loc = $location ? $location : $this->location;

		// prefix $key with $loc if needed
		if($this->belongsTo === MetaTypes::OPTION_PAGE and !Strings::startsWith($key, $loc."_")){
			$key = $loc.'_'.$key;
		}

		return Meta::save($loc, $this->belongsTo, $key, $value);
	}
}