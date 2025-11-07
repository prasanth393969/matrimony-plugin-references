<?php

namespace ACPT\Utils\Wordpress;

use ACPT\Constants\TaxonomyField;

class Terms
{
    /**
     * @param $postId
     * @return array
     */
    public static function getForPostId($postId): array
    {
        global $wpdb;

        $sql = "
            SELECT r.object_id, r.term_taxonomy_id, t.taxonomy FROM  `{$wpdb->prefix}term_relationships` r
            LEFT JOIN `{$wpdb->prefix}term_taxonomy` t ON t.term_taxonomy_id = r.term_taxonomy_id
            WHERE r.object_id = %s 
        ";

        $preparedQuery = $wpdb->prepare($sql,  [$postId]);

        return $wpdb->get_results($preparedQuery);
    }

	/**
	 * @param $postType
	 *
	 * @return array
	 */
	public static function getForPostType($postType): array
	{
		$terms = [];
		$taxonomies = get_object_taxonomies($postType);

		foreach ($taxonomies as $taxonomy){
			$taxonomy_details = get_taxonomy( $taxonomy );
			$queriedTerms = get_terms([
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'fields'     => 'id=>name',
			]);

			$values = [];
			foreach ($queriedTerms as $id => $term){
				$values[$taxonomy.TaxonomyField::SEPARATOR.$id] = $term;
			}

			$terms[$taxonomy_details->name] = $values;
		}

		return $terms;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public static function getList($args = []): array
	{
		try {
			$terms = [];
			$taxonomyParam = isset($args['taxonomy']) ? $args['taxonomy'] : null;
            $taxonomies = get_taxonomies(['show_ui' => true], 'objects');

			if($taxonomyParam !== null){
				$taxonomies = array_filter($taxonomies, function (\WP_Taxonomy $taxonomy) use($taxonomyParam) {
					return $taxonomyParam === $taxonomy->name;
				});
			}

            usort($taxonomies, function (\WP_Taxonomy $a, \WP_Taxonomy $b){
                return strtolower($a->label) <=> strtolower($b->label);
            });


            foreach ($taxonomies as $taxonomy){
				$queriedTerms = get_terms([
					'taxonomy'   => $taxonomy->name,
					'hide_empty' => false,
					'fields'     => 'id=>name',
				]);

				if(is_array($queriedTerms)){
					$terms[] = [
						'taxonomy' => self::formatTaxonomyLabel($taxonomy),
						'terms'    => $queriedTerms,
					];
				}
			}

			return $terms;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return [];
		}
	}

    /**
     * @param \WP_Taxonomy $taxonomy
     *
     * @return string
     */
    public static function formatTaxonomyLabel(\WP_Taxonomy $taxonomy)
    {
        return $taxonomy->label . " (".$taxonomy->name.")";
    }
}