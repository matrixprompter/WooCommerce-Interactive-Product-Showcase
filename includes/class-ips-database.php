<?php
/**
 * Database handler class
 */

if (!defined('ABSPATH')) {
    exit;
}

class IPS_Database {
    
    private static $instance = null;
    private $table_name;
    private $settings_table;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ips_showcases';
        $this->settings_table = $wpdb->prefix . 'ips_settings';
    }
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $showcases_table = $wpdb->prefix . 'ips_showcases';
        $settings_table = $wpdb->prefix . 'ips_settings';
        
        $sql_showcases = "CREATE TABLE IF NOT EXISTS $showcases_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            image_url text NOT NULL,
            hotspots longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        $sql_settings = "CREATE TABLE IF NOT EXISTS $settings_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_showcases);
        dbDelta($sql_settings);
        
        // Insert default settings
        self::insert_default_settings();
    }
    
    private static function insert_default_settings() {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'ips_settings';
        
        $default_settings = array(
            array('setting_key' => 'tag_bg_color', 'setting_value' => '#ff6b6b'),
            array('setting_key' => 'icon_color', 'setting_value' => '#ffffff'),
            array('setting_key' => 'button_text', 'setting_value' => 'View Product')
        );
        
        foreach ($default_settings as $setting) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $settings_table WHERE setting_key = %s",
                $setting['setting_key']
            ));
            
            if (!$exists) {
                $wpdb->insert($settings_table, $setting);
            }
        }
    }
    
    public function get_setting($key, $default = '') {
        global $wpdb;
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM {$this->settings_table} WHERE setting_key = %s",
            $key
        ));
        
        return $value ? $value : $default;
    }
    
    public function update_setting($key, $value) {
        global $wpdb;
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->settings_table} WHERE setting_key = %s",
            $key
        ));
        
        if ($exists) {
            return $wpdb->update(
                $this->settings_table,
                array('setting_value' => $value),
                array('setting_key' => $key)
            );
        } else {
            return $wpdb->insert(
                $this->settings_table,
                array(
                    'setting_key' => $key,
                    'setting_value' => $value
                )
            );
        }
    }
    
    public function get_all_settings() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT setting_key, setting_value FROM {$this->settings_table}",
            ARRAY_A
        );
        
        $settings = array();
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    public function create_showcase($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'title' => sanitize_text_field($data['title']),
                'image_url' => esc_url_raw($data['image_url']),
                'hotspots' => wp_json_encode($data['hotspots'])
            )
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    public function update_showcase($id, $data) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array(
                'title' => sanitize_text_field($data['title']),
                'image_url' => esc_url_raw($data['image_url']),
                'hotspots' => wp_json_encode($data['hotspots'])
            ),
            array('id' => absint($id))
        );
    }
    
    public function get_showcase($id) {
        global $wpdb;
        
        $showcase = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ), ARRAY_A);
        
        if ($showcase) {
            $showcase['hotspots'] = json_decode($showcase['hotspots'], true);
        }
        
        return $showcase;
    }
    
    public function get_all_showcases() {
        global $wpdb;
        
        $showcases = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC",
            ARRAY_A
        );
        
        foreach ($showcases as &$showcase) {
            $showcase['hotspots'] = json_decode($showcase['hotspots'], true);
        }
        
        return $showcases;
    }
    
    public function delete_showcase($id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => absint($id))
        );
    }
}