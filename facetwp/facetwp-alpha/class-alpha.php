<?php

class FacetWP_Facet_Alpha_Addon extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'A-Z Listing', 'fwp' );
        $this->fields = [ 'label_any' ];

        add_filter( 'facetwp_index_row', [ $this, 'index_alpha' ], 1, 2 );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {
        global $wpdb;

        $output = '';
        $facet = $params['facet'];
        $selected_values = (array) $params['selected_values'];

        // Simulate "OR" mode (ignore this facet's selection)
        if ( isset( FWP()->or_values ) && ( 1 < count( FWP()->or_values ) || ! isset( FWP()->or_values[ $facet['name'] ] ) ) ) {
            $post_ids = [];
            $or_values = FWP()->or_values; // Preserve the original
            unset( $or_values[ $facet['name'] ] );

            $counter = 0;
            foreach ( $or_values as $name => $vals ) {
                $post_ids = ( 0 == $counter ) ? $vals : array_intersect( $post_ids, $vals );
                $counter++;
            }

            // Return only applicable results
            $post_ids = array_intersect( $post_ids, FWP()->unfiltered_post_ids );
        }
        else {
            $post_ids = FWP()->unfiltered_post_ids;
        }

        $post_ids = empty( $post_ids ) ? [ 0 ] : $post_ids;
        $where_clause = ' AND post_id IN (' . implode( ',', $post_ids ) . ')';

        $sql = "
        SELECT DISTINCT BINARY UPPER(LEFT(facet_display_value, 1)) AS letter
        FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' $where_clause
        ORDER BY letter";
        $results = $wpdb->get_col( $sql );

        // Remove accents
        if ( ! empty( $results ) && apply_filters( 'facetwp_alpha_remove_accents', true, $params['facet']['name'] ) ) {
            $results = array_map( 'remove_accents', $results );
            $results = array_unique( $results );
        }

        $available_chars = apply_filters( 'facetwp_alpha_chars', [ '#', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
            'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' ], $params );

        $label_any = empty( $facet['label_any'] ) ? __( 'Any', 'fwp-front' ) : $facet['label_any'];
        $label_any = facetwp_i18n( $label_any );

        $output .= '<span class="facetwp-alpha facetwp-link available" data-id="">' . esc_attr( $label_any ) . '</span>';

        foreach ( $available_chars as $char ) {
            $match = false;
            $active = in_array( $char, $selected_values );

            if ( '#' == $char ) {
                foreach ( [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] as $num ) {
                    if ( in_array( (string) $num, $results ) ) {
                        $match = true;
                        break;
                    }
                }
            }
            elseif ( in_array( $char, $results ) ) {
                $match = true;
            }

            $classes = 'facetwp-alpha facetwp-link';
            $classes = ( $active || $match ) ? "$classes available" : $classes;
            $classes = ( $active ) ? "$classes selected" : $classes;

            $output .= '<span class="' . $classes . '" data-id="' . $char . '">' . $char . '</span>';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;

        // The "#" character is an alias for all numbers
        if ( '#' == $selected_values ) {
            $selected_values = [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ];
            $selected_values = implode( "','", $selected_values );
        }

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' AND BINARY UPPER(SUBSTR(facet_display_value, 1, 1)) IN ('$selected_values')";
        return $wpdb->get_col( $sql );
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        FWP()->display->assets['alpha-front.css'] = [ plugins_url( '', __FILE__ ) . '/assets/css/front.css', FACETWP_ALPHA_VERSION ];
        FWP()->display->assets['alpha-front.js'] = [ plugins_url( '', __FILE__ ) . '/assets/js/front.js', FACETWP_ALPHA_VERSION ];
    }

    /**
     * remove accents during indexing 
     */
    function index_alpha( $params, $class ) {

        if ( 'alpha' == $class->facet[ 'type' ] && apply_filters( 'facetwp_alpha_remove_accents', true, $params['facet_name'] ) ) {
            $params['facet_display_value'] = remove_accents( $params['facet_display_value' ] );
        }

        return $params;
    }
}
