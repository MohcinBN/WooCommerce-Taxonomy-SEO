<?php
/**
 * PHPUnit bootstrap file for WooCommerce Taxonomy SEO tests.
 *
 * @package WooTaxonomySEO
 */

// Composer autoloader.
if ( file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __DIR__ ) . '/vendor/autoload.php';
}

// Get the tests directory.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Check if the test library exists.
if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
    echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh?" . PHP_EOL;
    exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    // Load WooCommerce first.
    $wc_plugin = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
    if ( file_exists( $wc_plugin ) ) {
        require $wc_plugin;
    }

    // Load our plugin.
    require dirname( __DIR__ ) . '/woo-taxonomy-seo.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
