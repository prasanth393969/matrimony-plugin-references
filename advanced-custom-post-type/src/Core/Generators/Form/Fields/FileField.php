<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\WPAttachment;

class FileField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
	    $randomId = Strings::generateRandomId();
		$accept = (!empty($this->fieldModel->getExtra()['accept'])) ? $this->fieldModel->getExtra()['accept'] : $this->accept();
		$multiple = (!empty($this->fieldModel->getExtra()['multiple'])) ? $this->fieldModel->getExtra()['multiple'] : $this->multiple();
		$name = ($multiple) ? Strings::esc_attr($this->getIdName()).'[]' : Strings::esc_attr($this->getIdName());

		return "
			<input
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				data-target-id='".$randomId."'
				name='".$name."'
				placeholder='".$this->placeholder()."'
				type='file'
				".($multiple ? 'multiple' : '')."
				accept='".$accept."'
				class='acpt-file ".$this->cssClass()."'
				".$this->required()."
				".$this->appendDataValidateAndConditionalRenderingAttributes()."
			/>
			<input
			    id='".$randomId."'
				name='".$name."'
				type='hidden'
				value='".(is_array($this->defaultValue()) ? implode(",", $this->defaultValue()) : $this->defaultValue())."'
			/>
			" . $this->preview($randomId);
	}

    /**
     * @return bool
     */
    private function multiple()
    {
        if(!empty($this->fieldModel->getMetaField())){
            switch ($this->fieldModel->getMetaField()->getType()){
                case MetaFieldModel::AUDIO_MULTI_TYPE:
                case MetaFieldModel::GALLERY_TYPE:
                    return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    private function accept()
    {
        if(!empty($this->fieldModel->getMetaField())){
            switch ($this->fieldModel->getMetaField()->getType()){
                case MetaFieldModel::AUDIO_TYPE:
                case MetaFieldModel::AUDIO_MULTI_TYPE:
                    return 'audio/*';

                case MetaFieldModel::IMAGE_TYPE:
                case MetaFieldModel::GALLERY_TYPE:
                    return 'image/*';

                case MetaFieldModel::VIDEO_TYPE:
                    return 'video/*';
            }
        }

        return '*';
    }

    /**
     * @param $randomId
     * @return string|null
     */
	private function preview($randomId)
    {
        if(empty($this->defaultValue())){
            return null;
        }

        if(is_array($this->defaultValue())){
            $preview  = "<div class='acpt-file-preview'>";
            $preview  .= "<div class='gallery'>";

            foreach ($this->defaultValue() as $value){
                $wpAttachment = WPAttachment::fromUrl($value);

                if(!$wpAttachment->isEmpty()){
                    $preview  .= "<div class='file'>";
                    $preview  .= $wpAttachment->render([
                        'w' => 80,
                        'h' => 60,
                    ]);
                    $preview .= "<a data-value='".$value."' data-target='".$randomId."' class='acpt-delete-file' href='#'>Delete</a>";
                    $preview .= "</div>";
                }
            }

            $preview .= "</div>";
            $preview .= "</div>";

            return $preview;
        }

        if(is_string($this->defaultValue())){
            $wpAttachment = WPAttachment::fromUrl($this->defaultValue());

            if($wpAttachment->isEmpty()){
                return null;
            }

            $preview  = "<div class='acpt-file-preview'>";
            $preview  .= "<div class='file'>";
            $preview  .= $wpAttachment->render([
                'w' => 80,
                'h' => 60,
            ]);
            $preview .= "<a data-value='".$this->defaultValue()."' data-target='".$randomId."' class='acpt-delete-file' href='#'>Delete</a>";
            $preview .= "</div>";
            $preview .= "</div>";

            return $preview;
        }

        return null;
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
