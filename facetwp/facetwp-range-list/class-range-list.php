<?php

class FacetWP_Facet_Range_List_Addon extends FacetWP_Facet
{

    public $ui_fields;
    public $field_defaults;


    function __construct() {
        $this->label = __( 'Range List', 'fwp' );
        $this->fields = [ 'levels', 'ui_type', 'label_any', 'multiple', 'operator', 'ui_ghosts' ];
        $this->field_defaults = [
            'ui_type' => 'checkboxes'
        ];
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $from_clause  = $wpdb->prefix . 'facetwp_index f';
        $where_clause = $params['where_clause'];

        // Use "OR" mode when necessary
        $ui_type = isset( $params['facet']['ui_type'] ) && '' != $params['facet']['ui_type'] ? $params['facet']['ui_type'] : 'checkboxes'; // default = checkboxes
        $show_ghosts = FWP()->helper->facet_is( $facet, 'ui_ghosts', 'yes' );
        $is_single = FWP()->helper->facet_is( $facet, 'multiple', 'no' ) || in_array( $ui_type, [ 'radio','dropdown' ] );
        $using_or = FWP()->helper->facet_is( $facet, 'operator', 'or' ) && !in_array( $ui_type, [ 'radio','dropdown' ] );

        // Facet in "OR" mode
        if ( $is_single || $using_or ) {
            $where_clause = $this->get_where_clause( $facet );
        }

        $from_clause  = apply_filters( 'facetwp_facet_from', $from_clause, $facet );
        $where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );

        $sql = "
        SELECT f.facet_value, f.post_id
        FROM $from_clause
        WHERE f.facet_name = '{$facet['name']}' $where_clause";

        $results = $wpdb->get_results( $sql, ARRAY_A );
        $output  = [];

        // Build groups
        foreach ( $params['facet']['levels'] as $level => $setting ) {
            $min = (float) $this->get_range_value( 'min', $level, 'down', $params['facet']['levels'] );
            $max = (float) $this->get_range_value( 'max', $level, 'up', $params['facet']['levels'] );
            $auto_display = 'All';

            if ( ! empty( $min ) && ! empty( $max ) ) {
                $auto_display = $min . ' - ' . $max;
                $value = $min . '-' . $max;
            }
            elseif ( empty( $min ) && ! empty( $max ) ) {
                $auto_display = 'Up to ' . $max;
                $value = '0-' . $max;
            }
            elseif ( empty( $max ) ) {
                $auto_display = $min . ' and up';
                $value = $min . '+';
            }

            $display = empty( $setting['label'] ) ? $auto_display : $setting['label'];
            $counts = $this->get_counts( $results, $min, $max );

            if ( $show_ghosts || 0 < $counts ) {

                $output[] = [
                    'counter' => $this->get_counts( $results, $min, $max ),
                    'facet_value' => $value,
                    'facet_display_value' => $display,
                    'depth' => 0
                ];

            }
        }

