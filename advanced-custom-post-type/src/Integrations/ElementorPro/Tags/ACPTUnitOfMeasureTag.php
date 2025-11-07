<?php

namespace ACPT\Integrations\ElementorPro\Tags;

use ACPT\Core\Helper\Currencies;
use ACPT\Core\Helper\Lengths;
use ACPT\Core\Helper\Weights;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module;

class ACPTUnitOfMeasureTag extends ACPTAbstractTag
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
		return esc_html__( "ACPT unit of measure field", ACPT_PLUGIN_NAME );
	}

	public function register_controls()
	{
		parent::register_controls();

		$this->add_control(
			'render',
			[
				'label' => Translator::translate( 'Render as' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'both' => Translator::translate('Value and UOM'),
					'value' => Translator::translate('Only value'),
					'uom' => Translator::translate('Only UOM'),
				],
			]
		);

		$this->add_control(
			'value_format_decimal_points',
			[
				'label' => Translator::translate( 'Value format (Decimal points)' ),
				'type' => Controls_Manager::TEXT,
				'condition' => [
					'render' => [
						'both',
						'value',
					],
				],
			]
		);

		$this->add_control(
			'value_format_decimal_separator',
			[
				'label' => Translator::translate( 'Value format (Decimal separator)' ),
				'type' => Controls_Manager::TEXT,
				'condition' => [
					'render' => [
						'both',
						'value',
					],
				],
			]
		);

		$this->add_control(
			'value_format_thousands_separator',
			[
				'label' => Translator::translate( 'Value format (Thousands separator)' ),
				'type' => Controls_Manager::TEXT,
				'condition' => [
					'render' => [
						'both',
						'value',
					],
				],
			]
		);

		$this->add_control(
			'uom_format',
			[
				'label' => Translator::translate( 'UOM format' ),
				'type' => Controls_Manager::SELECT,
				'condition' => [
					'render' => [
						'both',
						'value',
					],
				],
				'options' => [
					'full' => Translator::translate('Full UOM name'),
					'abbreviation' => Translator::translate('Abbreviation'),
				],
			]
		);

		$this->add_control(
			'uom_position',
			[
				'label' => Translator::translate( 'UOM position' ),
				'type' => Controls_Manager::SELECT,
				'condition' => [
					'render' => [
						'both',
						'value',
					],
				],
				'options' => [
					'after' => Translator::translate('After value'),
					'before' => Translator::translate('Before value'),
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

            $fieldType = $field['fieldType'];

            switch ($fieldType){

                case MetaFieldModel::CURRENCY_TYPE:
                    if(is_array($value)){
                        $render .= $this->renderUom($value['amount'], $value['unit'], 'currency', $before, $after);
                    }

                    break;

                case MetaFieldModel::LENGTH_TYPE:
                    if(is_array($value)){
                        $render .= $this->renderUom($value['length'], $value['unit'], 'length', $before, $after);
                    }

                    break;

                case MetaFieldModel::WEIGHT_TYPE:
                    if(is_array($value)){
                        $render .= $this->renderUom($value['weight'], $value['unit'], 'weight', $before, $after);
                    }

                    break;

                default:
                    $render .= $value;
                    break;
            }
		}

		echo $render;
	}

    /**
     * @param      $value
     * @param      $unit
     * @param      $type
     * @param null $before
     * @param null $after
     *
     * @return mixed
     */
	private function renderUom($value, $unit, $type, $before = null, $after = null)
	{
		$render = (!empty($this->get_settings('render'))) ? $this->get_settings('render') : null;
		$decimalPoints = (!empty($this->get_settings('value_format_decimal_points'))) ? $this->get_settings('value_format_decimal_points') : 0;
		$decimalSeparator = (!empty($this->get_settings('value_format_decimal_separator'))) ? $this->get_settings('value_format_decimal_separator') : ".";
		$thousandsSeparator = (!empty($this->get_settings('value_format_thousands_separator'))) ? $this->get_settings('value_format_thousands_separator') : ",";
		$uomFormat = (!empty($this->get_settings('uom_format'))) ? $this->get_settings('uom_format') : "full";
		$uomPosition = (!empty($this->get_settings('uom_position'))) ? $this->get_settings('uom_position') : "after";

		$value = number_format($value, (int)$decimalPoints, $decimalSeparator, $thousandsSeparator);

		if($uomFormat === 'abbreviation'){
			switch ($type){
				case 'currency':
					$unit = Currencies::getSymbol($unit);
					break;

				case 'length':
					$unit = Lengths::getSymbol($unit);
					break;

				case 'weight':
					$unit = Weights::getSymbol($unit);
					break;
			}
		}

		if($render === 'value'){
			if($value === null){
				return null;
			}

			return $before . $value . $after;
		}

		if($render === 'uom'){
			if($unit === null){
				return null;
			}

			return $unit;
		}

		if($uomPosition === 'before'){
			if($unit === null or $value === null){
				return null;
			}

			return $before . $unit . ' ' . $value . $after;
		}

		if($unit === null or $value === null){
			return null;
		}

		return $before . $value . ' ' . $unit . $after;
	}
}
