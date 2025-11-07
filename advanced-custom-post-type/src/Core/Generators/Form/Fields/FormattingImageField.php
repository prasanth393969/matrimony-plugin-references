<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Utils\Wordpress\WPAttachment;

class FormattingImageField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $css = $this->fieldModel->getSettings()['css'] ?? "";
        $width = $this->fieldModel->getSettings()['imageWidth'] ?? "100%";
        $height = $this->fieldModel->getSettings()['imageHeight'] ?? "";

        if(!$this->defaultValue()){
            return null;
        }

        $wpAttachment = WPAttachment::fromUrl($this->defaultValue());

        if(!$wpAttachment->isImage()){
            return null;
        }

        return $wpAttachment->render([
            'w' => $width,
            'h' => $height,
            'class' => $css,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets() {
        // TODO: Implement enqueueFieldAssets() method.
    }
}
