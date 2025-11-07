<?php

namespace ACPT\Core\Generators\Meta;

use ACPT\Core\Generators\AbstractGenerator;
use ACPT\Core\Generators\Form\Fields\AbstractField as AbstractFormField;
use ACPT\Core\Generators\Meta\Fields\AbstractField;
use ACPT\Core\Helper\Fields;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\FormRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\Data\DataAggregator;
use ACPT\Utils\Wordpress\Translator;


class RepeaterFieldGenerator extends AbstractGenerator
{
    /**
     * @var MetaFieldModel
     */
    private $parentFieldModel;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $dataId;

	/**
	 * @var
	 */
	private $belongsTo;

	/**
	 * @var
	 */
	private $parentName;

	/**
	 * @var
	 */
	private $parentId;

	/**
	 * @var
	 */
	private $layout;

	/**
	 * @var MetaFieldModel|null
	 */
	private $leadingField;

    /**
     * @var null
     */
	private $cloneFieldId;

    /**
     * @var null
     */
    private $formId;

    /**
     * RepeaterFieldGenerator constructor.
     * @param MetaFieldModel $parentFieldModel
     * @param $parentName
     * @param $parentId
     * @param $belongsTo
     * @param string $layout
     * @param null $leadingFieldId
     * @param null $cloneFieldId
     * @param null $formId
     */
    public function __construct(
    	MetaFieldModel $parentFieldModel,
	    $parentName,
	    $parentId,
	    $belongsTo,
	    $layout = 'row',
	    $leadingFieldId = null,
        $cloneFieldId = null,
        $formId = null
    )
    {
        $this->parentFieldModel = $parentFieldModel;
        $this->parentName       = $parentName;
	    $this->belongsTo        = $belongsTo;
	    $this->parentId         = $parentId;
	    $this->layout           = $layout;
	    $this->cloneFieldId     = $cloneFieldId;
	    $this->formId           = $formId;

	    if($leadingFieldId !== null){
		    try {
			    $this->leadingField = MetaRepository::getMetaFieldById($leadingFieldId, true);
		    } catch (\Exception $exception){
                do_action("acpt/error", $exception);
            }
	    }
    }

    /**
     * @param array $data
     */
    public function setData( $data )
    {
        $this->data = $data;
    }

    /**
     * @param int $dataId
     */
    public function setDataId( $dataId )
    {
        $this->dataId = $dataId;
    }

    /**
     * @param null $generatedIndex
     *
     * @return string
     * @throws \Exception
     */
    public function generate($generatedIndex = null)
    {
        if(!empty($this->data)){

            $elements = '';

            foreach ( DataAggregator::aggregateNestedFieldsData($this->data) as $index => $data){
                $elements .= $this->generateElement($index, $data);
            }

            return $elements;
        }

        if(null === $generatedIndex){
            throw new \Exception('Missing generated index');
        }

        return $this->generateElement($generatedIndex, []);
    }

    /**
     * @param $elementIndex
     * @param array $data
     * @return string
     * @throws \Exception
     */
    private function generateElement($elementIndex, array $data = [])
    {
	    $id = 'element-'.rand(999999, 111111);

    	if($this->layout === 'table'){
		    return $this->generateElementWithTableLayout($id, $elementIndex, $data);
	    }

	    if($this->layout === 'block'){
		    return $this->generateElementWithBlockLayout($id, $elementIndex, $data);
	    }

        return $this->generateElementWithRowLayout($id, $elementIndex, $data);
    }

