<?php

namespace ACPT\Core\Models\Meta;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\MetaBox;
use ACPT\Constants\MetaGroupDisplay;
use ACPT\Constants\MetaTypes;
use ACPT\Constants\Operator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Abstracts\AbstractModel;
use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Traits\BelongsToTrait;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\PHP\Logics;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\Terms;

class MetaGroupModel extends AbstractModel implements \JsonSerializable
{
	use BelongsToTrait;

	/**
	 * @var string
	 */
	private string $name;

	/**
	 * @var string
	 */
	private ?string $label = null;

	/**
	 * @var string
	 */
	private ?string $display = MetaGroupDisplay::STANDARD;

	/**
	 * @var string
	 */
	private ?string $context = null;

	/**
	 * @var string
	 */
	private ?string $priority = null;

	/**
	 * @var BelongModel[]
	 */
	private array $belongs;

	/**
	 * @var MetaBoxModel[]
	 */
	private array $boxes;

	/**
	 * MetaGroup constructor.
	 *
	 * @param string $id
	 * @param string $name
	 * @param string|null $label
	 * @param string|null $display
	 */
	public function __construct(
		string $id,
		string $name,
		?string $label = null,
		?string $display = null
	) {
	    if(!Strings::alphanumericallyValid($name)){
		    throw new \DomainException($name . ' is not valid name');
	    }

	    if(!empty($display) and !in_array($display, [
		    MetaGroupDisplay::STANDARD,
		    MetaGroupDisplay::ACCORDION,
		    MetaGroupDisplay::VERTICAL_TABS,
		    MetaGroupDisplay::HORIZONTAL_TABS,
	    ])){
		    throw new \DomainException($display . ' is not valid display value');
	    }

		parent::__construct($id);
		$this->name = $name;
		$this->label = $label;
		$this->display = $display;
		$this->boxes = [];
		$this->belongs = [];
	}

	/**
	 * @param string $name
	 */
	public function changeName($name)
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
	 * @return string|null
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * @return string
	 */
	public function getDisplay(): ?string
	{
		return $this->display;
	}

	/**
	 * @return string
	 */
	public function getUIName(): string
	{
		if($this->getLabel()){
			return $this->getLabel();
		}

		return $this->getName();
	}

	/**
	 * @return BelongModel[]
	 */
	public function getBelongs(): array
	{
		return $this->belongs;
	}

	/**
	 * @param BelongModel $belong
	 */
	public function addBelong(BelongModel $belong)
	{
		if(!$this->existsInCollection($belong->getId(), $this->belongs)){
			$this->belongs[] = $belong;
		}
	}

	/**
	 * @param BelongModel $belongModel
	 */
	public function removeBelong(BelongModel $belongModel)
	{
		$this->belongs = $this->removeFromCollection($belongModel->getId(), $this->belongs);
	}

	/**
	 * @return MetaBoxModel[]
	 */
	public function getBoxes(): array
	{
		return $this->boxes;
	}

	/**
	 * @param $name
	 *
	 * @return MetaBoxModel|null
	 */
	public function getBox($name): ?MetaBoxModel
	{
		foreach ($this->getBoxes() as $box){
			if($name === $box->getName()){
				return $box;
			}
		}

		return null;
	}

	/**
	 * @param MetaBoxModel $box
	 */
	public function addBox(MetaBoxModel $box)
	{
		if(!$this->existsInCollection($box->getId(), $this->boxes)){
			$this->boxes[] = $box;
		}
	}

	/**
	 * @param MetaBoxModel $box
	 */
	public function removeBox(MetaBoxModel $box)
	{
		$this->boxes = $this->removeFromCollection($box->getId(), $this->boxes);
	}

	/**
	 * @return int
	 */
	private function getFieldsCount(): int
	{
		$count = 0;

		foreach ($this->getBoxes() as $boxModel){
			$count = $count + count(array_filter($boxModel->getFields(), function (MetaFieldModel $field){
			    return $field->getForgedBy() === null;
            }));
		}

		return $count;
	}

