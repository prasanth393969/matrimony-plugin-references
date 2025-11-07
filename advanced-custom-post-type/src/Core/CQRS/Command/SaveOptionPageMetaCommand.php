<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\OptionPage\OptionPageModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\Wordpress\Translator;

class SaveOptionPageMetaCommand extends AbstractSaveMetaCommand implements CommandInterface
{
	/**
	 * @var string
	 */
	protected $sessionKey;

	/**
	 * @var OptionPageModel
	 */
	protected OptionPageModel $optionPageModel;

	/**
	 * SaveOptionPageMetaCommand constructor.
	 *
	 * @param $sessionKey
	 * @param OptionPageModel $optionPageModel
	 * @param array $data
	 */
	public function __construct($sessionKey, OptionPageModel $optionPageModel, array $data = [])
	{
		parent::__construct($data);
		$this->sessionKey = $sessionKey;
		$this->optionPageModel = $optionPageModel;
	}

	/**
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function execute()
	{
		$metaGroups = MetaRepository::get([
			'belongsTo' => MetaTypes::OPTION_PAGE,
			'find' => $this->optionPageModel->getMenuSlug(),
            'clonedFields' => true,
		]);

		foreach ($metaGroups as $metaGroup){
			foreach ($metaGroup->getBoxes() as $boxModel) {
				foreach ($boxModel->getFields() as $fieldModel) {
					if($this->hasField($fieldModel)){
						$fieldModel->setBelongsToLabel(MetaTypes::OPTION_PAGE);
						$fieldModel->setFindLabel($this->optionPageModel->getMenuSlug());
						$elementId = Strings::toDBFormat($this->optionPageModel->getMenuSlug()) . '_' . Strings::toDBFormat( $fieldModel->getBox()->getName() ) . '_' . Strings::toDBFormat($fieldModel->getName() );
						$this->saveField($fieldModel, $elementId, MetaTypes::OPTION_PAGE);
					}
				}
			}
		}

		$_SESSION[$this->sessionKey]['success'][] = Translator::translate('Data saved correctly');
	}
}
