<?php

namespace ACPT\Integrations\ElementorPro\Tags;

use ACPT\Utils\Wordpress\WPAttachment;
use Elementor\Modules\DynamicTags\Module;

class ACPTMediaTag extends ACPTAbstractDataTag
{
	/**
	 * @inheritDoc
	 */
	public function get_categories()
	{
		return [
			Module::MEDIA_CATEGORY,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_name()
	{
		return 'acpt-media';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return esc_html__( "ACPT media field", ACPT_PLUGIN_NAME );
	}

	public function get_value( array $options = array() )
	{
		$field = $this->extractField();

		if(!empty($field)){
            $rawData = $this->getRawData();

            $after = $rawData['after'];
            $before = $rawData['before'];
            $value = $rawData['value'];

            if(empty($value)){
                return $this->emptyFile();
            }

            if($value instanceof WPAttachment){
                return [
                    'id' => $value->getId(),
                    'url' => $value->getSrc(),
                ];
            }

            return $this->emptyFile();
        }

		return $this->emptyFile();
	}

	/**
	 * @return array
	 */
	private function emptyFile()
	{
		return [
			'id' => 0,
			'url' => null,
		];
	}
}