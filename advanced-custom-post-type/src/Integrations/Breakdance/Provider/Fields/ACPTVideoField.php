<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use Breakdance\DynamicData\OembedData;
use Breakdance\DynamicData\StringData;

class ACPTVideoField extends ACPTOembedField
{
	/**
	 * @param mixed $attributes
	 *
	 * @return StringData
	 * @throws \Exception
	 */
	public function handler($attributes): OembedData
	{
		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		$oembed = new OembedData();

		if(empty($value)){
			return $oembed;
		}

		$attachment = wp_prepare_attachment_for_js($value);

		if(!empty($attachment)){
			$oembed->embedUrl = $attachment['url'];
			$oembed->format = $attachment['subtype'] ?? null;
			$oembed->type = $attachment['type'] ?? null;;
			$oembed->url = $attachment['url'];
		}

		return $oembed;
	}
}
