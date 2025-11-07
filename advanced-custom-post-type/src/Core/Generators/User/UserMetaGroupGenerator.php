<?php

namespace ACPT\Core\Generators\User;

use ACPT\Constants\MetaGroupDisplay;
use ACPT\Constants\Visibility;
use ACPT\Core\Generators\AbstractGenerator;
use ACPT\Core\Helper\Fields;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Utils\Checker\BoxVisibilityChecker;

/**
 * *************************************************
 * UserMetaBoxGenerator class
 * *************************************************
 *
 * @author Mauro Cassani
 * @link https://github.com/mauretto78/
 */
class UserMetaGroupGenerator extends AbstractGenerator
{
	/**
	 * @var MetaGroupModel
	 */
	private MetaGroupModel $groupModel;

	/**
	 * @var \WP_User
	 */
	private \WP_User $user;

	/**
	 * UserMetaGroupGenerator constructor.
	 *
	 * @param MetaGroupModel $groupModel
	 * @param \WP_User $user
	 */
	public function __construct(MetaGroupModel $groupModel, \WP_User $user)
	{
		$this->groupModel = $groupModel;
		$this->user = $user;
	}

	/**
	 * @return string
	 */
	public function render()
	{
		switch ($this->groupModel->getDisplay()){
			default:
			case MetaGroupDisplay::STANDARD:
				return $this->standardView();

			case MetaGroupDisplay::ACCORDION:
				return $this->accordion();

			case MetaGroupDisplay::VERTICAL_TABS:
				return $this->verticalTabs();

			case MetaGroupDisplay::HORIZONTAL_TABS:
				return $this->horizontalTabs();
		}
	}

    /**
     * @param MetaBoxModel $boxModel
     *
     * @return string
     */
    protected function getIdName(MetaBoxModel $boxModel)
    {
        $idName = Strings::toDBFormat($boxModel->getName()).'_'.$boxModel->getId();

        return esc_html($idName);
    }

	/**
	 * @return string
	 */
	private function standardView()
	{
        $return = '<div class="meta-box-sortables">';
        $return .= '<div class="metabox-holder">';

		foreach ($this->groupModel->getBoxes() as $boxModel){

		    $userId = $this->user ? $this->user->ID : null;
		    $isVisible = BoxVisibilityChecker::check(Visibility::IS_BACKEND, $boxModel, $userId);

		    if($isVisible){
                $rows = $this->fieldRows($boxModel->getFields());
                $hideTitle = $boxModel->getSetting("hide_title") ? true : false;
                $hideToggle = $boxModel->getSetting("hide_toggle") ? true : false;

                if(!empty($rows)){

                    // COOKIES
                    $className = 'acpt-metabox acpt-user-meta-box user-'.$userId.' postbox';
                    $cookieName = 'acpt-metabox_acpt-user-meta-box_user-'.$userId.'_postbox_closed';
                    if(isset($_COOKIE[$cookieName]) and $_COOKIE[$cookieName] == 1){
                        $className .= ' closed';
                    }

                    $return .= '<div class="'.$className.'" id="'.$this->getIdName($boxModel).'">';
                    $return .= '<div class="postbox-header">';

                    if($hideTitle === false){
                        $return .= '<h3 class="hnadle ui-sortable-handle">'. ((!empty($boxModel->getLabel())) ? $boxModel->getLabel() : $boxModel->getName()) . '</h3>';
                    }

                    if($hideToggle === false){
                        $return .= '<div class="handle-actions hide-if-no-js">';
                        $return .= '<button type="button" class="handlediv" aria-expanded="true">';
                        $return .= '<span class="screen-reader-text">'.__('Activate/deactivate the panel', ACPT_PLUGIN_NAME).':</span>';
                        $return .= '<span class="toggle-indicator acpt-toggle-indicator" data-target="'.$this->getIdName($boxModel).'" aria-hidden="true"></span>';
                        $return .= '</button>';
                        $return .= '</div>';
                    }

                    $return .= "</div>";
                    $return .= '<div class="acpt-user-meta-box-wrapper inside no-margin" id="user-meta-box-'. $boxModel->getId().'">';

                    foreach ($rows as $row){
                        $return .= "<div class='acpt-admin-meta-row ".($row['isVisible'] == 0 ? ' hidden' : '')."'>";

                        foreach ($row['fields'] as $field){
                            $return .= $field;
                        }

                        $return .= "</div>";
                    }

                    $return .= '</div>';
                    $return .= '</div>';
                }
            }
		}

        $return .= '</div>';
        $return .= '</div>';

		return $return;
	}

	/**
	 * @return string
	 */
	private function accordion()
	{
		$return = '<div class="acpt-admin-accordion-wrapper" style="max-width: 1400px;" id="'.$this->groupModel->getId().'">';

		foreach ($this->groupModel->getBoxes() as $index => $metaBoxModel){

            $userId = $this->user ? $this->user->ID : null;
            $isVisible = BoxVisibilityChecker::check(Visibility::IS_BACKEND, $metaBoxModel, $userId);

            if($isVisible){
                $rows = $this->fieldRows($metaBoxModel->getFields());

                if(!empty($rows)){
                    $return .= '<div class="acpt-metabox acpt-admin-accordion-item '.($index === 0 ? 'active' : '').'" data-target="'.$metaBoxModel->getId().'">';
                    $return .= '<div class="acpt-admin-accordion-title">';
                    $return .= $metaBoxModel->getUiName();
                    $return .= '</div>';

                    $return .= '<div id="'.$metaBoxModel->getId().'" class="acpt-admin-accordion-content">';
                    $return .= '<div class="acpt-user-meta-box-wrapper" id="user-meta-box-'. $metaBoxModel->getId().'">';

                    foreach ($rows as $row){
                        $return .= "<div class='acpt-admin-meta-row ".($row['isVisible'] == 0 ? ' hidden' : '')."'>";

                        foreach ($row['fields'] as $field){
                            $return .= $field;
                        }

                        $return .= "</div>";
                    }

                    $return .= '</div>';
                    $return .= '</div>';
                    $return .= '</div>';
                }
            }
		}

		$return .= '</div>';


		return $return;
	}

