<?php

class FacetWP_Facet_Hierarchy_Select_Addon extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'Hierarchy Select', 'fwp' );
        $this->fields = [ 'orderby', 'depth_labels' ];
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $from_clause = $wpdb->prefix . 'facetwp_index f';
        $where_clause = $this->get_where_clause( $facet );

        // Orderby
        $orderby = $this->get_orderby( $facet );

        $orderby = apply_filters( 'facetwp_facet_orderby', $orderby, $facet );
        $from_clause = apply_filters( 'facetwp_facet_from', $from_clause, $facet );
        $where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );

        $sql = "
        SELECT f.post_id, f.facet_value, f.facet_display_value, f.term_id, f.parent_id, f.depth, COUNT(DISTINCT f.post_id) AS counter
        FROM $from_clause
        WHERE f.facet_name = '{$facet['name']}' $where_clause
        GROUP BY f.facet_value
        ORDER BY $orderby";

        return $wpdb->get_results( $sql, ARRAY_A );
    }


    /**
     * Filter out irrelevant choices
     */
    function filter_load_values( $values, $selected_values ) {
        foreach ( $selected_values as $depth => $selected_value ) {
            $selected_id = -1;

            foreach ( $values as $key => $val ) {
                if ( $selected_value == $val['facet_value'] ) { // save the parent_id
                    $selected_id = $val['term_id'];
                }

                if ( $val['depth'] == ( $depth + 1 ) ) { // child of the selected value
                    if ( $val['parent_id'] != $selected_id ) {
                        unset( $values[ $key ] );
                    }
                }
            }
        }

        return $this->group_by_depth( $values );
    }


    /**
     * Group values in buckets by depth to make output easier
     */
    function group_by_depth( $values ) {
        $depths = [];

        foreach ( $values as $val ) {
            $depth = (int) $val['depth'];
            $depths[ $depth ][] = $val;
        }

        return $depths;
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $facet = $params['facet'];
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        $levels = $facet['levels'] ?? [];
        $active_levels = count( $selected_values );
        $grouped_values = $this->filter_load_values( $values, $selected_values );

        foreach ( $levels as $level_num => $level_name ) {
            $disabled = ( $level_num <= $active_levels ) ? '' : ' disabled';
            $class = empty( $disabled ) ? '' : ' is-disabled';
            $label = empty( $level_name ) ? __( 'Any', 'fwp' ) : $level_name;
            $label = facetwp_i18n( $label );

            // Add "is-empty" class if applicable
            $class .= empty( $grouped_values[ $level_num ] ) ? ' is-empty' : '';

            $output .= '<select class="facetwp-hierarchy_select' . $class . '" data-level="' . $level_num . '"' . $disabled . '>';
            $output .= '<option value="">' . esc_attr( $label ) . '</option>';

            if ( $level_num <= $active_levels && isset( $grouped_values[ $level_num ] ) ) {
                foreach ( $grouped_values[ $level_num ] as $row ) {
                    $selected = in_array( $row['facet_value'], $selected_values ) ? ' selected' : '';

                    // Determine whether to show counts
                    $display_value = esc_attr( $row['facet_display_value'] );
                    $show_counts = apply_filters( 'facetwp_facet_dropdown_show_counts', true, array( 'facet' => $facet ) );

                    if ( $show_counts ) {
                        $display_value .= ' (' . $row['counter'] . ')';
                    }

                    $output .= '<option value="' . esc_attr( $row['facet_value'] ) . '"' . $selected . '>' . $display_value . '</option>';
                }
            }

            $output .= '</select>';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = (array) $params['selected_values'];
        $selected_values = array_pop( $selected_values );

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' AND facet_value IN ('$selected_values')";
        return $wpdb->get_col( $sql );
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        FWP()->display->assets['hierarchy-select-front.js'] = [ plugins_url( '', __FILE__ ) . '/assets/js/front.js', FACETWP_HIERARCHY_SELECT_VERSION ];
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>

Vue.component('levels', {
    props: ['facet'],
    template: `
    <div>
        <div v-for="(label, index) in facet.levels">
            <div style="padding-bottom:10px">
                <input type="text" :value="label" @change="setValue(index, event.target.value)" :placeholder="getPlaceholder(index)" />
                <button class="button" @click="removeLabel(index)" v-if="index > 0">x</button>
            </div>
        </div>
        <button class="button" @click="addLabel()">Add label</button>
    </div>
    `,
    methods: {
        setValue: function(index, value) {
            this.facet.levels.splice(index, 1, value);
        },
        addLabel: function() {
            this.facet.levels.push('');
        },
        removeLabel: function(index) {
            Vue.delete(this.facet.levels, index);
        },
        getPlaceholder: function(index) {
            return 'Enter label (depth ' + index + ')';
        }
    },
    created() {
        this.facet.hierarchical = 'yes';

        if (this.facet.levels.length < 1) {
            this.facet.levels = [''];
        }
    }
});

</script>
<?php
    }


    function register_fields() {
        return [
            'depth_labels' => [
                'type' => 'alias',
                'items' => [
                    'levels' => [
                        'label' => __( 'Show depth levels', 'fwp' ),
                        'notes' => '(Required) Add a label for each hierarchy depth level',
                        'html' => '<input type="hidden" class="facet-levels" value="[]" /><levels :facet="facet"></levels>'
                    ]
                ]
            ]
        ];
    }
}
