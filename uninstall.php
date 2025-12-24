<?php
/**
 * Uninstall script
 * Fires when the plugin is uninstalled
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Define table names
$showcases_table = $wpdb->prefix . 'ips_showcases';
$settings_table = $wpdb->prefix . 'ips_settings';

// Drop tables
$wpdb->query("DROP TABLE IF EXISTS $showcases_table");
$wpdb->query("DROP TABLE IF EXISTS $settings_table");

// Delete options
delete_option('ips_version');
delete_option('ips_db_version');

// Clear any cached data
wp_cache_flush();