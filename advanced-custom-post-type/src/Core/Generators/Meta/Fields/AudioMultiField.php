<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\WPAttachment;

class AudioMultiField extends AudioField
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

        if(empty($this->getAttachments()) and is_array($this->getValue())){
            foreach ($this->getValue() as $audioUrl){
                $attachment = WPAttachment::fromUrl($audioUrl);
                $attachmentIds[] = $attachment->getId();
            }
        }

        $this->enqueueAssets();

        if($this->isChild() or $this->isNestedInABlock()){
            $field = $this->renderPlaylistInRepeater($attachmentIds);
        } else {
            $field = $this->renderPlaylist($attachmentIds);
        }

        return $this->renderField($field);
    }

    /**
     * @param array $attachmentIds
     * @return string
     */
    public function renderPlaylist($attachmentIds = [])
    {
        $attachmentIdsValue = (is_array($attachmentIds)) ? implode(',', $attachmentIds) : '';
        $deleteButtonClass = ($this->getDefaultValue() !== '' or $this->getDefaultValue() !== null) ? '' : 'hidden';
        $defaultValue = $this->defaultValue();

        $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::AUDIO_MULTI_TYPE.'">';
        $field .= '<div class="file-upload-wrapper" style="width: 100%">';
        $field .= '<div class="playlist-preview" data-target="'. Strings::esc_attr($this->getIdName()).'">'. $this->getPlaylistPreview() .'</div>';
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
        $field .= '<a class="upload-playlist-btn button-primary button">'.Translator::translate("Select files").'</a>';
        $field .= '<a data-target-id="'.Strings::esc_attr($this->getIdName()).'" class="delete-audio-btn upload-delete-btn button button-danger '.Strings::esc_attr($deleteButtonClass).'">'.Translator::translate("Delete all files").'</a>';

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
     * @param array $attachmentIds
     * @return string
     */
    public function renderPlaylistInRepeater($attachmentIds = [])
    {
        $id = "audio_multi_".Strings::generateRandomId();
        $attachmentIdsValue = (is_array($attachmentIds)) ? implode(',', $attachmentIds) : '';
        $deleteButtonClass = (!empty($this->getDefaultValue())) ? '' : 'hidden';
        $defaultValue = (!empty($this->getDefaultValue()) and is_array($this->getDefaultValue()) ) ? Strings::esc_attr(implode(',', $this->getDefaultValue())) : '';

        $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::AUDIO_MULTI_TYPE.'">';
        $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
        $field .= '<div class="file-upload-wrapper" style="width: 100%">';
        $field .= '<div class="playlist-preview" data-target="'. $id .'">'. $this->getPlaylistPreview() .'</div>';
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
        $field .= '<a data-parent-index="'.$this->getIndex().'" class="upload-playlist-btn button-primary button">'.Translator::translate("Select files").'</a>';
        $field .= '<a data-target-id="'.$id.'" class="upload-delete-btn button button-danger '.Strings::esc_attr($deleteButtonClass).'">'.Translator::translate("Delete all files").'</a>';
        $field .= '</div>';
        $field .= '</div>';

        return $field;
    }

    /**
     * @return string
     */
    private function getPlaylistPreview()
    {
        $defaultPlaylist = $this->getAttachments();

        // this code is needed for fields nested in a Repeater
        if(empty($defaultPlaylist) and is_array($this->getValue())){
            foreach ($this->getValue() as $audioUrl){
                $attachment = WPAttachment::fromUrl($audioUrl);
                $defaultPlaylist[] = $attachment;
            }
        }

        if($defaultPlaylist === ''){
            return 'No file selected';
        }

        if(empty($defaultPlaylist)){
            return 'No file selected';
        }

        if(!is_array($defaultPlaylist)){
            return 'No file selected';
        }

        $preview = '';

        foreach ($defaultPlaylist as $index => $audio){
            $preview .= '
			    <div class="audio" data-index="'.$index.'" draggable="true">
                    <div class="handle">
                        .<br/>.<br/>.
                    </div>
                    '.$this->formatAudio($audio).'
                    <a class="delete-playlist-audio-btn" data-parent-index="'.$this->getIndex().'" data-index="'.$index.'" href="#" title="'.Translator::translate("Delete").'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                            <path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path>
                        </svg>
                    </a>
                </div>';

        }

        return $preview;
    }

    /**
     * Enqueue necessary assets
     */
    private function enqueueAssets()
    {
        wp_enqueue_script( 'html5sortable', plugins_url( 'advanced-custom-post-type/assets/vendor/html5sortable/dist/html5sortable.min.js'), [], '2.2.0', true);
    }
}
