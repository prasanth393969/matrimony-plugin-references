<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\Translator;

class TimeField extends AbstractField
{
	public function render()
	{
        $this->enqueueAssets();
        $min = $this->getAdvancedOption('min') ?? null;
        $max = $this->getAdvancedOption('max') ?? null;
        $timeFormat = $this->getAdvancedOption('time_format') ?? get_option('time_format') ?? 'H:i:s';
        $defaultValue = Strings::esc_attr($this->defaultDate($timeFormat));
        $cssClass = 'acpt-timepicker acpt-admin-meta-field-input';

		if($this->hasErrors()){
			$cssClass .= ' has-errors';
		}

		if($this->isChild() or $this->isNestedInABlock()){

			if($this->isLeadingField()){
				$cssClass .= ' acpt-leading-field';
			}

			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::TIME_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[format]" value="'.$timeFormat.'">';
			$time = '<input autocomplete="off" '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value]" value="'.$defaultValue.'" type="text" class="'.$cssClass.'"';

		} else {
			$field = '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_type" value="' . MetaFieldModel::TIME_TYPE . '">';
			$field .= '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_format" value="' . $timeFormat . '">';
			$time = '<input autocomplete="off" ' . $this->required() . ' id="' . Strings::esc_attr( $this->getIdName() ) . '" name="' . Strings::esc_attr( $this->getIdName() ) . '" type="text" class="'.$cssClass.'" value="' . $defaultValue . '"';
		}

		if($min){
			$time .= ' data-min-date="'.$min.'"';
		}

		if($max){
			$time .= ' data-max-date="'.$max.'"';
		}

        $time .= ' data-format="'.Date::convertTimeFormatForJS($timeFormat).'"';
        $time .= $this->appendDataValidateAndLogicAttributes();
        $time .= '>';

		$field .= (new AfterAndBeforeFieldGenerator())->generate($this->metaField, $time);
        $field .= '<a href="#" data-target-id="'.Strings::esc_attr($this->getIdName()).'" class="acpt-datepicker-clear">'.Translator::translate("Clear").'</a>';

		return $this->renderField($field);
	}

    /**
     * @param $desiredFormat
     *
     * @return string|null
     */
    private function defaultDate( $desiredFormat)
    {
        try {
            $defaultValue = $this->getDefaultValue();
            $format = $this->getDefaultAttributeValue('format', null) ?? "H:i:s";

            if(empty($defaultValue)){
                return null;
            }

            $date = \DateTime::createFromFormat($format, $defaultValue);

            if(!$date instanceof \DateTime){
                $date = \DateTime::createFromFormat("H:i:s", $defaultValue);
            }

            if(!$date instanceof \DateTime){
                return null;
            }

            return $date->format($desiredFormat);
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
