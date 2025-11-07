<?php

namespace ACPT\Core\Generators\Form;

use ACPT\Constants\FormAction;
use ACPT\Constants\MetaTypes;
use ACPT\Core\CQRS\Command\HandleFormSubmissionCommand;
use ACPT\Core\Generators\Form\Fields\AbstractField;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Form\FormModel;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\PHP\Server;
use ACPT\Utils\PHP\Session;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\Nonce;

class FormGenerator
{
	const SUCCESS_SESSION_KEY = 'acpt_form_success';
	const ERRORS_SESSION_KEY = 'acpt_form_errors';

	/**
	 * @var FormModel
	 */
	private $formModel;

	/**
	 * @var null
	 */
	private $postId;

	/**
	 * @var null
	 */
	private $userId;

	/**
	 * @var null
	 */
	private $termId;

	/**
	 * FormGenerator constructor.
	 *
	 * @param FormModel $formModel
	 * @param null $postId
	 * @param null $termId
	 * @param null $userId
	 */
	public function __construct(FormModel $formModel, $postId = null, $termId = null, $userId = null)
	{
		$this->formModel = $formModel;
		$this->postId    = $postId;
		$this->userId    = $userId;
		$this->termId    = $termId;

		$locationBelong = ( $formModel->getMetaDatum('fill_meta_location_belong') !== null) ? $formModel->getMetaDatum('fill_meta_location_belong')->getValue() : null; // ---> customPostType
		$locationItem = ( $formModel->getMetaDatum('fill_meta_location_item') !== null) ? $formModel->getMetaDatum('fill_meta_location_item')->getValue() : null;

		if($this->postId === null and $locationBelong === MetaTypes::CUSTOM_POST_TYPE){
			$this->postId = $locationItem;
		}

		if($this->termId === null and $locationBelong === MetaTypes::TAXONOMY){
			$this->termId = $locationItem;
		}

		if($this->userId === null and $locationBelong === MetaTypes::USER){
			$this->userId = $locationItem;
		}
	}


	/**
	 * @return string
	 * @throws \Exception
	 */
	public function render()
	{
		ob_start();
		Session::start();

		if(
            isset($_POST['acpt_form_submission']) and
            $_POST['acpt_form_submission'] == 1 and
            $_POST['acpt_form_id'] === $this->formModel->getId()
        ){

            $savedRedirectTo = $this->formModel->getMetaDatum('redirect_to');
			$redirectTo = (!empty($savedRedirectTo)) ? $savedRedirectTo->getValue() : $_POST['_wp_http_referer'];

			unset($_POST['acpt_form_submission']);
			unset($_POST['acpt_form_id']);
			unset($_POST['_wp_http_referer']);

			$command = new HandleFormSubmissionCommand($this->formModel, $this->postId, $this->termId, $this->userId, $_POST, $_FILES);
			$submission = $command->execute();

			if($submission['success']){
				$outcomeMessage = ($this->formModel->getMetaDatum('outcome_message') !== null) ? $this->formModel->getMetaDatum('outcome_message')->getValue() : "The form was successfully submitted";
				Session::set(self::SUCCESS_SESSION_KEY, [$outcomeMessage]);
			} else {
				Session::set(self::ERRORS_SESSION_KEY, $submission['errors']);
			}

			$this->safeRedirect($redirectTo);
		}

		$this->enqueueAssets();

		$form = $this->showGenericFormErrors();
		$form .= $this->renderOpeningFormTag();
		$form .= Nonce::field($this->formModel->getId());
		$form .= '<input type="hidden" name="acpt_form_submission" value="1" />';
		$form .= '<input type="hidden" name="acpt_form_id" value="'.$this->formModel->getId().'" />';
		$form .= '<input type="hidden" name="redirect_to" value="'.$this->formModel->getId().'" />';
		$form .= '<div class="acpt-form">';
		$form .= '<div class="acpt-container">';

		foreach ($this->formModel->getFields() as $field){
			$form .= $this->renderField($field);
		}

		$form .= '</div>';
		$form .= '</div>';
		$form .= '</form>';

		$this->flushSession();

		ob_end_clean();

		return $form;
	}

