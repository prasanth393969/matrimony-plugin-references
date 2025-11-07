<?php

namespace ACPT\Core\Generators\Form\Fields;

class FormattingParagraphField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $css = $this->fieldModel->getSettings()['css'] ?? "";
        $value = (!empty($this->defaultValue())) ? html_entity_decode($this->defaultValue()) : null;

        return '<p class="'.$css.'">'.$value.'</p>';
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets() {
        // TODO: Implement enqueueFieldAssets() method.
    }
}
