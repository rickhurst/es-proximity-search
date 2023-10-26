# VIP ES Proximity Search Plugin

## Description

ES Proximity Search is a WordPress plugin developed as a proof of concept. It provides a custom post type called "geopost" and an example proximity search feature using VIP Search with Elasticsearch. This allows you to create geoposts with latitude and longitude information and perform location-based searches within your WordPress site.

This plugin depends on https://github.com/rickhurst/ep-geo-vip-search

## Installation

1. Download the plugin ZIP file or clone the repository to your WordPress plugins directory (usually `wp-content/plugins`).

2. Activate the "ES Proximity Search" plugin through the WordPress admin dashboard.

3. The plugin provides two WP-CLI commands for importing cities from a CSV file and performing proximity searches:

   - Import cities: `wp vip-es-proximity-search import-cities`
   - Find nearby geoposts: `wp vip-es-proximity-search find-nearby --lat=<latitude> --lon=<longitude> --distance=<distance>`

## Usage

### Import Cities

Use the `import-cities` command to import cities from the CSV file contained in assets folder to create geoposts with latitude and longitude set as post meta.

```
wp vip-es-proximity-search import-cities
``````

### WP-CLI proximity search

Use `wp vip-es-proximity-search find-nearby --lat=<latitude> --lon=<longitude> --distance=<distance>` and specify the latitude and longitude of the location you want to search from.

e.g. wp vip-es-proximity-search find-nearby --lat=51.533652078523176 --lon=-0.13456804385843052 --distance=50mi
