<?php

namespace ACPT\Core\Generators\Form\Fields;

class FormattingLinkField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $css = $this->fieldModel->getSettings()['css'] ?? "";
        $linkValue = $this->fieldModel->getExtra()['linkValue'] ?? "";
        $linkTarget = $this->fieldModel->getExtra()['linkValue'] ?? "_self";

        return "<a class='".$css."' href='".$linkValue."' target='".$linkTarget."'>".$this->defaultValue()."</a>";
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets() {
        // TODO: Implement enqueueFieldAssets() method.
    }
}
