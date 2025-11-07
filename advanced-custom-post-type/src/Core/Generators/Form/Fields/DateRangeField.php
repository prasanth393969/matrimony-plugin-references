<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\Translator;

class DateRangeField extends DateField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
        if($this->isNested and $this->fieldModel->getMetaField() !== null){
            $min = $this->fieldModel->getMetaField()->getAdvancedOption("min") ?? null;
            $max = $this->fieldModel->getMetaField()->getAdvancedOption("max") ?? null;
        } else {
            $min = (!empty($this->fieldModel->getExtra()['minDate'])) ? Strings::esc_attr($this->fieldModel->getExtra()['minDate']) : null;
            $max = (!empty($this->fieldModel->getExtra()['maxDate'])) ? Strings::esc_attr($this->fieldModel->getExtra()['maxDate']) : null;
        }

        $format = $this->defaultDateFormat();
        $defaultValue = Strings::esc_attr($this->defaultDateInterval($format));

		$field = "
		    <input type='hidden' name='". Strings::esc_attr($this->getIdName("format"))."' value='".$format."'>
			<input
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				placeholder='".$this->placeholder()."'
				value='".$defaultValue."'
				type='text'
				data-format='".Date::convertDateFormatForJS($format)."'
				class='acpt-daterangepicker ".$this->cssClass()."'
		";

		if($min){
			$field .= ' data-min-date="'.$min.'"';
		}

		if($max){
			$field .= ' data-max-date="'.$max.'"';
		}

		$field .= '/><a href="#" class="acpt-datepicker-clear">'.Translator::translate("Clear").'</a>';

		if($this->fieldModel->getMetaField() !== null){
			return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
		}

		return $field;
	}

    /**
     * @param $desiredFormat
     *
     * @return mixed|string|null
     */
    private function defaultDateInterval( $desiredFormat)
    {
        try {
            $defaultValue = $this->defaultValue();
            $format = $this->defaultExtraValue('format') ?? "Y-m-d";

            if(empty($defaultValue)){
                return null;
            }

            $defaultValue = explode(" - ", $defaultValue);

            if(count($defaultValue) !== 2){
                return null;
            }

            if(
                is_array($defaultValue) and
                isset($defaultValue[0]) and
                isset($defaultValue[1])
            ){
                $from = \DateTime::createFromFormat($format, $defaultValue[0]);
                $to = \DateTime::createFromFormat($format, $defaultValue[1]);

                if(!$from instanceof \DateTime){
                    return null;
                }

                if(!$to instanceof \DateTime){
                    return null;
                }

                return $from->format($desiredFormat) . " - " . $to->format($desiredFormat);
            }

            return $defaultValue;
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return null;
        }
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() 
	{
	    wp_enqueue_script( 'momentjs', plugins_url( 'advanced-custom-post-type/assets/vendor/moment/moment.min.js'), [], '2.18.1', true);
	    wp_enqueue_script( 'daterangepicker-js', plugins_url( 'advanced-custom-post-type/assets/vendor/daterangepicker/js/daterangepicker.min.js'), [], '3.1.0', true);
	    wp_enqueue_style( 'daterangepicker-css', plugins_url( 'advanced-custom-post-type/assets/vendor/daterangepicker/css/daterangepicker.min.css'), [], '3.1.0', 'all');
	}
}
