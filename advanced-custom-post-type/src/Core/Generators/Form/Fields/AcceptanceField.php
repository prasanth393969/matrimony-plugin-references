<?php

namespace ACPT\Core\Generators\Form\Fields;

class AcceptanceField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $css = $this->fieldModel->getSettings()['css'] ?? "";
        $value = (!empty($this->defaultValue())) ? html_entity_decode($this->defaultValue()) : null;
        $id = $this->getIdName();

        $acceptance  = "<div class='acpt-acceptance'>";
        $acceptance .= "
            <input
                name='".$this->getIdName()."' 
                class='acpt-acceptance-input' 
                type='checkbox' 
                value='1'
                id='".$id."'
                ".$this->required()."
                ".$this->appendDataValidateAndConditionalRenderingAttributes()."
            >";
        $acceptance .= '<label class="acpt-acceptance-label" for="'.$id.'"><span class="'.$css.'">'.$value.'</span></label>';
        $acceptance .= "</div>";

        return $acceptance;
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets()
    {
        // TODO: Implement enqueueFieldAssets() method.
    }
}