<?php

namespace ACPT\Includes;

use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Models\CustomPostType\CustomPostTypeModel;
use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Models\Taxonomy\TaxonomyModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT_Lite\Core\Repository\CustomPostTypeRepository as ACPTLiteCustomPostTypeRepository;
use ACPT_Lite\Core\Repository\MetaRepository as ACPTLiteMetaRepository;
use ACPT_Lite\Core\Repository\TaxonomyRepository as ACPTLiteTaxonomyRepository;
use ACPT_Lite\Includes\ACPT_Lite_DB;

class ACPT_Lite_Importer
{
    /**
     * Import settings from ACPT Lite
     */
    public static function import()
    {
        if(!class_exists(ACPT_Lite_DB::class)){
            return;
        }

        try {
            // 1. Custom post types
            $acptLiteCustomPostTypes = ACPTLiteCustomPostTypeRepository::get([]);

            foreach ($acptLiteCustomPostTypes as $acptLiteCustomPostType){

                $acptCustomPostType = CustomPostTypeModel::hydrateFromArray([
                    'id' => $acptLiteCustomPostType->getId(),
                    'name' => $acptLiteCustomPostType->getName(),
                    'singular' => $acptLiteCustomPostType->getSingular(),
                    'plural' => $acptLiteCustomPostType->getPlural(),
                    'icon' => $acptLiteCustomPostType->getIcon(),
                    'native' => $acptLiteCustomPostType->isNative(),
                    'supports' => $acptLiteCustomPostType->getSupports(),
                    'labels' => $acptLiteCustomPostType->getLabels(),
                    'settings' => $acptLiteCustomPostType->getSettings(),
                ]);

                // taxonomies
                foreach ($acptCustomPostType->getTaxonomies() as $acptLiteTaxonomy){
                    $acptTaxonomy = TaxonomyModel::hydrateFromArray([
                        'id' => $acptLiteTaxonomy->getId(),
                        'slug' => $acptLiteTaxonomy->getSlug(),
                        'singular' => $acptLiteTaxonomy->getSingular(),
                        'plural' => $acptLiteTaxonomy->getPlural(),
                        'native' => $acptLiteTaxonomy->isNative(),
                        'labels' => $acptLiteTaxonomy->getLabels(),
                        'settings' => $acptLiteTaxonomy->getSettings(),
                    ]);

                    $acptCustomPostType->addTaxonomy($acptTaxonomy);
                    TaxonomyRepository::assocToPostType($acptLiteCustomPostType->getId(), $acptLiteTaxonomy->getId());
                }

                CustomPostTypeRepository::save($acptCustomPostType);
            }

            // 2. Taxonomies
            $acptLiteTaxonomies = ACPTLiteTaxonomyRepository::get([]);

            foreach ($acptLiteTaxonomies as $acptLiteTaxonomy){
                $acptTaxonomy = TaxonomyModel::hydrateFromArray([
                    'id' => $acptLiteTaxonomy->getId(),
                    'slug' => $acptLiteTaxonomy->getSlug(),
                    'singular' => $acptLiteTaxonomy->getSingular(),
                    'plural' => $acptLiteTaxonomy->getPlural(),
                    'native' => $acptLiteTaxonomy->isNative(),
                    'labels' => $acptLiteTaxonomy->getLabels(),
                    'settings' => $acptLiteTaxonomy->getSettings(),
                ]);

                TaxonomyRepository::save($acptTaxonomy);

                foreach ($acptLiteTaxonomy->getCustomPostTypes() as $acptLiteCustomPostType){
                    TaxonomyRepository::assocToPostType($acptLiteCustomPostType->getId(), $acptLiteTaxonomy->getId());
                }
            }

            // 3. Meta
            $acptLiteMetaGroups = ACPTLiteMetaRepository::get([]);

            foreach ($acptLiteMetaGroups as $acptLiteMetaGroup){
                $acptMetaGroup = MetaGroupModel::hydrateFromArray([
                    'id' => $acptLiteMetaGroup->getId(),
                    'name' => $acptLiteMetaGroup->getName(),
                    'label' => $acptLiteMetaGroup->getLabel(),
                    'display' => $acptLiteMetaGroup->getDisplay(),
                ]);

                $acptMetaGroup->setPriority($acptLiteMetaGroup->getPriority());
                $acptMetaGroup->setContext($acptLiteMetaGroup->getContext());

                foreach ($acptLiteMetaGroup->getBelongs() as $acptLiteBelong){
                    $acptBelong = BelongModel::hydrateFromArray([
                        'id' => $acptLiteBelong->getId(),
                        'belongsTo' => $acptLiteBelong->getBelongsTo(),
                        'sort' => $acptLiteBelong->getSort(),
                        'logic' => $acptLiteBelong->getLogic(),
                        'operator' => $acptLiteBelong->getOperator(),
                        'find' => $acptLiteBelong->getFind(),
                    ]);

                    $acptMetaGroup->addBelong($acptBelong);
                }

                foreach ($acptLiteMetaGroup->getBoxes() as $acptLiteBox){
                    $acptBox = MetaBoxModel::hydrateFromArray([
                        'id' => $acptLiteBox->getId(),
                        'group' => $acptMetaGroup,
                        'name' => $acptLiteBox->getName(),
                        'label' => $acptLiteBox->getLabel(),
                        'sort' => $acptLiteBox->getSort(),
                    ]);

                    foreach ($acptLiteBox->getFields() as $acptLiteField){
                        $acptField = MetaFieldModel::hydrateFromArray([
                            'id' => $acptLiteField->getId(),
                            'box' => $acptBox,
                            'name' => $acptLiteField->getName(),
                            'label' => $acptLiteField->getLabel(),
                            'type' => $acptLiteField->getType(),
                            'showInArchive' => $acptLiteField->isShowInArchive(),
                            'isRequired' => $acptLiteField->isRequired(),
                            'sort' => $acptLiteField->getSort(),
                            'defaultValue' => $acptLiteField->getDefaultValue(),
                            'description' => $acptLiteField->getDescription(),
                        ]);

                        $acptBox->addField($acptField);
                    }

                    $acptMetaGroup->addBox($acptBox);
                }

                MetaRepository::saveMetaGroup($acptMetaGroup);
            }

        } catch (\Exception $exception){
        }
    }
}