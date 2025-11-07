<?php

namespace ACPT\Integrations\ElementorPro\Tags;

use ACPT\Utils\Wordpress\WPAttachment;
use Elementor\Modules\DynamicTags\Module;

class ACPTGalleryTag extends ACPTAbstractDataTag
{
	/**
	 * @inheritDoc
	 */
	public function get_categories()
	{
		return [
			Module::GALLERY_CATEGORY,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_name()
	{
		return 'acpt-gallery';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return esc_html__( "ACPT Gallery field", ACPT_PLUGIN_NAME );
	}

	public function get_value( array $options = array() )
	{
		$field = $this->extractField();
		$gallery = [];

		if(!empty($field)){
            $rawData = $this->getRawData();
            $value = $rawData['value'];

            if(empty($value)){
                return $gallery;
            }

            if(!is_array($value)){
                return $gallery;
            }

            $sort = $field['sort'] ?? 'asc';

            if($sort === 'desc'){
                $value = array_reverse($value);
            }

            if($sort === 'rand'){
                shuffle($value);
            }

            /** @var WPAttachment $image */
            foreach ($value as $image) {
                if($image instanceof WPAttachment and !$image->isEmpty()){
                    $gallery[] = [
                        'id' => $image->getId(),
                        'url' => $image->getSrc(),
                    ];
                }
            }

            return $gallery;
        }

        return $gallery;
	}
}
