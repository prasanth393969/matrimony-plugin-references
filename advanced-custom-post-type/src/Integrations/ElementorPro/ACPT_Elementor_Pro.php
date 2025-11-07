<?php

namespace ACPT\Integrations\ElementorPro;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\ElementorPro\Constants\TagsConstants;
use ACPT\Integrations\ElementorPro\DisplayConditions\ACPTFieldsCondition;
use Elementor\Core\DynamicTags\Manager as DynamicTagsManager;
use ElementorPro\Modules\DisplayConditions\Classes\Conditions_Manager;

class ACPT_Elementor_Pro extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "elementor_pro";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		$isActive = is_plugin_active( 'elementor-pro/elementor-pro.php' );

		if(!$isActive){
			return false;
		}

		return ACPT_ENABLE_META == 1 and $isActive;
	}

	/**
	 * @inheritDoc
	 */
	protected function runIntegration()
	{
		add_action( 'elementor/dynamic_tags/register', [$this, 'registerTags'] );
		add_action( 'elementor/display_conditions/register', [$this, 'registerDynamicConditions'] );
	}

    /**
     * @param Conditions_Manager $dynamic_tags_manager
     */
    public function registerDynamicConditions( Conditions_Manager $dynamic_tags_manager )
    {
        $condition = new ACPTFieldsCondition();
        $dynamic_tags_manager->register_condition_instance($condition);
    }

	/**
	 * @param DynamicTagsManager $dynamic_tags_manager
	 */
	public function registerTags( DynamicTagsManager $dynamic_tags_manager )
	{
		$dynamic_tags_manager->register_group(
			TagsConstants::GROUP_NAME,
			[
				'title' => esc_html__( 'ACPT fields', ACPT_PLUGIN_NAME )
			]
		);

		$fields = DynamicDataProvider::getInstance()->getFields();

		if(!empty($fields)){
			foreach ($fields as $tag => $tags){
				$dynamic_tags_manager->register(new $tag());
			}
		}
	}
}