<?php

defined( 'ABSPATH' ) or exit;

class FacetWP_Woo_Variation_Swatches
{

    function __construct() {
        add_filter( 'facetwp_facet_option', [ $this, 'render_colors' ], 10, 3 );
    }


    /**
     * wvs_get_product_attribute_image() was removed in 2.0
     */
    function get_image( $term ) {
        if ( function_exists( 'wvs_get_product_attribute_image' ) ) {
            return wvs_get_product_attribute_image( $term );
        }

        return woo_variation_swatches()->get_frontend()->get_product_attribute_image( $term );
    }


    /**
     * wvs_get_product_attribute_color() was removed in 2.0
     */
    function get_color( $term ) {
        if ( function_exists( 'wvs_get_product_attribute_color' ) ) {
            return wvs_get_product_attribute_color( $term );
        }

        return woo_variation_swatches()->get_frontend()->get_product_attribute_color( $term );
    }


    function render_colors( $item, $value, $params ) {

        if ( 'color' == $params['facet']['type'] ) {

            $attrs = wc_get_attribute_taxonomy_ids();
            $attr = wc_get_attribute( $attrs[ str_replace( [ 'tax/pa_', 'cf/attribute_pa_' ], '', $params['facet']['source'] ) ] );

            if ( ! isset( $attr->type ) ) {
                return $item;
            }

            if ( 0 < (int) $value['term_id'] ) {
                $term = get_term( (int) $value['term_id'] );

                if ( ! is_wp_error( $term ) && ! empty( $term ) ) {

                    $img = '';
                    if ( 'image' == $attr->type ) {
                        $img = (int) $this->get_image( $term );
                        $img = ( 0 < $img ) ? wp_get_attachment_image_url( $img ) : '';
                    }

                    $color = $this->get_color( $term );
                    $color = empty( $color ) ? $value['facet_display_value'] : $color;
                    $class = 'facetwp-color';
                    if ( $value[ 'overflow' ] ) {
                        $class .= ' facetwp-overflow facetwp-hidden';
                    }

                    $selected = in_array( $value['facet_value'], $params['selected_values'] ) ? ' checked' : '';
                    $selected .= ( 0 == $value['counter'] ) ? ' disabled' : '';

                    $label = apply_filters( 'facetwp_facet_display_value', $term->name, [
                        'selected' => $selected,
                        'facet' => $params['facet'],
                        'row' => $value
                    ]);

                    $item = '<div class="' . $class . $selected . '" data-value="' . $value['facet_value'] . '" data-color="' . esc_attr( $color ) . '" data-img="' . esc_attr( $img ) . '" title="' . esc_attr( $label ) . '"></div>';
                }
            }

        }
        return $item;
    }
}

new FacetWP_Woo_Variation_Swatches();
