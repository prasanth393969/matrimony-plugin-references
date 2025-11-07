<?php

namespace ACPT\Core\Shortcodes\Form;

use ACPT\Core\Generators\Form\FormGenerator;
use ACPT\Core\Repository\FormRepository;
use ACPT\Utils\Wordpress\WPUtils;

class FormShortcode
{
	/**
	 * @param $atts
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function render($atts)
	{
		if(!ACPT_ENABLE_FORMS){
			return null;
		}

		if(!ACPT_IS_LICENSE_VALID){
			return null;
		}

		if(!isset($atts['id'])){
			return null;
		}

		$formModel = FormRepository::getByKey($atts['id']);

		if($formModel === null){
			return null;
		}

		if(isset($atts['pid']) and !WPUtils::postExists($atts['pid'])){
			return "The post ID is not valid.";
		}

		if(isset($atts['tid']) and !WPUtils::termExists($atts['tid'])){
			return "The term ID is not valid.";
		}

		if(isset($atts['uid']) and !WPUtils::userExists($atts['uid'])){
			return "The user ID is not valid.";
		}

		$pid = $atts['pid'] ?? null;
		$tid = $atts['tid'] ?? null;
		$uid = $atts['uid'] ?? null;

		$formBuilder = new FormGenerator($formModel, $pid, $tid, $uid);

		return $formBuilder->render();
	}
}