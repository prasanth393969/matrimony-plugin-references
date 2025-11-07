<?php

class FacetWP_Facet_Color_Addon extends FacetWP_Facet
{
    public $field_defaults;

    function __construct() {
        $this->label = __( 'Color', 'fwp' );
        $this->fields = [ 'parent_term', 'modifiers', 'operator', 'count', 'soft_limit', 'orderby' ];
        $this->field_defaults = [
            'orderby' => 'count'
        ];
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $from_clause = $wpdb->prefix . 'facetwp_index f';
        $where_clause = $params['where_clause'];

        // Orderby
        $orderby = $this->get_orderby( $facet );
        $orderby = 'f.depth, ' . $orderby;

        // Limit
        $limit = $this->get_limit( $facet );

        // Facet in "OR" mode
        if ( 'or' == $facet['operator'] ) {
            $where_clause = $this->get_where_clause( $facet );
        }

        $orderby = apply_filters( 'facetwp_facet_orderby', $orderby, $facet );
        $from_clause = apply_filters( 'facetwp_facet_from', $from_clause, $facet );
        $where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );

        $sql = "
        SELECT f.facet_value, f.facet_display_value, f.term_id, f.parent_id, f.depth, COUNT(DISTINCT f.post_id) AS counter
        FROM $from_clause
        WHERE f.facet_name = '{$facet['name']}' $where_clause
        GROUP BY f.facet_value
        ORDER BY $orderby
        LIMIT $limit";

        $output = $wpdb->get_results( $sql, ARRAY_A );

        return $output;
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $facet = $params['facet'];

        $output = '';
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];
        $soft_limit = empty( $facet['soft_limit'] ) ? 0 : (int) $facet['soft_limit'];

        $key = 0;
        foreach ( $values as $key => $result ) {
            $class = 'facetwp-color';
            $result[ 'overflow' ] = false;
            if ( 0 < $soft_limit && $key >= $soft_limit ) {
                $class .= ' facetwp-overflow facetwp-hidden';
                $result[ 'overflow' ] = true;
            }

            $selected = in_array( $result['facet_value'], $selected_values ) ? ' checked' : '';
            $selected .= ( 0 == $result['counter'] ) ? ' disabled' : '';

            $label = apply_filters( 'facetwp_facet_display_value', '', [
                'selected' => $selected,
                'facet' => $facet,
                'row' => $result
            ]);

            $item = '<div class="' . $class . $selected . '" data-value="' . $result['facet_value'] . '" data-color="' . esc_attr( $result['facet_display_value'] ) . '" title="' . esc_attr( $label ) . '"></div>';
            $output .= apply_filters( 'facetwp_facet_option', $item, $result, $params );
        }

        if ( 0 < $soft_limit && $soft_limit <= $key ) {
            $output .= '<div class="facetwp-toggle-wrap"><a class="facetwp-toggle">' . facetwp_i18n( __( 'See {num} more', 'fwp-front' ) ) . '</a>';
            $output .= '<a class="facetwp-toggle facetwp-hidden">' . facetwp_i18n( __( 'See less', 'fwp-front' ) ) . '</a></div>';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $output = array();
        $facet = $params['facet'];
        $selected_values = $params['selected_values'];

        $sql = $wpdb->prepare( "SELECT DISTINCT post_id
            FROM {$wpdb->prefix}facetwp_index
            WHERE facet_name = %s",
            $facet['name']
        );

        // Match ALL values
        if ( 'and' == $facet['operator'] ) {
            foreach ( $selected_values as $key => $value ) {
                $results = facetwp_sql( $sql . " AND facet_value IN ('$value')", $facet );
                $output = ( $key > 0 ) ? array_intersect( $output, $results ) : $results;

                if ( empty( $output ) ) {
                    break;
                }
            }
        }
        // Match ANY value
        else {
            $selected_values = implode( "','", $selected_values );
            $output = facetwp_sql( $sql . " AND facet_value IN ('$selected_values')", $facet );
        }

        return $output;
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        FWP()->display->assets['color-front.css'] = [ plugins_url( '', __FILE__ ) . '/assets/css/front.css', FACETWP_COLOR_VERSION ];
        FWP()->display->assets['color-front.js'] = [ plugins_url( '', __FILE__ ) . '/assets/js/front.js', FACETWP_COLOR_VERSION ];
    }
}
