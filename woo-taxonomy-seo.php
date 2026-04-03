<?php
/**
 * Plugin Name: WooCommerce Taxonomy SEO
 * Plugin URI: https://github.com/MohcinBN/WooCommerce-Taxonomy-SEO
 * Description: SEO optimization for WooCommerce product categories and tags. Add custom meta titles, descriptions, canonical URLs, and robots directives.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Tokayah
 * Author URI: https://mohcinbounouara.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woo-taxonomy-seo
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 *
 * @package WooTaxonomySEO
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'WOO_TAXONOMY_SEO_VERSION', '1.0.0' );
define( 'WOO_TAXONOMY_SEO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOO_TAXONOMY_SEO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WOO_TAXONOMY_SEO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
final class Woo_Taxonomy_SEO {

    /**
     * Single instance of the class.
     *
     * @var Woo_Taxonomy_SEO|null
     */
    private static $instance = null;

    /**
     * Supported taxonomies.
     *
     * @var array
     */
    private $taxonomies = array( 'product_cat', 'product_tag' );

    /**
     * Meta fields configuration.
     *
     * @var array
     */
    private $meta_fields = array();

    /**
     * Get single instance of the class.
     *
     * @return Woo_Taxonomy_SEO
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->define_meta_fields();
        $this->init_hooks();
    }

    /**
     * Define meta fields configuration.
     *
     * @return void
     */
    private function define_meta_fields() {
        $this->meta_fields = array(
            'seo_title' => array(
                'label'       => __( 'SEO Title', 'woo-taxonomy-seo' ),
                'description' => __( 'Custom title tag for this taxonomy. Leave empty to use the default.', 'woo-taxonomy-seo' ),
                'type'        => 'text',
                'maxlength'   => 70,
                'placeholder' => __( 'Enter SEO title (recommended: 50-60 characters)', 'woo-taxonomy-seo' ),
            ),
            'seo_description' => array(
                'label'       => __( 'Meta Description', 'woo-taxonomy-seo' ),
                'description' => __( 'Custom meta description for search engines. Recommended: 150-160 characters.', 'woo-taxonomy-seo' ),
                'type'        => 'textarea',
                'maxlength'   => 200,
                'placeholder' => __( 'Enter meta description (recommended: 150-160 characters)', 'woo-taxonomy-seo' ),
            ),
            'canonical_url' => array(
                'label'       => __( 'Canonical URL', 'woo-taxonomy-seo' ),
                'description' => __( 'Custom canonical URL. Leave empty to use the default taxonomy URL.', 'woo-taxonomy-seo' ),
                'type'        => 'url',
                'placeholder' => 'https://',
            ),
            'robots_noindex' => array(
                'label'       => __( 'Noindex', 'woo-taxonomy-seo' ),
                'description' => __( 'Prevent search engines from indexing this page.', 'woo-taxonomy-seo' ),
                'type'        => 'checkbox',
            ),
            'robots_nofollow' => array(
                'label'       => __( 'Nofollow', 'woo-taxonomy-seo' ),
                'description' => __( 'Prevent search engines from following links on this page.', 'woo-taxonomy-seo' ),
                'type'        => 'checkbox',
            ),
            'og_title' => array(
                'label'       => __( 'Open Graph Title', 'woo-taxonomy-seo' ),
                'description' => __( 'Title for social media sharing. Leave empty to use SEO title.', 'woo-taxonomy-seo' ),
                'type'        => 'text',
                'maxlength'   => 95,
                'placeholder' => __( 'Enter Open Graph title', 'woo-taxonomy-seo' ),
            ),
            'og_description' => array(
                'label'       => __( 'Open Graph Description', 'woo-taxonomy-seo' ),
                'description' => __( 'Description for social media sharing. Leave empty to use meta description.', 'woo-taxonomy-seo' ),
                'type'        => 'textarea',
                'maxlength'   => 200,
                'placeholder' => __( 'Enter Open Graph description', 'woo-taxonomy-seo' ),
            ),
            'og_image' => array(
                'label'       => __( 'Open Graph Image', 'woo-taxonomy-seo' ),
                'description' => __( 'Image for social media sharing. Recommended: 1200x630 pixels.', 'woo-taxonomy-seo' ),
                'type'        => 'image',
            ),
        );
    }

    /**
     * Initialize hooks.
     *
     * @return void
     */
    private function init_hooks() {
        // Check if WooCommerce is active.
        add_action( 'plugins_loaded', array( $this, 'check_woocommerce' ) );

        // Load text domain.
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // Admin hooks.
        add_action( 'admin_init', array( $this, 'register_term_meta' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Add meta fields to taxonomy edit screens.
        foreach ( $this->taxonomies as $taxonomy ) {
            add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_term_fields' ) );
            add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_term_fields' ), 10, 2 );
            add_action( "created_{$taxonomy}", array( $this, 'save_term_fields' ) );
            add_action( "edited_{$taxonomy}", array( $this, 'save_term_fields' ) );
        }

        // Frontend SEO output.
        add_action( 'wp_head', array( $this, 'output_seo_meta' ), 1 );
        add_filter( 'document_title_parts', array( $this, 'filter_document_title' ), 20 );
        add_filter( 'wpseo_title', array( $this, 'filter_yoast_title' ), 20 );
        add_filter( 'the_seo_framework_title_from_generation', array( $this, 'filter_tsf_title' ), 20 );

        // Add settings link to plugins page.
        add_filter( 'plugin_action_links_' . WOO_TAXONOMY_SEO_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );

        // Declare HPOS compatibility.
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
    }

    /**
     * Check if WooCommerce is active.
     *
     * @return void
     */
    public function check_woocommerce() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
        }
    }

    /**
     * Display notice if WooCommerce is not active.
     *
     * @return void
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: WooCommerce plugin name */
                    esc_html__( '%s requires WooCommerce to be installed and active.', 'woo-taxonomy-seo' ),
                    '<strong>WooCommerce Taxonomy SEO</strong>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Load plugin text domain.
     *
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'woo-taxonomy-seo',
            false,
            dirname( WOO_TAXONOMY_SEO_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Register term meta.
     *
     * @return void
     */
    public function register_term_meta() {
        foreach ( $this->taxonomies as $taxonomy ) {
            foreach ( $this->meta_fields as $key => $field ) {
                register_term_meta(
                    $taxonomy,
                    '_wts_' . $key,
                    array(
                        'type'              => 'string',
                        'single'            => true,
                        'sanitize_callback' => array( $this, 'sanitize_meta_field' ),
                        'show_in_rest'      => true,
                    )
                );
            }
        }
    }

    /**
     * Sanitize meta field value.
     *
     * @param mixed $value The value to sanitize.
     * @return string
     */
    public function sanitize_meta_field( $value ) {
        return sanitize_text_field( $value );
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook The current admin page.
     * @return void
     */
    public function enqueue_admin_assets( $hook ) {
        $screen = get_current_screen();

        if ( ! $screen || ! in_array( $screen->taxonomy, $this->taxonomies, true ) ) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'woo-taxonomy-seo-admin',
            WOO_TAXONOMY_SEO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WOO_TAXONOMY_SEO_VERSION
        );

        wp_enqueue_script(
            'woo-taxonomy-seo-admin',
            WOO_TAXONOMY_SEO_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'wp-media-utils' ),
            WOO_TAXONOMY_SEO_VERSION,
            true
        );

        wp_localize_script(
            'woo-taxonomy-seo-admin',
            'wooTaxonomySEO',
            array(
                'mediaTitle'  => __( 'Select or Upload Image', 'woo-taxonomy-seo' ),
                'mediaButton' => __( 'Use this image', 'woo-taxonomy-seo' ),
            )
        );
    }

    /**
     * Add fields to the "Add New" term form.
     *
     * @param string $taxonomy The taxonomy slug.
     * @return void
     */
    public function add_term_fields( $taxonomy ) {
        wp_nonce_field( 'wts_save_term_meta', 'wts_term_meta_nonce' );
        ?>
        <div class="wts-seo-fields">
            <h3><?php esc_html_e( 'SEO Settings', 'woo-taxonomy-seo' ); ?></h3>
            <?php
            foreach ( $this->meta_fields as $key => $field ) {
                $this->render_add_field( $key, $field );
            }
            ?>
        </div>
        <?php
    }

    /**
     * Add fields to the "Edit" term form.
     *
     * @param WP_Term $term     The term object.
     * @param string  $taxonomy The taxonomy slug.
     * @return void
     */
    public function edit_term_fields( $term, $taxonomy ) {
        wp_nonce_field( 'wts_save_term_meta', 'wts_term_meta_nonce' );
        ?>
        <tr class="form-field wts-seo-header">
            <th colspan="2">
                <h2><?php esc_html_e( 'SEO Settings', 'woo-taxonomy-seo' ); ?></h2>
            </th>
        </tr>
        <?php
        foreach ( $this->meta_fields as $key => $field ) {
            $value = get_term_meta( $term->term_id, '_wts_' . $key, true );
            $this->render_edit_field( $key, $field, $value, $term );
        }
    }

    /**
     * Render a field for the "Add New" form.
     *
     * @param string $key   The field key.
     * @param array  $field The field configuration.
     * @return void
     */
    private function render_add_field( $key, $field ) {
        $field_id   = 'wts_' . $key;
        $field_name = '_wts_' . $key;
        ?>
        <div class="form-field wts-field wts-field-<?php echo esc_attr( $field['type'] ); ?>">
            <label for="<?php echo esc_attr( $field_id ); ?>">
                <?php echo esc_html( $field['label'] ); ?>
            </label>
            <?php $this->render_field_input( $key, $field, '', $field_id, $field_name ); ?>
            <p class="description"><?php echo esc_html( $field['description'] ); ?></p>
        </div>
        <?php
    }

    /**
     * Render a field for the "Edit" form.
     *
     * @param string  $key   The field key.
     * @param array   $field The field configuration.
     * @param string  $value The current value.
     * @param WP_Term $term  The term object.
     * @return void
     */
    private function render_edit_field( $key, $field, $value, $term ) {
        $field_id   = 'wts_' . $key;
        $field_name = '_wts_' . $key;
        ?>
        <tr class="form-field wts-field wts-field-<?php echo esc_attr( $field['type'] ); ?>">
            <th scope="row">
                <label for="<?php echo esc_attr( $field_id ); ?>">
                    <?php echo esc_html( $field['label'] ); ?>
                </label>
            </th>
            <td>
                <?php $this->render_field_input( $key, $field, $value, $field_id, $field_name ); ?>
                <p class="description"><?php echo esc_html( $field['description'] ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Render the field input element.
     *
     * @param string $key        The field key.
     * @param array  $field      The field configuration.
     * @param string $value      The current value.
     * @param string $field_id   The field ID.
     * @param string $field_name The field name.
     * @return void
     */
    private function render_field_input( $key, $field, $value, $field_id, $field_name ) {
        switch ( $field['type'] ) {
            case 'text':
            case 'url':
                $maxlength = isset( $field['maxlength'] ) ? $field['maxlength'] : '';
                ?>
                <input
                    type="<?php echo esc_attr( $field['type'] ); ?>"
                    id="<?php echo esc_attr( $field_id ); ?>"
                    name="<?php echo esc_attr( $field_name ); ?>"
                    value="<?php echo esc_attr( $value ); ?>"
                    class="large-text wts-input"
                    placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
                    <?php echo $maxlength ? 'maxlength="' . esc_attr( $maxlength ) . '"' : ''; ?>
                    data-maxlength="<?php echo esc_attr( $maxlength ); ?>"
                />
                <?php if ( $maxlength ) : ?>
                    <span class="wts-char-count">
                        <span class="wts-current">0</span> / <?php echo esc_html( $maxlength ); ?>
                    </span>
                <?php endif; ?>
                <?php
                break;

            case 'textarea':
                $maxlength = isset( $field['maxlength'] ) ? $field['maxlength'] : '';
                ?>
                <textarea
                    id="<?php echo esc_attr( $field_id ); ?>"
                    name="<?php echo esc_attr( $field_name ); ?>"
                    rows="3"
                    class="large-text wts-input"
                    placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
                    <?php echo $maxlength ? 'maxlength="' . esc_attr( $maxlength ) . '"' : ''; ?>
                    data-maxlength="<?php echo esc_attr( $maxlength ); ?>"
                ><?php echo esc_textarea( $value ); ?></textarea>
                <?php if ( $maxlength ) : ?>
                    <span class="wts-char-count">
                        <span class="wts-current">0</span> / <?php echo esc_html( $maxlength ); ?>
                    </span>
                <?php endif; ?>
                <?php
                break;

            case 'checkbox':
                ?>
                <label class="wts-checkbox-label">
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr( $field_id ); ?>"
                        name="<?php echo esc_attr( $field_name ); ?>"
                        value="1"
                        <?php checked( $value, '1' ); ?>
                    />
                    <?php echo esc_html( $field['label'] ); ?>
                </label>
                <?php
                break;

            case 'image':
                $image_url = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
                ?>
                <div class="wts-image-field">
                    <input
                        type="hidden"
                        id="<?php echo esc_attr( $field_id ); ?>"
                        name="<?php echo esc_attr( $field_name ); ?>"
                        value="<?php echo esc_attr( $value ); ?>"
                        class="wts-image-id"
                    />
                    <div class="wts-image-preview" <?php echo $image_url ? '' : 'style="display:none;"'; ?>>
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="" />
                    </div>
                    <button type="button" class="button wts-upload-image">
                        <?php esc_html_e( 'Select Image', 'woo-taxonomy-seo' ); ?>
                    </button>
                    <button type="button" class="button wts-remove-image" <?php echo $value ? '' : 'style="display:none;"'; ?>>
                        <?php esc_html_e( 'Remove Image', 'woo-taxonomy-seo' ); ?>
                    </button>
                </div>
                <?php
                break;
        }
    }

    /**
     * Save term meta fields.
     *
     * @param int $term_id The term ID.
     * @return void
     */
    public function save_term_fields( $term_id ) {
        // Verify nonce.
        if ( ! isset( $_POST['wts_term_meta_nonce'] ) ||
             ! wp_verify_nonce( sanitize_key( $_POST['wts_term_meta_nonce'] ), 'wts_save_term_meta' ) ) {
            return;
        }

        // Check permissions.
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        // Save each field.
        foreach ( $this->meta_fields as $key => $field ) {
            $field_name = '_wts_' . $key;

            if ( 'checkbox' === $field['type'] ) {
                $value = isset( $_POST[ $field_name ] ) ? '1' : '';
            } else {
                $value = isset( $_POST[ $field_name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) ) : '';
            }

            if ( empty( $value ) ) {
                delete_term_meta( $term_id, $field_name );
            } else {
                update_term_meta( $term_id, $field_name, $value );
            }
        }
    }

    /**
     * Output SEO meta tags in the head.
     *
     * @return void
     */
    public function output_seo_meta() {
        if ( ! is_tax( $this->taxonomies ) ) {
            return;
        }

        $term = get_queried_object();

        if ( ! $term || ! isset( $term->term_id ) ) {
            return;
        }

        $output = array();

        // Meta description.
        $description = $this->get_meta_description( $term );
        if ( $description ) {
            $output[] = sprintf( '<meta name="description" content="%s" />', esc_attr( $description ) );
        }

        // Robots directives.
        $robots = $this->get_robots_directives( $term );
        if ( $robots ) {
            $output[] = sprintf( '<meta name="robots" content="%s" />', esc_attr( $robots ) );
        }

        // Canonical URL.
        $canonical = $this->get_canonical_url( $term );
        if ( $canonical ) {
            $output[] = sprintf( '<link rel="canonical" href="%s" />', esc_url( $canonical ) );
        }

        // Open Graph tags.
        $og_tags = $this->get_open_graph_tags( $term );
        foreach ( $og_tags as $property => $content ) {
            $output[] = sprintf( '<meta property="%s" content="%s" />', esc_attr( $property ), esc_attr( $content ) );
        }

        // Twitter Card tags.
        $output[] = '<meta name="twitter:card" content="summary_large_image" />';

        if ( ! empty( $output ) ) {
            echo "\n<!-- WooCommerce Taxonomy SEO -->\n";
            echo implode( "\n", $output );
            echo "\n<!-- /WooCommerce Taxonomy SEO -->\n\n";
        }
    }

    /**
     * Get meta description for a term.
     *
     * @param WP_Term $term The term object.
     * @return string
     */
    public function get_meta_description( $term ) {
        $description = get_term_meta( $term->term_id, '_wts_seo_description', true );

        if ( empty( $description ) && ! empty( $term->description ) ) {
            $description = wp_trim_words( wp_strip_all_tags( $term->description ), 25, '...' );
        }

        return $description;
    }

    /**
     * Get robots directives for a term.
     *
     * @param WP_Term $term The term object.
     * @return string
     */
    public function get_robots_directives( $term ) {
        $directives = array();

        $noindex  = get_term_meta( $term->term_id, '_wts_robots_noindex', true );
        $nofollow = get_term_meta( $term->term_id, '_wts_robots_nofollow', true );

        if ( $noindex ) {
            $directives[] = 'noindex';
        } else {
            $directives[] = 'index';
        }

        if ( $nofollow ) {
            $directives[] = 'nofollow';
        } else {
            $directives[] = 'follow';
        }

        return implode( ', ', $directives );
    }

    /**
     * Get canonical URL for a term.
     *
     * @param WP_Term $term The term object.
     * @return string
     */
    public function get_canonical_url( $term ) {
        $canonical = get_term_meta( $term->term_id, '_wts_canonical_url', true );

        if ( empty( $canonical ) ) {
            $canonical = get_term_link( $term );
        }

        return is_wp_error( $canonical ) ? '' : $canonical;
    }

    /**
     * Get Open Graph tags for a term.
     *
     * @param WP_Term $term The term object.
     * @return array
     */
    public function get_open_graph_tags( $term ) {
        $tags = array();

        // OG Type.
        $tags['og:type'] = 'website';

        // OG URL.
        $tags['og:url'] = $this->get_canonical_url( $term );

        // OG Title.
        $og_title = get_term_meta( $term->term_id, '_wts_og_title', true );
        if ( empty( $og_title ) ) {
            $og_title = get_term_meta( $term->term_id, '_wts_seo_title', true );
        }
        if ( empty( $og_title ) ) {
            $og_title = $term->name;
        }
        $tags['og:title'] = $og_title;

        // OG Description.
        $og_description = get_term_meta( $term->term_id, '_wts_og_description', true );
        if ( empty( $og_description ) ) {
            $og_description = $this->get_meta_description( $term );
        }
        if ( $og_description ) {
            $tags['og:description'] = $og_description;
        }

        // OG Image.
        $og_image_id = get_term_meta( $term->term_id, '_wts_og_image', true );
        if ( $og_image_id ) {
            $image_url = wp_get_attachment_image_url( $og_image_id, 'large' );
            if ( $image_url ) {
                $tags['og:image'] = $image_url;
            }
        } elseif ( function_exists( 'get_term_meta' ) ) {
            // Try to get WooCommerce category thumbnail.
            $thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
            if ( $thumbnail_id ) {
                $image_url = wp_get_attachment_image_url( $thumbnail_id, 'large' );
                if ( $image_url ) {
                    $tags['og:image'] = $image_url;
                }
            }
        }

        // OG Site Name.
        $tags['og:site_name'] = get_bloginfo( 'name' );

        // OG Locale.
        $tags['og:locale'] = get_locale();

        return $tags;
    }

    /**
     * Filter the document title parts.
     *
     * @param array $title_parts The document title parts.
     * @return array
     */
    public function filter_document_title( $title_parts ) {
        if ( ! is_tax( $this->taxonomies ) ) {
            return $title_parts;
        }

        $term = get_queried_object();

        if ( ! $term || ! isset( $term->term_id ) ) {
            return $title_parts;
        }

        $seo_title = get_term_meta( $term->term_id, '_wts_seo_title', true );

        if ( ! empty( $seo_title ) ) {
            $title_parts['title'] = $seo_title;
        }

        return $title_parts;
    }

    /**
     * Filter Yoast SEO title (if active).
     *
     * @param string $title The title.
     * @return string
     */
    public function filter_yoast_title( $title ) {
        if ( ! is_tax( $this->taxonomies ) ) {
            return $title;
        }

        $term = get_queried_object();

        if ( ! $term || ! isset( $term->term_id ) ) {
            return $title;
        }

        $seo_title = get_term_meta( $term->term_id, '_wts_seo_title', true );

        return ! empty( $seo_title ) ? $seo_title : $title;
    }

    /**
     * Filter The SEO Framework title (if active).
     *
     * @param string $title The title.
     * @return string
     */
    public function filter_tsf_title( $title ) {
        return $this->filter_yoast_title( $title );
    }

    /**
     * Add settings link to plugins page.
     *
     * @param array $links Plugin action links.
     * @return array
     */
    public function add_settings_link( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ),
            __( 'Categories', 'woo-taxonomy-seo' )
        );

        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Declare HPOS compatibility.
     *
     * @return void
     */
    public function declare_hpos_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }

    /**
     * Get supported taxonomies.
     *
     * @return array
     */
    public function get_taxonomies() {
        return $this->taxonomies;
    }

    /**
     * Get meta fields configuration.
     *
     * @return array
     */
    public function get_meta_fields() {
        return $this->meta_fields;
    }
}

/**
 * Get the main plugin instance.
 *
 * @return Woo_Taxonomy_SEO
 */
function woo_taxonomy_seo() {
    return Woo_Taxonomy_SEO::instance();
}

// Initialize the plugin.
woo_taxonomy_seo();
