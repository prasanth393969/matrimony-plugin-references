<?php

namespace ACPT\Utils\ExportCode\Exporter;

use ACPT\Core\Repository\DynamicBlockRepository;
use ACPT\Utils\ExportCode\DTO\ExportCodeStringsDto;

class ExportBlockCodeStrings extends AbstractExportCodeStrings
{
    /**
     * @param $find
     *
     * @return ExportCodeStringsDto
     * @throws \Exception
     */
    public function export( $find )
    {
        $blockModel = DynamicBlockRepository::getById($find);

        if(!$blockModel){
            throw new \Exception($find . ' is not a valid meta group id');
        }

        $controls = [];

        foreach ($blockModel->getControls() as $control){
            $controls[] = [
                'id' => $control->getId(),
                'name' => $control->getName(),
                'label' => $control->getLabel(),
                'type' => $control->getType(),
                'default' => $control->getDefault(),
                'description' => $control->getDescription(),
                'settings' => $control->getSettings(),
            ];
        }

        $json = [
            'id' => $blockModel->getId(),
            'title' => $blockModel->getTitle(),
            'name' =>  $blockModel->getName(),
            'category' =>  $blockModel->getCategory(),
            'icon' =>  $blockModel->getIcon(),
            'css' =>  $blockModel->getCSS(),
            'callback' =>  $blockModel->getCallback(),
            'keywords' =>  $blockModel->getKeywords(),
            'postTypes' =>  $blockModel->getPostTypes(),
            'supports' => $blockModel->getSupports(),
            'controls' => $controls,
        ];


        $dto = new ExportCodeStringsDto();
        $dto->acpt = '
<php
save_acpt_block('.var_export($json, true).');
';
        $dto->wordpress = null;

        return $dto;
    }
}