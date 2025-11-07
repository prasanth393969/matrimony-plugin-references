<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\Translator;

class DateField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
        if($this->isNested and $this->fieldModel->getMetaField() !== null){
            $min = $this->fieldModel->getMetaField()->getAdvancedOption("min") ?? null;
            $max = $this->fieldModel->getMetaField()->getAdvancedOption("max") ?? null;
            $format = $this->fieldModel->getMetaField()->getAdvancedOption("date_format") ?? get_option('date_format') ?? 'Y-m-d';
        } else {
            $min = (!empty($this->fieldModel->getExtra()['minDate'])) ? Strings::esc_attr($this->fieldModel->getExtra()['minDate']) : null;
            $max = (!empty($this->fieldModel->getExtra()['maxDate'])) ? Strings::esc_attr($this->fieldModel->getExtra()['maxDate']) : null;
            $format = (!empty($this->fieldModel->getExtra()['dateFormat'])) ? Strings::esc_attr($this->fieldModel->getExtra()['dateFormat']) : $this->fieldModel->getMetaField()->getAdvancedOption("date_format") ?? get_option('date_format') ?? 'Y-m-d';
        }

        $defaultValue = Strings::esc_attr($this->defaultDate($format));

		$field = "
		    <input type='hidden' name='". Strings::esc_attr($this->getIdName("format"))."' value='".$format."'>
			<input
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				placeholder='".$this->placeholder()."'
				value='".$defaultValue."'
				type='text'
				class='acpt-datepicker ".$this->cssClass()."'
				data-format='".Date::convertDateFormatForJS($format)."'
				data-min-date='".$min."'
				data-max-date='".$max."'
				".$this->required()."
				".$this->appendDataValidateAndConditionalRenderingAttributes()."
			/>
            <a href='#' class='acpt-datepicker-clear'>".Translator::translate("Clear")."</a>
			";

		if($this->fieldModel->getMetaField() !== null){
			return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
		}

		return $field;
	}

    /**
     * @param $desiredFormat
     *
     * @return string|null
     */
    private function defaultDate($desiredFormat)
    {
        try {
            $defaultValue = $this->defaultValue();
            $format = $this->defaultExtraValue('format') ?? "Y-m-d";

            if(empty($defaultValue)){
                return null;
            }

            $date = \DateTime::createFromFormat($format, $defaultValue);

            if(!$date instanceof \DateTime){
                return null;
            }

            return $date->format($desiredFormat);
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return null;
        }
    }

    /**
     * @return string
     */
    protected function defaultDateFormat()
    {
        if($this->isNested and $this->fieldModel->getMetaField() !== null){
            return $this->fieldModel->getMetaField()->getAdvancedOption("date_format") ?? get_option('date_format') ?? 'Y-m-d';
        }

        if(isset($this->fieldModel->getExtra()['dateFormat']) and !empty($this->fieldModel->getExtra()['dateFormat'])){
            return $this->fieldModel->getExtra()['dateFormat'];
        }

        if($this->fieldModel->getMetaField() !== null){
            return $this->fieldModel->getMetaField()->getAdvancedOption("date_format") ?? get_option('date_format') ?? 'Y-m-d';
        }

        return get_option('date_format') ?? 'Y-m-d';
    }

    /**
     * @return string
     */
    protected function defaultTimeFormat()
    {
        if($this->isNested and $this->fieldModel->getMetaField() !== null){
            return $this->fieldModel->getMetaField()->getAdvancedOption("time_format") ?? get_option('time_format') ?? 'H:i:s';
        }

        if(isset($this->fieldModel->getExtra()['timeFormat']) and !empty($this->fieldModel->getExtra()['timeFormat'])){
            return $this->fieldModel->getExtra()['timeFormat'];
        }

        if($this->fieldModel->getMetaField() !== null){
            return $this->fieldModel->getMetaField()->getAdvancedOption("time_format") ?? get_option('time_format') ?? 'H:i:s';
        }

        return get_option('date_format') ?? 'Y-m-d';
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets()
    {
        wp_enqueue_script( 'momentjs', plugins_url( 'advanced-custom-post-type/assets/vendor/moment/moment.min.js'), ['jquery'], '2.18.1', true);
        wp_enqueue_script( 'daterangepicker-js', plugins_url( 'advanced-custom-post-type/assets/vendor/daterangepicker/js/daterangepicker.min.js'), [], '3.1.0', true);
        wp_enqueue_style( 'daterangepicker-css', plugins_url( 'advanced-custom-post-type/assets/vendor/daterangepicker/css/daterangepicker.min.css'), [], '3.1.0', 'all');
	}
}
