<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Repository\TaxonomyRepository;

class DuplicateTaxonomyCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $taxonomy;

    /**
     * DuplicateTaxonomyCommand constructor.
     * @param $taxonomy
     */
    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function execute()
    {
        $taxonomy = TaxonomyRepository::get([
            'taxonomy' => $this->taxonomy
        ]);

        if(empty($taxonomy)){
            throw new \Exception("Taxonomy not found");
        }

        $taxonomyModel = $taxonomy[0];
        $newTaxonomyModel = $taxonomyModel->duplicate();

        TaxonomyRepository::save($newTaxonomyModel);

        foreach ($newTaxonomyModel->getCustomPostTypes() as $customPostTypeModel){
            TaxonomyRepository::assocToPostType($customPostTypeModel->getId(), $newTaxonomyModel->getId());
        }
    }
}