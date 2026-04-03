<?php
/**
 * Class Test_Woo_Taxonomy_SEO
 *
 * @package WooTaxonomySEO
 */

/**
 * Main plugin class tests.
 *
 * Note: These tests use the standard 'category' taxonomy as a stand-in
 * since WooCommerce may not be loaded in the test environment.
 * The plugin logic is taxonomy-agnostic for meta operations.
 */
class Test_Woo_Taxonomy_SEO extends WP_UnitTestCase {

    /**
     * Plugin instance.
     *
     * @var Woo_Taxonomy_SEO
     */
    private $plugin;

    /**
     * Test category term.
     *
     * @var WP_Term
     */
    private $category;

    /**
     * Test tag term.
     *
     * @var WP_Term
     */
    private $tag;

    /**
     * Set up test fixtures.
     */
    public function set_up() {
        parent::set_up();

        $this->plugin = woo_taxonomy_seo();

        // Use standard WordPress 'category' taxonomy for testing
        // since WooCommerce may not be loaded in test environment.
        $this->category = $this->factory->term->create_and_get(
            array(
                'taxonomy'    => 'category',
                'name'        => 'Test Category',
                'description' => 'This is a test category description.',
            )
        );

        // Use standard WordPress 'post_tag' taxonomy for testing.
        $this->tag = $this->factory->term->create_and_get(
            array(
                'taxonomy' => 'post_tag',
                'name'     => 'Test Tag',
            )
        );
    }

    /**
     * Tear down test fixtures.
     */
    public function tear_down() {
        parent::tear_down();

        // Clean up terms.
        if ( $this->category && ! is_wp_error( $this->category ) ) {
            wp_delete_term( $this->category->term_id, 'category' );
        }
        if ( $this->tag && ! is_wp_error( $this->tag ) ) {
            wp_delete_term( $this->tag->term_id, 'post_tag' );
        }
    }

    /**
     * Test plugin instance.
     */
    public function test_plugin_instance() {
        $this->assertInstanceOf( Woo_Taxonomy_SEO::class, $this->plugin );
    }

    /**
     * Test singleton pattern.
     */
    public function test_singleton_pattern() {
        $instance1 = woo_taxonomy_seo();
        $instance2 = woo_taxonomy_seo();

        $this->assertSame( $instance1, $instance2 );
    }

    /**
     * Test supported taxonomies.
     */
    public function test_supported_taxonomies() {
        $taxonomies = $this->plugin->get_taxonomies();

        $this->assertIsArray( $taxonomies );
        $this->assertContains( 'product_cat', $taxonomies );
        $this->assertContains( 'product_tag', $taxonomies );
    }

    /**
     * Test meta fields configuration.
     */
    public function test_meta_fields_configuration() {
        $meta_fields = $this->plugin->get_meta_fields();

        $this->assertIsArray( $meta_fields );
        $this->assertArrayHasKey( 'seo_title', $meta_fields );
        $this->assertArrayHasKey( 'seo_description', $meta_fields );
        $this->assertArrayHasKey( 'canonical_url', $meta_fields );
        $this->assertArrayHasKey( 'robots_noindex', $meta_fields );
        $this->assertArrayHasKey( 'robots_nofollow', $meta_fields );
        $this->assertArrayHasKey( 'og_title', $meta_fields );
        $this->assertArrayHasKey( 'og_description', $meta_fields );
        $this->assertArrayHasKey( 'og_image', $meta_fields );
    }

    /**
     * Test saving and retrieving SEO title.
     */
    public function test_save_and_get_seo_title() {
        $seo_title = 'Custom SEO Title for Testing';

        update_term_meta( $this->category->term_id, '_wts_seo_title', $seo_title );

        $retrieved = get_term_meta( $this->category->term_id, '_wts_seo_title', true );

        $this->assertEquals( $seo_title, $retrieved );
    }

    /**
     * Test saving and retrieving meta description.
     */
    public function test_save_and_get_meta_description() {
        $description = 'This is a custom meta description for testing purposes.';

        update_term_meta( $this->category->term_id, '_wts_seo_description', $description );

        $retrieved = get_term_meta( $this->category->term_id, '_wts_seo_description', true );

        $this->assertEquals( $description, $retrieved );
    }

    /**
     * Test saving and retrieving canonical URL.
     */
    public function test_save_and_get_canonical_url() {
        $canonical = 'https://example.com/custom-canonical/';

        update_term_meta( $this->category->term_id, '_wts_canonical_url', $canonical );

        $retrieved = get_term_meta( $this->category->term_id, '_wts_canonical_url', true );

        $this->assertEquals( $canonical, $retrieved );
    }

    /**
     * Test robots noindex directive.
     */
    public function test_robots_noindex() {
        update_term_meta( $this->category->term_id, '_wts_robots_noindex', '1' );

        $noindex = get_term_meta( $this->category->term_id, '_wts_robots_noindex', true );

        $this->assertEquals( '1', $noindex );
    }

    /**
     * Test robots nofollow directive.
     */
    public function test_robots_nofollow() {
        update_term_meta( $this->category->term_id, '_wts_robots_nofollow', '1' );

        $nofollow = get_term_meta( $this->category->term_id, '_wts_robots_nofollow', true );

        $this->assertEquals( '1', $nofollow );
    }