    /**
     * @param $id
     * @param $elementIndex
     * @param array $data
     *
     * @return string
     * @throws \Exception
     */
    private function generateElementWithTableLayout($id, $elementIndex, array $data = [])
    {
	    $return = '<tr id='.$id.' class="sortable-li sortable-li-'.$this->parentId.' ">';
	    $return .= '<td class="sortable-handle" width="30">
						<div class="handle">
		                    .<br/>.<br/>.
		                </div>
					</td>';

	    foreach ($this->parentFieldModel->getChildren() as $index => $child){
		    $value = $this->getDefaultValue($data, $child->getNormalizedName());
		    $extra = $data[$index] ?? [];
		    $repeaterField = $this->getNestedField($child, $elementIndex, $value, $extra);

            if($repeaterField instanceof AbstractFormField){
                $return .= '<td>'.$repeaterField->renderElement().'</td>';
            } elseif($repeaterField instanceof AbstractField) {
                $return .= '<td>'.$repeaterField->render().'</td>';
            } else {
                $return .= '<td></td>';
            }
	    }

	    $return .= '<td width="120">
				<a 
	                class="button small button-danger remove-grouped-element" 
	                data-parent-id="'.$this->parentFieldModel->getId().'"
	                data-layout="'.$this->layout.'"
	                data-element="'.$this->parentFieldModel->getLabelOrName().'" 
	                data-elements="elements" 
	                data-target-id="'.$id.'" 
	                data-index="'.$elementIndex.'"
	                href="#"
	                title="'.Translator::translate('Remove').' '.$this->parentFieldModel->getLabelOrName().'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                        <path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path>
                    </svg>
                </a>
			</td>';
	    $return .= '</tr>';

	    return $return;
    }

    /**
     * @param $id
     * @param $elementIndex
     * @param array $data
     *
     * @return string
     * @throws \Exception
     */
	private function generateElementWithBlockLayout($id, $elementIndex, array $data = [])
	{
		$return = '';
		$return .= '<li id="'.$id.'" class="sortable-li sortable-li-'.$this->parentId . '">
                <div class="handle">
                    .<br/>.<br/>.
                </div>
                <span class="sortable-li_collapsed_placeholder">'.$this->collapsedPlaceholder($elementIndex, $data).'</span>
                <div class="sortable-content">';

		$return .= '<div class="acpt-table-responsive">';
		$return .= '<table class="acpt-table acpt-vertical-table">';
		$return .= '<tbody>';
		$return .= '</tbody>';

		foreach ($this->parentFieldModel->getChildren() as $index => $child){
			$value = $this->getDefaultValue($data, $child->getNormalizedName());
            $extra = $data[$index] ?? [];
			$repeaterField = $this->getNestedField($child, $elementIndex, $value, $extra);
			$return .= '<tr>';
			$return .= '<th><span class="text-ellipsis">'.$child->getLabelOrName().'</span></th>';

            if($repeaterField instanceof AbstractFormField){
                $return .= '<td>'.$repeaterField->renderElement().'</td>';
            } elseif($repeaterField instanceof AbstractField) {
                $return .= '<td>'.$repeaterField->render().'</td>';
            } else {
                $return .= '<td></td>';
            }

			$return .= '</tr>';
		}

		$return .= '</table>';
		$return .= '</div>';
		$return .= '</div>
                <a 
	                class="button small button-danger remove-grouped-element" 
	                data-parent-id="'.$this->parentFieldModel->getId().'"
	                data-element="'.$this->parentFieldModel->getLabelOrName().'" 
	                data-elements="elements" 
	                data-target-id="'.$id.'" 
	                data-index="'.$elementIndex.'"
	                href="#"
	                title="'.Translator::translate('Remove').' '.$this->parentFieldModel->getLabelOrName().'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                        <path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path>
                    </svg>
                </a>
                <a title="'.Translator::translate("Show/hide elements").'" class="button small sortable-li_toggle_visibility" data-target-id="'.$id.'" href="#">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="components-panel__arrow" aria-hidden="true" focusable="false">
						<path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path>
					</svg>
				</a>
            </li>';

		return $return;
	}

