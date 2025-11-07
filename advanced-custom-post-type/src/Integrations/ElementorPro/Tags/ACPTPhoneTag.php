<?php

namespace ACPT\Integrations\ElementorPro\Tags;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\Wordpress\Translator;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module;

class ACPTPhoneTag extends ACPTAbstractTag
{
	/**
	 * @inheritDoc
	 */
	public function get_categories()
	{
		return [
			Module::TEXT_CATEGORY,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_name()
	{
		return 'acpt-unit-of-measure';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return esc_html__( "ACPT phone field", ACPT_PLUGIN_NAME );
	}

	public function register_controls()
	{
		parent::register_controls();

		$this->add_control(
			'format',
			[
				'label' => Translator::translate( 'Phone format' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					Phone::FORMAT_E164 => Translator::translate(Phone::FORMAT_E164),
					Phone::FORMAT_INTERNATIONAL => Translator::translate(Phone::FORMAT_INTERNATIONAL),
					Phone::FORMAT_NATIONAL => Translator::translate(Phone::FORMAT_NATIONAL),
					Phone::FORMAT_RFC3966 => Translator::translate(Phone::FORMAT_RFC3966),
				],
			]
		);
	}

	public function render()
	{
		$render = '';
		$field = $this->extractField();

		if(!empty($field)){
            $rawData = $this->getRawData();

            $after = $rawData['after'];
            $before = $rawData['before'];
            $value = $rawData['value'];

            if(empty($value)){
                return $render;
            }

            $fieldType = $field['fieldType'];

            switch ($fieldType){

                case MetaFieldModel::PHONE_TYPE:

                    $format = (!empty($this->get_settings('format'))) ? $this->get_settings('format') : Phone::FORMAT_E164;
                    $val = $value['value'] ?? null;
                    $dial = $value['dial'] ?? null;

                    if(is_scalar($val)){
                        $render .= Phone::format($val, $dial, $format);
                    }

                    break;
            }
		}

		echo $render;
	}
}
