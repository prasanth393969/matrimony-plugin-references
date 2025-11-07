<?php

namespace ACPT\Core\Generators\OptionPage;

use ACPT\Constants\MetaTypes;
use ACPT\Core\CQRS\Command\SaveOptionPageMetaCommand;
use ACPT\Core\Generators\AbstractGenerator;
use ACPT\Core\Models\OptionPage\OptionPageModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Includes\ACPT_Loader;
use ACPT\Utils\PHP\Session;
use ACPT\Utils\Wordpress\Nonce;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\UserPermissions;

/**
 * *************************************************
 * OptionPageGenerator class
 * *************************************************
 *
 * @author Mauro Cassani
 * @link https://github.com/mauretto78/
 */
class OptionPageGenerator extends AbstractGenerator
{
	private const SESSION_KEY = 'option_page_save_outcome';

	/**
	 * @var OptionPageModel
	 */
	private OptionPageModel $optionPageModel;

	/**
	 * @var ACPT_Loader
	 */
	private ACPT_Loader $loader;

	/**
	 * @var bool
	 */
	private $lazy;

	/**
	 * OptionPageGenerator constructor.
	 *
	 * @param ACPT_Loader $loader
	 * @param OptionPageModel $optionPageModel
	 * @param bool $lazy
	 */
	public function __construct(ACPT_Loader $loader, OptionPageModel $optionPageModel, $lazy = false)
	{
		$this->optionPageModel = $optionPageModel;
		$this->loader = $loader;
		$this->lazy = $lazy;
	}

	/**
	 * Register page
	 */
	public function registerPage()
	{
		$this->loader->addAction('admin_menu', $this, 'addMenuPage', 99);
	}

	/**
	 * Call add_menu_page Wordpress function
	 */
	public function addMenuPage()
	{
		add_menu_page(
			Translator::translateString($this->optionPageModel->getPageTitle()),
			Translator::translateString($this->optionPageModel->getMenuTitle()),
			$this->capability($this->optionPageModel),
			$this->optionPageModel->getMenuSlug(),
			function () {
				return $this->lazyRenderPage($this->optionPageModel);
			},
			$this->optionPageModel->renderIcon(),
			$this->optionPageModel->getPosition()
		);

		foreach ($this->optionPageModel->getChildren() as $childPageModal){
			add_submenu_page(
				$this->optionPageModel->getMenuSlug(),
				Translator::translateString($childPageModal->getPageTitle()),
				Translator::translateString($childPageModal->getMenuTitle()),
				$this->capability($childPageModal),
				$childPageModal->getMenuSlug(),
				function () use ($childPageModal) {
					return $this->lazyRenderPage($childPageModal);
				},
				$childPageModal->getPosition()
			);
		}
	}

	/**
	 * @param OptionPageModel $optionPageModel
	 *
	 * @return string
	 */
	private function capability(OptionPageModel $optionPageModel)
	{
		return ($optionPageModel->hasPermissions()) ? "read_".$optionPageModel->getMenuSlug() : $optionPageModel->getCapability();
	}

	/**
	 * @param OptionPageModel $optionPageModel
	 *
	 * @return string|void
	 * @throws \Exception
	 */
	private function lazyRenderPage(OptionPageModel $optionPageModel)
	{
		if($this->lazy){
			return '';
		}

		return $this->renderPage($optionPageModel);
	}

	/**
	 * @param OptionPageModel $optionPageModel
	 *
	 * @throws \Exception
	 */
	public function renderPage(OptionPageModel $optionPageModel)
	{
		$permissions = $optionPageModel->userPermissions();

		Session::start();

		$this->enqueueScripts("save-option-page");
		$nonceAction = $this->nonceAction($optionPageModel);
		$sessionKey = self::SESSION_KEY;

		wp_editor('', 'no-show'); // Hack for enqueuing WP Editor

		if(isset($_POST[$nonceAction]) and Nonce::verify($_POST[$nonceAction])){
			unset($_POST[$nonceAction]);
			unset($_POST['_wp_http_referer']);

			$command = new SaveOptionPageMetaCommand($sessionKey, $optionPageModel, $_POST);
			$command->execute();
			$this->safeRedirect($optionPageModel->getMenuSlug());
		}

		$return = '<div class="wrap" style="max-width: 1400px">';
		$return .= '<div id="no-show"></div>'; // Hack for enqueuing WP Editor
		$return .= '<h1 style="margin-bottom: 10px" class="wp-heading-inline">'.Translator::translateString($optionPageModel->getPageTitle()).'</h1>';

		if($optionPageModel->getDescription()){
			$return .= '<p>'.Translator::translateString($optionPageModel->getDescription()) . '</p>';
		}

		// flush messages
		if(!empty($_SESSION[$sessionKey])){
			foreach ($_SESSION[$sessionKey] as $level => $messages) {
				foreach ($messages as $message){
					$return .= '<div class="notice notice-'.$level.'"><p>'.$message.'</p></div>';
				}
			}

			$_SESSION[$sessionKey] = [];
		}

		$metaGroups = MetaRepository::get([
			'belongsTo' => MetaTypes::OPTION_PAGE,
			'find' => $optionPageModel->getMenuSlug(),
            'clonedFields' => true,
		]);

		$return .= '<form method="post" action="">';
		$return .= '<input type="hidden" name="option_page_id" value="'.$optionPageModel->getId().'">';
		$emptyBoxesCount = 0;

		foreach ($metaGroups as $metaGroup){
			if(count($metaGroup->getBoxes()) > 0){
				$groupGenerator = new OptionPageMetaGroupGenerator($metaGroup, $optionPageModel, $permissions);
				$group = $groupGenerator->render();
				$return .= $group;

				preg_match_all('/acpt-admin-meta-wrapper/', $group, $fieldMatches);

				if(!empty($fieldMatches[0])){
					$emptyBoxesCount++;
				}
			}
		}

		if($permissions['edit'] === true){
			if(!empty($metaGroups) and $emptyBoxesCount > 0){
				$return .= '<div id="acpt-option-page-buttons" class="acpt-buttons-group">';
				$return .= '<button id="save-option-page" class="button button-primary">'.Translator::translate('Save').'</button>';
				$return .= '<input class="button button-secondary" type="reset" value="Reset">';
				$return .= '</div>';
			}
		}

		// flush messages
		if(empty($metaGroups) or $emptyBoxesCount === 0){
			$return .= '<div class="notice notice-warning"><p>'.Translator::translate('No meta group found.').'</p></div>';
		}

		if($permissions['edit'] === true){
			$return .= Nonce::field($this->nonceAction($optionPageModel));
		}

		$return .= '</form>';
		$return .= '</div>';

		echo $return;
	}

	/**
	 * @param OptionPageModel $optionPageModel
	 *
	 * @return string
	 */
	private function nonceAction(OptionPageModel $optionPageModel)
	{
		return 'save-options_'.$optionPageModel->getId();
	}

	/**
	 * Safe redirect
	 *
	 * @param $menuSlug
	 */
	private function safeRedirect($menuSlug)
	{
		wp_safe_redirect(
			esc_url(
				site_url( '/wp-admin/admin.php?page=' . $menuSlug )
			)
		);

		exit();
	}
}