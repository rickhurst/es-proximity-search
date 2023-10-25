<?php
/*
Plugin Name: es-proximity-search
Description: Provides geopost custom post type with a ES-based proximity search.
Version: 1.0
Author: Rick Hurst
*/

// Register custom post type
function register_geopost_custom_post_type() {
    register_post_type('geopost', array(
        'labels' => array(
            'name' => 'geoposts',
            'singular_name' => 'geopost',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
    ));
}
add_action('init', 'register_geopost_custom_post_type');

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('es-proximity-search import-cities', 'import_cities_command');
}

function import_cities_command($args, $assoc_args) {
    // Load and parse the CSV file
    $csv_file = plugin_dir_path(__FILE__) . 'assets/uk_towns.csv';

    $data = array_map('str_getcsv', file($csv_file));

    // Loop through the CSV data and create posts
    foreach ($data as $row) {
        $city = $row[0];
        $lat = $row[1];
        $lng = $row[2];

        $post_id = wp_insert_post(array(
            'post_title' => $city,
            'post_type' => 'geopost',
            'post_status' => 'publish',
        ));

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, 'lat', $lat);
            update_post_meta($post_id, 'lng', $lng);
            WP_CLI::success("Created geopost for $city");
        } else {
            WP_CLI::error("Failed to create geopost for $city");
        }
    }
}
