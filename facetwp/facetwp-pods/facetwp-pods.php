<?php
/*
Plugin Name: FacetWP - Pods integration
Description: Pods integration with FacetWP
Version: 1.2.4
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-pods
*/

defined( 'ABSPATH' ) or exit;

/**
 * Class FacetWP_Pods_Addon
 *
 * @since 0.1
 */
class FacetWP_Pods_Addon {

    /**
     * @var array Fields data.
     *
     * @since 0.1
     */
    public $fields = array();

    /**
     * FacetWP_Pods_Addon constructor.
     *
     * @since 0.1
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Handle hooks.
     *
     * @since 0.1
     */
    public function init() {
        if ( ! defined( 'PODS_VERSION' ) ) {
            return;
        }

        add_filter( 'facetwp_facet_sources', array( $this, 'facet_sources' ) );
        add_filter( 'facetwp_indexer_query_args', array( $this, 'lookup_fields' ) );
        add_filter( 'facetwp_indexer_row_data', array( $this, 'get_index_rows' ), 1, 2 );
        //add_filter( 'facetwp_excluded_custom_fields', array( $this, 'exclude_internal_fields' ) );
        add_filter( 'facetwp_builder_item_value', array( $this, 'layout_builder_field_value' ), 10, 2 );
    }

    /**
     * Add Pods fields to sources list.
     *
     * @param $sources Sources list.
     *
     * @return array Sources list with Pods fields added.
     *
     * @since 0.1
     */
    public function facet_sources( $sources ) {
        $this->setup_fields();

        $sources['pods'] = array(
            'label'   => 'Pods',
            'choices' => array(),
            'weight'  => 25,
        );

        foreach ( $this->fields as $choice_id => $field ) {
            $choice_label = sprintf( '[%1$s] %2$s', $field['pod_label'], $field['label'] );

            $sources['pods']['choices'][ $choice_id ] = $choice_label;
        }

        return $sources;
    }

    /**
     * Hijack the "facetwp_indexer_query_args" hook to lookup the fields once.
     *
     * @param array $args Arguments.
     *
     * @return array Arguments.
     *
     * @since 0.1
     */
    public function lookup_fields( $args ) {
        $this->setup_fields();

        return $args;
    }

    /**
     * Setup all post type Pods fields.
     *
     * @return array Pods fields.
     *
     * @since 0.1
     */
    public function setup_fields() {
        if ( ! empty( $this->fields ) ) {
            return $this->fields;
        }

        $fields = array();

        $pods_api = pods_api();

        $params = array(
            'type'       => 'post_type',
            'table_info' => false,
        );

        $post_type_pods = $pods_api->load_pods( $params );

        if ( function_exists( 'UPT' ) ) {
            // Check for user pod.
            $params = array(
                'name'       => 'user',
                'table_info' => false,
            );

            $user_pod = $pods_api->load_pod( $params );

            // If we have a user pod, add it to the list.
            if ( $user_pod ) {
                $post_type_pods[] = $user_pod;
            }
        }

        foreach ( $post_type_pods as $pod ) {
            foreach ( $pod['fields'] as $field ) {
                $field['pod_label'] = $pod['label'];

                // If this is the user pod, set the compatible post type.
                if ( 'user' === $field['pod'] ){
                    $field['pod'] = 'upt_user';
                }

                $choice_id = sprintf( 'pods/%1$s/%2$s', $field['pod'], $field['name'] );

                $fields[ $choice_id ] = $field;
            }
        }

        $this->fields = $fields;

        return $fields;
    }