	/**
     * Manage the redirect
     *
	 * @param $redirectTo
	 */
	private function safeRedirect($redirectTo)
	{
	    // if redirectTo is empty, redirect to the referer page
	    if(empty($redirectTo)){
            wp_safe_redirect($_SERVER['HTTP_REFERER']);
            exit();
        }

	    $theURLContainsSiteUrl = Strings::contains(site_url(), $redirectTo);

        // In case of external redirect, like https://google.com,
        // flush the cache and then redirect
        if(!$theURLContainsSiteUrl){
            Session::flush(self::SUCCESS_SESSION_KEY);
            Session::flush(self::ERRORS_SESSION_KEY);
            $this->redirectToExternalUrl($redirectTo);
        }

        $redirectTo = str_replace(site_url(), "", $redirectTo);

        // If the redirection is not on the same page,
        // flush the cache and then redirect
        if($redirectTo !== ""){
            Session::flush(self::SUCCESS_SESSION_KEY);
            Session::flush(self::ERRORS_SESSION_KEY);
        }

		wp_safe_redirect(esc_url(site_url($redirectTo)));
		exit();
	}

    /**
     * @param $redirectTo
     */
    private function redirectToExternalUrl($redirectTo)
    {
        wp_redirect($redirectTo);
        exit();
    }

	/**
	 * Display general errors and success messages
	 *
	 * @return string
	 */
	private function showGenericFormErrors()
	{
		$messages = '<div class="acpt-form-messages">';

		if(Session::has(self::SUCCESS_SESSION_KEY)){
			foreach (Session::get(self::SUCCESS_SESSION_KEY) as $successMessage){
				$messages .= '<div class="acpt-message acpt-success-message">' .$successMessage. '</div>';
			}
		}

		if(Session::has(self::ERRORS_SESSION_KEY)){
			foreach (Session::get(self::ERRORS_SESSION_KEY) as $id => $errorMessages){
				if($id === $this->formModel->getId()){
					if(is_array($errorMessages)){
						foreach ($errorMessages as $errorMessage){
							$messages .= '<div id="'.$id.'" class="acpt-message acpt-error-message">' .$errorMessage. '</div>';
						}
					} elseif(is_string($errorMessages)) {
                        $messages .= '<div id="'.$id.'" class="acpt-message acpt-error-message">' .$errorMessages. '</div>';
                    }
				}
			}
		}

		$messages .= '</div>';

		return $messages;
	}

	/**
	 * Flush the session
	 */
	private function flushSession()
	{
		Session::flush(self::SUCCESS_SESSION_KEY);
		Session::flush(self::ERRORS_SESSION_KEY);
	}

	/**
	 * @return string
	 */
	private function renderOpeningFormTag()
	{
		switch ($this->formModel->getAction()){
			case FormAction::FILL_META:
			case FormAction::PHP:
				return '<form id="'.$this->formModel->getKey().'" action="" method="post" enctype="multipart/form-data">';

			case FormAction::CUSTOM:
				$action = $this->formModel->getMetaDatum('custom_action') !== null ? $this->formModel->getMetaDatum('custom_action')->getValue() : '';
				$method = $this->formModel->getMetaDatum('custom_method') !== null ? $this->formModel->getMetaDatum('custom_method')->getValue() :  'POST';

				return '<form id="'.$this->formModel->getKey().'" data-acpt-custom-form action="'.$action.'" method="'.$method.'" enctype="multipart/form-data">';

			case FormAction::AJAX:
				return '<form id="'.$this->formModel->getKey().'" data-acpt-ajax-form action="">';
		}
	}

