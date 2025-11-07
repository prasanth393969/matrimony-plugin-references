<?php

defined( 'ABSPATH' ) or exit;

class FacetWP_Iconic_Woo_Attribute_Swatches
{

    function __construct() {
        add_filter( 'facetwp_facet_option', [ $this, 'render_colors' ], 10, 3 );
    }

    function render_colors( $item, $value, $params ) {

        if ( 'color' == $params['facet']['type'] ) {

            global $iconic_was;

            $attribute_id = $iconic_was->attributes_class()->get_attribute_id_by_slug( str_replace( [ 'tax/', 'cf/attribute_' ], '', $params['facet']['source'] ) );
            
            if ( 1 > (int) $attribute_id ) {
                return $item;
            }
            
            $default_swatch_data = $iconic_was->attributes_class()->get_attribute_option_value( $attribute_id );

            // supported swatch_types 'image-swatch', 'colour-swatch'            
            if ( ! isset( $default_swatch_data['swatch_type']) || ! in_array( $swatch_type = $default_swatch_data['swatch_type'], [ 'image-swatch', 'colour-swatch' ] ) ) {
                return $item;
            }

            if ( 0 < (int)$value['term_id'] ) {

                $term = get_term( (int) $value['term_id'] );

                if ( ! is_wp_error( $term ) && ! empty( $term ) ) {

                    $img = ""; // default image url blank
                    
                    if ( 'image-swatch' == $swatch_type ) {
                        $img_id = Iconic_WAS_Swatches::get_swatch_value( 'taxonomy', 'image-swatch', $term );
                        if ( 0 < $img_id ) $img = wp_get_attachment_image_url( $img_id, 'thumbnail' );
                    }        

                    // data-color can fall back to colour-swatch even if image-swatch is now used
                    $hex = Iconic_WAS_Swatches::get_swatch_value( 'taxonomy', 'colour-swatch', $term );        
                    $color = ( ! empty( $hex ) ) ? sanitize_hex_color( $hex ) : $value['facet_display_value'];
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

new FacetWP_Iconic_Woo_Attribute_Swatches();
