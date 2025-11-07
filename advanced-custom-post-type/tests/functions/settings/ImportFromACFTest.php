<?php

namespace ACPT\Tests;

class ImportFromACFTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function import_from_acf()
    {
        $acf = json_decode(file_get_contents(__DIR__.'/../../../tests/_inc/support/files/acf.json'), true);

        foreach ($acf as $group){

            // only field groups
            if(isset($group['fields']) and is_array($group['fields'])){
                $title = $this->toSnakeCase($group['title']);
                $label = $group['title'];

                $groupWasCreated = save_acpt_meta_group([
                    'name' => $title,
                    'label' => $label,
                ]);

                $this->assertTrue($groupWasCreated);

                $fields = [];

                foreach ($group['fields'] as $field){

                    $fieldTitle = $this->toSnakeCase($field['label']);
                    $fieldLabel = $field['label'];
                    $isRequired = $field['required'] == 1;
                    $fieldType = ucfirst($field['type']);

                    $fields[] = [
                        'label' => $fieldLabel,
                        'name' => $fieldTitle,
                        'type' => $fieldType,
                        'showInArchive' => false,
                        'isRequired' => $isRequired,
                    ];
                }

                $boxWasCreated = save_acpt_meta_box([
                    'groupName' => $title,
                    'name' => "box_" . $title,
                    'label' => "Box " . $label,
                    'fields' => $fields
                ]);

                $this->assertTrue($boxWasCreated);

                $groupWasDeleted = delete_acpt_meta_group($title);

                $this->assertTrue($groupWasDeleted);
            }
        }
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    function toSnakeCase($string)
    {
        $string = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));

        return preg_replace('/\s+/', '', $string);
    }
}