	/**
	 * @param FormFieldModel $fieldModel
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function renderField(FormFieldModel $fieldModel)
	{
		$class = 'ACPT\\Core\\Generators\\Form\\Fields\\'.$fieldModel->getType().'Field';
        $value = null;

		if(class_exists($class)){
			/** @var AbstractField $field */
			$field = new $class($this->formModel, $fieldModel, $this->postId, $this->termId, $this->userId);

			if($fieldModel->getMetaField() !== null){
                if(!empty($this->postId)){
                    $value = Meta::fetch($this->postId, MetaTypes::CUSTOM_POST_TYPE, $fieldModel->getMetaField()->getDbName());
                }

                if(!empty($this->termId)){
                    $value = Meta::fetch($this->termId, MetaTypes::TAXONOMY, $fieldModel->getMetaField()->getDbName());
                }

                if(!empty($this->userId)){
                    $value = Meta::fetch($this->userId, MetaTypes::USER, $fieldModel->getMetaField()->getDbName());
                }
            }

            if($value !== '' and $value !== null){
                $field->setValue($value);
            }

			return $field->renderElement();
		}
	}

	/**
	 * Enqueue necessary assets
	 *
	 * @param null $action
	 */
	private function enqueueAssets($action = null)
	{
        $home = get_option('home');
        $pluginsUrl = plugins_url();

        if(Server::isSecure()){
            $home = Url::secureUrl($home);
            $pluginsUrl = Url::secureUrl($pluginsUrl);
        }

        $belongsTo = $this->formModel->getMetaDatum("fill_meta_location_belong") ? $this->formModel->getMetaDatum("fill_meta_location_belong")->getValue() : null;
        $elementId = $this->postId ?? $this->termId ?? $this->userId;

        wp_register_script( 'globals-acpt-run', '', [], '', true );
        wp_enqueue_script('globals-acpt-run');
        wp_add_inline_script( 'globals-acpt-run', '
            document.globals = {site_url: "'.$home.'", plugins_url: "'.$pluginsUrl.'", ajax_url: "'. esc_js( admin_url( 'admin-ajax.php', 'relative' ) ) .'" };
		');

        // front-end CSS
		wp_register_style( 'front-end-css', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/front-end.css' : 'advanced-custom-post-type/assets/static/css/front-end.min.css'), [], ACPT_PLUGIN_VERSION );
		wp_enqueue_style( 'front-end-css' );
		$this->injectCSSVariables();

		// front-end JS
		wp_register_script('front-end-js',  plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/front-end.js' : 'advanced-custom-post-type/assets/static/js/front-end.min.js') );
		wp_enqueue_script('front-end-js');
		wp_scripts()->add_data('front-end-js', 'type', 'module');

        // validation
		wp_register_script('ACPTFormValidator',  plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/ACPTFormValidator.js' : 'advanced-custom-post-type/assets/static/js/ACPTFormValidator.min.js') );
		wp_enqueue_script('ACPTFormValidator');

		wp_register_script( 'ACPTFormValidator-run', '', [], '', true );
		wp_enqueue_script('ACPTFormValidator-run');
        wp_add_inline_script( 'ACPTFormValidator-run', '
			window.addEventListener("load", () => {
			    if(typeof validator === "undefined"){
                  const validator = new ACPTFormValidator("'.$action.'");
                  validator.run();
                }
			});
		');

        // conditional rendering
        if($elementId !== null and $belongsTo !== null){

            wp_register_script('ACPTConditionalRules',  plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/ACPTConditionalRules.js' : 'advanced-custom-post-type/assets/static/js/ACPTConditionalRules.min.js') );
            wp_enqueue_script('ACPTConditionalRules');

            wp_register_script( 'ACPTConditionalRules-run', '', [], '', true );
            wp_enqueue_script('ACPTConditionalRules-run');
            wp_add_inline_script( 'ACPTConditionalRules-run', '
			    if(typeof conditionalRules === "undefined"){
				    const conditionalRules = new ACPTConditionalRules("'.Url::fullUrl().'", "'.$action.'", "'.$belongsTo.'", "'.$elementId.'");
				    conditionalRules.run();
				}
			');
        }

		// Custom action form handling
		if($this->formModel->getAction() === FormAction::CUSTOM){

			wp_register_script( 'custom-form-handling', '', [], '', true );
			wp_enqueue_script('custom-form-handling');
			wp_add_inline_script( 'custom-form-handling', '
					 '.$this->wpAjaxRequest().'
				
					// Custom form submission
					const forms = document.body.querySelectorAll("[data-acpt-custom-form]");
					forms.forEach((form) => {
						form.addEventListener("submit", (e) => {
							e.preventDefault();
							e.stopPropagation();
							
							const formData = new FormData(e.target);
							const dataObject = Object.fromEntries(formData.entries());
							
							// save form submission
							wpAjaxRequest("saveFormSubmissionAction", dataObject);
							
							form.submit();
						});
					});
				');
		}

		// Ajax form handling
		if($this->formModel->getAction() === FormAction::AJAX){

			$ajaxAction = $this->formModel->getMetaDatum("ajax_action") !== null ? $this->formModel->getMetaDatum("ajax_action")->getValue() : null;
			$ajaxHandling = $this->formModel->getMetaDatum("ajax_handling") !== null ? $this->formModel->getMetaDatum("ajax_handling")->getValue() : null;

			if($ajaxAction !== null and $ajaxHandling !== null){
				wp_register_script( 'ajax-form-handling', '', [], '', true );
				wp_enqueue_script('ajax-form-handling');
				wp_add_inline_script( 'ajax-form-handling', '
					 '.$this->wpAjaxRequest().'
				
					// AJAX form submission
					const forms = document.body.querySelectorAll("[data-acpt-ajax-form]");
					forms.forEach((form) => {
						form.addEventListener("submit", (e) => {
							e.preventDefault();
							e.stopPropagation();
							
							const formData = new FormData(e.target);
							const dataObject = Object.fromEntries(formData.entries());
							
							// save form submission
							wpAjaxRequest("saveFormSubmissionAction", dataObject);
							
							// call custom AJAX route
							wpAjaxRequest("'.$ajaxAction.'", dataObject)
				                .then(data => {
				                    '.$ajaxHandling.'
				                })
				            ;
						});
					});
				');
			}
		}
	}

    /**
     * Inject CSS variables
     */
	private function injectCSSVariables()
    {
        $id = "css_variables_".$this->formModel->getName();
        $css = (!empty($this->formModel->getMetaDatum("css_variables"))) ? $this->formModel->getMetaDatum("css_variables")->getValue() : null;

        if($css !== null){
            $css = unserialize($css);
        }

        if(!is_array($css)){
            return;
        }

        $props = [
            'border_radius',
            'border_thickness',
            'border_color',
            'button_color',
            'error_color',
            'gray_color',
            'input_bg',
            'input_color',
            'label_color',
            'label_font_weight',
            'light_gray_color',
            'primary_color',
            'primary_hover_color',
            'success_color',
            'warning_color',
            'half_gap',
            'gap',
            'font_size_m',
            'font_size_s',
            'font_size_xs',
            'font_size_l',
            'font_size_xl',
        ];

        if(!empty(array_diff(array_keys($css), $props))){
            return;
        }

        wp_register_style($id, '', [], ACPT_PLUGIN_VERSION, true );
        wp_enqueue_style($id);
        wp_add_inline_style( $id, "
            :root {
                /* spacing */
                --acpt-half-gap: ".$css['half_gap']['light'].";
                --acpt-gap: ".$css['gap']['light'].";
            
                /* border */
                --acpt-border-thickness: ".$css['border_thickness']['light'].";
                --acpt-border-radius: ".$css['border_radius']['light'].";
            
                /* typography */
                --acpt-label-font-weight: ".$css['label_font_weight']['light'].";
                
                /* font size */
                --acpt-font-size-m: ".$css['font_size_m']['light'].";
                --acpt-font-size-s: ".$css['font_size_s']['light'].";
                --acpt-font-size-xs: ".$css['font_size_xs']['light'].";
                --acpt-font-size-l: ".$css['font_size_l']['light'].";
                --acpt-font-size-xl: ".$css['font_size_xl']['light'].";
            
                /* colors */
                --acpt-primary-color: ".$css['primary_color']['light'].";
                --acpt-primary-hover-color: ".$css['primary_hover_color']['light'].";
                --acpt-success-color: ".$css['success_color']['light'].";
                --acpt-warning-color: ".$css['warning_color']['light'].";
                --acpt-error-color: ".$css['error_color']['light'].";
                --acpt-border-color: ".$css['border_color']['light'].";
                --acpt-input-bg: ".$css['input_bg']['light'].";
                --acpt-label-color: ".$css['label_color']['light'].";
                --acpt-input-color: ".$css['input_color']['light'].";
                --acpt-gray-color: ".$css['gray_color']['light'].";
                --acpt-light-gray-color: ".$css['light_gray_color']['light'].";
                --acpt-button-color: ".$css['button_color']['light'].";
            }
            
            @media (prefers-color-scheme: dark) {
                :root {
                    /* spacing */
                    --acpt-half-gap: ".$css['half_gap']['dark'].";
                    --acpt-gap: ".$css['gap']['dark'].";
                    
                    /* border */
                    --acpt-border-thickness: ".$css['border_thickness']['dark'].";
                    --acpt-border-radius: ".$css['border_radius']['dark'].";
                    
                    /* typography */
                    --acpt-label-font-weight: ".$css['label_font_weight']['dark'].";
                    
                    /* font size */
                    --acpt-font-size-m: ".$css['font_size_m']['dark'].";
                    --acpt-font-size-s: ".$css['font_size_s']['dark'].";
                    --acpt-font-size-xs: ".$css['font_size_xs']['dark'].";
                    --acpt-font-size-l: ".$css['font_size_l']['dark'].";
                    --acpt-font-size-xl: ".$css['font_size_xl']['dark'].";
                
                    /* colors */
                    --acpt-primary-color: ".$css['primary_color']['dark'].";
                    --acpt-primary-hover-color: ".$css['primary_hover_color']['dark'].";
                    --acpt-success-color: ".$css['success_color']['dark'].";
                    --acpt-warning-color: ".$css['warning_color']['dark'].";
                    --acpt-error-color: ".$css['error_color']['dark'].";
                    --acpt-border-color: ".$css['border_color']['dark'].";
                    --acpt-input-bg: ".$css['input_bg']['dark'].";
                    --acpt-label-color: ".$css['label_color']['dark'].";
                    --acpt-input-color: ".$css['input_color']['dark'].";
                    --acpt-gray-color: ".$css['gray_color']['dark'].";
                    --acpt-light-gray-color: ".$css['light_gray_color']['dark'].";
                    --acpt-button-color: ".$css['button_color']['dark'].";
                }
            }
        ");
    }

	/**
	 * @return string
	 */
	private function wpAjaxRequest()
	{
		return '
			/**
		     *
		     * @param action
		     * @param data
		     * @return {Promise<any>}
		     */
		    const wpAjaxRequest = async (action, data) => {
		
		        let formData;
		        formData = (data instanceof FormData) ? data : new FormData();
		        formData.append("action", action);
		        formData.append("data", JSON.stringify(data));
		
		        const baseAjaxUrl = (typeof ajaxurl === "string") ? ajaxurl : "/wp-admin/admin-ajax.php";
		
		        let response = await fetch(baseAjaxUrl, {
		            method: "POST",
		            body: formData
		        });
		
		        return await response.json();
		    };
		';
	}
}