<?php

namespace ACPT\Core\Generators\Form\Fields;

class FormattingHeadingField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $headingLevel = $this->fieldModel->getExtra()['headingLevel'] ?? "h1";
        $css = $this->fieldModel->getSettings()['css'] ?? "";

        return '<'.$headingLevel.' class="'.$css.'">'.$this->defaultValue().'</'.$headingLevel.'>';
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets() {
        // TODO: Implement enqueueFieldAssets() method.
    }
}
