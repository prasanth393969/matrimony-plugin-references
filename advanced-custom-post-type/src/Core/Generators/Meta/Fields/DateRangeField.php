<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\Translator;

class DateRangeField extends AbstractField
{
	public function render()
	{
		$this->enqueueAssets();
        $min = $this->getAdvancedOption('min') ?? null;
        $max = $this->getAdvancedOption('max') ?? null;
        $format = $this->getAdvancedOption('date_format') ?? get_option('date_format') ?? 'Y-m-d';
		$defaultValue = Strings::esc_attr($this->defaultDateInterval($format));

		if($this->isChild() or $this->isNestedInABlock()){
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::DATE_RANGE_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[format]" value="'.$format.'">';
			$dateRange = '<input autocomplete="off" '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value]" value="'.$defaultValue.'" type="text" class="acpt-daterangepicker regular-text acpt-admin-meta-field-input"';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::DATE_RANGE_TYPE.'">';
            $field .= '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_format" value="' . $format . '">';
			$dateRange = '<input autocomplete="off" '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'" name="'. Strings::esc_attr($this->getIdName()).'" type="text" class="acpt-daterangepicker acpt-admin-meta-field-input" value="'.$defaultValue.'"';
		}

		if($min){
			$dateRange .= ' data-min-date="'.$min.'"';
		}

		if($max){
			$dateRange .= ' data-max-date="'.$max.'"';
		}

        $dateRange .= ' data-format="'.Date::convertDateFormatForJS($format).'"';
		$dateRange .= '>';

		$field .= (new AfterAndBeforeFieldGenerator())->generate($this->metaField, $dateRange);
        $field .= '<a href="#" data-target-id="'.Strings::esc_attr($this->getIdName()).'" class="acpt-datepicker-clear">'.Translator::translate("Clear").'</a>';

		return $this->renderField($field);
	}

    /**
     * @param $desiredFormat
     *
     * @return mixed|string|null
     */
	private function defaultDateInterval( $desiredFormat)
	{
		try {
            $defaultValue = $this->getDefaultValue();
            $format = $this->getDefaultAttributeValue('format', null) ?? "Y-m-d";

            if(empty($defaultValue)){
                return null;
            }

            if(is_string($defaultValue)){
                $defaultValue = explode(" - ", $defaultValue);
            }

            if(!is_array($defaultValue)){
                return null;
            }

            if(count($defaultValue) !== 2){
                return null;
            }

            $fromString = $defaultValue['from'] ?? $defaultValue[0] ?? null;
            $toString = $defaultValue['to'] ?? $defaultValue[1] ?? null;

            if(empty($fromString) or empty($toString)){
                return null;
            }

            $from = \DateTime::createFromFormat($format, $fromString);
            $to = \DateTime::createFromFormat($format, $toString);

            if(!$from instanceof \DateTime){
                $from = \DateTime::createFromFormat("Y-m-d", $fromString);
            }

            if(!$to instanceof \DateTime){
                $to = \DateTime::createFromFormat("Y-m-d", $toString);
            }

            if(!$from instanceof \DateTime){
                return null;
            }

            if(!$to instanceof \DateTime){
                return null;
            }

            return $from->format($desiredFormat) . " - " . $to->format($desiredFormat);
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

		    return null;
        }
	}

	private function enqueueAssets()
	{
		wp_enqueue_script( 'momentjs', plugins_url( 'advanced-custom-post-type/assets/vendor/moment/moment.min.js'), [], '2.18.1', true);
		wp_enqueue_script( 'daterangepicker-js', plugins_url( 'advanced-custom-post-type/assets/vendor/daterangepicker/js/daterangepicker.min.js'), [], '3.1.0', true);
		wp_enqueue_style( 'daterangepicker-css', plugins_url( 'advanced-custom-post-type/assets/vendor/daterangepicker/css/daterangepicker.min.css'), [], '3.1.0', 'all');
	}
}