    /**
     * Handle indexing of pods/{pod}/{field} sources.
     *
     * @param array $rows   Rows to index.
     * @param array $params Index parameters.
     *
     * @param array Rows to index.
     *
     * @since 0.1
     */
    public function get_index_rows( $rows, $params ) {
        $defaults = $params['defaults'];
        $facet    = $params['facet'];

        if ( ! isset( $facet['source'] ) || 0 !== strpos( $facet['source'], 'pods/' ) ) {
            return $rows;
        }

        // pods/{pod_name}/{field_name}
        $props = explode( '/', $facet['source'] );

        $pod_name   = $props[1];
        $field_name = $props[2];

        $item_id = $defaults['post_id'];

        $post = get_post( $item_id );

        // Check if this matches the source post type.
        if ( ! $post || $pod_name !== $post->post_type ) {
            return $rows;
        }

        // Check if this is a compatible user pod.
        if ( 'upt_user' === $pod_name ) {
            // Integration is not available.
            if ( ! function_exists( 'UPT' ) ) {
                return $rows;
            }

            // Set the real pod name.
            $pod_name = 'user';

            // Get the real user ID.
            $item_id = UPT()->get_user_id( $item_id );

            if ( ! $item_id ) {
                return $rows;
            }
        }

        $pod = $this->setup_pod( $pod_name, $item_id );

        // Pod not found or item does not exist.
        if ( ! $pod || ! $pod->valid() || ! $pod->exists() ) {
            return $rows;
        }

        $field_settings = $pod->fields( $field_name );
        $field_data = $pod->fields( $field_name, 'data' );

        // Field not found.
        if ( ! $field_settings ) {
            return $rows;
        }

        $field_params = [
            'name'    => $field_name,
            'keyed'   => true,
            'output'  => 'names'
        ];

        // We want to index term slugs instead of IDs
        if ( 'pick' == $field_settings['type'] && 'taxonomy' == $field_settings['pick_object'] ) {
            $field_params['output'] = 'arrays';
        }

        $values = $pod->field( $field_params );

        if ( ! is_array( $values ) ) {
            $values = [ $values ];
        }

        // Values for pre-defined lists don't include labels
        // so we have to manually intersect values with $field_data
        if ( 'pick' == $field_settings['type'] ) {
            if ( 'custom-simple' == $field_settings['pick_object'] ) {
                $values = array_intersect_key( $field_data, array_flip( $values ) );
            }
            elseif ( 'taxonomy' == $field_settings['pick_object'] ) {
                $temp_values = [];

                // Force an array-of-arrays when pick_format_type = single
                if ( isset( $values['term_id'] ) ) {
                    $values = [ $values ];
                }

                foreach ( $values as $val ) {
                    $temp_values[ $val['slug'] ] = $val['name'];
                }

                $values = $temp_values;
            }
        }

        $is_first = true;
        $numeric_array_keys = false;

        foreach ( $values as $value => $display_value ) {

            if ( $is_first && 0 === $value ) {
                $numeric_array_keys = true;
            }

            $is_first = false;

            // If numeric array, set the value to the display value
            if ( $numeric_array_keys ) {
                $value = $display_value;
            }

            $row_value = array(
                'value' => $value,
                'display_value' => $display_value,
            );

            $row = $this->setup_index_row( $row_value, $pod, $params );

            if ( $row ) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Setup index row for value.
     *
     * @param array  $row_value Row value data.
     * @param Pods   $pod       Pod object.
     * @param array  $params    Index parameters.
     *
     * @return array|false Row data or false if bad value.
     *
     * @since 0.1
     */
    public function setup_index_row( $row_value, $pod, $params ) {
        $row = false;

        if ( ! is_scalar( $row_value['value'] ) || ! is_scalar( $row_value['display_value'] ) ) {
            return $row;
        }

        $row = $params['defaults'];

        // Set some Pods-related stuff so people can filter our rows and do more with them later.
        $row['pods_name']    = $pod->pod;
        $row['pods_type']    = $pod->pod_data['type'];
        $row['pods_item_id'] = $pod->id();

        // Set values.
        $row['facet_value']         = $row_value['value'];
        $row['facet_display_value'] = $row_value['display_value'];

        return $row;
    }

    /**
     * @param array $excluded_fields Excluded fields.
     *
     * @return array Excluded fields.
     *
     * @since 0.1
     */
    public function exclude_internal_fields( $excluded_fields ) {
        /** @var wpdb $wpdb */
        global $wpdb;

        // Exclude post meta keys from _pods_pod, _pods_field, etc.
        $sql = "
            SELECT DISTINCT `pm`.`meta_key`
            FROM `{$wpdb->postmeta}` AS `pm`
            LEFT JOIN `{$wpdb->posts}` AS `p`
                ON `p`.`ID` = `pm`.`post_id`
            WHERE `p`.`post_type` LIKE '_pods_%'
        ";

        $internal_fields = $wpdb->get_col( $sql );

        $excluded_fields = array_merge( $excluded_fields, $internal_fields );

        // Exclude post meta keys used for relationship order storage.
        $sql = "
            SELECT DISTINCT `meta_key`
            FROM `{$wpdb->postmeta}`
            WHERE `meta_key` LIKE '_pods_%'
        ";

        $internal_fields = $wpdb->get_col( $sql );

        $excluded_fields = array_merge( $excluded_fields, $internal_fields );

        // Exclude post meta keys for our Pods.
        $this->setup_fields();

        $internal_fields = array();

        foreach ( $this->fields as $choice_id => $field ) {
            $internal_fields[] = basename( $choice_id );
        }

        $excluded_fields = array_merge( $excluded_fields, $internal_fields );

        $excluded_fields = array_unique( array_filter( $excluded_fields ) );

        return $excluded_fields;
    }

    /**
     * Setup Pods object.
     *
     * @param string $pod_name Pod name.
     * @param mixed  $item_id  Item ID.
     *
     * @return Pods The pod object.
     *
     * @since 0.1
     */
    protected function setup_pod( $pod_name, $item_id ) {
        /** @var Pods $pod */
        static $pod;

        if ( ! $pod || $pod->pod !== $pod_name ) {
            // Setup Pods object if we need to.
            $pod = pods( $pod_name, $item_id );
        } elseif ( (int) $pod->id !== (int) $item_id && $pod->id !== $item_id ) {
            // Fetch the row if it isn't already the current one
            $pod->fetch( $item_id );
        }

        return $pod;
    }


    /**
     * Support Pods fields in the FacetWP layout builder
     *
     * @return mixed Pods field value
     *
     * @since 1.1
     */
    public function layout_builder_field_value( $value, $item ) {
        if ( 0 === strpos( $item['source'], 'pods/' ) ) {

            // pods/{pod_name}/{field_name}
            $props = explode( '/', $item['source'] );

            $pod_name   = $props[1];
            $field_name = $props[2];

            $item_id = get_the_ID();

            $post = get_post( $item_id );

            // Check if this matches the source post type.
            if ( ! $post || $pod_name !== $post->post_type ) {
                return $value;
            }

            // Check if this is a compatible user pod.
            if ( 'upt_user' === $pod_name ) {

                // Integration is not available.
                if ( ! function_exists( 'UPT' ) ) {
                    return $value;
                }

                // Set the real pod name.
                $pod_name = 'user';

                // Get the real user ID.
                $item_id = UPT()->get_user_id( $item_id );

                if ( ! $item_id ) {
                    return $value;
                }
            }

            $pod = pods( $pod_name, $item_id );

            // Pod not found or item does not exist.
            if ( ! $pod || ! $pod->valid() || ! $pod->exists() ) {
                return $value;
            }

            $field_settings = $pod->fields( $field_name );

            // Field not found.
            if ( ! $field_settings ) {
                return $value;
            }

            $field_type = $field_settings['type'];

            // pick, file, avatar, taxonomy, comment
            if ( 'file' == $field_type ) {

                $file = $pod->display( $field_name, false );
                $check = wp_check_filetype( $file );
                $pod_value = '';

                if ( ! empty( $check ) ) {
                    if ( 0 === strpos( $check['type'], 'image/' ) ) {
                        $pod_value = pods_image( $file, 'thumbnail' );
                    }
                    elseif ( 0 === strpos( $check['type'], 'video/' ) ) {
                        $pod_value = pods_video( $file );
                    }
                    elseif ( 0 === strpos( $check['type'], 'audio/' ) ) {
                        $pod_value = pods_audio( $file );
                    }
                    else {

                        // link to file
                        $pod_value = pods_image ( $file, 'thumbnail' );
                        $pod_value = '<a href="' . $file . '">' . $pod_value . '</a>';
                    }
                }
            }
            elseif ( 'code' == $field_type ) {
                $pod_value = '<code>' . esc_textarea( $pod->field( $field_name, true, true ) ) . '</code>';
            }
            else {
                $pod_value = $pod->display( $field_name );
            }

            $value = str_replace( $item['source'], $pod_value, $value );
        }

        return $value;
    }
}

new FacetWP_Pods_Addon();
