<?php
/**
 * PHPUnit bootstrap file for Taxonomy SEO for WooCommerce tests.
 *
 * @package TaxonomySEOForWooCommerce
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI test file.

// Composer autoloader.
if ( file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __DIR__ ) . '/vendor/autoload.php';
}

// Get the tests directory.
$tsfw_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $tsfw_tests_dir ) {
    $tsfw_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Check if the test library exists.
if ( ! file_exists( "{$tsfw_tests_dir}/includes/functions.php" ) ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI output only.
    echo "Could not find {$tsfw_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh?" . PHP_EOL;
    exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$tsfw_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function tsfw_manually_load_plugin() {
    // Load WooCommerce first.
    $wc_plugin = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
    if ( file_exists( $wc_plugin ) ) {
        require $wc_plugin;
    }

    // Load our plugin.
    require dirname( __DIR__ ) . '/taxonomy-seo-for-woocommerce.php';
}

tests_add_filter( 'muplugins_loaded', 'tsfw_manually_load_plugin' );

// Start up the WP testing environment.
require "{$tsfw_tests_dir}/includes/bootstrap.php";
