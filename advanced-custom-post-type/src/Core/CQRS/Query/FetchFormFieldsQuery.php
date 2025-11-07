<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Constants\FormAction;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Form\FormModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\FormRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\Wordpress\Terms;
use ACPT\Utils\Wordpress\Users;

class FetchFormFieldsQuery implements QueryInterface
{
	/**
	 * @var
	 */
	private $id;

	/**
	 * @var FormModel|null
	 */
	private $formModel;

	/**
	 * FetchFormFieldsQuery constructor.
	 *
	 * @param $id
	 *
	 * @throws \Exception
	 */
	public function __construct($id)
	{
		$this->id = $id;
		$this->formModel = FormRepository::getById($this->id);
	}

	/**
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function execute()
	{
		return [
			'form' => $this->formModel,
			'saved' => $this->savedFields(),
			'fields' => $this->availableFields(),
		];
	}

	/**
	 * @return array
	 */
	private function savedFields(): array
	{
		return $this->formModel->getFields();
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function availableFields(): array
	{
		return array_merge(
			$this->typographyFields(),
			$this->standardFields(),
			$this->WordPressPostFields(),
			$this->WordPressTaxonomyFields(),
			$this->WordPressUserFields(),
			$this->ACPTFields(),
		);
	}

    private function typographyFields(): array
    {
        return [
            [
                "id" => Uuid::v4(),
                "metaFieldId" => null,
                "group" => "Typography elements",
                "name" => "heading",
                "label" => null,
                "description" => null,
                "type" => FormFieldModel::FORMATTING_HEADING_TYPE,
                "isRequired" => false,
                "validation" => false,
                "isTextual" => true,
                "isReusable" => true,
                "isTypography" => true,
                "extra" => [
                    "fontSize" => "l",
                    "defaultValue" => "This is a heading",
                    "placeholder" => null,
                ],
            ],
            [
                "id" => Uuid::v4(),
                "metaFieldId" => null,
                "group" => "Typography elements",
                "name" => "paragraph",
                "label" => null,
                "description" => null,
                "type" => FormFieldModel::FORMATTING_PARAGRAPH_TYPE,
                "isRequired" => false,
                "validation" => false,
                "isTextual" => true,
                "isReusable" => true,
                "isTypography" => true,
                "extra" => [
                    "fontSize" => "m",
                    "defaultValue" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
                    "placeholder" => null,
                ],
            ],
            [
                "id" => Uuid::v4(),
                "metaFieldId" => null,
                "group" => "Typography elements",
                "name" => "link",
                "label" => null,
                "description" => null,
                "type" => FormFieldModel::FORMATTING_LINK_TYPE,
                "isRequired" => false,
                "validation" => false,
                "isTextual" => true,
                "isReusable" => true,
                "isTypography" => true,
                "extra" => [
                    "fontSize" => "m",
                    "defaultValue" => "This is a heading",
                    "placeholder" => null,
                    "headingLevel" => "h1",
                ],
            ],
            [
                "id" => Uuid::v4(),
                "metaFieldId" => null,
                "group" => "Typography elements",
                "name" => "image",
                "label" => null,
                "description" => null,
                "type" => FormFieldModel::FORMATTING_IMAGE_TYPE,
                "isRequired" => false,
                "validation" => false,
                "isTextual" => false,
                "isReusable" => true,
                "isTypography" => true,
                "extra" => [
                    "defaultValue" => null,
                    "placeholder" => null,
                ],
            ],
        ];
    }

	/**
	 * @return array
	 */
	private function standardFields(): array
	{
		if($this->formModel->getAction() === FormAction::FILL_META){
			return [
				[
					"id" => Uuid::v4(),
					"metaFieldId" => null,
					"group" => "Standard fields",
					"name" => "captcha",
					"label" => "Recaptcha",
					"description" => null,
					"type" => FormFieldModel::CAPTCHA_TYPE,
					"isRequired" => true,
					"validation" => false,
					"isTextual" => false,
					"isReusable" => false,
                    "isTypography" => false,
					"extra" => [
						"defaultValue" => null,
						"placeholder" => null,
					],
				],
				[
					"id" => Uuid::v4(),
					"metaFieldId" => null,
					"group" => "Standard fields",
					"name" => "turnstile",
					"label" => "Turnstile",
					"description" => null,
					"type" => FormFieldModel::TURNSTILE_TYPE,
					"isRequired" => true,
					"validation" => false,
					"isTextual" => false,
					"isReusable" => false,
                    "isTypography" => false,
					"extra" => [
						"defaultValue" => null,
						"placeholder" => null,
					],
				],
                [
                    "id" => Uuid::v4(),
                    "metaFieldId" => null,
                    "group" => "Standard fields",
                    "name" => "acceptance",
                    "label" => null,
                    "description" => null,
                    "type" => FormFieldModel::ACCEPTANCE_TYPE,
                    "isRequired" => true,
                    "validation" => false,
                    "isTextual" => true,
                    "isReusable" => true,
                    "isTypography" => true,
                    "extra" => [
                        "fontSize" => "m",
                        "defaultValue" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
                        "placeholder" => null,
                    ],
                ],
				[
					"id" => Uuid::v4(),
					"metaFieldId" => null,
					"group" => "Standard fields",
					"name" => "button",
					"label" => null,
					"description" => null,
					"type" => FormFieldModel::BUTTON_TYPE,
					"isRequired" => false,
					"validation" => false,
					"isTextual" => false,
					"isReusable" => true,
                    "isTypography" => false,
					"extra" => [
                        "fontSize" => "m",
                        "disabled" => false,
						"defaultValue" => null,
						"placeholder" => null,
						"type" => "submit",
						"label" => "Button"
					],
				],
			];
		}

		return [
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "text",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::TEXT_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => "placeholder"
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "email",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::EMAIL_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => "noreply@gmail.com"
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "number",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::NUMBER_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => 1,
					"min" => 1,
					"max" => 100,
					"step" => 1,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "phone",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::PHONE_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => "+44000000"
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "color",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::COLOR_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "disabled" => false,
					"defaultValue" => "#202020",
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "country",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::COUNTRY_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "date",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::DATE_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => "2023-06-01"
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "url",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::URL_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => 'https://acpt.io',
					"hideLabel" => false,
					"labelPlaceholder" => "Enter text link",
					"labelDefaultValue" => "",
					"urlType" => "absolute",
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "range",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::RANGE_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => false,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => 50,
					"placeholder" => null,
					"min" => 1,
					"max" => 100,
					"step" => 1,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "checkbox",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::CHECKBOX_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => false,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"options" => [
						[
							"label" => "label",
							"value" => "value",
						],
						[
							"label" => "label",
							"value" => "value",
						]
					]
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "radio",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::RADIO_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => false,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"options" => [
						[
							"label" => "label",
							"value" => "value",
						],
						[
							"label" => "label",
							"value" => "value",
						]
					]
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "select",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::SELECT_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => false,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"isMulti" => false,
					"empty" => false,
					"options" => [
						[
							"id" => Uuid::v4(),
							"label" => "label",
							"value" => "value",
						],
						[
							"id" => Uuid::v4(),
							"label" => "label",
							"value" => "value",
						]
					]
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "textarea",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::TEXTAREA_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => "Textarea",
					"rows" => 6,
					"cols" => 30,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "file",
				"label" => "label",
				"description" => "description",
				"type" => FormFieldModel::FILE_TYPE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => false,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "hidden",
				"label" => null,
				"description" => null,
				"type" => FormFieldModel::HIDDEN_TYPE,
				"isRequired" => false,
				"validation" => false,
				"isTextual" => false,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "captcha",
				"label" => "label",
				"description" => null,
				"type" => FormFieldModel::CAPTCHA_TYPE,
				"isRequired" => false,
				"validation" => false,
				"isTextual" => false,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "turnstile",
				"label" => "label",
				"description" => null,
				"type" => FormFieldModel::TURNSTILE_TYPE,
				"isRequired" => false,
				"validation" => false,
				"isTextual" => false,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "Standard fields",
				"name" => "button",
				"label" => null,
				"description" => null,
				"type" => FormFieldModel::BUTTON_TYPE,
				"isRequired" => false,
				"validation" => false,
				"isTextual" => false,
				"isReusable" => true,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"type" => "submit",
					"label" => "Button"
				],
			],
		];
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function WordPressPostFields(): array
	{
		if($this->showWordPressPostFields() === false){
			return [];
		}

		$users = [];

		foreach(array_slice(Users::getList(),0,3) as $id => $user){
			$users[] = [
				"id" => Uuid::v4(),
				"label" => $user,
				"value" => $id,
			];
		}

		$postType = $this->formModel->getMetaDatum('fill_meta_location_find')->getValue();
		$terms = Terms::getForPostType($postType);

		$fields = [];

		// title
		if(post_type_supports( $postType, 'title' )){
			$fields[] = [
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress post fields",
				"name" => "post-title",
				"label" => "Post title",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_POST_TITLE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
				    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			];
		}

		// editor
		if(post_type_supports( $postType, 'editor' )){
			$fields[] = [
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress post fields",
				"name" => "post-content",
				"label" => "Post content",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_POST_CONTENT,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"wysiwyg" => false,
					"rows" => 6,
					"cols" => 30,
				],
			];
		}