	/**
	 * @return string
	 */
	private function verticalTabs()
	{
		$return = '<div class="acpt-metabox acpt-admin-vertical-tabs-wrapper" id="'.$this->groupModel->getId().'" style="max-width: 1400px;">';
		$return .= '<div class="acpt-admin-vertical-tabs">';

		foreach ($this->groupModel->getBoxes() as $index => $metaBoxModel){
            $userId = $this->user ? $this->user->ID : null;
            $isVisible = BoxVisibilityChecker::check(Visibility::IS_BACKEND, $metaBoxModel, $userId);

            if($isVisible){
                $rows = $this->fieldRows($metaBoxModel->getFields());

                if(!empty($rows)){
                    $return .= '<div class="acpt-admin-vertical-tab '.($index === 0 ? 'active' : '').'" data-target="'.$metaBoxModel->getId().'">';
                    $return .= $metaBoxModel->getUiName();
                    $return .= '</div>';
                }
            }
		}

		$return .= '</div>';
		$return .= '<div class="acpt-admin-vertical-panels">';

		foreach ($this->groupModel->getBoxes() as $index => $metaBoxModel){

            $userId = $this->user ? $this->user->ID : null;
            $isVisible = BoxVisibilityChecker::check(Visibility::IS_BACKEND, $boxModel, $userId);

            if($isVisible){
                $rows = $this->fieldRows($metaBoxModel->getFields());

                if(!empty($rows)){
                    $return .= '<div id="'.$metaBoxModel->getId().'" class="acpt-admin-vertical-panel '.($index === 0 ? 'active' : '').'">';

                    foreach ($rows as $row){
                        $return .= "<div class='acpt-admin-meta-row ".($row['isVisible'] == 0 ? ' hidden' : '')."'>";

                        foreach ($row['fields'] as $field){
                            $return .= $field;
                        }

                        $return .= "</div>";
                    }

                    $return .= '</div>';
                }
            }
		}

		$return .= '</div>';
		$return .= '</div>';

		return $return;
	}

	/**
	 * @return string
	 */
	private function horizontalTabs()
	{
		$return = '<div class="acpt-metabox acpt-admin-horizontal-tabs-wrapper" id="'.$this->groupModel->getId().'">';
		$return .= '<div class="acpt-admin-horizontal-tabs">';

		foreach ($this->groupModel->getBoxes() as $index => $metaBoxModel){

		    $userId = $this->user ? $this->user->ID : null;
            $isVisible = BoxVisibilityChecker::check(Visibility::IS_BACKEND, $metaBoxModel, $userId);

            if($isVisible){
                $rows = $this->fieldRows($metaBoxModel->getFields());

                if(!empty($rows)){
                    $return .= '<div class="acpt-admin-horizontal-tab '.($index === 0 ? 'active' : '').'" data-target="'.$metaBoxModel->getId().'">';
                    $return .= $metaBoxModel->getUiName();
                    $return .= '</div>';
                }
            }
		}

		$return .= '</div>';
		$return .= '<div class="acpt-admin-horizontal-panels" style="max-width: 1400px;">';

		foreach ($this->groupModel->getBoxes() as $index => $metaBoxModel){

		    $userId = $this->user ? $this->user->ID : null;
            $isVisible = BoxVisibilityChecker::check(Visibility::IS_BACKEND, $metaBoxModel, $userId);

            if($isVisible){
                $rows = $this->fieldRows($metaBoxModel->getFields());

                if(!empty($rows)){
                    $return .= '<div id="'.$metaBoxModel->getId().'" class="acpt-admin-horizontal-panel '.($index === 0 ? 'active' : '').'">';

                    foreach ($rows as $row){
                        $return .= "<div class='acpt-admin-meta-row ".($row['isVisible'] == 0 ? ' hidden' : '')."'>";

                        foreach ($row['fields'] as $field){
                            $return .= $field;
                        }

                        $return .= "</div>";
                    }

                    $return .= '</div>';
                }
            }
		}

		$return .= '</div>';
		$return .= '</div>';

		return $return;
	}

	/**
	 * @param $fields
	 *
	 * @return array
	 */
	private function fieldRows($fields)
	{
		$rows = Fields::extractFieldRows($fields);
		$fieldRows = [];
		$visibleFieldsTotalCount = 0;

		// build the field rows array
		foreach ($rows as $index => $row){

			$visibleFieldsRowCount = 0;

			foreach ($row as $field){
				$userFieldGenerator = new UserMetaFieldGenerator($field, $this->user);
				$userField = $userFieldGenerator->generate();

				if($userField){
					if($userField->isVisible()){
						$visibleFieldsTotalCount++;
						$visibleFieldsRowCount++;
					}

					$fieldRows[$index]['fields'][] = $userField->render();
					$fieldRows[$index]['isVisible'] = $visibleFieldsRowCount;
				}
			}
		}

		if($visibleFieldsTotalCount > 0){
			return $fieldRows;
		}

		return [];
	}
}
