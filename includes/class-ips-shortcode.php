<?php
/**
 * Shortcode handler class
 */

if (!defined('ABSPATH')) {
    exit;
}

class IPS_Shortcode {
    
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
        add_shortcode('ips_showcase', array($this, 'render_showcase'));
    }
    
    public function render_showcase($atts) {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts);
        
        $showcase_id = absint($atts['id']);
        
        if ($showcase_id === 0) {
            return '<p>' . esc_html__('Invalid showcase ID.', 'interactive-product-showcase') . '</p>';
        }
        
        $showcase = $this->db->get_showcase($showcase_id);
        
        if (!$showcase) {
            return '<p>' . esc_html__('Showcase not found.', 'interactive-product-showcase') . '</p>';
        }
        
        $settings = $this->db->get_all_settings();
        $tag_bg_color = isset($settings['tag_bg_color']) ? $settings['tag_bg_color'] : '#ff6b6b';
        $icon_color = isset($settings['icon_color']) ? $settings['icon_color'] : '#ffffff';
        $button_text = isset($settings['button_text']) ? $settings['button_text'] : 'View Product';
        
        // Generate unique ID for this showcase instance
        $unique_id = 'ips-' . $showcase_id . '-' . uniqid();
        
        ob_start();
        ?>
        
        <div class="ips-showcase-container" id="<?php echo esc_attr($unique_id); ?>" data-showcase-id="<?php echo esc_attr($showcase_id); ?>">
            <style>
                #<?php echo esc_attr($unique_id); ?> {
                    max-width: 100%;
                    margin: 0 auto;
                    position: relative;
                }

                #<?php echo esc_attr($unique_id); ?> .ips-image-wrapper {
                    position: relative;
                    display: inline-block;
                    width: 100%;
                }

                #<?php echo esc_attr($unique_id); ?> .ips-main-image {
                    width: 100%;
                    height: auto;
                    display: block;
                    border-radius: 8px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                }

                #<?php echo esc_attr($unique_id); ?> .ips-hotspot {
                    position: absolute;
                    width: 26px;
                    height: 26px;
                    background-color: <?php echo esc_attr($tag_bg_color); ?>;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: <?php echo esc_attr($icon_color); ?>;
                    font-size: 16px;
                    font-weight: bold;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                    transition: all 0.3s ease;
                    animation: ips-pulse-<?php echo esc_attr($showcase_id); ?> 2s infinite;
                    z-index: 999998;
                    transform: translate(-50%, -50%);
                }

                #<?php echo esc_attr($unique_id); ?> .ips-hotspot:hover {
                    transform: translate(-50%, -50%) scale(1.15);
                    animation: none;
                }

                #<?php echo esc_attr($unique_id); ?> .ips-hotspot.active {
                    transform: translate(-50%, -50%) scale(1.15);
                    animation: none;
                }

                @keyframes ips-pulse-<?php echo esc_attr($showcase_id); ?> {
                    0%, 100% {
                        transform: translate(-50%, -50%) scale(1);
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                    }
                    50% {
                        transform: translate(-50%, -50%) scale(1.08);
                        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
                    }
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> {
                    position: fixed !important;
                    background: white;
                    border-radius: 12px;
                    padding: 18px;
                    box-shadow: 0 8px 32px rgba(0,0,0,0.35);
                    display: none;
                    width: 220px;
                    z-index: 2147483647 !important;
                    top: 0;
                    left: 0;
                    opacity: 0;
                    transition: opacity 0.1s ease;
                }

                @media (max-width: 768px) {
                    #<?php echo esc_attr($unique_id); ?> .ips-hotspot {
                        width: 18px;
                        height: 18px;
                        font-size: 14px;
                    }
                }
                
                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?>.active {
                    display: block !important;
                    opacity: 1 !important;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> * {
                    list-style: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> *::before,
                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> *::after {
                    display: none !important;
                    content: none !important;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-content {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 0 !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-image {
                    width: 100% !important;
                    aspect-ratio: 1 / 1;
                    object-fit: cover;
                    border-radius: 8px;
                    margin: 0 0 12px 0 !important;
                    padding: 0 !important;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-title {
                    font-size: 15px !important;
                    font-weight: bold;
                    color: #333;
                    margin: 0 0 12px 0 !important;
                    padding: 0 !important;
                    line-height: 1.3 !important;
                    text-align: center;
                    width: 100%;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-description {
                    font-size: 12px !important;
                    color: #666;
                    line-height: 1.4 !important;
                    margin: 0 0 12px 0 !important;
                    padding: 0 !important;
                    text-align: center;
                    width: 100%;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-button {
                    display: block !important;
                    padding: 8px 16px !important;
                    margin: 0 !important;
                    background-color: <?php echo esc_attr($tag_bg_color); ?> !important;
                    color: <?php echo esc_attr($icon_color); ?> !important;
                    text-decoration: none !important;
                    border-radius: 6px;
                    text-align: center !important;
                    font-weight: 600;
                    font-size: 12px !important;
                    transition: all 0.3s ease;
                    width: 100% !important;
                    box-sizing: border-box;
                    border: none !important;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-button:hover {
                    opacity: 0.9;
                    transform: translateY(-2px);
                    color: <?php echo esc_attr($icon_color); ?> !important;
                    text-decoration: none !important;
                }

                .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-arrow {
                    position: absolute;
                    width: 0;
                    height: 0;
                    border-style: solid;
                    z-index: 2147483647;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                @media (max-width: 768px) {
                    .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> {
                        width: 220px;
                        padding: 14px;
                        max-width: calc(100vw - 30px);
                    }

                    .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-title {
                        font-size: 14px !important;
                    }

                    .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-description {
                        font-size: 12px !important;
                    }

                    .ips-popup-overlay-<?php echo esc_attr($unique_id); ?> .ips-popup-button {
                        padding: 8px 14px !important;
                        font-size: 12px !important;
                    }
                }
            </style>
            
            <div class="ips-image-wrapper">
                <img src="<?php echo esc_url($showcase['image_url']); ?>" alt="<?php echo esc_attr($showcase['title']); ?>" class="ips-main-image">
                
                <?php foreach ($showcase['hotspots'] as $index => $hotspot): ?>
                    <div class="ips-hotspot" 
                         data-index="<?php echo esc_attr($index); ?>" 
                         data-product-image="<?php echo esc_url($hotspot['product_image']); ?>"
                         data-product-title="<?php echo esc_attr($hotspot['product_title']); ?>"
                         data-product-description="<?php echo esc_attr($hotspot['description']); ?>"
                         data-product-url="<?php echo esc_url($hotspot['product_url']); ?>"
                         style="left: <?php echo esc_attr($hotspot['x']); ?>%; top: <?php echo esc_attr($hotspot['y']); ?>%;">
                        !
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                var containerID = '#<?php echo esc_js($unique_id); ?>';
                var popupClass = 'ips-popup-overlay-<?php echo esc_js($unique_id); ?>';
                var activePopup = null;
                var activeHotspot = null;
                var $popupElement = null;
                
                // Create popup element and append to body
                function createPopup() {
                    if ($popupElement) return $popupElement;
                    
                    $popupElement = $('<div class="' + popupClass + '">' +
                        '<div class="ips-popup-arrow"></div>' +
                        '<div class="ips-popup-content"></div>' +
                    '</div>');
                    
                    $('body').append($popupElement);
                    return $popupElement;
                }
                
                // Hotspot click handler
                $(document).on('click', containerID + ' .ips-hotspot', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var $hotspot = $(this);
                    
                    // If clicking the same hotspot, close it
                    if (activeHotspot && activeHotspot[0] === $hotspot[0]) {
                        closeActivePopup();
                        return;
                    }
                    
                    // Close previously active popup
                    if (activePopup) {
                        closeActivePopup();
                    }
                    
                    // Create popup if doesn't exist
                    var $popup = createPopup();
                    
                    // Get product data
                    var productImage = $hotspot.data('product-image');
                    var productTitle = $hotspot.data('product-title');
                    var productDescription = $hotspot.data('product-description');
                    var productUrl = $hotspot.data('product-url');
                    
                    // Build popup content
                    var content = '';
                    if (productImage) {
                        content += '<img src="' + productImage + '" alt="' + productTitle + '" class="ips-popup-image">';
                    }
                    content += '<div class="ips-popup-title">' + productTitle + '</div>';
                    if (productDescription) {
                        content += '<div class="ips-popup-description">' + productDescription + '</div>';
                    }
                    if (productUrl) {
                        content += '<a href="' + productUrl + '" class="ips-popup-button" target="_blank" rel="noopener noreferrer"><?php echo esc_js($button_text); ?></a>';
                    }
                    
                    $popup.find('.ips-popup-content').html(content);
                    
                    // Make popup visible but transparent for measurement
                    $popup.css('display', 'block');
                    
                    // Position popup FIRST (before making it visible)
                    positionPopup($hotspot, $popup);
                    
                    // Then show popup with active class
                    setTimeout(function() {
                        $popup.addClass('active');
                        $hotspot.addClass('active');
                    }, 10);
                    
                    activePopup = $popup;
                    activeHotspot = $hotspot;
                });
                
                // Close popup when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest(containerID + ' .ips-hotspot').length && 
                        !$(e.target).closest('.' + popupClass).length) {
                        closeActivePopup();
                    }
                });
                
                // Prevent popup click from closing
                $(document).on('click', '.' + popupClass, function(e) {
                    e.stopPropagation();
                });
                
                // Function to close active popup
                function closeActivePopup() {
                    if (activePopup) {
                        activePopup.removeClass('active');
                        setTimeout(function() {
                            if (activePopup) {
                                activePopup.css('display', 'none');
                            }
                        }, 100);
                    }
                    if (activeHotspot) {
                        activeHotspot.removeClass('active');
                    }
                    activePopup = null;
                    activeHotspot = null;
                }
                
                // Position popup based on hotspot location
                function positionPopup($hotspot, $popup) {
                    var hotspotOffset = $hotspot.offset();
                    var hotspotWidth = $hotspot.outerWidth();
                    var hotspotHeight = $hotspot.outerHeight();
                    
                    var $arrow = $popup.find('.ips-popup-arrow');
                    var isMobile = $(window).width() <= 768;
                    
                    var popupWidth = $popup.outerWidth();
                    var popupHeight = $popup.outerHeight();
                    var windowWidth = $(window).width();
                    var windowHeight = $(window).height();
                    var scrollTop = $(window).scrollTop();
                    
                    // Reset all positioning styles
                    $popup.css({
                        'top': '',
                        'left': '',
                        'transform': ''
                    });
                    
                    $arrow.css({
                        'top': '',
                        'bottom': '',
                        'left': '',
                        'right': '',
                        'transform': '',
                        'border-width': '',
                        'border-color': ''
                    });
                    
                    if (isMobile) {
                        // Mobile: Show popup below hotspot
                        var topPosition = hotspotOffset.top - scrollTop + hotspotHeight + 15;
                        var leftPosition = hotspotOffset.left + (hotspotWidth / 2) - (popupWidth / 2);
                        
                        // Adjust if goes off screen horizontally
                        if (leftPosition < 15) {
                            leftPosition = 15;
                        } else if (leftPosition + popupWidth > windowWidth - 15) {
                            leftPosition = windowWidth - popupWidth - 15;
                        }
                        
                        // Check if goes below viewport
                        if (topPosition + popupHeight > windowHeight - 15) {
                            topPosition = hotspotOffset.top - scrollTop - popupHeight - 15;
                            
                            $arrow.css({
                                'bottom': '-10px',
                                'left': (hotspotOffset.left + (hotspotWidth / 2) - leftPosition) + 'px',
                                'border-width': '10px 10px 0 10px',
                                'border-color': 'white transparent transparent transparent'
                            });
                        } else {
                            $arrow.css({
                                'top': '-10px',
                                'left': (hotspotOffset.left + (hotspotWidth / 2) - leftPosition) + 'px',
                                'border-width': '0 10px 10px 10px',
                                'border-color': 'transparent transparent white transparent'
                            });
                        }
                        
                        $popup.css({
                            'top': topPosition + 'px',
                            'left': leftPosition + 'px'
                        });
                        
                    } else {
                        // Desktop: Show popup to the right
                        var topPosition = hotspotOffset.top - scrollTop + (hotspotHeight / 2) - (popupHeight / 2);
                        var leftPosition = hotspotOffset.left + hotspotWidth + 15;
                        
                        // Check if goes off right edge
                        if (leftPosition + popupWidth > windowWidth - 20) {
                            leftPosition = hotspotOffset.left - popupWidth - 15;
                            
                            $arrow.css({
                                'right': '-10px',
                                'top': '50%',
                                'transform': 'translateY(-50%)',
                                'border-width': '10px 0 10px 10px',
                                'border-color': 'transparent transparent transparent white'
                            });
                        } else {
                            $arrow.css({
                                'left': '-10px',
                                'top': '50%',
                                'transform': 'translateY(-50%)',
                                'border-width': '10px 10px 10px 0',
                                'border-color': 'transparent white transparent transparent'
                            });
                        }
                        
                        // Check vertical bounds
                        if (topPosition < 20) {
                            topPosition = 20;
                        } else if (topPosition + popupHeight > windowHeight - 20) {
                            topPosition = windowHeight - popupHeight - 20;
                        }
                        
                        $popup.css({
                            'top': topPosition + 'px',
                            'left': leftPosition + 'px'
                        });
                    }
                }
                
                // Reposition on scroll and resize
                var resizeTimer;
                $(window).on('resize scroll', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        if (activePopup && activeHotspot) {
                            positionPopup(activeHotspot, activePopup);
                        }
                    }, 50);
                });
            });
        })(jQuery);
        </script>
        
        <?php
        return ob_get_clean();
    }
}