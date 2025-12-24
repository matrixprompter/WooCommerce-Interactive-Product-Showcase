<?php
/**
 * Admin functionality class
 */

if (!defined('ABSPATH')) {
    exit;
}

class IPS_Admin {
    
    private static $instance = null;
    private $db;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->db = IPS_Database::get_instance();
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Handle AJAX requests
        add_action('wp_ajax_ips_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_ips_save_showcase', array($this, 'ajax_save_showcase'));
        add_action('wp_ajax_ips_delete_showcase', array($this, 'ajax_delete_showcase'));
        add_action('wp_ajax_ips_search_products', array($this, 'ajax_search_products'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Product Showcase', 'interactive-product-showcase'),
            __('Product Showcase', 'interactive-product-showcase'),
            'manage_options',
            'interactive-product-showcase',
            array($this, 'render_admin_page'),
            'dashicons-images-alt2',
            30
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_interactive-product-showcase' !== $hook) {
            return;
        }
        
        // Enqueue WordPress media uploader
        wp_enqueue_media();
        
        // Enqueue color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue admin styles
        wp_enqueue_style(
            'ips-admin-style',
            IPS_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            IPS_VERSION
        );
        
        // Enqueue admin scripts
        wp_enqueue_script(
            'ips-admin-script',
            IPS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-draggable', 'wp-color-picker'),
            IPS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('ips-admin-script', 'ipsAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ips_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this showcase?', 'interactive-product-showcase'),
                'error' => __('An error occurred. Please try again.', 'interactive-product-showcase'),
                'saved' => __('Settings saved successfully!', 'interactive-product-showcase'),
                'showcase_saved' => __('Showcase saved successfully!', 'interactive-product-showcase'),
                'select_image' => __('Please select an image first.', 'interactive-product-showcase'),
                'select_product' => __('Please select a product.', 'interactive-product-showcase')
            )
        ));
    }
    
    public function render_admin_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'design';
        $edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
        
        ?>
        <div class="wrap ips-admin-wrap">
            <h1><?php echo esc_html__('Interactive Product Showcase', 'interactive-product-showcase'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=interactive-product-showcase&tab=design" class="nav-tab <?php echo $active_tab === 'design' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Design', 'interactive-product-showcase'); ?>
                </a>
                <a href="?page=interactive-product-showcase&tab=add-image" class="nav-tab <?php echo $active_tab === 'add-image' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Add Image', 'interactive-product-showcase'); ?>
                </a>
                <a href="?page=interactive-product-showcase&tab=images" class="nav-tab <?php echo $active_tab === 'images' ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html__('Images', 'interactive-product-showcase'); ?>
                </a>
            </h2>
            
            <div class="ips-tab-content">
                <?php
                switch ($active_tab) {
                    case 'design':
                        $this->render_design_tab();
                        break;
                    case 'add-image':
                        $this->render_add_image_tab($edit_id);
                        break;
                    case 'images':
                        $this->render_images_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    private function render_design_tab() {
        $settings = $this->db->get_all_settings();
        $tag_bg_color = isset($settings['tag_bg_color']) ? $settings['tag_bg_color'] : '#ff6b6b';
        $icon_color = isset($settings['icon_color']) ? $settings['icon_color'] : '#ffffff';
        $button_text = isset($settings['button_text']) ? $settings['button_text'] : 'View Product';
        ?>
        
        <div class="ips-design-settings">
            <form id="ips-design-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="tag_bg_color"><?php echo esc_html__('Tag Background Color', 'interactive-product-showcase'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="tag_bg_color" name="tag_bg_color" value="<?php echo esc_attr($tag_bg_color); ?>" class="ips-color-picker" />
                            <p class="description"><?php echo esc_html__('Choose the background color for the hotspot tags.', 'interactive-product-showcase'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="icon_color"><?php echo esc_html__('Icon Color', 'interactive-product-showcase'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="icon_color" name="icon_color" value="<?php echo esc_attr($icon_color); ?>" class="ips-color-picker" />
                            <p class="description"><?php echo esc_html__('Choose the color for the exclamation mark icon.', 'interactive-product-showcase'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="button_text"><?php echo esc_html__('Button Text', 'interactive-product-showcase'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="button_text" name="button_text" value="<?php echo esc_attr($button_text); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html__('Enter the text for the product link button.', 'interactive-product-showcase'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php echo esc_html__('Save Settings', 'interactive-product-showcase'); ?></button>
                </p>
            </form>
            
            <div id="ips-message" class="notice" style="display: none;"></div>
        </div>
        
        <?php
    }
    
    private function render_add_image_tab($edit_id = 0) {
        $showcase = null;
        if ($edit_id > 0) {
            $showcase = $this->db->get_showcase($edit_id);
        }
        
        $title = $showcase ? $showcase['title'] : '';
        $image_url = $showcase ? $showcase['image_url'] : '';
        $hotspots = $showcase ? $showcase['hotspots'] : array();
        ?>
        
        <div class="ips-add-image-section">
            <form id="ips-showcase-form">
                <input type="hidden" id="showcase_id" value="<?php echo esc_attr($edit_id); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="showcase_title"><?php echo esc_html__('Showcase Title', 'interactive-product-showcase'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="showcase_title" name="showcase_title" value="<?php echo esc_attr($title); ?>" class="regular-text" required />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html__('Select Image', 'interactive-product-showcase'); ?></label>
                        </th>
                        <td>
                            <button type="button" id="ips-select-image" class="button"><?php echo esc_html__('Select Image', 'interactive-product-showcase'); ?></button>
                            <input type="hidden" id="showcase_image_url" value="<?php echo esc_url($image_url); ?>">
                        </td>
                    </tr>
                </table>
                
                <div id="ips-hotspot-editor" style="<?php echo $image_url ? '' : 'display: none;'; ?>">
                    <h3><?php echo esc_html__('Add Tags', 'interactive-product-showcase'); ?></h3>
                    <p class="description"><?php echo esc_html__('Click on the image to add product tags.', 'interactive-product-showcase'); ?></p>
                    
                    <div id="ips-image-container" style="position: relative; display: inline-block; margin-top: 20px;">
                        <?php if ($image_url): ?>
                            <img id="ips-main-image" src="<?php echo esc_url($image_url); ?>" style="max-width: 100%; height: auto;">
                            <?php foreach ($hotspots as $index => $hotspot): ?>
                                <div class="ips-hotspot-marker" data-index="<?php echo esc_attr($index); ?>" style="position: absolute; left: <?php echo esc_attr($hotspot['x']); ?>%; top: <?php echo esc_attr($hotspot['y']); ?>%;">
                                    <span class="ips-hotspot-number"><?php echo esc_html($index + 1); ?></span>
                                    <button type="button" class="ips-remove-hotspot">×</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" id="hotspots_data" value='<?php echo esc_attr(wp_json_encode($hotspots)); ?>'>
                </div>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large"><?php echo esc_html__('Save Showcase', 'interactive-product-showcase'); ?></button>
                </p>
            </form>
            
            <div id="ips-message" class="notice" style="display: none;"></div>
        </div>
        
        <!-- Product Selection Modal -->
        <div id="ips-product-modal" class="ips-modal" style="display: none;">
            <div class="ips-modal-content">
                <span class="ips-modal-close">&times;</span>
                <h2><?php echo esc_html__('Select Product', 'interactive-product-showcase'); ?></h2>
                
                <div class="ips-product-search">
                    <input type="text" id="ips-product-search-input" placeholder="<?php echo esc_attr__('Search products...', 'interactive-product-showcase'); ?>" class="regular-text">
                    <button type="button" id="ips-product-search-btn" class="button"><?php echo esc_html__('Search', 'interactive-product-showcase'); ?></button>
                </div>
                
                <div id="ips-product-results" class="ips-product-list"></div>
                
                <div id="ips-product-details" style="display: none;">
                    <h3><?php echo esc_html__('Product Details', 'interactive-product-showcase'); ?></h3>
                    <input type="hidden" id="selected_product_id">
                    
                    <table class="form-table">
                        <tr>
                            <th><label><?php echo esc_html__('Product Image', 'interactive-product-showcase'); ?></label></th>
                            <td><img id="selected_product_image" src="" style="max-width: 150px; height: auto;"></td>
                        </tr>
                        <tr>
                            <th><label><?php echo esc_html__('Product Title', 'interactive-product-showcase'); ?></label></th>
                            <td><span id="selected_product_title"></span></td>
                        </tr>
                        <tr>
                            <th><label for="product_description"><?php echo esc_html__('Description', 'interactive-product-showcase'); ?></label></th>
                            <td>
                                <textarea id="product_description" rows="4" class="large-text" placeholder="<?php echo esc_attr__('Enter a short product description...', 'interactive-product-showcase'); ?>"></textarea>
                                <p class="description"><?php echo esc_html__('Enter a custom description for this product.', 'interactive-product-showcase'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <button type="button" id="ips-save-hotspot" class="button button-primary"><?php echo esc_html__('Add Hotspot', 'interactive-product-showcase'); ?></button>
                </div>
            </div>
        </div>
        
        <?php
    }
    
    private function render_images_tab() {
        $showcases = $this->db->get_all_showcases();
        ?>
        
        <div class="ips-images-list">
            <h2><?php echo esc_html__('All Showcases', 'interactive-product-showcase'); ?></h2>
            
            <?php if (empty($showcases)): ?>
                <p><?php echo esc_html__('No showcases found. Create your first one!', 'interactive-product-showcase'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Preview', 'interactive-product-showcase'); ?></th>
                            <th><?php echo esc_html__('Title', 'interactive-product-showcase'); ?></th>
                            <th><?php echo esc_html__('Shortcode', 'interactive-product-showcase'); ?></th>
                            <th><?php echo esc_html__('Hotspots', 'interactive-product-showcase'); ?></th>
                            <th><?php echo esc_html__('Created', 'interactive-product-showcase'); ?></th>
                            <th><?php echo esc_html__('Actions', 'interactive-product-showcase'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($showcases as $showcase): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo esc_url($showcase['image_url']); ?>" style="max-width: 100px; height: auto;">
                                </td>
                                <td><?php echo esc_html($showcase['title']); ?></td>
                                <td>
                                    <input type="text" readonly value='[ips_showcase id="<?php echo esc_attr($showcase['id']); ?>"]' class="regular-text ips-shortcode-input" onclick="this.select();">
                                    <button type="button" class="button button-small ips-copy-shortcode" data-shortcode='[ips_showcase id="<?php echo esc_attr($showcase['id']); ?>"]'>
                                        <?php echo esc_html__('Copy', 'interactive-product-showcase'); ?>
                                    </button>
                                </td>
                                <td><?php echo esc_html(count($showcase['hotspots'])); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($showcase['created_at']))); ?></td>
                                <td>
                                    <a href="?page=interactive-product-showcase&tab=add-image&edit=<?php echo esc_attr($showcase['id']); ?>" class="button button-small">
                                        <?php echo esc_html__('Edit', 'interactive-product-showcase'); ?>
                                    </a>
                                    <button type="button" class="button button-small ips-delete-showcase" data-id="<?php echo esc_attr($showcase['id']); ?>">
                                        <?php echo esc_html__('Delete', 'interactive-product-showcase'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <?php
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('ips_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'interactive-product-showcase')));
        }
        
        $tag_bg_color = isset($_POST['tag_bg_color']) ? sanitize_hex_color($_POST['tag_bg_color']) : '';
        $icon_color = isset($_POST['icon_color']) ? sanitize_hex_color($_POST['icon_color']) : '';
        $button_text = isset($_POST['button_text']) ? sanitize_text_field($_POST['button_text']) : '';
        
        $this->db->update_setting('tag_bg_color', $tag_bg_color);
        $this->db->update_setting('icon_color', $icon_color);
        $this->db->update_setting('button_text', $button_text);
        
        wp_send_json_success(array('message' => __('Settings saved successfully!', 'interactive-product-showcase')));
    }
    
    public function ajax_save_showcase() {
        check_ajax_referer('ips_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'interactive-product-showcase')));
        }
        
        $showcase_id = isset($_POST['showcase_id']) ? absint($_POST['showcase_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
        $hotspots = isset($_POST['hotspots']) ? json_decode(stripslashes($_POST['hotspots']), true) : array();
        
        if (empty($title) || empty($image_url)) {
            wp_send_json_error(array('message' => __('Title and image are required.', 'interactive-product-showcase')));
        }
        
        // Clean HTML from descriptions
        foreach ($hotspots as &$hotspot) {
            if (isset($hotspot['description'])) {
                $hotspot['description'] = wp_strip_all_tags($hotspot['description']);
            }
        }
        
        $data = array(
            'title' => $title,
            'image_url' => $image_url,
            'hotspots' => $hotspots
        );
        
        if ($showcase_id > 0) {
            $result = $this->db->update_showcase($showcase_id, $data);
            $id = $showcase_id;
        } else {
            $id = $this->db->create_showcase($data);
            $result = $id !== false;
        }
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Showcase saved successfully!', 'interactive-product-showcase'),
                'showcase_id' => $id,
                'shortcode' => '[ips_showcase id="' . $id . '"]'
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save showcase.', 'interactive-product-showcase')));
        }
    }
    
    public function ajax_delete_showcase() {
        check_ajax_referer('ips_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'interactive-product-showcase')));
        }
        
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        
        if ($this->db->delete_showcase($id)) {
            wp_send_json_success(array('message' => __('Showcase deleted successfully!', 'interactive-product-showcase')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete showcase.', 'interactive-product-showcase')));
        }
    }
    
    public function ajax_search_products() {
        check_ajax_referer('ips_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'interactive-product-showcase')));
        }
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 20,
            's' => $search,
            'post_status' => 'publish'
        );
        
        $products = get_posts($args);
        
        $results = array();
        foreach ($products as $product) {
            $product_obj = wc_get_product($product->ID);
            if ($product_obj) {
                $results[] = array(
                    'id' => $product->ID,
                    'title' => $product->post_title,
                    'image' => get_the_post_thumbnail_url($product->ID, 'medium'),
                    'url' => get_permalink($product->ID),
                    'description' => $product_obj->get_short_description() ? $product_obj->get_short_description() : wp_trim_words($product->post_content, 20)
                );
            }
        }
        
        wp_send_json_success($results);
    }
}