	/**
	 * @return string
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param $context
	 *
	 * @throws \Exception
	 */
	public function setContext($context)
	{
		$allowedValues = [
			MetaBox::CONTEXT_ADVANCED,
			MetaBox::CONTEXT_NORMAL,
			MetaBox::CONTEXT_SIDE,
		];

		if(!in_array($context, $allowedValues)){
			throw new \Exception("Context is not valid");
		}

		$this->context = $context;
	}

	/**
	 * @return string
	 */
	public function getPriority()
	{
		return $this->priority;
	}

    /**
     * @return MetaGroupModel
     */
    public function duplicate(): MetaGroupModel
    {
        $duplicate = clone $this;
        $duplicate->id = Uuid::v4();
        $duplicate->changeName(Strings::getTheFirstAvailableName($duplicate->getName(), MetaRepository::getGroupNames()));

        $belongs = $duplicate->getBelongs();
        $duplicate->belongs = [];

        foreach ($belongs as $belongsModel){
            $duplicate->belongs[] = $belongsModel->duplicate();
        }

        $boxes = $duplicate->getBoxes();
        $duplicate->boxes = [];

        foreach ($boxes as $boxModel){
            $duplicate->boxes[] = $boxModel->duplicateFrom($duplicate);
        }

        return $duplicate;
    }

