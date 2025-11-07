<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class GenerateGutenbergTemplateQuery implements QueryInterface
{
	/**
	 * @var array
	 */
	private $field;

	/**
	 * @var null
	 */
	private $contextId;
	/**
	 * @var array
	 */
	private array $attributes;

	/**
	 * GenerateGutenbergTemplateQuery constructor.
	 *
	 * @param $field
	 * @param $contextId
	 * @param array $attributes
	 */
	public function __construct($field, $contextId = null, $attributes = [])
	{
		$this->field = $field;
		$this->contextId = $contextId;
		$this->attributes = $attributes;
	}

	/**
	 * @return array|mixed
	 */
	public function execute()
	{
        if($this->field['type'] === MetaFieldModel::FLEXIBLE_CONTENT_TYPE or $this->field['type'] === MetaFieldModel::REPEATER_TYPE){
            return $this->repeaterLoopTemplate();
        }

        return $this->relationalFieldTemplate();
	}

    /**
     * @return array
     */
    private function repeaterLoopTemplate()
    {
        return [
            [
                'advanced-custom-post-type/repeater-loop-item',
                [],
                [
                    [
                        'core/paragraph', [
                            'content' => "This is a dummy content"
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
	private function relationalFieldTemplate()
    {
        $templates = [];

        switch ($this->field['type']){

            // POST_TYPE
            case MetaFieldModel::POST_TYPE:

                if(!empty($this->field['relations'])){
                    $to = $this->field['relations']['to'];
                    $toType = $to['type'];
                    $toValue = $to['value'];

                    switch ($toType){
                        case MetaTypes::CUSTOM_POST_TYPE:
                            $templateType = $this->attributes['templateType'] ?? 'title-excerpt';
                            $templates[] = $this->generateSinglePostTemplate($templateType);
                            break;

                        case MetaTypes::TAXONOMY:
                            $templateType = $this->attributes['templateType'] ?? 'title-excerpt';
                            $templates[] =  $this->generateTermsTemplate($templateType);
                            break;

                        case MetaTypes::USER:
                            $templateType = $this->attributes['templateType'] ?? 'avatar-name';
                            $templates[] = $this->generateSingleUserTemplate($templateType);
                            break;
                    }
                }
                break;

            // POST_OBJECT_TYPE
            case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
            case MetaFieldModel::POST_OBJECT_TYPE:
                $templateType = $this->attributes['templateType'] ?? 'title-excerpt';
                $templates[] = $this->generateSinglePostTemplate($templateType);
                break;

            // POST_OBJECT_MULTI_TYPE
            case MetaFieldModel::TERM_OBJECT_TYPE:
            case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
                $templateType = $this->attributes['templateType'] ?? 'title-excerpt';
                $templates[] =  $this->generateTermsTemplate($templateType);
                break;

            // USER_TYPE
            case MetaFieldModel::USER_TYPE:
            case MetaFieldModel::USER_MULTI_TYPE:
                $templateType = $this->attributes['templateType'] ?? 'avatar-name';
                $templates[] = $this->generateSingleUserTemplate($templateType);
                break;
        }

        return $this->generateTemplate($templates);
    }

	/**
	 * @param $templateType
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	private function generateSinglePostTemplate($templateType)
	{
		switch ($templateType){
			case 'title-excerpt':
				return [
					'core/column',
					[],
					[
						[
							'core/heading', [
								'level' => 3,
								'content' => '{{wp_post_title:link}}'
							]
						],
						[
							'core/paragraph', ['content' => '{{wp_post_excerpt}}']
						]
					]
				];

			case 'title-image-excerpt':
				return [
					'core/column',
					[],
					[
						[
							'core/heading', [
								'level' => 3,
								'content' => '{{wp_post_title:link}}'
							]
						],
                        [
                            'core/image', [
                                'alt' => '{{wp_post_thumbnail_url}}',
                                'width' => 'auto',
                                'url' => 'https://placehold.co/600x400?text=Thumbnail',
                            ],
                        ],
						[
							'core/paragraph', ['content' => '{{wp_post_excerpt}}']
						]
					]
				];

			case 'title':
				return [
					'core/column',
					[],
					[
						[
							'core/paragraph', ['content' => '{{wp_post_title}}' ]
						]
					]
				];

			case 'link':
				return [
					'core/column',
					[],
					[
						[
							'core/paragraph', ['content' => '{{wp_post_title:link}}']
						]
					]
				];

			case 'title-image':
				return [
					'core/column',
					[],
					[
						[
                            'core/image', [
                                'alt' => '{{wp_post_thumbnail_url}}',
                                'width' => 'auto',
                                'url' => 'https://placehold.co/600x400?text=Thumbnail',
                            ],
						],
						[
							'core/heading', [
								'level' => 3,
								'content' => '{{wp_post_title:link}}'
							]
						],
					]
				];

			default:
				return [];
		}
	}

	/**
	 * @param $templateType
	 *
	 * @return array
	 */
	private function generateTermsTemplate($templateType)
	{
		switch ($templateType){

			case 'title':
				return [
					'core/column',
					[],
					[
						[
							'core/paragraph', ['content' => '{{term_name}}' ]
						]
					]
				];

			case 'link':
				return [
					'core/column',
					[],
					[
						[
							'core/paragraph', ['content' => '{{term_names:link}}' ]
						]
					]
				];

			default:
				return [];
		}
	}

	/**
	 * @param $templateType
	 *
	 * @return array
	 */
	private function generateSingleUserTemplate($templateType)
	{
		switch ($templateType){

			case 'avatar':
				return [
					'core/column',
					[],
					[
						[
							'core/paragraph', ['content' => '{{wp_user_avatar}}' ]
						]
					]
				];

			case 'avatar-name':
				return [
					'core/column',
					[],
					[
						[
							'core/paragraph', ['content' => '{{wp_user_avatar}} <span>{{wp_user_name}}</span>' ]
						]
					]
				];

			case 'avatar-name-bio':
				return [
					'core/column',
					[],
					[
						[
							'core/heading', [
								'level' => 3,
								'content' => '{{wp_user_avatar}} <span>{{wp_user_name}}</span>'
							]
						],
						[
							'core/paragraph', ['content' => '{{wp_user_bio}}' ]
						]
					]
				];

			case 'name-bio':
				return [
					'core/column',
					[],
					[
						[
							'core/heading', [
								'level' => 3,
								'content' => '{{wp_user_name}}'
							]
						],
						[
							'core/paragraph', ['content' => '{{wp_user_bio}}' ]
						]
					]
				];

			case 'name':
				return [
					'core/column',
					[],
					[
						[
							'core/paragraph', ['content' => '{{wp_user_name}}' ]
						]
					]
				];

			default:
				return [];
		}
	}

	/**
	 * @param $templates
	 * @param int $numberOfColumns
	 *
	 * @return array
	 */
	private function generateTemplate($templates, $numberOfColumns = 3)
	{
		$columns = [];
		$templates = array_chunk($templates, $numberOfColumns);

		foreach ($templates as $templateChunk){
			$columns[] = [
				'core/columns',
				[],
				$templateChunk
			];
		}

		return $columns;
	}

	/**
	 * @return array
	 */
	private function allowedBlocks()
	{
		return [
			'core/archives',
			'core/audio',
			'core/button',
			'core/buttons',
			'core/categories',
			'core/code',
			'core/column',
			'core/columns',
			'core/coverImage',
			'core/embed',
			'core/file',
			'core/group',
			'core/freeform',
			'core/paragraph',
			'core/image',
			'core/heading',
			'core/gallery',
			'core/list',
			'core/quote',
			'core/shortcode',
			'core/archives',
			'core/audio',
			'core/button',
			'core/buttons',
			'core/calendar',
			'core/categories',
			'core/code',
			'core/columns',
			'core/column',
			'core/cover',
			'core/embed',
			'core/file',
			'core/group',
			'core/freeform',
			'core/html',
			'core/media-text',
			'core/latest-comments',
			'core/latest-posts',
			'core/missing',
			'core/more',
			'core/nextpage',
			'core/page-list',
			'core/preformatted',
			'core/pullquote',
			'core/rss',
			'core/search',
			'core/separator',
			'core/block',
			'core/social-links',
			'core/social-link',
			'core/spacer',
			'core/table',
			'core/tag-cloud',
			'core/text-columns',
			'core/verse',
			'core/video',
			'core/site-logo',
			'core/site-tagline',
			'core/site-title',
			'core/query',
			'core/post-template',
			'core/query-title',
			'core/query-pagination',
			'core/query-pagination-next',
			'core/query-pagination-numbers',
			'core/query-pagination-previous',
			'core/post-title',
			'core/post-content',
			'core/post-date',
			'core/post-excerpt',
			'core/post-featured-image',
			'core/post-terms',
			'core/loginout'
		];
	}
}