<?php

namespace ACPT\Utils\ExportCode\Exporter;

use ACPT\Core\Repository\FormRepository;
use ACPT\Utils\ExportCode\DTO\ExportCodeStringsDto;

class ExportFormCodeStrings extends AbstractExportCodeStrings
{
    /**
     * @param $find
     *
     * @return ExportCodeStringsDto
     * @throws \Exception
     */
    public function export( $find )
    {
        $formModel = FormRepository::getById($find);

        if(!$formModel){
            throw new \Exception($find . ' is not a valid form id');
        }

        $fields = [];
        $meta = [];

        foreach ($formModel->getFields() as $fieldModel){

            $validationRules = [];

            foreach ($fieldModel->getValidationRules() as $validationRuleModel){
                $validationRules[] = [
                    'id' => $validationRuleModel->getId(),
                    'condition' => $validationRuleModel->getCondition(),
                    'value' => $validationRuleModel->getValue(),
                    'message' => $validationRuleModel->getMessage(),
                ];
            }

            $fields[] = [
                'id' => $fieldModel->getId(),
                'group' => $fieldModel->getGroup(),
                'key' => $fieldModel->getKey(),
                'name' => $fieldModel->getName(),
                'label' => $fieldModel->getLabel(),
                'type' => $fieldModel->getType(),
                'description' => $fieldModel->getDescription(),
                'isRequired' => (bool)$fieldModel->isRequired(),
                'extra' => $fieldModel->getExtra() ?? [],
                'settings' => $fieldModel->getSettings() ?? [],
                'validationRules' => $validationRules,
            ];
        }

        foreach ($formModel->getMeta() as $metaModel){
            $meta[] = [
                'key' => $metaModel->getKey(),
                'value' => $metaModel->getValue()
            ];
        }

        $json = [
            'id' => $formModel->getId(),
            'name' => $formModel->getName(),
            'label' => $formModel->getLabel(),
            'key' => $formModel->getKey(),
            'action' => $formModel->getAction(),
            'fields' => $fields,
            'meta' => $meta,
        ];

        $dto = new ExportCodeStringsDto();
        $dto->acpt = '
<php
save_acpt_form('.var_export($json, true).');
';
        $dto->wordpress = null;

        return $dto;
    }
}