		// excerpt
		if(post_type_supports( $postType, 'excerpt' )){
			$fields[] = [
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress post fields",
				"name" => "post-excerpt",
				"label" => "Post excerpt",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_POST_EXCERPT,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"rows" => 6,
					"cols" => 30,
				],
			];
		}

		// author
		if(post_type_supports( $postType, 'author' )){
			$fields[] = [
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress post fields",
				"name" => "post-author",
				"label" => "Post author",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_POST_AUTHOR,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => false,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"empty" => false,
					"options" => $users
				],
			];
		}

		// thumbnail
		if(post_type_supports( $postType, 'thumbnail' )){
			$fields[] = [
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress post fields",
				"name" => "post-thumbnail",
				"label" => "Post thumbnail",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_POST_THUMBNAIL,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => false,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			];
		}

		return array_merge($fields, [
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress post fields",
				"name" => "post-date",
				"label" => "Post date",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_POST_DATE,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress post fields",
				"name" => "post-taxonomies",
				"label" => "Post taxonomies",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_POST_TAXONOMIES,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => false,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"isMulti" => true,
					"empty" => false,
					"options" => $terms
				],
			],
		]);
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	private function showWordPressPostFields(): bool
	{
		return $this->metaGroupBelongsTo(MetaTypes::CUSTOM_POST_TYPE);
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function WordPressUserFields(): array
	{
		if($this->showWordPressUserFields() === false){
			return [];
		}

		return [
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress user fields",
				"name" => "user-email",
				"label" => "User email",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_USER_EMAIL,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress user fields",
				"name" => "user-first-name",
				"label" => "User first name",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_USER_FIRST_NAME,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"rows" => 6,
					"cols" => 30,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress user fields",
				"name" => "user-last-name",
				"label" => "User last name",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_USER_LAST_NAME,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress user fields",
				"name" => "username",
				"label" => "Username",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_USER_USERNAME,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress user fields",
				"name" => "user-password",
				"label" => "User password",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_USER_PASSWORD,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress user fields",
				"name" => "user-bio",
				"label" => "User bio",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_USER_BIO,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"rows" => 6,
					"cols" => 30,
				],
			],
		];
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	private function showWordPressTaxonomyFields(): bool
	{
		return $this->metaGroupBelongsTo(MetaTypes::TAXONOMY);
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function WordPressTaxonomyFields(): array
	{
		if($this->showWordPressTaxonomyFields() === false){
			return [];
		}

		return [
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress taxonomy fields",
				"name" => "tax-name",
				"label" => "Taxonomy name",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_TERM_NAME,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress taxonomy fields",
				"name" => "tax-description",
				"label" => "Taxonomy description",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_TERM_DESCRIPTION,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
					"rows" => 6,
					"cols" => 30,
				],
			],
			[
				"id" => Uuid::v4(),
				"metaFieldId" => null,
				"group" => "WordPress taxonomy fields",
				"name" => "tax-slug",
				"label" => "Taxonomy slug",
				"description" => null,
				"type" => FormFieldModel::WORDPRESS_TERM_SLUG,
				"isRequired" => false,
				"validation" => true,
				"isTextual" => true,
				"isReusable" => false,
                "isTypography" => false,
				"extra" => [
                    "fontSize" => "m",
                    "disabled" => false,
					"defaultValue" => null,
					"placeholder" => null,
				],
			],
		];
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	private function showWordPressUserFields(): bool
	{
		return $this->metaGroupBelongsTo(MetaTypes::USER);
	}

	/**
	 * @param $belongsTo
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function metaGroupBelongsTo($belongsTo)
	{
		if($this->formModel->getAction() !== FormAction::FILL_META){
			return false;
		}

		foreach ($this->formModel->getMeta() as $metadataModel){
			if($metadataModel->getKey() === 'fill_meta_location_belong' and $metadataModel->getValue() === $belongsTo){
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function ACPTFields(): array
	{
		$fields = [];

		$belong = $this->formModel->getMetaDatum('fill_meta_location_belong');
		$find = $this->formModel->getMetaDatum('fill_meta_location_find');

		foreach ($this->formModel->getMeta() as $metadataModel){
			if($metadataModel->getKey() === 'fill_meta_fields'){
				$fieldIds = $metadataModel->getFormattedValue();

				foreach ($fieldIds as $fieldId){
					if(is_string($fieldId)){
						$fieldModel = MetaRepository::getMetaFieldById($fieldId);

						// disable relational and nestable fields
						if($fieldModel !== null and !$fieldModel->isFlexible()){

							$fieldName = '';

							if($belong !== null and $find !== null and $belong->getValue() === MetaTypes::OPTION_PAGE){
								$fieldName .= $find->getValue() . '_';
							}

							$fieldName .= $fieldModel->getDbName();

							$field = [
								"id" => Uuid::v4(),
								"metaFieldId" => $fieldModel->getId(),
								"group" => "ACPT fields",
								"belong" => $belong ? $belong->getValue() : null,
								"find" => $find ? $find->getValue() : null,
								"name" => $fieldName,
								"label" => $fieldModel->getLabel() ? $fieldModel->getLabel() : $fieldModel->getName(),
								"description" => $fieldModel->getDescription() !== '' ? $fieldModel->getDescription() : 'description',
								"type" => $fieldModel->resolveFieldTypeForForms(),
								"isRequired" =>  $fieldModel->isRequired(),
								"validation" => ($fieldModel->canFieldHaveValidationAndLogicRules() or $fieldModel->isMedia()),
                                "validationRules" => $fieldModel->getValidationRules(),
								"isTextual" => $fieldModel->isTextual(),
								"isMedia" => $fieldModel->isMedia(),
								"isRelational" => $fieldModel->isRelational(),
								"isReusable" => false,
                                "isTypography" => false,
								"extra" => [
                                    "fontSize" => "m",
                                    "disabled" => false,
									"defaultValue" => (Strings::isJson($fieldModel->getDefaultValue()) ? json_decode($fieldModel->getDefaultValue()) : $fieldModel->getDefaultValue()),
									"placeholder" => null,
								],
							];

							// add extra
							switch ($fieldModel->getType()){

                                case MetaFieldModel::DATE_RANGE_TYPE:
                                case MetaFieldModel::DATE_TYPE:
                                    $field['extra']['minDate'] = $fieldModel->getAdvancedOption('min') ?? null;
                                    $field['extra']['maxDate'] = $fieldModel->getAdvancedOption('max') ?? null;
                                    $field['extra']['dateFormat'] = $fieldModel->getAdvancedOption('date_format') ?? null;
                                    $field['extra']['step'] = $fieldModel->getAdvancedOption('step') ?? null;
                                    break;

                                case MetaFieldModel::DATE_TIME_TYPE:
                                    $field['extra']['dateFormat'] = $fieldModel->getAdvancedOption('date_format') ?? null;
                                    $field['extra']['timeFormat'] = $fieldModel->getAdvancedOption('time_format') ?? null;
                                    break;

                                case MetaFieldModel::TIME_TYPE:
                                    $field['extra']['timeFormat'] = $fieldModel->getAdvancedOption('time_format') ?? null;
                                    break;

								case MetaFieldModel::CURRENCY_TYPE:
									$field['extra']['uom'] = 'USD';
									$field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? 0.01;
                                    $field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? 99999999999999;
                                    $field['extra']['step'] = $fieldModel->getAdvancedOption('step') ?? 0.01;
									break;

								case MetaFieldModel::WEIGHT_TYPE:
									$field['extra']['uom'] = 'KILOGRAM';
                                    $field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? 0.01;
                                    $field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? 99999999999999;
                                    $field['extra']['step'] = $fieldModel->getAdvancedOption('step') ?? 0.01;
									break;

								case MetaFieldModel::LENGTH_TYPE:
									$field['extra']['uom'] = 'METER';
                                    $field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? 0.01;
                                    $field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? 99999999999999;
                                    $field['extra']['step'] = $fieldModel->getAdvancedOption('step') ?? 0.01;
									break;

								case MetaFieldModel::TEXT_TYPE:
									$field['extra']['placeholder'] = 'placeholder';
                                    $field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? null;
                                    $field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? null;
									break;

								case MetaFieldModel::EMAIL_TYPE:
									$field['extra']['placeholder'] = 'noreply@gmail.com';
                                    $field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? null;
                                    $field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? null;
									break;

								case MetaFieldModel::URL_TYPE:
									$field['extra']['placeholder'] = 'https://acpt.io';
									$field['extra']['hideLabel'] = false;
									$field['extra']['labelPlaceholder'] = "Enter text link";
									$field['extra']['urlType'] = "absolute";
                                    $field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? null;
                                    $field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? null;
									break;

								case MetaFieldModel::NUMBER_TYPE:
								case MetaFieldModel::RANGE_TYPE:
									$field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? 1;
									$field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? 100;
									$field['extra']['step'] = $fieldModel->getAdvancedOption('step') ?? 1;
									break;

								case MetaFieldModel::EDITOR_TYPE:
									$field['extra']['placeholder'] = 'Editor';
									$field['extra']['wysiwyg'] = true;
									$field['extra']['rows'] = $fieldModel->getAdvancedOption('rows') ?? 6;
									$field['extra']['cols'] = $fieldModel->getAdvancedOption('cols') ?? 30;
                                    $field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? null;
                                    $field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? null;
									break;

								case MetaFieldModel::HTML_TYPE:
								case MetaFieldModel::TEXTAREA_TYPE:
									$field['extra']['placeholder'] = 'Textarea';
									$field['extra']['wysiwyg'] = false;
                                    $field['extra']['rows'] = $fieldModel->getAdvancedOption('rows') ?? 6;
                                    $field['extra']['cols'] = $fieldModel->getAdvancedOption('cols') ?? 30;
                                    $field['extra']['min'] = $fieldModel->getAdvancedOption('min') ?? null;
                                    $field['extra']['max'] = $fieldModel->getAdvancedOption('max') ?? null;
									break;

                                case MetaFieldModel::REPEATER_TYPE:
                                    $field['extra']['layout'] = $fieldModel->getAdvancedOption('layout');
                                    break;

								case MetaFieldModel::USER_TYPE:
								case MetaFieldModel::POST_OBJECT_TYPE:
								case MetaFieldModel::TERM_OBJECT_TYPE:
                                    $field['extra']["isMulti"] = false;
                                    break;

								case MetaFieldModel::RADIO_TYPE:
								case MetaFieldModel::SELECT_TYPE:
									$field['extra']["isMulti"] = false;
									$field['extra']["display"] = $fieldModel->getAdvancedOption('display') ?? "block";

									foreach ($fieldModel->getOptions() as $optionModel){
										$field['extra']["options"][] = [
											"id" => $optionModel->getId(),
											"label" => $optionModel->getLabel(),
											"value" => $optionModel->getValue(),
										];
									}

									break;

                                case MetaFieldModel::POST_TYPE:
                                    $field['extra']["isMulti"] = $fieldModel->getRelations()[0]->isMany();
                                    break;

                                case MetaFieldModel::USER_MULTI_TYPE:
                                case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
                                case MetaFieldModel::ADDRESS_MULTI_TYPE:
                                case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
                                    $field['extra']["isMulti"] = true;
                                    break;

                                case MetaFieldModel::CHECKBOX_TYPE:
								case MetaFieldModel::SELECT_MULTI_TYPE:
									$field['extra']["isMulti"] = true;

									foreach ($fieldModel->getOptions() as $optionModel){
										$field['extra']["options"][] = [
											"id" => $optionModel->getId(),
											"label" => $optionModel->getLabel(),
											"value" => $optionModel->getValue(),
										];
									}

									break;

								case MetaFieldModel::IMAGE_SLIDER_TYPE:
								case MetaFieldModel::GALLERY_TYPE:
									$field['extra']["multiple"] = true;
									$field['extra']["accept"] = "image/*";
									break;

								case MetaFieldModel::IMAGE_TYPE:
									$field['extra']["multiple"] = false;
									$field['extra']["accept"] = "image/*";
									break;

								case MetaFieldModel::VIDEO_TYPE:
									$field['extra']["multiple"] = false;
									$field['extra']["accept"] = "video/*";
									break;

                                case MetaFieldModel::AUDIO_TYPE:
                                    $field['extra']["multiple"] = false;
                                    $field['extra']["accept"] = "audio/*";
                                    break;

                                case MetaFieldModel::AUDIO_MULTI_TYPE:
                                    $field['extra']["multiple"] = true;
                                    $field['extra']["accept"] = "audio/*";
                                    break;

                            }

							$fields[] = $field;
						}
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * @param $type
	 *
	 * @return mixed
	 */
	private function resolveFieldType($type)
	{
		if($type === MetaFieldModel::EDITOR_TYPE){
			return MetaFieldModel::TEXTAREA_TYPE;
		}

		if($type === MetaFieldModel::SELECT_MULTI_TYPE){
			return MetaFieldModel::SELECT_TYPE;
		}

        if($type === MetaFieldModel::USER_MULTI_TYPE){
            return MetaFieldModel::USER_TYPE;
        }

        if($type === MetaFieldModel::TERM_OBJECT_MULTI_TYPE){
            return MetaFieldModel::TERM_OBJECT_TYPE;
        }

        if($type === MetaFieldModel::POST_OBJECT_MULTI_TYPE){
            return MetaFieldModel::POST_OBJECT_TYPE;
        }

		if(in_array($type, [
			MetaFieldModel::AUDIO_MULTI_TYPE,
			MetaFieldModel::AUDIO_TYPE,
			MetaFieldModel::GALLERY_TYPE,
			MetaFieldModel::IMAGE_TYPE,
			MetaFieldModel::IMAGE_SLIDER_TYPE,
			MetaFieldModel::VIDEO_TYPE,
		])){
			return MetaFieldModel::FILE_TYPE;
		}

		return $type;
	}
}