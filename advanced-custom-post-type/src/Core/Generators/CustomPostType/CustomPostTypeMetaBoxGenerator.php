<?php

namespace ACPT\Core\Generators\CustomPostType;

use ACPT\Constants\Visibility;
use ACPT\Core\Generators\AbstractGenerator;
use ACPT\Core\Helper\Fields;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Checker\BoxVisibilityChecker;

/**
 * *************************************************
 * MetaBoxGenerator class
 * *************************************************
 *
 * @author Mauro Cassani
 * @link https://github.com/mauretto78/
 */
class CustomPostTypeMetaBoxGenerator extends AbstractGenerator
{
	/**
	 * @param MetaBoxModel $metaBoxModel
	 * @param $postTypeName
	 * @param array $formFields
	 * @param null $postId
	 */
    public function addMetaBox(MetaBoxModel $metaBoxModel, $postTypeName, $formFields = [], $postId = null)
    {
        // end update_edit_form
        add_action('post_edit_form_tag', function() {
            echo ' enctype="multipart/form-data"';
        });

        $this->adminInit(function() use($metaBoxModel, $formFields, $postTypeName, $postId) {

            $isVisible = BoxVisibilityChecker::check(Visibility::IS_BACKEND, $metaBoxModel, $postId);

            if(!$isVisible){
                return;
            }
            
            if(
                    $postId === null or
                    (isset($_GET['post']) and $_GET['post'] == $postId) or
                    (isset($_GET['id']) and $_GET['id'] == $postId)
            ){
                $boxLabel = (!empty($metaBoxModel->getLabel())) ? $metaBoxModel->getLabel() : $metaBoxModel->getName();
                $randomId = Strings::generateRandomId();
                $idBox = 'acpt_metabox_'. $randomId;
                $rows = $this->fieldRows($metaBoxModel->getFields(), $postId);
                $postTypeName = $this->getPostTypeName($postTypeName);

                if(!empty($rows)){
                    add_meta_box(
                            $idBox,
                            $boxLabel,
                            function($post, $data) use ($rows) {

                                foreach ($rows as $row){
                                    echo "<div class='acpt-admin-meta-row ".($row['isVisible'] == 0 ? ' hidden' : '')."'>";

                                    foreach ($row['fields'] as $field){
                                        echo $field;
                                    }

                                    echo "</div>";
                                }
                            },
                            strtolower($postTypeName),
                            $metaBoxModel->getContext(),
                            $metaBoxModel->getPriority(),
                            [$formFields]
                    );

                    add_filter('postbox_classes_'.strtolower($postTypeName).'_'.$idBox, function($classes) use ($metaBoxModel) {

                        array_push($classes,'acpt-metabox');
                        array_push($classes, $metaBoxModel->getUiName());

                        if(isset($_GET['post'])){
                            array_push($classes,'post-id-' . $_GET['post']);
                        }

                        // COOKIES
                        if(isset($_GET['post'])){
                            $cookieName = 'postbox_acpt-metabox_'.$metaBoxModel->getUiName().'_post-id-'.$_GET['post'].'_closed';
                            if(isset($_COOKIE[$cookieName]) and $_COOKIE[$cookieName] == 1){
                                array_push($classes,'closed');
                            }
                        }

                        if($metaBoxModel->getSetting("hide_title")){
                            array_push($classes,'hide-title');
                        }

                        if($metaBoxModel->getSetting("hide_toggle")){
                            array_push($classes,'hide-toggle');
                        }

                        return $classes;
                    });
                }
            }
        });
    }

	/**
	 * @param $fields
	 * @param $postId
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function fieldRows($fields, $postId = null)
	{
		$rows = Fields::extractFieldRows($fields);
		$fieldRows = [];
		$visibleFieldsTotalCount = 0;

		// build the field rows array
		foreach ($rows as $index => $row){

			$visibleFieldsRowCount = 0;

			foreach ($row as $field){
			    if($field instanceof MetaFieldModel){
                    $fieldGenerator = CustomPostTypeMetaBoxFieldGenerator::generate($field, $postId);

                    if($fieldGenerator){
                        if($fieldGenerator->isVisible()){
                            $visibleFieldsTotalCount++;
                            $visibleFieldsRowCount++;
                        }

                        $fieldRows[$index]['fields'][] = $fieldGenerator->render();
                        $fieldRows[$index]['isVisible'] = $visibleFieldsRowCount;
                    }
                }
			}
		}

		if($visibleFieldsTotalCount > 0){
			return $fieldRows;
		}

		return [];
	}
}

