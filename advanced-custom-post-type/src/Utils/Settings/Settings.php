<?php

namespace ACPT\Utils\Settings;

use ACPT\Core\Models\Settings\SettingsModel;
use ACPT\Core\Repository\SettingsRepository;

class Settings
{
    /**
     * @param $key
     * @param null $defaultValue
     *
     * @return string|null
     */
    public static function get($key, $defaultValue = null)
    {
        try {
            $fetched = SettingsRepository::getSingle($key);

            return ($fetched !== null and !empty($fetched)) ? $fetched->getDecodedValue() : $defaultValue;
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return $defaultValue;
        }
    }

    /**
     * @param string          $key
     * @param SettingsModel[] $settings
     *
     * @return string|null
     */
    public static function fromSettings($key, array $settings)
    {
        $fetched = array_filter($settings, function (SettingsModel $model) use ($key){
            return $model->getKey() === $key;
        });

        if(empty($fetched)){
            return null;
        }

        $fetched = array_values($fetched);

        return $fetched[0]->getDecodedValue();
    }
}