	/**
	 * @param $priority
	 *
	 * @throws \Exception
	 */
	public function setPriority($priority)
	{
		$allowedValues = [
			MetaBox::PRIORITY_CORE,
			MetaBox::PRIORITY_DEFAULT,
			MetaBox::PRIORITY_HIGH,
			MetaBox::PRIORITY_LOW,
		];

		if(!in_array($priority, $allowedValues)){
			throw new \Exception("Priority is not valid");
		}

		$this->priority = $priority;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'label' => $this->getLabel(),
			'display' => $this->getDisplay(),
			'UIName' => $this->getUIName(),
			'belongs' => $this->getBelongs(),
			"fieldsCount" => $this->getFieldsCount(),
			'boxes' => $this->getBoxes(),
			'context' => $this->getContext(),
			'priority' => $this->getPriority(),
		];
	}

	/**
	 * @param string $format
	 *
	 * @return array
	 */
	public function arrayRepresentation(string $format = 'full'): array
	{
		$boxes = [];
		$belongs = [];

		foreach ($this->getBoxes() as $metaBoxModel){
			$boxes[] = $metaBoxModel->arrayRepresentation($format);
		}

		foreach ($this->getBelongs() as $belong){
			$belongs[] = $belong->arrayRepresentation();
		}

		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'label' => $this->getLabel(),
			'display' => $this->getDisplay(),
			'UIName' => $this->getUIName(),
			'belongs' => $belongs,
			"fieldsCount" => $this->getFieldsCount(),
			'boxes' => $boxes,
			'context' => $this->getContext(),
			'priority' => $this->getPriority(),
		];
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
			'name' => [
				'required' => true,
				'type' => 'string',
			],
			'new_name' => [
				'required' => false,
				'type' => 'string',
			],
			'UIName' => [
				'required' => false,
				'type' => 'string',
			],
			'fieldsCount' => [
				'required' => false,
				'type' => 'string|integer',
			],
			'label' => [
				'required' => false,
				'type' => 'string',
			],
			'display' => [
				'required' => false,
				'type' => 'string',
			],
			'belongs' => [
				'required' => false,
				'type' => 'array',
			],
			'boxes' => [
				'required' => false,
				'type' => 'array',
			],
			'context' => [
				'required' => false,
				'type' => 'string',
			],
			'priority' => [
				'required' => false,
				'type' => 'string',
			],
		];
	}

    /**
     * This method is a helper to check the visibility of a meta group.
     * Constrains can be passed by reference of got from $_GET and $_POST variables.
     *
     * This method is also used by get_acpt_fields() function
     *
     * @param array $args
     *
     * @return bool
     */
    public function isVisible($args = []): bool
    {
        try {
            // extract logic
            $belongs = $this->getBelongs();

            // no location rules
            if(empty($belongs)){
                return false;
            }

            $pagenow = Url::pagenow();
            $logicBlocks = Logics::extractLogicBlocks($belongs);

            // extract base arguments from env
            $page     = $args['option_page'] ?? $_GET['page'] ?? $args['find'] ?? null;
            $postId   = $args['post_id'] ?? $args['comment_id'] ?? $_GET['post'] ?? $_POST['post_ID'] ?? null;
            $termId   = $args['term_id'] ?? $_GET['tag_ID'] ?? null;
            $belongTo = $args['belongsTo'] ?? null;
            $find     = $args['find'] ?? null;

            // review only belongTo (get all fields belonging to CPT, TAXONOMY, or OPTION PAGES)
            if($find === null){

                $allowed = [
                    MetaTypes::CUSTOM_POST_TYPE,
                    MetaTypes::OPTION_PAGE,
                    MetaTypes::TAXONOMY,
                ];

                if(wp_doing_ajax()){
                    $allowed[] = MetaTypes::USER;
                }

                if(in_array($belongTo, $allowed)){
                    return $this->reviewOnlyBelongTo($belongTo, $logicBlocks);
                }
            }

            // determine postType and taxonomy
            $postType = null;
            $taxonomy = null;

            if(($pagenow === "post-new.php" || $pagenow === "edit.php") and isset($_GET['post_type'])){
                $postType = $_GET['post_type'];
            } elseif($postId !== null){
                $postType = get_post_type($postId);
            } elseif ($belongTo === MetaTypes::CUSTOM_POST_TYPE){
                $postType = $find;
            }

            if(isset($_GET['taxonomy'])){
                $taxonomy = $_GET['taxonomy'];
            } elseif($termId !== null){
                $term = get_term($termId);
                $taxonomy = $term->taxonomy;
            } elseif ($belongTo === MetaTypes::TAXONOMY){
                $taxonomy = $find;
            }

            /** @var BelongModel[] $logicBlock */
            foreach ($logicBlocks as $logicBlock){

                $matches = 0;

                foreach ($logicBlock as $model){

                    $belongsTo = $model->getBelongsTo();
                    $find = $model->getFind();
                    $operator = $model->getOperator();

                    switch ($belongsTo){

                        // 1. CUSTOM_POST_TYPE
                        case MetaTypes::CUSTOM_POST_TYPE:
                            if(self::reviewCondition($postType, $operator, $find)){
                                $matches++;
                            }

                            break;

                        // 2. POST_ID
                        case BelongsTo::POST_ID:

                            if(self::reviewCondition($postId, $operator, $find)){
                                $matches++;
                            }

                            break;

                        // 3. PARENT_POST_ID
                        case BelongsTo::PARENT_POST_ID:

                            $postParent = get_post_parent($postId);

                            if($postParent !== null){
                                if(self::reviewCondition($postParent->ID, $operator, $find)){
                                    $matches++;
                                }
                            }

                            break;

                        // 4. POST_TAX
                        // 5. POST_CAT
                        case BelongsTo::POST_TAX:
                        case BelongsTo::POST_CAT:

                            $post = get_post($postId);

                            if($post instanceof \WP_Post){
                                $terms = Terms::getForPostId($post->ID);

                                if(!empty($terms)){
                                    foreach ($terms as $term){
                                        if(self::reviewCondition($term->term_taxonomy_id, $operator, $find)){
                                            $matches++;
                                        }
                                    }
                                }
                            }

                            break;

                        // 6. POST_TEMPLATE
                        case BelongsTo::POST_TEMPLATE:

                            $file = Meta::fetch($postId, MetaTypes::CUSTOM_POST_TYPE, '_wp_page_template', true);

                            if(self::reviewCondition($file, $operator, $find)){
                                $matches++;
                            }

                            break;

                        // 7. TAXONOMY
                        case MetaTypes::TAXONOMY:

                            if(self::reviewCondition($taxonomy, $operator, $find)){
                                $matches++;
                            }

                            break;

                        // 8. TAXONOMY
                        case BelongsTo::TERM_ID:

                            if(self::reviewCondition($termId, $operator, $find)){
                                $matches++;
                            }

                            break;

                        // 9. ATTACHMENT
                        case MetaTypes::MEDIA:

                            $mimeType = get_post_mime_type($postId);

                            if($mimeType){
                                if($find === "All formats"){
                                    $find = $mimeType;
                                }

                                if(self::reviewCondition($mimeType, $operator, $find)){
                                    $matches++;
                                }
                            }

                            break;

                        // 10. Option pages
                        case MetaTypes::OPTION_PAGE:

                            if(self::reviewCondition($page, $operator, $find)){
                                $matches++;
                            }

                            break;


                        // 11. Comments
                        case MetaTypes::COMMENT:

                            $commentPages = [
                                'comment.php',
                            ];

                            if(in_array($pagenow, $commentPages)){
                                $matches++;
                            }

                            break;

                        // 12. ALL USERS
                        case MetaTypes::USER:

                            $userPages = [
                                'users.php',
                                'user-new.php',
                                'user-edit.php',
                                'profile.php',
                            ];

                            if(in_array($pagenow, $userPages)){
                                $matches++;
                            }

                            // REST API requests
                            // Example: /wp-json/wp/v2/users/1
                            // /?rest_route=/wp/v2/users/1
                            if(wp_is_serving_rest_request()){

                                $route = (isset($_GET['rest_route'])) ? $_GET['rest_route'] : parse_url(Url::fullUrl(), PHP_URL_PATH);
                                $params = explode("/", $route);

                                if(count($params) >= 4){
                                    if($params[count($params)-2] === 'users' or $params[count($params)-1] === 'users'){
                                        $matches++;
                                    }
                                }
                            }

                            break;

                        // 13. USER_ID
                        case BelongsTo::USER_ID:

                            $userPages = [
                                'profile.php',
                                'user-edit.php',
                            ];

                            // REST API requests. Example:
                            // /wp-json/wp/v2/users/1
                            // /?rest_route=/wp/v2/users/1
                            if(wp_is_serving_rest_request()){

                                $route = (isset($_GET['rest_route'])) ? $_GET['rest_route'] : parse_url(Url::fullUrl(), PHP_URL_PATH);
                                $params = explode("/", $route); // /wp/v2/users/1

                                if(count($params) >= 4){
                                    $type = $params[count($params)-2];
                                    $lastPart = $params[count($params)-1];

                                    if($type === 'users'){
                                        if(self::reviewCondition($lastPart, $operator, $find)){
                                            $matches++;
                                        }
                                    }
                                }
                            }

                            if(in_array($pagenow, $userPages)){
                                if ( ! function_exists( 'wp_get_current_user' ) ) {
                                    include_once ABSPATH . '/wp-includes/pluggable.php';
                                }

                                $userId = $args['user_id'] ?? $_GET['user_id'] ?? get_current_user_id() ?? $find;

                                if(self::reviewCondition($userId, $operator, $find)){
                                    $matches++;
                                }
                            }

                            break;
                    }
                }

                if($matches === count($logicBlock)){
                    return true;
                }
            }

            return false;
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return false;
        }
    }

    /**
     * @param string $belongTo
     * @param array  $logicBlocks
     *
     * @return bool
     */
    private function reviewOnlyBelongTo($belongTo, $logicBlocks = [])
    {
        foreach ($logicBlocks as $logicBlock){

            $matches = 0;

            foreach ($logicBlock as $model) {
                if($belongTo == $model->getBelongsTo()){
                    $matches++;
                }
            }

            if($matches === count($logicBlock)){
                return true;
            }
        }

        return false;
    }

    /**
     * @param $find
     * @param $operator
     * @param $check
     *
     * @return bool
     */
    private function reviewCondition($find, $operator, $check)
    {
        switch ($operator){
            case Operator::EQUALS:
                if($find == $check){
                    return true;
                }

                break;

            case Operator::NOT_EQUALS:
                if($find != $check){
                    return true;
                }

                break;

            case Operator::IN:
                if(is_string($find) and is_string($check)){
                    $check = Strings::matches($find, $check);

                    if(count($check) > 0){
                        return true;
                    }
                }

                break;

            case Operator::NOT_IN:
                if(is_string($find) and is_string($check)){
                    $check = Strings::matches($find, $check);

                    if( empty($check)){
                        return true;
                    }
                }

                break;
        }

        return false;
    }
}