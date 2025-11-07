<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\WPAttachment;

class ImageSliderField extends AbstractField
{
    public function render()
    {
        $attachments = $this->getAttachments();
        $attachmentIds = [];

        foreach ($attachments as $index => $attachment){
            $attachmentIds[] = $attachment->getId();
        }

        if($this->isChild() or $this->isNestedInABlock()){
            $field = $this->renderSliderInRepeater($attachments, $attachmentIds);
        } else {
            $field = $this->renderSlider($attachments, $attachmentIds);
        }

        return $this->renderField($field);
    }

    /**
     * @param WPAttachment[] $attachments
     * @param                $attachmentIds
     *
     * @return string
     */
    private function renderSlider($attachments, $attachmentIds)
    {
        $id = Strings::esc_attr($this->getIdName());
        $attachmentIdsValue = (is_array($attachmentIds)) ? implode(',', $attachmentIds) : '';
        $deleteButtonClass = empty($this->getDefaultValue()) ? 'hidden' : '';
        $defaultValue = $this->getDefaultValue();
        $defaultUrlValues = (is_array($defaultValue)) ? implode(',', $defaultValue) : '';

        $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::IMAGE_SLIDER_TYPE.'">';
        $field .= '<input id="'.Strings::esc_attr($this->getIdName()).'_attachment_id" name="'. esc_html($this->getIdName()).'_attachment_id" type="hidden" value="' .$attachmentIdsValue.'">';
        $field .= '<input readonly '.$this->required().' id="'. $id.'_copy" type="text" class="hidden" value="'. $defaultUrlValues .'" '.$this->appendDataValidateAndLogicAttributes().'>';

        $field .= '<div class="image-slider-preview" style="width: 100%">'. $this->getSliderPreview($id, $attachments) .'</div>';
        $field .= '<div class="image-slider-wrapper" style="width: 100%">';

        $field .= $this->imageUpload($id, 0, "Left image", $deleteButtonClass, $this->getIdName(), $defaultValue[0] ?? null);
        $field .= $this->imageUpload($id, 1, "Right image", $deleteButtonClass, $this->getIdName(), $defaultValue[1] ?? null);

        $field .= '</div>';

        return $field;
    }

    /**
     * @param $id
     * @param WPAttachment[] $attachments
     *
     * @return string
     */
    private function getSliderPreview($id, $attachments)
    {
        $defaultPercent = $this->getAdvancedOption('default_percent') ?? 50;
        $empty = "<div class='acpt-image-slider' id='".$id."_slider' data-default-percent='".$defaultPercent."'>
                <span class='placeholder'>Upload the left and the right images to see the slider</span>
        </div>";

        if(empty($attachments)){
            return $empty;
        }

        if(!is_array($attachments)){
            return $empty;
        }

        if(count($attachments) !== 2){
            return $empty;
        }

        $slider = "<div class='acpt-image-slider' id='".$id."_slider' style='--position: ".$defaultPercent."%'>";
        $slider .= '<div class="image-container">';
        $slider .= '<img
            class="image-before slider-image"
            src="'.$attachments[0]->getSrc().'"
            alt="'.$attachments[0]->getAlt().'"
        />';
        $slider .= '<img
            class="image-after slider-image"
            src="'.$attachments[1]->getSrc().'"
            alt="'.$attachments[1]->getAlt().'"
          />';
        $slider .= "</div>";
        $slider .= '<input type="range" min="0" max="100" value="'.$defaultPercent.'" class="slider">';
        $slider .= '<div class="slider-line" aria-hidden="true"></div>';
        $slider .= '<div class="slider-button" aria-hidden="true">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="30"
            height="30"
            fill="currentColor"
            viewBox="0 0 256 256"
          >
            <rect width="256" height="256" fill="none"></rect>
            <line
              x1="128"
              y1="40"
              x2="128"
              y2="216"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="16"
            ></line>
            <line
              x1="96"
              y1="128"
              x2="16"
              y2="128"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="16"
            ></line>
            <polyline
              points="48 160 16 128 48 96"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="16"
            ></polyline>
            <line
              x1="160"
              y1="128"
              x2="240"
              y2="128"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="16"
            ></line>
            <polyline
              points="208 96 240 128 208 160"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="16"
            ></polyline>
          </svg>
        </div>';
        $slider .= "</div>";

        return $slider;
    }

