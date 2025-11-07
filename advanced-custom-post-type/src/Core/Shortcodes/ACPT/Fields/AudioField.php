<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\PHP\Audio;
use ACPT\Utils\Wordpress\WPAttachment;

class AudioField extends AbstractField
{
    public function render()
    {
        if(!$this->isFieldVisible()){
            return null;
        }

        $rawData = $this->fetchRawData();

        if(!isset($rawData['value'])){
            return null;
        }

        $wpAttachment = $this->getAttachment($rawData);

        if(empty($wpAttachment)){
            return null;
        }

        if($this->payload->preview){
            return $this->renderAudioPreview($wpAttachment);
        }

        $render = $this->metaBoxFieldModel->getAdvancedOption("render") ?? "html";

        if($render === "id"){
            return $wpAttachment->getId();
        }

        if($render === "url"){
            return $wpAttachment->getSrc();
        }

        return $this->renderAudio($wpAttachment);
    }

    /**
     * @param WPAttachment $attachment
     * @return string
     */
    protected function renderAudioPreview(WPAttachment $attachment)
    {
        $label = !empty($attachment->getTitle()) ? $attachment->getTitle() : $attachment->getSrc();

        return "<a href='".$attachment->getSrc()."'>".$label."</a>";
    }

    /**
     * @param WPAttachment $attachment
     * @return string
     */
    private function renderAudio(WPAttachment $attachment)
    {
        if(empty($this->metaBoxFieldModel)){
            return null;
        }

        $style = $this->payload->render ?? 'light';
        $customPlayer = ($this->metaBoxFieldModel !== null and $this->metaBoxFieldModel->getAdvancedOption('custom_audio_player') == 1) ? true : false;
        $disableCover = ($this->metaBoxFieldModel !== null and $this->metaBoxFieldModel->getAdvancedOption('disable_cover') == 1) ? true : false;

        return Audio::single($attachment, $customPlayer, $style, false, $disableCover);
    }
}