        return $output;
    }


    /**
     * Get the lowest value
     */
    function get_range_value( $type, $level, $direction, $levels ) {
        $val = "";

        if ( isset( $levels[ $level ][ $type ] ) && "" != $levels[ $level ][ $type ] ) {
            $val = (float) $levels[ $level ][ $type ];
        }
        elseif ( $level >= 0 && $level < count( $levels ) ) {
            $type = ( 'min' == $type ) ? 'max' : 'min';
            $level = ( 'up' == $direction ) ? $level + 1 : $level - 1;
            $val = $this->get_range_value( $type, $level, $direction, $levels );
        }

        return $val;
    }


    /**
     * Filter out irrelevant choices
     */
    function get_counts( $results, $start, $end ) {
        $count = 0;

        foreach ( $results as $result ) {
            if ( $result['facet_value'] >= $start ) {
                if ( empty( $end ) || $result['facet_value'] <= $end ) {
                    $count += 1;
                }
            }
        }

        return $count;
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {
        return FWP()->helper->facet_types['checkboxes']->render( $params );
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}'";

        $parts = [];
        $operator = FWP()->helper->facet_is( $facet, 'operator', 'or' ) ? 'OR' : 'AND';

        foreach ( $selected_values as $row ) {
            $row = explode( '-', $row );
            $row = array_map( 'floatval', $row );

            $fragment = "facet_value >= $row[0]";

            if ( ! empty( $row[1] ) ) {
                $fragment .= " AND facet_value <= $row[1]";
            }

            $parts[] = "($fragment)";
        }

        if ( ! empty( $parts ) ) {
            $sql .= ' AND (' . implode( " $operator ", $parts ) . ')';
        }

        return facetwp_sql( $sql, $facet );
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        FWP()->display->assets['range-list-front.js'] = [ plugins_url( '', __FILE__ ) . '/assets/js/front.js', FACETWP_RANGE_LIST_VERSION ];
        $facets = FWP()->helper->get_facets_by( 'type', 'range_list' );
        $active_facets = array_keys( FWP()->display->active_facets );
        foreach ( $facets as $facet ) {
            if ( in_array( $facet['name'], $active_facets ) && isset( $facet['ui_type'] ) && '' != $facet['ui_type'] ) {
                $facet_class = FWP()->helper->facet_types[ $facet['ui_type'] ];
                if ( method_exists( $facet_class, 'front_scripts' ) ) {
                    $facet_class->front_scripts(); 
                }
            }
        }
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>

<style type="text/css">
.facet-level-row {
    padding-bottom: 10px;
}

.facetwp-row .facet-level-row input.min-max {
    width: 100px;
}
</style>

<script>

Vue.component('range-list', {
    props: ['facet'],
    data() {
        return {
            autofill: []
        }
    },
    template: `
    <div>
        <div class="facet-level-row" v-for="(row, index) in facet.levels">
            <input type="text" class="min-max" v-model="facet.levels[index].min" @input="updateLabels()" placeholder="Min" />
            <input type="text" class="min-max" v-model="facet.levels[index].max" @input="updateLabels()" placeholder="Max" />
            <input type="text" v-model="facet.levels[index].label" @input="maybeAutofill(index)" placeholder="Label" />
            <span @click="removeRange(index)" class="qb-remove" v-html="FWP.svg['minus-circle']"></span>
        </div>
        <button class="button" @click="addRange()">Add Range</button>
    </div>
    `,
    created() {
        for (var i = 0; i < this.facet.levels.length; i++) {
            this.maybeAutofill(i);
        }
    },
    methods: {
        addRange: function() {
            this.facet.levels.push({
                min: '',
                max: '',
                label: ''
            });
            this.autofill.push(true);
        },
        removeRange: function(index) {
            Vue.delete(this.facet.levels, index);
            Vue.delete(this.autofill, index);
            this.updateLabels();
        },
        maybeAutofill: function(index) {
            var label = this.facet.levels[index].label;
            this.autofill[index] = ('' == label) ? true : false;
        },
        updateLabels: function() {
            for (var i = 0; i < this.facet.levels.length; i++) {
                if (false === this.autofill[i]) {
                    continue;
                }

                let sep = ' ';
                let min = this.facet.levels[i].min;
                let max = this.facet.levels[i].max;
                min = min.length ? parseFloat(min) : this.findLowest(i);
                max = max.length ? parseFloat(max) : this.findHighest(i);

                if ('number' === typeof min && 'number' === typeof max) {
                    sep = ' - ';
                }
                if ('string' !== typeof min || 'string' !== typeof max) {
                    this.facet.levels[i].label = min + sep + max;
                }
                else {
                    this.facet.levels[i].label = '';
                }
            }
        },
        findLowest: function(index) {
            let val = 'Up to';

            if (0 < index) {
                let lower = this.facet.levels[index-1].max;
                val = (lower.length) ? parseFloat(lower) : this.findLowest(index-1);
            }

            return val;
        },
        findHighest: function(index) {
            let val = 'and Up';

            if (index < this.facet.levels.length-1) {
                let upper = this.facet.levels[index+1].min;
                val = (upper.length) ? parseFloat(upper) : this.findHighest(index+1);
            }

            return val;
        }
    }
});

</script>
<?php
    }


    function register_fields() {
        return [
            'levels' => [
                'label' => __( 'Ranges', 'fwp' ),
                'html' => '<range-list :facet="facet"></range-list><input type="hidden" class="facet-levels" value="[]" />',
                'notes' => 'Enter Min/Max values as a number, with or without decimals. For decimals use dots, not commas. Don\'t use thousands separators, spaces or other characters.'
            ]
        ];
    }
}
