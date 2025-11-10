<?php
/**
 * Plugin Name: Easy Sitemap
 * Plugin URI:  https://github.com/snowbedding/easy-sitemap
 * Description: Advanced WordPress HTML sitemap plugin with modern PSR-4 autoloading, OOP architecture, intelligent caching, and comprehensive filtering capabilities. Perfect for user navigation and SEO.
 * Version: 2.0.0
 * Author: snowbedding
 * Author URI: https://github.com/snowbedding
 * Text Domain: easy-sitemap
 * Domain Path: /languages
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Tested up to: 6.9
 * Requires PHP: 7.2
 *
 * @package EasySitemap
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EASY_SITEMAP_VERSION', '2.0.0');
define('EASY_SITEMAP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EASY_SITEMAP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EASY_SITEMAP_PLUGIN_FILE', __FILE__);

// Include the autoloader
require_once EASY_SITEMAP_PLUGIN_DIR . 'classes/Autoloader.php';

// Register the autoloader
EasySitemap\Autoloader::register(EASY_SITEMAP_PLUGIN_DIR);

// Initialize the plugin
$easy_sitemap_plugin = EasySitemap\Plugin::get_instance();

/**
 * Legacy function for backward compatibility
 * @deprecated Use EasySitemap\Plugin::get_instance() instead
 */
function easy_sitemap_init() {
    return EasySitemap\Plugin::get_instance();
}