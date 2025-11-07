<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class TextareaField extends AbstractField
{
    /**
	 * @inheritDoc
	 */
	public function render()
	{
        if($this->isNested and $this->fieldModel->getMetaField() !== null){
            $wysiwyg = $this->fieldModel->getMetaField()->getType() === MetaFieldModel::EDITOR_TYPE;
            $min = $this->fieldModel->getMetaField()->getAdvancedOption("min") ?? null;
            $max = $this->fieldModel->getMetaField()->getAdvancedOption("max") ?? null;
            $rows =  $this->fieldModel->getMetaField()->getAdvancedOption("rows") ?? 6;
            $cols =  $this->fieldModel->getMetaField()->getAdvancedOption("cols") ?? 30;
        } else {
            $wysiwyg = $this->isWysiwyg();
            $min = (!empty($this->fieldModel->getExtra()['min'])) ? Strings::esc_attr($this->fieldModel->getExtra()['min']) : null;
            $max = (!empty($this->fieldModel->getExtra()['max'])) ? Strings::esc_attr($this->fieldModel->getExtra()['max']) : null;
            $rows = (!empty($this->fieldModel->getExtra()['rows'])) ? $this->fieldModel->getExtra()['rows'] : 6;
            $cols = (!empty($this->fieldModel->getExtra()['cols'])) ? $this->fieldModel->getExtra()['cols'] : 30;
        }

		$defaultValue = $this->defaultValue();

		// render Quill editor
		if($wysiwyg){

		    $id = Strings::generateRandomId();

			return '
				<input 
					type="text" 
					style="display: none;"
					value="'.Strings::htmlspecialchars($defaultValue).'" 
					name="'.Strings::esc_attr($this->getIdName()).'"
					id="'.Strings::esc_attr($id).'_hidden"
				/>
				<div data-rows="'.$rows.'" data-cols="'.$cols.'" data-min="'.$min.'" data-max="'.$max.'" class="acpt-quill" id="'.Strings::esc_attr($id).'">
					'.$defaultValue.'
				</div>
			'.$this->characterCounter($defaultValue, $max, $min);
		}

		$field = "
			<textarea
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				placeholder='".$this->placeholder()."'
				class='acpt-textarea ".Strings::esc_attr($this->cssClass())."'
				rows='".$rows."'
				cols='".$cols."'
				".$this->required()."
				".$this->appendMaxLengthAndMinLength($max, $min)."
				".$this->appendDataValidateAndConditionalRenderingAttributes()."
			>".Strings::htmlspecialchars($defaultValue)."</textarea>";

        $field .= $this->characterCounter($defaultValue, $max, $min);

		if($this->fieldModel->getMetaField() !== null){
			return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
		}

		return $field;
	}

    /**
     * @param null $value
     * @param null $max
     * @param null $min
     * @return string|null
     */
    private function characterCounter($value = null, $max = null, $min = null)
    {
        if(empty($max) and empty($min)){
            return null;
        }

        $strLen = (!empty($value)) ? strlen($value) : 0;
        $css = '';

        if((!empty($max) and $strLen >= $max) or $strLen < $min){
            $css = 'danger';
        } elseif(!empty($max) and $strLen >= $max-5){
            $css = 'warning';
        }

        return '<div data-min="'.$min.'" data-max="'.$max.'" class="acpt-textarea-ch-counter"><span class="count '.$css.'">'.(!empty($value and is_string($value)) ? $strLen : 0).'</span>/<span class="max">'.($max ? $max : "∞").'</span></div>';
    }

    /**
     * @return bool
     */
    protected function isWysiwyg()
    {
        return (!empty($this->fieldModel->getExtra()['wysiwyg'])) ? $this->fieldModel->getExtra()['wysiwyg'] : false;
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets()
	{
		if($this->isWysiwyg()){
			wp_enqueue_script( 'quill-js', plugins_url( 'advanced-custom-post-type/assets/vendor/quill/quill.min.js'), [], '3.1.0', true);
			wp_enqueue_style( 'quill-css', plugins_url( 'advanced-custom-post-type/assets/vendor/quill/quill.snow.css'), [], '3.1.0', 'all');
		}
	}
}
