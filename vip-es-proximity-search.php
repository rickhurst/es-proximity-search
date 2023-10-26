<?php
/*
Plugin Name: es-proximity-search
Description: Proof of concept provides geopost custom post type and an example proximity search using VIP Search (Elasticsearch)
Version: 1.0
Author: Rick Hurst
*/

// Register Geopost custom post type
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
    WP_CLI::add_command('vip-es-proximity-search import-cities', 'import_cities_command');
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
            update_post_meta($post_id, 'latitude', $lat);
            update_post_meta($post_id, 'longitude', $lng);
            WP_CLI::success("Created geopost for $city");
        } else {
            WP_CLI::error("Failed to create geopost for $city");
        }
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vip-es-proximity-search find-nearby', 'find_nearby_command');
}

function find_nearby_command($args, $assoc_args) {
    $distance = $assoc_args['distance'];
    $lat = $assoc_args['lat'];
    $lon = $assoc_args['lon'];

    // Define WP_Query arguments for geo-location query
    $query_args = [
        'ep_integrate'   => true,
        'posts_per_page' => 100,
        'post_type'      => 'geopost',
        'orderby'        => 'geo_distance',
        'order'          => 'asc',
        'geo_distance'   => [
            'distance'           => $distance,
            'geo_point.location' => [
                'lat' => $lat,
                'lon' => $lon,
            ],
        ],
    ];

    // Execute the WP_Query
    $nearby_posts = new WP_Query($query_args);

    // Check if any posts were found
    if ($nearby_posts->have_posts()) {
        while ($nearby_posts->have_posts()) {
            $nearby_posts->the_post();
            $post_title = get_the_title();
            $post_lat = get_post_meta(get_the_ID(), 'latitude', true);
            $post_lng = get_post_meta(get_the_ID(), 'longitude', true);

            WP_CLI::line("$post_title (Lat: $post_lat, Lng: $post_lng)");
        }
    } else {
        WP_CLI::success("No posts found within the specified distance.");
    }

    wp_reset_postdata();
}

add_filter( 'vip_search_post_meta_allow_list', 'vip_search_indexable_geo_post_meta', 10, 2 );

function vip_search_indexable_geo_post_meta( $allow, $post = null ) {
	$allow['latitude'] = true;
	$allow['longitude'] = true;
	return $allow;
}
