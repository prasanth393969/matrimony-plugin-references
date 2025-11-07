<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\PHP\ImageSlider;
use ACPT\Utils\Wordpress\WPAttachment;

class ImageSliderField extends AbstractField
{
    public function render()
    {
        if(!$this->isFieldVisible()){
            return null;
        }

	    $rawData = $this->fetchRawData();
        $attachments = $this->getAttachments($rawData);

        if(empty($attachments)){
            return null;
        }

        if(count($attachments) !== 2){
            return null;
        }

        if($this->payload->preview){

            $return = '<div style="display: flex; gap: 5px;">';

            foreach ($attachments as $image){
                $wpAttachment = WPAttachment::fromUrl($image);

                if(!$wpAttachment->isEmpty()){
                    $return .= $this->addBeforeAndAfter($wpAttachment->render([
                        'style' => 'border: 1px solid #c3c4c7; object-fit: fill;',
                        'size' => 'thumbnail',
                        'w' => 80,
                        'h' => 60,
                    ]));
                }
            }

            $return .= '</div>';

            return $return;
        }

        $width = ($this->payload->width !== null) ? $this->payload->width : null;
        $height = ($this->payload->height !== null) ? $this->payload->height : null;

        return ImageSlider::render($attachments, $this->metaBoxFieldModel->getAdvancedOption('default_percent') ?? 50, $width, $height);
    }
}