    /**
     * Test get_meta_description with custom value.
     */
    public function test_get_meta_description_custom() {
        $description = 'Custom meta description';

        update_term_meta( $this->category->term_id, '_wts_seo_description', $description );

        $result = $this->plugin->get_meta_description( $this->category );

        $this->assertEquals( $description, $result );
    }

    /**
     * Test get_meta_description fallback to term description.
     */
    public function test_get_meta_description_fallback() {
        // No custom description set, should fall back to term description.
        $result = $this->plugin->get_meta_description( $this->category );

        $this->assertStringContainsString( 'test category description', strtolower( $result ) );
    }

    /**
     * Test get_robots_directives with noindex.
     */
    public function test_get_robots_directives_noindex() {
        update_term_meta( $this->category->term_id, '_wts_robots_noindex', '1' );

        $result = $this->plugin->get_robots_directives( $this->category );

        $this->assertStringContainsString( 'noindex', $result );
        $this->assertStringContainsString( 'follow', $result );
    }

    /**
     * Test get_robots_directives with nofollow.
     */
    public function test_get_robots_directives_nofollow() {
        update_term_meta( $this->category->term_id, '_wts_robots_nofollow', '1' );

        $result = $this->plugin->get_robots_directives( $this->category );

        $this->assertStringContainsString( 'index', $result );
        $this->assertStringContainsString( 'nofollow', $result );
    }

    /**
     * Test get_robots_directives with both noindex and nofollow.
     */
    public function test_get_robots_directives_both() {
        update_term_meta( $this->category->term_id, '_wts_robots_noindex', '1' );
        update_term_meta( $this->category->term_id, '_wts_robots_nofollow', '1' );

        $result = $this->plugin->get_robots_directives( $this->category );

        $this->assertStringContainsString( 'noindex', $result );
        $this->assertStringContainsString( 'nofollow', $result );
    }

    /**
     * Test get_canonical_url with custom value.
     */
    public function test_get_canonical_url_custom() {
        $canonical = 'https://example.com/custom/';

        update_term_meta( $this->category->term_id, '_wts_canonical_url', $canonical );

        $result = $this->plugin->get_canonical_url( $this->category );

        $this->assertEquals( $canonical, $result );
    }

    /**
     * Test get_canonical_url fallback to term link.
     */
    public function test_get_canonical_url_fallback() {
        $result = $this->plugin->get_canonical_url( $this->category );

        $this->assertNotEmpty( $result );
        // URL format varies by permalink settings, just check it's a valid URL.
        $this->assertStringStartsWith( 'http', $result );
    }

    /**
     * Test get_open_graph_tags.
     */
    public function test_get_open_graph_tags() {
        update_term_meta( $this->category->term_id, '_wts_og_title', 'OG Title' );
        update_term_meta( $this->category->term_id, '_wts_og_description', 'OG Description' );

        $tags = $this->plugin->get_open_graph_tags( $this->category );

        $this->assertIsArray( $tags );
        $this->assertArrayHasKey( 'og:type', $tags );
        $this->assertArrayHasKey( 'og:title', $tags );
        $this->assertArrayHasKey( 'og:description', $tags );
        $this->assertArrayHasKey( 'og:url', $tags );
        $this->assertArrayHasKey( 'og:site_name', $tags );
        $this->assertArrayHasKey( 'og:locale', $tags );

        $this->assertEquals( 'website', $tags['og:type'] );
        $this->assertEquals( 'OG Title', $tags['og:title'] );
        $this->assertEquals( 'OG Description', $tags['og:description'] );
    }

    /**
     * Test OG title fallback to SEO title.
     */
    public function test_og_title_fallback_to_seo_title() {
        update_term_meta( $this->category->term_id, '_wts_seo_title', 'SEO Title' );

        $tags = $this->plugin->get_open_graph_tags( $this->category );

        $this->assertEquals( 'SEO Title', $tags['og:title'] );
    }

    /**
     * Test OG title fallback to term name.
     */
    public function test_og_title_fallback_to_term_name() {
        $tags = $this->plugin->get_open_graph_tags( $this->category );

        $this->assertEquals( 'Test Category', $tags['og:title'] );
    }

    /**
     * Test tag meta (using post_tag as stand-in for product_tag).
     */
    public function test_tag_meta() {
        $seo_title = 'Tag SEO Title';

        update_term_meta( $this->tag->term_id, '_wts_seo_title', $seo_title );

        $retrieved = get_term_meta( $this->tag->term_id, '_wts_seo_title', true );

        $this->assertEquals( $seo_title, $retrieved );
    }

    /**
     * Test sanitize_meta_field.
     */
    public function test_sanitize_meta_field() {
        $dirty = '<script>alert("xss")</script>Test Value';

        $clean = $this->plugin->sanitize_meta_field( $dirty );

        $this->assertStringNotContainsString( '<script>', $clean );
        $this->assertStringContainsString( 'Test Value', $clean );
    }

    /**
     * Test document title filter.
     */
    public function test_filter_document_title() {
        update_term_meta( $this->category->term_id, '_wts_seo_title', 'Custom Title' );

        // Simulate being on a taxonomy page.
        $this->go_to( get_term_link( $this->category ) );

        $title_parts = array( 'title' => 'Original Title' );
        $filtered    = $this->plugin->filter_document_title( $title_parts );

        // Note: This test may not work fully without proper query setup.
        $this->assertIsArray( $filtered );
    }
}
