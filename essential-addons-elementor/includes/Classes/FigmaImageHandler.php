<?php

namespace Essential_Addons_Elementor\Pro\Classes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Exception;
use WP_Error;

/**
 * @property string $namespace
 */
#[\AllowDynamicProperties ]
class FigmaImageHandler
{

    /**
     * Register AJAX actions
     */
    public function register()
    {
        add_action( 'wp_ajax_eael_upload_figma_images', [ $this, 'eael_handle_figma_images_upload' ] );
    }

    /**
     * Handle multiple Figma image uploads via AJAX
     */
    public function eael_handle_figma_images_upload()
    {
        // Verify nonce
        if ( ! isset( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( $_POST[ 'nonce' ], 'essential-addons-elementor' ) ) {
            wp_send_json_error( 'Invalid nonce', 403 );
            return;
        }

        // Check user capabilities
        if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( 'upload_files' ) ) {
            wp_send_json_error( 'Insufficient permissions', 403 );
            return;
        }

        // Get and validate figma URLs
        $figma_urls = isset( $_POST[ 'figma_urls' ] ) ? json_decode( wp_unslash( $_POST[ 'figma_urls' ] ) ) : [  ];

        if ( empty( $figma_urls ) || ! is_array( $figma_urls ) ) {
            wp_send_json_error( 'No valid image URLs provided', 400 );
            return;
        }

        $uploaded_images = [  ];

        foreach ( $figma_urls as $figma_url ) {
            $uploaded_image = $this->eael_process_single_figma_image( $figma_url );
            if ( ! is_wp_error( $uploaded_image ) ) {
                $uploaded_images[ $figma_url ] = $uploaded_image;
            }
        }

        wp_send_json_success( [
            'images' => $uploaded_images
         ] );
    }

    /**
     * Validate Figma URL
     *
     * @param string $url The URL to validate
     * @return bool
     */
    private function validate_figma_url( $url ) {
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return false;
        }

        $parsed_url = parse_url( $url );

        if ( empty( $parsed_url['host'] ) ) {
            return false;
        }

        if ( empty( $parsed_url['scheme'] ) || $parsed_url['scheme'] !== 'https' ) {
            return false;
        }

        $allowed_suffixes = [
            'storage.googleapis.com'
        ];

        foreach ( $allowed_suffixes as $suffix ) {
            if ( substr( $parsed_url['host'], -strlen( $suffix ) ) === $suffix ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process a single Figma image
     *
     * @param string $figma_url The Figma image URL
     * @return array|WP_Error
     */
    public function eael_process_single_figma_image( $figma_url )
    {
        if ( empty( $figma_url ) ) {
            return new WP_Error( 'missing_url', 'No image URL provided' );
        }

        if ( ! $this->validate_figma_url( $figma_url ) ) {
            return new WP_Error( 'invalid_url', 'Invalid Figma URL' );
        }

        // Download image from Figma
        $response = wp_remote_get( $figma_url );
        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'download_failed', 'Failed to download image' );
        }

        $image_data   = wp_remote_retrieve_body( $response );
        $content_type = wp_remote_retrieve_header( $response, 'content-type' );

        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $actual_mime = finfo_buffer( $finfo, $image_data );
        finfo_close( $finfo );

        if ( $actual_mime !== $content_type ) {
            return new WP_Error( 'mime_mismatch', 'File content does not match declared type' );
        }

        // Determine file extension
        $extension = '';
        if ( $content_type === 'image/png' ) {
            $extension = 'png';
        } elseif ( $content_type === 'image/jpeg' || $content_type === 'image/jpg' ) {
            $extension = 'jpg';
        } elseif ( $content_type === 'image/webp' ) {
            $extension = 'webp';
        } else {
            return new WP_Error( 'unsupported_type', 'Unsupported image type' );
        }

        // Save image to WordPress
        $upload_dir = wp_upload_dir();
        $file_name  = 'figma_image_' . time() . '_' . wp_generate_password( 6, false ) . '.' . $extension;
        $file_path  = $upload_dir[ 'path' ] . '/' . $file_name;

        if ( ! file_put_contents( $file_path, $image_data ) ) {
            return new WP_Error( 'save_failed', 'Failed to save image' );
        }

        // Insert into media library
        $attachment_id = wp_insert_attachment( [
            'post_mime_type' => $content_type,
            'post_title'     => sanitize_file_name( $file_name ),
            'post_content'   => '',
            'post_status'    => 'inherit'
         ], $file_path );

        if ( is_wp_error( $attachment_id ) ) {
            return new WP_Error( 'upload_failed', 'Failed to upload image to media library' );
        }

        // Generate attachment metadata
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
        wp_update_attachment_metadata( $attachment_id, $attach_data );

        return [
            'id'  => $attachment_id,
            'url' => wp_get_attachment_url( $attachment_id )
         ];
    }
}