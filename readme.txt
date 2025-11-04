=== Easy Sitemap ===
Contributors: snowbedding
Tags: sitemap, html sitemap, shortcode, seo, navigation
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.2
Stable tag: 2.0.0
License: GPLv2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced HTML sitemap plugin with shortcode generator, intelligent caching, and comprehensive filtering for posts, pages, and custom post types.

== Description ==

Easy Sitemap is a powerful WordPress plugin that creates comprehensive HTML sitemaps for your website. With modern PSR-4 autoloading and clean OOP architecture, it provides flexible content organization and optimal performance through intelligent caching.

### ✨ Key Features

* **Modern Architecture**: PSR-4 autoloading with clean object-oriented design
* **HTML Sitemaps**: Generate clean, SEO-friendly HTML sitemaps with semantic markup
* **Powerful Shortcode**: Single comprehensive `[easy_sitemap]` shortcode with extensive attributes
* **Universal CPT Support**: Automatic support for all public custom post types including WooCommerce
* **Smart Taxonomy Detection**: Intelligent taxonomy resolution for each post type
* **Advanced Filtering**: Post types, categories, tags, taxonomies, authors, date ranges, inclusions/exclusions
* **Hierarchical Display**: Pages by parent-child relationships, posts grouped by categories
* **Smart Caching**: Transient-based caching for guests only with configurable expiry
* **Responsive Design**: Mobile-optimized layouts with multi-column support (up to 6 columns)
* **Shortcode Generator**: Interactive visual builder in admin panel
* **Custom CSS**: Safe inline styling without theme modifications
* **Performance Optimized**: Efficient database queries with no_found_rows optimization
* **Quicktags Support**: Classic editor integration for easy shortcode insertion

### 🚀 Shortcodes

* `[easy_sitemap]` - Complete sitemap with all content types

### 📋 Shortcode Attributes

**Basic Attributes:**
* `post_type` - Filter by post type (post, page, product, etc.)
* `limit` - Number of items to display (default: 1000)
* `orderby` - Sort by: date, title, modified, menu_order, rand, ID, author, name (default: date)
* `order` - Sort order: DESC (default) or ASC

**Filtering Attributes:**
* `include` - Include specific post IDs (comma-separated)
* `exclude` - Exclude specific post IDs (comma-separated)
* `category` - Filter by category slugs (comma-separated)
* `tag` - Filter by tag slugs (comma-separated)
* `taxonomy` + `term` - Filter by custom taxonomy
* `author` - Filter by author ID
* `date_from` / `date_to` - Date range in YYYY-MM-DD format

**Display Attributes:**
* `show_dates` - Show publication dates (0/1, default: 0)
* `show_excerpts` - Show post excerpts (0/1, default: 0)
* `show_images` - Show featured images (0/1, default: 0)
* `hierarchical` - Hierarchical display for pages/posts (0/1)
* `depth` - Hierarchy depth (0 = unlimited)
* `columns` - Number of columns (default: 1, max: 6)
* `class` - Custom CSS class for styling

**Performance Attributes:**
* `cache` - Enable caching for this shortcode (0/1, default: settings)
* `cache_expiry` - Cache lifetime in seconds

### 🔧 Admin Features

* **Settings Panel**: Configure caching and styling
* **Shortcode Generator**: Visual builder with live preview
* **Performance Settings**: Cache expiry and optimization options
* **Custom CSS Editor**: Add custom styles safely

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/easy-sitemap/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Easy Sitemap to configure options
4. Use the Shortcode Generator or add shortcodes manually to your pages

== Frequently Asked Questions ==

= How do I create a sitemap page? =

1. Create a new page in WordPress (Pages → Add New)
2. Add the shortcode `[easy_sitemap]` to the page content
5. Publish the page

= How do I use the Shortcode Generator? =

1. Go to Settings → Easy Sitemap
2. Use the "Build your shortcode" section at the top
3. Select your desired options (post type, limit, display options, etc.)
4. The shortcode will be generated automatically
5. Click "Copy Shortcode" or select and copy manually
6. Paste into any page or post

= Does it support custom post types? =

