<?php

namespace ACPT\Utils\ExportCode\Exporter;

use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\ExportCode\DTO\ExportCodeStringsDto;

class ExportMetaCodeStrings extends AbstractExportCodeStrings
{
    /**
     * @param $find
     *
     * @return ExportCodeStringsDto
     * @throws \Exception
     */
    public function export( $find )
    {
        $groupModel = MetaRepository::get([
            'id' => $find
        ])[0];

        if(!$groupModel){
            throw new \Exception($find . ' is not a valid meta group id');
        }

        $belongs = [];
        $boxes = [];

        foreach ($groupModel->getBelongs() as $belong){
            $belongs[] = [
                'belongsTo' => $belong->getBelongsTo(),
                'operator'  => $belong->getOperator(),
                "find"      => $belong->getFind(),
            ];
        }

        foreach ($groupModel->getBoxes() as $box){

            $fields = [];

            foreach ($box->getFields() as $fieldModel){
                $fields[] = $fieldModel->arrayRepresentation();
            }

            $boxes[] = [
                'id' => $box->getId(),
                'name' => $box->getName(),
                'label' => $box->getLabel(),
                'fields' => $fields
            ];
        }

        $json = [
            'id' => $groupModel->getId(),
            'name' => $groupModel->getName(),
            'label' => $groupModel->getLabel(),
            'context' => $groupModel->getContext(),
            'priority' => $groupModel->getPriority(),
            'display' => $groupModel->getDisplay(),
            'belongs' => $belongs,
            'boxes' => $boxes,
        ];

        $dto = new ExportCodeStringsDto();
        $dto->acpt = '<php
save_acpt_meta_group('.var_export($json, true).');
';
        $dto->wordpress = null;

        return $dto;
    }
}