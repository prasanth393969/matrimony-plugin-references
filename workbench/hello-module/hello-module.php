<?php
/**
 * Plugin Name: Matrimony Workbench - Hello Module
 * Description: Demo module to validate PR-based workflow.
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    add_menu_page(
        'Matrimony Hello',
        'Matrimony',
        'manage_options',
        'matrimony-hello',
        'matrimony_workbench_render_hello_page',
        'dashicons-heart',
        58
    );

    add_submenu_page(
        'matrimony-hello',
        'Hello',
        'Hello',
        'manage_options',
        'matrimony-hello',
        'matrimony_workbench_render_hello_page'
    );
});

function matrimony_workbench_render_hello_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }

    echo '<div class="wrap">';
    echo '<h1>Matrimony Workbench</h1>';
    echo '<p><strong>Hello from Workbench module ✅</strong></p>';
    echo '<p>This file was added via PR-based workflow.</p>';
    echo '</div>';
}
