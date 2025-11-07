<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\WPAttachment;

class GalleryField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$attachmentIds = [];
		foreach ($this->getAttachments() as $index => $attachment){
			$attachmentIds[] = $attachment->getId();
		}

		$this->enqueueAssets();

		if($this->isChild() or $this->isNestedInABlock()){
			$field = $this->renderGalleryInRepeater($attachmentIds);
		} else {
			$field = $this->renderGallery($attachmentIds);
		}

		return $this->renderField($field);
	}

	/**
	 * @param $attachmentIds
	 *
	 * @return string
	 */
	private function renderGallery($attachmentIds)
	{
		$attachmentIdsValue = (is_array($attachmentIds)) ? implode(',', $attachmentIds) : '';
		$deleteButtonClass = ($this->getDefaultValue() !== '' or $this->getDefaultValue() !== null) ? '' : 'hidden';
		$defaultValue = $this->defaultValue();

		$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::GALLERY_TYPE.'">';
		$field .= '<div class="file-upload-wrapper" style="width: 100%">';
        $field .= '<div class="gallery-preview image-preview" data-target="'. Strings::esc_attr($this->getIdName()).'">'. $this->getGalleryPreview($attachmentIds) .'</div>';
        $field .= '<div class="btn-wrapper">';
		$field .= '<input id="'.Strings::esc_attr($this->getIdName()).'_attachment_id" name="'. esc_html($this->getIdName()).'_attachment_id" type="hidden" value="' .$attachmentIdsValue.'">';
		$field .= '<input readonly '.$this->required().' id="'. Strings::esc_attr($this->getIdName()).'_copy" type="text" class="hidden" value="'. $defaultValue .'" '.$this->appendDataValidateAndLogicAttributes().'>';
		$field .= '<div class="inputs-wrapper" data-target="'. Strings::esc_attr($this->getIdName()).'">';

		if(is_array($this->getDefaultValue())){
			foreach ($this->getDefaultValue() as $index => $value){
				$field .= '<input name="'. Strings::esc_attr($this->getIdName()).'[]" data-index="'.$index.'" type="hidden" value="'.$value.'">';
			}
		}

		$field .= '</div>';
		$field .= '<a class="upload-gallery-btn button-primary button">'.Translator::translate("Select images").'</a>';
		$field .= '<a data-target-id="'.Strings::esc_attr($this->getIdName()).'" class="upload-delete-btn button button-danger '.Strings::esc_attr($deleteButtonClass).'">'.Translator::translate("Delete all images").'</a>';
		$field .= '</div>';
		$field .= '</div>';

		return $field;
	}

	/**
	 * @return string|void
	 */
	private function defaultValue()
	{
		if(empty($this->getDefaultValue()) or !is_array($this->getDefaultValue())){
			return '';
		}

		return ( !empty($this->getDefaultValue()) and is_array($this->getDefaultValue()) ) ? Strings::esc_attr(implode(',', $this->getDefaultValue())) : '';
	}

	/**
	 * @param $attachmentIds
	 *
	 * @return string
	 */
	private function renderGalleryInRepeater($attachmentIds)
	{
		$id = "gallery_".Strings::generateRandomId();
		$attachmentIdsValue = (is_array($attachmentIds)) ? implode(',', $attachmentIds) : '';
		$deleteButtonClass = (!empty($this->getDefaultValue())) ? '' : 'hidden';
		$defaultValue = (!empty($this->getDefaultValue()) and is_array($this->getDefaultValue()) ) ? Strings::esc_attr(implode(',', $this->getDefaultValue())) : '';

		$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::GALLERY_TYPE.'">';
		$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
        $field .= '<div class="file-upload-wrapper" style="width: 100%">';
        $field .= '<div class="gallery-preview image-preview" data-target="'. $id .'">'. $this->getGalleryPreview($attachmentIds) .'</div>';
		$field .= '<div class="btn-wrapper">';
		$field .= '<input id="'.$id.'[attachment_id]['.$this->getIndex().']" type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[attachment_id]" value="' .$attachmentIdsValue.'">';
		$field .= '<input readonly '.$this->required().' id="'. $id.'_copy" type="text" class="hidden" value="'. $defaultValue .'" '.$this->appendDataValidateAndLogicAttributes().'>';
		$field .= '<div class="inputs-wrapper" data-target="'. $id.'" data-target-copy="'.Strings::esc_attr($this->getIdName()).'[value]">';

		if(is_array($this->getDefaultValue())){
			foreach ($this->getDefaultValue() as $index => $value){
				$field .= '<input name="'. Strings::esc_attr($this->getIdName()).'[value][]" data-index="'.$index.'" type="hidden" value="'.$value.'">';
			}
		}

		$field .= '</div>';
		$field .= '<a data-parent-index="'.$this->getIndex().'" class="upload-gallery-btn button-primary button">'.Translator::translate("Select images").'</a>';
		$field .= '<a data-target-id="'.$id.'" class="upload-delete-btn button button-danger '.Strings::esc_attr($deleteButtonClass).'">'.Translator::translate("Delete all images").'</a>';
		$field .= '</div>';
		$field .= '</div>';

		return $field;
	}

    /**
     * @param array $attachmentIds
     *
     * @return string
     */
	private function getGalleryPreview($attachmentIds = [])
	{
	    // from attachment IDs
	    if(!empty($attachmentIds)){

            $preview = '';

            foreach ($attachmentIds as $index => $attachmentId){
                if(is_numeric($attachmentId)){
                    $attachment = WPAttachment::fromId($attachmentId);

                    if(!$attachment->isEmpty()){
                        $preview .= '
                            <div class="image" data-index="'.$index.'" draggable="true">
                                '.$attachment->render().'
                                <a class="delete-gallery-img-btn" data-parent-index="'.$this->getIndex().'" data-index="'.$index.'" href="#" title="'.Translator::translate("Delete").'">x</a>
                            </div>
                        ';
                    }
                }
            }

            return $preview;
        }

	    // from saved values (URLs)
		$defaultGallery = $this->getDefaultValue();

		if($defaultGallery === ''){
			return 'No image selected';
		}

		if(empty($defaultGallery)){
			return 'No image selected';
		}

		if(!is_array($defaultGallery)){
			return 'No image selected';
		}

		$preview = '';

		foreach ($defaultGallery as $index => $image){
		    if(is_string($image)){
                $attachment = WPAttachment::fromUrl($image);

                if(!$attachment->isEmpty()){
                    $preview .= '
                        <div class="image" data-index="'.$index.'" draggable="true">
                            '.$attachment->render().'
                            <a class="delete-gallery-img-btn" data-parent-index="'.$this->getIndex().'" data-index="'.$index.'" href="#" title="'.Translator::translate("Delete").'">x</a>
                        </div>
                    ';
                }
            }
		}

		return $preview;
	}

	/**
	 * Enqueue necessary assets
	 */
	private function enqueueAssets()
	{
		wp_enqueue_script( 'html5sortable', plugins_url( 'advanced-custom-post-type/assets/vendor/html5sortable/dist/html5sortable.min.js'), ['jquery'], '2.2.0', true);
	}
}