    /**
     * @param $id
     * @param $elementIndex
     * @param array $data
     *
     * @return string
     * @throws \Exception
     */
	private function generateElementWithRowLayout($id, $elementIndex, array $data = [])
	{
		$return = '';
		$return .= '<li id="'.$id.'" class="sortable-li sortable-li-'.$this->parentId . '">
                <div class="handle">
                    .<br/>.<br/>.
                </div>
               <span class="sortable-li_collapsed_placeholder">'.$this->collapsedPlaceholder($elementIndex, $data).'</span>
                <div class="sortable-content">';

		$rows = Fields::extractFieldRows($this->parentFieldModel->getChildren());

		foreach ($rows as $row){
			$randomId = Strings::generateRandomId();
			$return .= "<div class='acpt-admin-meta-row' id='".$randomId."'>";
			$visibleFieldsCount = 0;

			/** @var MetaFieldModel $child */
			foreach ($row as $index => $child){

			    $child->setParentId($this->parentFieldModel->getId());

			    if($child->getType() === MetaFieldModel::CLONE_TYPE){

                    foreach ($data as $i => $datum){
                        if($child->getNormalizedName() === $datum['key']){
                            unset($data[$i]);
                        }
                    }

                    $value = array_values($data);
                } else {
                    $value = $this->getDefaultValue($data, $child->getNormalizedName());
                }

                $extra = $data[$index] ?? [];
				$repeaterField = $this->getNestedField($child, $elementIndex, $value, $extra);

				if($repeaterField->isVisible()){
					$visibleFieldsCount++;
				}

                if($repeaterField instanceof AbstractFormField){
                    $return .= $repeaterField->renderElement();
                } elseif($repeaterField instanceof AbstractField) {
                    $return .= $repeaterField->render();
                }
			}

			// hidden row containing only not visible fields
			if($visibleFieldsCount == 0){
				$return = str_replace("<div class='acpt-admin-meta-row' id='".$randomId."'>", "<div class='acpt-admin-meta-row hidden' id='".$randomId."'>", $return);
			}

			$return .= "</div>";
		}

		$return .= '</div>
                <a 
	                class="button small button-danger remove-grouped-element" 
	                data-parent-id="'.$this->parentFieldModel->getId().'"
	                data-element="'.$this->parentFieldModel->getLabelOrName().'" 
	                data-elements="elements" 
	                data-target-id="'.$id.'" 
	                data-index="'.$elementIndex.'"
	                href="#"
	                title="'.Translator::translate('Remove').' '.$this->parentFieldModel->getLabelOrName().'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                        <path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path>
                    </svg>
                </a>
                <a title="'.Translator::translate("Show/hide elements").'" class="button small sortable-li_toggle_visibility" data-target-id="'.$id.'" href="#">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="components-panel__arrow" aria-hidden="true" focusable="false">
						<path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path>
					</svg>
				</a>
            </li>';

		return $return;
	}

	/**
	 * @param $elementIndex
	 * @param array $data
	 *
	 * @return string
	 */
    private function collapsedPlaceholder($elementIndex, array $data = [])
    {
    	if($this->leadingField === null){
    		return Translator::translate("Collapsed element").' #'.$elementIndex;
	    }

	    $value = $this->getDefaultValue($data, $this->leadingField->getNormalizedName());

    	if(is_array($value)){
		    $value = implode(", ", $value);
	    }

    	return '<span class="label">'.$this->leadingField->getLabelOrName().'</span>' . ': <span class="value">' . $value . '</span>';
    }

    /**
     * @param $data
     * @param $key
     *
     * @return string
     */
    private function getDefaultValue($data, $key)
    {
        if(empty($data)){
            return null;
        }

        foreach ($data as $datum){
            if($key === $datum['key']){
                return $datum['value'] ?? null;
            }
        }

        return null;
    }

    /**
     * @param MetaFieldModel $fieldModel
     * @param int $index
     * @param null $value
     * @param array $extra
     *
     * @return mixed
     * @throws \Exception
     */
    private function getNestedField(MetaFieldModel $fieldModel, $index, $value = null, $extra = [])
    {
        // Get a form field
        if(!empty($this->formId)){
            $formModel = FormRepository::getById($this->formId);
            $formFieldModel = FormFieldModel::copyFromMetaField($fieldModel);
            $formFieldModel->setBelong($this->belongsTo);
            $formFieldModel->setFind($this->dataId);

            $class = 'ACPT\\Core\\Generators\\Form\\Fields\\'.$formFieldModel->getType().'Field';

            if(class_exists($class)){

                /** @var AbstractField $field */
                $field = new $class($formModel, $formFieldModel);
                $field->setIsNested(true);
                $field->setIndex($index);
                $field->setParentField($fieldModel->getParentField());
                $field->setValue($value);
                $field->setExtra($extra);

                return $field;
            }

            return null;
        }

        // Get an admin meta field
	    $className = 'ACPT\\Core\\Generators\\Meta\\Fields\\'.$fieldModel->getType().'Field';

	    if(class_exists($className)){
		    return new $className($fieldModel, $this->belongsTo, $this->dataId, $index, $value, $this->parentName, 0, $this->cloneFieldId);
	    }

        return null;
    }
}