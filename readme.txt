=== WooCommerce Taxonomy SEO ===
Contributors: Mohcin Bounouara
Tags: woocommerce, seo, product categories, product tags, meta description
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SEO optimization for WooCommerce product categories and tags. Add custom meta titles, descriptions, canonical URLs, and robots directives.

== Description ==

**WooCommerce Taxonomy SEO** provides complete SEO control for your WooCommerce product categories and product tags. Unlike general SEO plugins that may not fully support WooCommerce taxonomies, this plugin is specifically designed for e-commerce stores.

= Features =

* **SEO Title** - Custom title tags for category and tag pages
* **Meta Description** - Custom meta descriptions with character counter
* **Canonical URL** - Set custom canonical URLs to avoid duplicate content
* **Robots Directives** - Control indexing with noindex/nofollow options
* **Open Graph Tags** - Custom social media titles, descriptions, and images
* **Twitter Card Support** - Automatic Twitter Card meta tags
* **Character Counters** - Real-time character counting for optimal SEO
* **WooCommerce Integration** - Uses category thumbnails as fallback OG images
* **Lightweight** - No bloat, only what you need for taxonomy SEO
* **Compatible** - Works alongside Yoast SEO, The SEO Framework, and other SEO plugins

= Why Use This Plugin? =

Many SEO plugins provide limited support for WooCommerce product categories and tags. This plugin fills that gap by providing:

1. **Dedicated SEO fields** directly on the category/tag edit screens
2. **E-commerce focused** features like automatic product image fallbacks
3. **Lightweight code** that doesn't slow down your store
4. **Full control** over how search engines see your taxonomy pages

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 5.0 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the `woo-taxonomy-seo` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Products > Categories or Products > Tags to add SEO settings

= Manual Installation =

1. Download the plugin zip file
2. Go to Plugins > Add New > Upload Plugin
3. Upload the zip file and click "Install Now"
4. Activate the plugin

== Frequently Asked Questions ==

= Does this plugin work with other SEO plugins? =

Yes! WooCommerce Taxonomy SEO is designed to work alongside popular SEO plugins like Yoast SEO and The SEO Framework. It will override their taxonomy settings when you provide custom values.

= Will this slow down my site? =

No. The plugin is lightweight and only loads its assets on taxonomy edit screens in the admin. Frontend output is minimal and optimized.

= Can I use this for custom taxonomies? =

Currently, the plugin supports WooCommerce's built-in `product_cat` and `product_tag` taxonomies. Support for custom taxonomies may be added in future versions.

= What happens if I leave a field empty? =

Empty fields will use sensible defaults:
- SEO Title: Uses the term name
- Meta Description: Uses the term description (if available)
- Canonical URL: Uses the default term permalink
- OG Image: Falls back to the WooCommerce category thumbnail

= Is this plugin GDPR compliant? =

Yes. This plugin does not collect, store, or transmit any personal data. All SEO metadata is stored locally in your WordPress database.

== Changelog ==

= 1.0.0 =
* Initial release
* SEO title and meta description fields
* Canonical URL support
* Robots noindex/nofollow directives
* Open Graph title, description, and image
* Twitter Card support
* Character counters with visual feedback
* WooCommerce HPOS compatibility

== Upgrade Notice ==

= 1.0.0 =
Initial release of WooCommerce Taxonomy SEO.

== Privacy Policy ==

WooCommerce Taxonomy SEO does not:
* Track users
* Collect personal data
* Send data to external servers
* Use cookies

All data is stored locally in your WordPress database as term meta.
