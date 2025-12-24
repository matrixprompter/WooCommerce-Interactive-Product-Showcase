<?php
/**
 * Plugin Name: Interactive Product Showcase
 * Description: Add interactive hotspots to images to showcase products with popups
 * Version: 1.0.0
 * Author: Samet Gunduz
 * Text Domain: interactive-product-showcase
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('IPS_VERSION', '1.0.0');
define('IPS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IPS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IPS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once IPS_PLUGIN_DIR . 'includes/class-ips-database.php';
require_once IPS_PLUGIN_DIR . 'includes/class-ips-admin.php';
require_once IPS_PLUGIN_DIR . 'includes/class-ips-shortcode.php';

// Initialize plugin
class Interactive_Product_Showcase {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize components
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('interactive-product-showcase', false, dirname(IPS_PLUGIN_BASENAME) . '/languages');
        
        // Initialize database
        IPS_Database::get_instance();
        
        // Initialize admin
        if (is_admin()) {
            IPS_Admin::get_instance();
        }
        
        // Initialize shortcode
        IPS_Shortcode::get_instance();
        
        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function activate() {
        IPS_Database::create_tables();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'ips-frontend-style',
            IPS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            IPS_VERSION
        );
        
        wp_enqueue_script(
            'ips-frontend-script',
            IPS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            IPS_VERSION,
            true
        );
    }
}

// Initialize plugin
function ips_init() {
    return Interactive_Product_Showcase::get_instance();
}

// Start the plugin
ips_init();