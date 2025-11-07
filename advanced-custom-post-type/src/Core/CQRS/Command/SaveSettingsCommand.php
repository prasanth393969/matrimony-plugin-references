<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Settings\SettingsModel;
use ACPT\Core\Repository\SettingsRepository;
use ACPT\Includes\ACPT_DB;
use ACPT\Integrations\WooCommerce\ACPT_WooCommerce;

class SaveSettingsCommand implements CommandInterface
{
	/**
	 * @var array
	 */
	private array $settings;

	/**
	 * SaveOptionPagesCommand constructor.
	 *
	 * @param array $settings
	 */
	public function __construct(array $settings = [])
	{
		$this->settings = $settings;
	}

	/**
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function execute()
	{
	    $deleteUnusedTables = false;

		foreach ($this->settings as $key => $value){
			$id = (SettingsRepository::getSingle($key) !== null) ? SettingsRepository::getSingle($key)->getId() : Uuid::v4();
            $value = (is_array($value)) ? json_encode($value) : $value;

			$model = SettingsModel::hydrateFromArray([
				'id' => $id,
				'key' => $key,
				'value' => $value
			]);

			SettingsRepository::save($model);

			if($key === SettingsModel::DELETE_UNUSED_TABLES and $value == 1){
                $deleteUnusedTables = true;
            }
		}

		if($deleteUnusedTables){
		    $this->removeOrCreateFeatureTables($this->settings);
        } else {
            ACPT_DB::removeOrCreateFeatureTables(SettingsModel::ENABLE_META);
            ACPT_DB::removeOrCreateFeatureTables(SettingsModel::ENABLE_FORMS);
            ACPT_DB::removeOrCreateFeatureTables(SettingsModel::ENABLE_OP);
            ACPT_DB::removeOrCreateFeatureTables(SettingsModel::ENABLE_CPT);
            ACPT_DB::removeOrCreateFeatureTables(SettingsModel::ENABLE_TAX);
            ACPT_DB::removeOrCreateFeatureTables(SettingsModel::ENABLE_BLOCKS);
            ACPT_DB::removeOrCreateFeatureTables("woocommerce");
        }
	}

    /**
     * Delete unused tables
     *
     * @param array $settings
     *
     * @throws \Exception
     */
	private function removeOrCreateFeatureTables($settings = [])
    {
        foreach ($settings as $key => $value){
            $action = $value == 1 ? "create" : "delete";
            ACPT_DB::removeOrCreateFeatureTables($key, $action);
        }

        // WooCommerce
        $wooCommerceAction = !ACPT_WooCommerce::active() ? "delete" : "create";
        ACPT_DB::removeOrCreateFeatureTables("woocommerce", $wooCommerceAction);
    }
}