Yes! The plugin automatically supports all public custom post types. Use the `post_type` attribute:

```
[easy_sitemap post_type="product"]
[easy_sitemap post_type="portfolio"]
[easy_sitemap post_type="event"]
```

= How does the caching system work? =

The plugin uses WordPress transients for efficient caching:
- Only caches output for non-logged-in users (guests) to ensure dynamic content for logged-in users
- Cache keys are generated from shortcode attributes and content parameters for uniqueness
- Default cache expiry is 1 hour (3600 seconds), configurable from 5 minutes to 24 hours (300-86400 seconds)
- Configure global cache settings in Settings → Easy Sitemap
- Override global settings per shortcode with `cache="0"` to disable caching for dynamic content
- Automatic cache clearing on plugin deactivation

= Can I customize the appearance? =

Yes! Multiple customization options:

1. **Custom CSS**: Add styles in Settings → Easy Sitemap → Custom CSS
2. **CSS Classes**: Use the `class` attribute for custom styling
3. **Responsive**: All layouts are mobile-friendly by default

Common CSS selectors:
- `.easy-sitemap` - Main wrapper
- `.easy-sitemap-list` - List container
- `.easy-sitemap-item` - Individual items
- `.easy-sitemap-date` - Date display
- `.easy-sitemap-excerpt` - Excerpt text
- `.easy-sitemap-image` - Featured images

= How do I filter content? =

Use various filtering attributes:

```
[easy_sitemap post_type="post" category="news,blog"]
[easy_sitemap author="1" date_from="2023-01-01"]
[easy_sitemap tag="featured" show_images="1"]
[easy_sitemap taxonomy="product_cat" term="electronics"]
```

= Does it work with WooCommerce? =

Yes! Full WooCommerce support:
- Product sitemaps: `[easy_sitemap post_type="product"]`
- Product category filtering: `taxonomy="product_cat" term="category-slug"`
- Product tag filtering: `taxonomy="product_tag" term="tag-slug"`
- Featured images display for products

== Screenshots ==
1. Frontend Display
2. Shortcode Generator
3. All Supported Attributes

== Changelog ==

= 2.0.0 =
* Complete rewrite with modern PSR-4 autoloading and clean OOP architecture
* Implemented transient-based caching system with guest-only caching for optimal performance
* Enhanced `[easy_sitemap]` shortcode with comprehensive filtering and display options
* Added hierarchical display for pages (parent-child relationships) and category grouping for posts
* Introduced interactive visual shortcode generator in admin panel with live preview
* Added automatic support for all public custom post types with intelligent taxonomy detection
* Implemented responsive multi-column layouts (up to 6 columns) with mobile optimization and CSS Grid/Flexbox fallback
* Added advanced filtering: date ranges, author filtering, taxonomy filtering, post inclusions/exclusions
* Added display options: publication dates, excerpts, featured images with automatic thumbnail support
* Added custom CSS support with safe inline styling and CSS class attributes
* Improved performance with optimized database queries using no_found_rows optimization
* Enhanced security with comprehensive input sanitization and output escaping
* Simplified admin interface with unified settings page containing all configuration options
* Added Quicktags support for classic editor integration
* Added Chinese (zh_CN) language support
* Focus on HTML sitemaps with clean, semantic markup and SEO-friendly structure
* Added extensive hooks and filters for developers

= 1.0 =
* Initial release with basic shortcode functionality
* Support for posts, pages, and WooCommerce products
* Basic quicktags for editor integration

== Upgrade Notice ==

= 2.0.0 =
This major update includes a complete architecture rewrite with modern PSR-4 autoloading, intelligent caching, and comprehensive filtering options. We recommend exploring the new visual shortcode generator for enhanced functionality.

== Support ==

For support, bug reports, or feature requests, please visit:
[Easy Sitemap Support](https://github.com/snowbedding/wordpress-easy-sitemap)

== Contributing ==

Contributions are welcome! Please feel free to submit pull requests or open issues on GitHub.

== License ==

This plugin is licensed under the GPLv2 or later.
License URI: https://www.gnu.org/licenses/gpl-2.0.html