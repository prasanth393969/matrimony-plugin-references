<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\WPAttachment;

class AudioField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->enqueueAssets();
        $attachmentId = (isset($this->getAttachments()[0])) ? $this->getAttachments()[0]->getId() : '';
        $preview = $this->preview();

        if($this->isChild() or $this->isNestedInABlock()){
            $id = "audio_".Strings::generateRandomId();
            $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::AUDIO_TYPE.'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
        } else {
            $id = Strings::esc_attr($this->getIdName());
            $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::AUDIO_TYPE.'">';
        }

        $field .= '<div class="file-upload-wrapper">';
        $field .= '<div class="audio-preview">'. $preview .'</div>';
        $field .= '<div class="btn-wrapper">';

        if($this->isChild() or $this->isNestedInABlock()){
            $field .= '<input id="'.$id.'[attachment_id]['.$this->getIndex().']" name="'. esc_html($this->getIdName()).'[attachment_id]" type="hidden" value="' .$attachmentId.'">';
            $field .= '<input readonly '.$this->required().' id="'.$id.'" name="'. Strings::esc_attr($this->getIdName()).'[value]" type="text" class="hidden" value="' .Strings::esc_attr($this->getDefaultValue()) .'" '.$this->appendDataValidateAndLogicAttributes().'>';
        } else {
            $field .= '<input id="'.$id.'_attachment_id" name="'. esc_html($this->getIdName()).'_attachment_id" type="hidden" value="' .$attachmentId.'">';
            $field .= '<input readonly '.$this->required().' id="'.$id.'" name="'. Strings::esc_attr($this->getIdName()).'" type="text" class="hidden" value="' .Strings::esc_attr($this->getDefaultValue()) .'" '.$this->appendDataValidateAndLogicAttributes().'>';
        }

        $field .= '<a class="upload-audio-btn button button-primary">'.Translator::translate("Upload").'</a>';
        $field .= '<button data-target-id="'.$id.'" class="upload-delete-btn delete-audio-btn button button-secondary">'.Translator::translate("Delete").'</button>';

        $field .= '</div>';
        $field .= '</div>';


        return $this->renderField($field);
    }

    /**
     * @return string
     */
    private function preview()
    {
        if(!empty($this->getDefaultValue()) and is_string($this->getDefaultValue())){
            $attachment = (isset($this->getAttachments()[0])) ? $this->getAttachments()[0] : null;

            if(empty($attachment)){
                $attachment = WPAttachment::fromUrl($this->getDefaultValue());
            }

            if($attachment->isEmpty()){
                return null;
            }

            return "<div class='audio'>".$this->formatAudio($attachment)."</div>";
        }

        return '<span class="placeholder">'.Translator::translate("No audio selected").'</span>';
    }

    /**
     * @param WPAttachment $attachment
     * @return string
     */
    protected function formatAudio(WPAttachment $attachment)
    {
        if(!$attachment->isAudio()) {
            return '<span class="placeholder">Wrong audio data</span>';
        }

        $src = esc_url($attachment->getSrc());
        $title = $attachment->getTitle() ?? 'Unknown title';
        $album = (isset($attachment->getMetadata()['album']) and !empty($attachment->getMetadata()['album'])) ? $attachment->getMetadata()['album'] :  'Unknown album';
        $artist = (isset($attachment->getMetadata()['artist']) and !empty($attachment->getMetadata()['artist'])) ? $attachment->getMetadata()['artist'] :  'Unknown artist';

        $audio  = '<div class="acpt-audio-meta-wrapper">';
        $audio .= '<div class="acpt-audio-meta">';
        $audio .= '<h5 class="title">'.$title.'</h5>';
        $audio .= '<div class="meta">';

        if(!empty($artist)){
            $audio .= '<span class="artist">'.$artist.'</span>';
        }

        if(!empty($album)){
            $audio .= '<span class="album">- '.$album.'</span>';
        }

        $audio .= '</div>';
        $audio .= '</div>';
        $audio .= '<audio class="acpt-audio-player" controls src="'.$src.'"></audio>';
        $audio .= '</div>';

        return $audio;
    }

    private function enqueueAssets()
    {
        wp_enqueue_script( 'audio-player-js', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/audio-player.js' : 'advanced-custom-post-type/assets/static/js/audio-player.min.js'), [], '1.0.0', true);
    }
}