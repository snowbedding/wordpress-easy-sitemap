# Easy Sitemap (WordPress Plugin)

Advanced HTML sitemap generator for WordPress with modern PSR-4 autoloading, OOP architecture, intelligent caching, and extensive customization options.

**Version:** 2.0.0

## 📞 Support

For support, bug reports, or feature requests:
- [WordPress Plugin Directory](https://wordpress.org/plugins/easy-sitemap/)
- [Easy Sitemap Support](https://github.com/snowbedding/wordpress-easy-sitemap/issues)

## ✨ Features

- **Modern Architecture**: PSR-4 autoloading with clean OOP design
- **Powerful Shortcode**: Single comprehensive `[easy_sitemap]` shortcode with extensive attributes
- **Visual Shortcode Generator**: Interactive builder in admin panel
- **Intelligent Caching**: Transient-based caching with configurable expiry (guests only)
- **Universal CPT Support**: Automatic support for all public custom post types including WooCommerce
- **Smart Taxonomy Detection**: Automatic taxonomy resolution for each post type
- **Advanced Filtering**: Post types, categories, tags, taxonomies, authors, date ranges, inclusions/exclusions
- **Hierarchical Display**: Pages by parent-child relationships, posts grouped by categories
- **Display Options**: Publication dates, excerpts, featured images
- **Multi-Column Layout**: Responsive grid layouts (up to 6 columns)
- **Custom CSS**: Safe inline styling without theme modifications
- **Performance Optimized**: Efficient database queries with `no_found_rows` optimization
- **SEO Friendly**: Clean, semantic markup with proper structure
- **Quicktags Support**: Classic editor integration for easy shortcode insertion

## 📋 Requirements

- **WordPress**: 5.0+
- **PHP**: 7.2+
- **MySQL**: 5.6+

## 🚀 Installation

1. Download and extract the plugin files
2. Upload the `easy-sitemap` folder to `wp-content/plugins/`
3. Activate the plugin through WordPress admin → **Plugins**
4. Go to **Settings → Easy Sitemap** to configure options
5. Use the Shortcode Generator or add shortcodes to your content

## 📖 Usage

### Basic Usage

Insert the shortcode into any page, post, or custom post type:

```php
[easy_sitemap]
```

This displays a complete sitemap with all posts, pages, and custom post types.

### Shortcode Attributes

#### Core Attributes
- `post_type` - Filter by post type (e.g., `post`, `page`, `product`, `portfolio`)
- `limit` - Number of items to display (default: 1000, max: 1000)
- `orderby` - Sort order: `date`, `title`, `modified`, `menu_order`, `rand`, `ID`, `author`, `name` (default: `date`)
- `order` - Sort direction: `DESC` (default) or `ASC`

#### Content Filtering
- `include` - Include specific post IDs (comma-separated): `1,2,3`
- `exclude` - Exclude specific post IDs (comma-separated): `4,5,6`
- `category` - Filter by category slugs (comma-separated): `news,blog`
- `tag` - Filter by tag slugs (comma-separated): `featured,popular`
- `taxonomy` + `term` - Custom taxonomy filtering: `taxonomy="product_cat" term="electronics"`
- `author` - Filter by author ID: `1`
- `date_from` / `date_to` - Date range (YYYY-MM-DD format): `date_from="2023-01-01"`

#### Display Options
- `show_dates` - Show publication dates (0/1, default: 0)
- `show_excerpts` - Show post excerpts (0/1, default: 0)
- `show_images` - Show featured images (0/1, default: 0)
- `hierarchical` - Hierarchical display for pages/posts (0/1)
- `depth` - Hierarchy depth (0 = unlimited, default: 0)
- `columns` - Number of columns (default: 1, max: 6)
- `class` - Custom CSS class for styling

#### Performance Options
- `cache` - Enable caching for this instance (0/1, default: use global setting)
- `cache_expiry` - Cache lifetime in seconds (default: 3600)

### Usage Examples

```php
// Basic sitemap with date display
[easy_sitemap show_dates="1"]

// Posts from specific categories
[easy_sitemap post_type="post" category="news,blog" limit="20"]

// Hierarchical pages ordered by menu
[easy_sitemap post_type="page" hierarchical="1" orderby="menu_order"]

// WooCommerce products with images
[easy_sitemap post_type="product" show_images="1" limit="12"]

// Custom post type with excerpts
[easy_sitemap post_type="portfolio" show_excerpts="1" show_images="1"]

// Filtered by date range and author
[easy_sitemap author="1" date_from="2023-01-01" date_to="2023-12-31"]

// Custom taxonomy filtering
[easy_sitemap taxonomy="product_cat" term="electronics" show_images="1"]

// Multi-column display
[easy_sitemap post_type="post" columns="3" limit="30"]
```

## ⚙️ Configuration

### Shortcode Generator
Located in **Settings → Easy Sitemap**:

1. Select post type from dropdown
2. Set display limits and sorting options
3. Configure filtering (categories, tags, etc.)
4. Toggle display options (dates, excerpts, images)
5. Generated shortcode appears automatically
6. Click "Copy Shortcode" or select manually

### Performance Settings

#### Caching Configuration
- **Enable Caching**: Global toggle for caching system
- **Cache Expiry**: Lifetime in seconds (300-86400, default: 3600)
- **Per-Shortcode Control**: Override global settings with `cache` attribute

#### How Caching Works
- Uses WordPress transients for efficient storage
- Only caches output for non-logged-in users (guests) to ensure dynamic content for logged-in users
- Cache keys are generated from shortcode attributes and content parameters for uniqueness
- Default cache expiry: 3600 seconds (1 hour), configurable from 300-86400 seconds
- Configurable global cache settings in Settings → Easy Sitemap
- Override global settings per shortcode with `cache="0"` to disable caching for dynamic content
- Automatic cache clearing on plugin deactivation

### Custom Styling

Add custom CSS in **Settings → Easy Sitemap → Custom CSS**:

```css
/* Main wrapper */
.easy-sitemap {
    margin: 20px 0;
}

/* List styling */
.easy-sitemap-list {
    list-style: none;
    padding-left: 0;
}

/* Individual items */
.easy-sitemap-item {
    margin: 8px 0;
    padding: 5px 0;
}

/* Date styling */
.easy-sitemap-date {
    color: #666;
    font-size: 0.9em;
}

/* Excerpt styling */
.easy-sitemap-excerpt {
    margin: 5px 0;
    font-style: italic;
}

/* Image styling */
.easy-sitemap-image {
    max-width: 100px;
    height: auto;
    margin: 5px 10px 5px 0;
}
```

## 🔧 Advanced Usage

### Custom Post Types

The plugin automatically detects and supports all public custom post types:

```php
// Any registered CPT
[easy_sitemap post_type="portfolio"]
[easy_sitemap post_type="event"]
[easy_sitemap post_type="testimonial"]
```

### Taxonomy Integration

Smart taxonomy detection for filtering:

```php
// WooCommerce
[easy_sitemap post_type="product" taxonomy="product_cat" term="electronics"]
[easy_sitemap post_type="product" taxonomy="product_tag" term="featured"]

// Custom taxonomies
[easy_sitemap post_type="portfolio" taxonomy="portfolio_category" term="web-design"]
```

## 🐛 Troubleshooting

### Featured Images Not Showing
- Ensure `show_images="1"` is set
- Verify posts have featured images set
- Plugin automatically enables thumbnail support for all public post types

### Shortcode Generator Issues
- Try manual copy if "Copy Shortcode" button fails
- Check browser clipboard permissions
- Textbox is auto-selected for manual selection

### Performance Issues
- Enable caching in settings
- Increase cache expiry for stable content
- Use `limit` attribute to reduce output
- Disable caching per shortcode with `cache="0"` for dynamic content

### Styling Conflicts
- Use browser developer tools to inspect elements
- Add custom CSS with higher specificity
- Use the `class` attribute for unique styling

## 🏗️ Development

### Architecture

Modern PSR-4 autoloaded architecture with clean separation of concerns:

```
classes/
├── EasySitemap/
│   ├── Admin/
│   │   └── Admin.php           # Admin interface and settings
│   ├── Frontend/
│   │   ├── Assets.php          # Frontend asset management
│   │   └── Shortcodes.php      # Shortcode processing engine
│   ├── Autoloader.php          # PSR-4 autoloading system
│   └── Plugin.php              # Main plugin bootstrap and initialization
├── assets/
│   ├── css/
│   │   ├── admin.css           # Admin interface styles
│   │   └── frontend.css        # Frontend sitemap styles
│   └── js/
│       ├── admin.js            # Admin functionality
│       └── frontend.js         # Frontend interactions
├── languages/
│   ├── sb-easy-sitemap-zh_CN.mo # Chinese translations (compiled)
│   └── sb-easy-sitemap-zh_CN.po # Chinese translations (source)
└── index.php                   # Plugin entry point
```

### Key Classes

- `EasySitemap\Plugin` - Main plugin controller
- `EasySitemap\Admin\Admin` - Admin settings and interface
- `EasySitemap\Frontend\Shortcodes` - Shortcode processing engine
- `EasySitemap\Frontend\Assets` - Asset management

### Hooks and Filters

```php
// Custom image size filter for featured images
add_filter( 'easy_sitemap_image_size', function( $size ) {
    return 'medium'; // Default: 'thumbnail'
} );

// Custom CSS class filter for main wrapper
add_filter( 'easy_sitemap_wrapper_class', function( $class ) {
    return $class . ' custom-wrapper';
} );

// Modify shortcode attributes before processing
add_filter( 'easy_sitemap_shortcode_atts', function( $atts ) {
    // Modify attributes here
    return $atts;
} );

// Modify query arguments before execution
add_filter( 'easy_sitemap_query_args', function( $query_args, $atts ) {
    // Modify query arguments here
    return $query_args;
}, 10, 2 );

// Modify the final output HTML
add_filter( 'easy_sitemap_output', function( $output, $atts, $posts ) {
    // Modify or extend output here
    return $output;
}, 10, 3 );

// Custom post type support filter
add_filter( 'easy_sitemap_supported_post_types', function( $post_types ) {
    // Add or remove supported post types
    return $post_types;
} );
```

### Internationalization

- Text domain: `easy-sitemap`
- Translation files: `languages/` directory
- Currently includes Chinese (zh_CN) translations
- Fully translatable admin interface and frontend output

## 📄 License

Licensed under the GNU General Public License v2.0 or later.

```
Copyright (C) 2024 pandasilk

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**Made with ❤️ for the WordPress community**