    /**
     * @param $id
     * @param $index
     * @param $label
     * @param $deleteButtonClass
     * @param $baseName
     * @param $defaultValue
     *
     * @return string
     */
    private function imageUpload( $id, $index, $label, $deleteButtonClass, $baseName, $defaultValue = null)
    {
        $field  = '<div class="file-upload-wrapper">';
        $field .= '<div class="image-preview"><div class="image">'. $this->imagePreview($index, $label) .'</div></div>';
        $field .= '<div class="btn-wrapper">';
        $field .= '<input name="'. Strings::esc_attr($baseName).'['.$index.']" data-index="'.$index.'" type="hidden" value="'.$defaultValue.'">';
        $field .= '<a data-parent-index="'.$this->getIndex().'" data-index="'.$index.'" data-target-id="'.$id.'" class="upload-image-slider-btn button button-primary">'.Translator::translate("Upload").'</a>';
        $field .= '<button data-parent-index="'.$this->getIndex().'" data-index="'.$index.'" data-target-id="'.$id.'" class="upload-delete-slider-btn button button-secondary '.$deleteButtonClass.'">'.Translator::translate("Delete").'</button>';
        $field .= '</div>';
        $field .= '</div>';

        return $field;
    }

    /**
     * @param $index
     * @param $label
     *
     * @return string
     */
    private function imagePreview($index, $label)
    {
        if(!empty($this->getDefaultValue()) and is_array($this->getDefaultValue())){
            $url = $this->getDefaultValue()[$index] ?? null;

            if(!empty($url)){
                $attachment = WPAttachment::fromUrl($url);

                return $attachment->render();
            }
        }

        return '<span class="placeholder">'.Translator::translate($label).'</span>';
    }

    /**
     * @param WPAttachment[] $attachments
     * @param                $attachmentIds
     *
     * @return string
     */
    private function renderSliderInRepeater($attachments, $attachmentIds)
    {
        $id = "image_slider_".Strings::generateRandomId();
        $deleteButtonClass = empty($this->getDefaultValue()) ? 'hidden' : '';
        $defaultValue = $this->getDefaultValue();
        $defaultUrlValues = (is_array($defaultValue)) ? implode(',', $defaultValue) : '';
        $attachmentIdsValue = (is_array($attachmentIds)) ? implode(',', $attachmentIds) : '';

        $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::IMAGE_SLIDER_TYPE.'">';
        $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
        $field .= '<input id="'.$id.'[attachment_id]['.$this->getIndex().']" name="'. esc_html($this->getIdName()).'[attachment_id]" type="hidden" value="' .$attachmentIdsValue.'">';
        $field .= '<input readonly '.$this->required().' id="'. $id.'_copy" type="text" class="hidden" value="'. $defaultUrlValues .'" '.$this->appendDataValidateAndLogicAttributes().'>';

        $field .= '<div class="image-slider-preview" style="width: 100%">'. $this->getSliderPreview($id, $attachments) .'</div>';
        $field .= '<div class="image-slider-wrapper" style="width: 100%">';

        $field .= $this->imageUpload($id, 0, "Left image", $deleteButtonClass, $this->getIdName().'[value]', $defaultValue[0] ?? null);
        $field .= $this->imageUpload($id, 1, "Right image", $deleteButtonClass, $this->getIdName().'[value]', $defaultValue[1] ?? null);

        $field .= '</div>';

        return $field;
    }
}