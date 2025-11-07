<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\RepeaterFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Maps;
use ACPT\Utils\Wordpress\Translator;

class RepeaterField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        if(empty($this->fieldModel->getMetaField())){
            return null;
        }

        $defaultValue = $this->defaultValue();
        $belongsTo = $this->fieldModel->getBelong();
        $extra = $this->fieldModel->getExtra();
        $minimumBlocks = $this->fieldModel->getMetaField()->getAdvancedOption('minimum_blocks');
        $maximumBlocks = $this->fieldModel->getMetaField()->getAdvancedOption('maximum_blocks');
        $leadingFieldId = $this->fieldModel->getMetaField()->getAdvancedOption('leading_field');
        $layout = $extra['layout'] ?? 'row';
        $nestedListId = Strings::generateRandomId();

        $field = "<div class='acpt-form-messages'>";

        if($minimumBlocks){
            $field .= '<input type="hidden" name="'. $this->fieldModel->getId().'_min_blocks" value="'.$minimumBlocks.'">';
        }

        if($maximumBlocks){
            $field .= '<input type="hidden" name="'. $this->fieldModel->getId().'_max_blocks" value="'.$maximumBlocks.'">';
        }

        $field .= $this->renderRepeaterLayout($layout, $nestedListId, $minimumBlocks, $maximumBlocks, $leadingFieldId);
        $field .= "</div>";

        if(!empty($this->fieldModel->getMetaField()->getChildren())){
            $field .= '<a 
                data-layout="'.$layout.'"
                data-parent-index="'.$nestedListId.'" 
                data-parent-name="'.$this->getIdName().'" 
                data-media-type="'.$belongsTo.'"
                data-form-id="'.$this->formModel->getId().'" 
                data-group-id="'.$this->fieldModel->getMetaField()->getId().'" 
                href="#" 
                class="add-grouped-element"
            >
                '.Translator::translate('Add').' '.$this->fieldModel->getMetaField()->getLabelOrName().'
            </a>';
        }

        return $field;
    }

    /**
     * @param $layout
     * @param $nestedListId
     * @param null $minimumBlocks
     * @param null $maximumBlocks
     * @param null $leadingFieldId
     *
     * @return string
     * @throws \Exception
     */
    private function renderRepeaterLayout(
        $layout,
        $nestedListId,
        $minimumBlocks = null,
        $maximumBlocks = null,
        $leadingFieldId = null
    )
    {
        $defaultData = $this->defaultValue();

        if($layout === 'table'){
            return $this->renderRepeaterWithTableLayout($nestedListId, $defaultData, $minimumBlocks, $maximumBlocks);
        }

        if($layout === 'block'){
            return $this->renderRepeaterWithBlockLayout($nestedListId, $defaultData, $minimumBlocks, $maximumBlocks, $leadingFieldId);
        }

        return $this->renderRepeaterWithRowLayout($nestedListId, $defaultData, $minimumBlocks, $maximumBlocks, $leadingFieldId);
    }

    /**
     * @param $nestedListId
     * @param $defaultData
     * @param null $minimumBlocks
     * @param null $maximumBlocks
     *
     * @return string
     * @throws \Exception
     */
    private function renderRepeaterWithTableLayout(
        $nestedListId,
        $defaultData,
        $minimumBlocks = null,
        $maximumBlocks = null
    )
    {
        $field = '<div class="acpt-table-responsive" style="margin: 10px 0;">';
        $field .= '<table id="'.$this->fieldModel->getMetaField()->getId().'" class="acpt-table">';
        $field .= '<tbody id="acpt-sortable-'.$nestedListId.'" class="acpt-sortable"';

        if($minimumBlocks){
            $field .= ' data-min-blocks="'.$minimumBlocks.'"';
        }

        if($maximumBlocks){
            $field .= ' data-max-blocks="'.$maximumBlocks.'"';
        }

        $field .= '>';
        $field .= '<tr>';
        $field .= '<th width="30"></th>';

        foreach ($this->fieldModel->getMetaField()->getChildren() as $child){
            $field .= '<th>'.$child->getLabelOrName().'</th>';
        }

        $field .= '<th></th>';
        $field .= '</tr>';

        if($defaultData and $defaultData !== '' and is_array($defaultData)) {
            $repeaterFieldGenerator = new RepeaterFieldGenerator(
                $this->fieldModel->getMetaField(),
                $this->getIdName(),
                $nestedListId,
                $this->fieldModel->getBelong(),
                'table',
                null,
                $this->formModel->getId()
            );
            $repeaterFieldGenerator->setDataId($this->fieldModel->getFind());
            $repeaterFieldGenerator->setData($defaultData);

            $field .= $repeaterFieldGenerator->generate();
        } else {
            $field .= '<tr><td colspan="'.(count($this->fieldModel->getMetaField()->getChildren())+2).'"><p data-message-id="'.$this->fieldModel->getMetaField()->getId().'" class="update-nag notice notice-warning inline no-records">'.Translator::translate('No fields saved, generate the first one clicking on "Add element" button').'</p></td></tr>';
        }

        $field .= '</tbody>';
        $field .= '</table>';
        $field .= '</div>';

        return $field;
    }

    /**
     * @param $nestedListId
     * @param $defaultData
     * @param null $minimumBlocks
     * @param null $maximumBlocks
     * @param null $leadingFieldId
     *
     * @return string
     * @throws \Exception
     */
    private function renderRepeaterWithBlockLayout(
        $nestedListId,
        $defaultData,
        $minimumBlocks = null,
        $maximumBlocks = null,
        $leadingFieldId = null
    )
    {
        $field = '<ul id="'.$this->fieldModel->getMetaField()->getId().'" class="acpt-sortable"';

        if($minimumBlocks){
            $field .= ' data-min-blocks="'.$minimumBlocks.'"';
        }

        if($maximumBlocks){
            $field .= ' data-max-blocks="'.$maximumBlocks.'"';
        }

        $field .= '>';

        if($defaultData and $defaultData !== '' and is_array($defaultData)) {
            $repeaterFieldGenerator = new RepeaterFieldGenerator(
                $this->fieldModel->getMetaField(), $this->getIdName(),
                $nestedListId,
                $this->fieldModel->getBelong(),
                'block',
                $leadingFieldId,
                null,
                $this->formModel->getId()
            );
            $repeaterFieldGenerator->setDataId($this->fieldModel->getFind(),);
            $repeaterFieldGenerator->setData($defaultData);

            $field .= $repeaterFieldGenerator->generate();
        } else {
            $field .= '<p data-message-id="'.$this->fieldModel->getMetaField()->getId().'" class="update-nag notice notice-warning inline no-records">'.Translator::translate('No fields saved, generate the first one clicking on "Add element" button').'</p>';
        }

        $field .= '</ul>';

        return $field;
    }

    /**
     * @param $nestedListId
     * @param $defaultData
     * @param null $minimumBlocks
     * @param null $maximumBlocks
     * @param null $leadingFieldId
     *
     * @return string
     * @throws \Exception
     */
    private function renderRepeaterWithRowLayout(
        $nestedListId,
        $defaultData,
        $minimumBlocks = null,
        $maximumBlocks = null,
        $leadingFieldId = null
    )
    {
        $field = '<ul id="'.$this->fieldModel->getMetaField()->getId().'" class="acpt-sortable"';

        if($minimumBlocks){
            $field .= ' data-min-blocks="'.$minimumBlocks.'"';
        }

        if($maximumBlocks){
            $field .= ' data-max-blocks="'.$maximumBlocks.'"';
        }

        $field .= '>';

        if($defaultData and $defaultData !== '' and is_array($defaultData)) {
            $repeaterFieldGenerator = new RepeaterFieldGenerator(
                $this->fieldModel->getMetaField(), $this->getIdName(),
                $nestedListId,
                $this->fieldModel->getBelong(),
                'row',
                $leadingFieldId,
                null,
                $this->formModel->getId()
            );
            $repeaterFieldGenerator->setDataId($this->fieldModel->getFind());
            $repeaterFieldGenerator->setData($defaultData);

            $field .= $repeaterFieldGenerator->generate();
        } else {
            $field .= '<p data-message-id="'.$this->fieldModel->getMetaField()->getId().'" class="update-nag notice notice-warning inline no-records">'.Translator::translate('No fields saved, generate the first one clicking on "Add element" button').'</p>';
        }

        $field .= '</ul>';

        return $field;
    }

    /**
     * Enqueue child fields assets if needed
     */
    public function enqueueFieldAssets()
    {
        if(empty($this->fieldModel->getMetaField())){
            return;
        }

        if(!empty($this->fieldModel->getMetaField()->getChildren())){
            foreach ($this->fieldModel->getMetaField()->getChildren() as $child){

                // ADDRESS_TYPE
                if($child->getType() === FormFieldModel::ADDRESS_TYPE){
                    if(!empty(Maps::googleMapsKey())){
                        wp_register_script('admin_google_maps_js',  plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/google-maps.js' : 'advanced-custom-post-type/assets/static/js/google-maps.min.js'), ['jquery'], ACPT_PLUGIN_VERSION );
                        wp_enqueue_script('admin_google_maps_js');

                        wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key='.Maps::googleMapsKey().'&libraries=places&callback=init', false, '3', true);
                        wp_enqueue_script('google-maps');
                    }
                }

                // DATE fields
                if(
                    $child->getType() === FormFieldModel::DATE_RANGE_TYPE or
                    $child->getType() === FormFieldModel::DATE_TYPE or
                    $child->getType() === FormFieldModel::DATE_TIME_TYPE or
                    $child->getType() === FormFieldModel::TIME_TYPE
                ){
                    wp_enqueue_script( 'momentjs', plugins_url( 'advanced-custom-post-type/assets/vendor/moment/moment.min.js'), [], '2.18.1', true);
                    wp_enqueue_script( 'daterangepicker-js', plugins_url( 'advanced-custom-post-type/assets/vendor/daterangepicker/js/daterangepicker.min.js'), [], '3.1.0', true);
                    wp_enqueue_style( 'daterangepicker-css', plugins_url( 'advanced-custom-post-type/assets/vendor/daterangepicker/css/daterangepicker.min.css'), [], '3.1.0', 'all');
                }

                // EDITOR_TYPE
                if($child->getType() === MetaFieldModel::EDITOR_TYPE){
                    wp_enqueue_script( 'quill-js', plugins_url( 'advanced-custom-post-type/assets/vendor/quill/quill.min.js'), [], '3.1.0', true);
                    wp_enqueue_style( 'quill-css', plugins_url( 'advanced-custom-post-type/assets/vendor/quill/quill.snow.css'), [], '3.1.0', 'all');
                }

                // HTML
                if($child->getType() === FormFieldModel::HTML_TYPE){
                    wp_register_style( 'codemirror-css', plugins_url( 'advanced-custom-post-type/assets/vendor/codemirror/codemirror5.min.css'), [], "5.65.16" );
                    wp_enqueue_style( 'codemirror-css' );

                    wp_register_script('codemirror-js',  plugins_url( 'advanced-custom-post-type/assets/vendor/codemirror/codemirror5.min.js') );
                    wp_enqueue_script('codemirror-js');

                    // Emmet
                    wp_register_script('codemirror-browser-js',  plugins_url( 'advanced-custom-post-type/assets/vendor/codemirror/browser.js') );
                    wp_enqueue_script('codemirror-browser-js');

                    wp_register_script('codemirror-htmlmixed-js',  plugins_url( 'advanced-custom-post-type/assets/vendor/codemirror/mode/htmlmixed/htmlmixed.min.js') );
                    wp_enqueue_script('codemirror-htmlmixed-js');
                }

                // PHONE_TYPE
                if($child->getType() === FormFieldModel::PHONE_TYPE){
                    wp_enqueue_script( 'intlTelInput-js', plugins_url('advanced-custom-post-type/assets/vendor/intlTelInput/js/intlTelInput.min.js'), [], '1.10.60', true);
                    wp_enqueue_style( 'intlTelInput-css', plugins_url('advanced-custom-post-type/assets/vendor/intlTelInput/css/intlTelInput.min.css'), [], '1.10.60', 'all');
                }

                // QR_CODE_TYPE
                if($child->getType() === FormFieldModel::QR_CODE_TYPE){
                    wp_enqueue_script( 'qrcodejs', plugins_url( 'advanced-custom-post-type/assets/vendor/qrcode/qrcode.min.js'), [], '1.0.0', true);
                }
            }
        }
    }
}
