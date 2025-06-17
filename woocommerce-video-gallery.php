<?php
/**
 * Plugin Name: WooCommerce Video Gallery
 * Plugin URI: https://rebeccaenderby.com
 * Description: Enhance WooCommerce product galleries with video support, including lightbox functionality
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * Text Domain: wc-video-gallery
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_VIDEO_GALLERY_VERSION', '1.0.0');
define('WC_VIDEO_GALLERY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_VIDEO_GALLERY_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WC_Video_Gallery {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Hook into WordPress
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        
        // Product meta box
        add_action('add_meta_boxes', array($this, 'add_video_meta_box'));
        add_action('save_post_product', array($this, 'save_video_meta'));
        
        // Gallery modifications
        add_filter('woocommerce_single_product_image_thumbnail_html', array($this, 'add_video_to_gallery'), 10, 2);
        add_action('woocommerce_before_single_product_summary', array($this, 'modify_gallery_wrapper'), 5);
        
        // Ensure media scripts are loaded
        add_action('admin_footer-post.php', array($this, 'print_media_templates'));
        add_action('admin_footer-post-new.php', array($this, 'print_media_templates'));
    }
    
    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php esc_html_e('WooCommerce Video Gallery requires WooCommerce to be installed and active.', 'wc-video-gallery'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Initialise plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('wc-video-gallery', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_scripts($hook) {
        global $post_type;
        
        if (('post.php' === $hook || 'post-new.php' === $hook) && 'product' === $post_type) {
            // Ensure media scripts are loaded
            if (!did_action('wp_enqueue_media')) {
                wp_enqueue_media();
            }
            
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-sortable');
            
            wp_enqueue_script(
                'wc-video-gallery-admin',
                WC_VIDEO_GALLERY_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-sortable'),
                WC_VIDEO_GALLERY_VERSION,
                true
            );
            
            wp_enqueue_style(
                'wc-video-gallery-admin',
                WC_VIDEO_GALLERY_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WC_VIDEO_GALLERY_VERSION
            );
            
            wp_localize_script('wc-video-gallery-admin', 'wcVideoGallery', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc-video-gallery-nonce'),
                'strings' => array(
                    'selectVideo' => __('Select Video', 'wc-video-gallery'),
                    'useVideo' => __('Use this video', 'wc-video-gallery'),
                    'removeVideo' => __('Remove video', 'wc-video-gallery'),
                    'selectThumbnail' => __('Select Thumbnail', 'wc-video-gallery'),
                    'useThumbnail' => __('Use as thumbnail', 'wc-video-gallery')
                ),
                'debug' => defined('WP_DEBUG') && WP_DEBUG
            ));
        }
    }
    
    /**
     * Print media templates
     */
    public function print_media_templates() {
        global $post_type;
        if ('product' === $post_type) {
            wp_print_media_templates();
        }
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function frontend_scripts() {
        if (is_product()) {
            // Enqueue Magnific Popup for lightbox
            wp_enqueue_script(
                'magnific-popup',
                WC_VIDEO_GALLERY_PLUGIN_URL . 'assets/js/jquery.magnific-popup.min.js',
                array('jquery'),
                '1.1.0',
                true
            );
            
            wp_enqueue_style(
                'magnific-popup',
                WC_VIDEO_GALLERY_PLUGIN_URL . 'assets/css/magnific-popup.css',
                array(),
                '1.1.0'
            );
            
            // Enqueue our custom scripts
            wp_enqueue_script(
                'wc-video-gallery-frontend',
                WC_VIDEO_GALLERY_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery', 'magnific-popup', 'wc-single-product'),
                WC_VIDEO_GALLERY_VERSION,
                true
            );
            
            wp_enqueue_style(
                'wc-video-gallery-frontend',
                WC_VIDEO_GALLERY_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                WC_VIDEO_GALLERY_VERSION
            );
            
            // Add inline script to handle video URLs
            wp_add_inline_script('wc-video-gallery-frontend', '
                var wcVideoGalleryVideos = {};
                jQuery(".wc-video-popup").each(function(index) {
                    wcVideoGalleryVideos[index] = jQuery(this).attr("href");
                });
            ', 'before');
        }
    }
    
    /**
     * Add video meta box
     */
    public function add_video_meta_box() {
        add_meta_box(
            'wc_video_gallery_meta',
            __('Product Gallery Videos', 'wc-video-gallery'),
            array($this, 'render_video_meta_box'),
            'product',
            'side',
            'low'
        );
    }
    
    /**
     * Render video meta box
     */
    public function render_video_meta_box($post) {
        wp_nonce_field('wc_video_gallery_meta_nonce', 'wc_video_gallery_meta_nonce');
        
        $videos = get_post_meta($post->ID, '_wc_gallery_videos', true);
        $videos = is_array($videos) ? $videos : array();
        ?>
        <div id="wc-video-gallery-container">
            <div class="wc-video-items">
                <?php foreach ($videos as $index => $video) : 
                    $thumbnail_id = isset($video['thumbnail_id']) ? $video['thumbnail_id'] : '';
                    $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
                ?>
                    <div class="wc-video-item" data-index="<?php echo esc_attr($index); ?>">
                        <video width="100%" controls preload="metadata">
                            <source src="<?php echo esc_url($video['url']); ?>#t=0.1" type="<?php echo esc_attr($video['type']); ?>">
                        </video>
                        <input type="hidden" name="wc_gallery_videos[<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($video['id']); ?>">
                        <input type="hidden" name="wc_gallery_videos[<?php echo esc_attr($index); ?>][url]" value="<?php echo esc_url($video['url']); ?>">
                        <input type="hidden" name="wc_gallery_videos[<?php echo esc_attr($index); ?>][type]" value="<?php echo esc_attr($video['type']); ?>">
                        <input type="text" name="wc_gallery_videos[<?php echo esc_attr($index); ?>][position]" value="<?php echo esc_attr($video['position'] ?? 1); ?>" placeholder="<?php esc_attr_e('Position in gallery', 'wc-video-gallery'); ?>" class="wc-video-position">
                        
                        <div class="wc-video-thumbnail-section">
                            <input type="hidden" name="wc_gallery_videos[<?php echo esc_attr($index); ?>][thumbnail_id]" value="<?php echo esc_attr($thumbnail_id); ?>" class="wc-video-thumbnail-id">
                            <div class="wc-video-thumbnail-preview">
                                <?php if ($thumbnail_url) : ?>
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" style="max-width: 100px; height: auto;">
                                <?php else : ?>
                                    <span><?php esc_html_e('No thumbnail', 'wc-video-gallery'); ?></span>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button wc-select-thumbnail" data-index="<?php echo esc_attr($index); ?>"><?php esc_html_e('Select Thumbnail', 'wc-video-gallery'); ?></button>
                            <?php if ($thumbnail_id) : ?>
                                <button type="button" class="button wc-remove-thumbnail" data-index="<?php echo esc_attr($index); ?>"><?php esc_html_e('Remove', 'wc-video-gallery'); ?></button>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="button wc-remove-video"><?php esc_html_e('Remove Video', 'wc-video-gallery'); ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="wc-add-video" class="button button-primary"><?php esc_html_e('Add Video', 'wc-video-gallery'); ?></button>
            <p class="description"><?php esc_html_e('Videos will appear in the product gallery. Set position to control where they appear among images. Select a thumbnail image for each video.', 'wc-video-gallery'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Save video meta
     */
    public function save_video_meta($post_id) {
        // Check nonce
        if (!isset($_POST['wc_video_gallery_meta_nonce']) || !wp_verify_nonce($_POST['wc_video_gallery_meta_nonce'], 'wc_video_gallery_meta_nonce')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save videos
        $videos = isset($_POST['wc_gallery_videos']) ? $_POST['wc_gallery_videos'] : array();
        $sanitised_videos = array();
        
        foreach ($videos as $video) {
            if (!empty($video['id']) && !empty($video['url'])) {
                $sanitised_videos[] = array(
                    'id' => absint($video['id']),
                    'url' => esc_url_raw($video['url']),
                    'type' => sanitize_text_field($video['type']),
                    'position' => absint($video['position'] ?? 1),
                    'thumbnail_id' => !empty($video['thumbnail_id']) ? absint($video['thumbnail_id']) : ''
                );
            }
        }
        
        update_post_meta($post_id, '_wc_gallery_videos', $sanitised_videos);
    }
    
    /**
     * Modify gallery wrapper
     */
    public function modify_gallery_wrapper() {
        // Add a custom class to the gallery wrapper for targeting
        add_filter('woocommerce_single_product_image_gallery_classes', function($classes) {
            $classes[] = 'wc-video-gallery-enabled';
            return $classes;
        });
    }
    
    /**
     * Add video to gallery
     */
    public function add_video_to_gallery($html, $attachment_id) {
        global $product;
        
        if (!$product) {
            return $html;
        }
        
        $videos = get_post_meta($product->get_id(), '_wc_gallery_videos', true);
        
        if (empty($videos) || !is_array($videos)) {
            return $html;
        }
        
        // Get current gallery position
        static $gallery_position = 0;
        $gallery_position++;
        
        $video_html = '';
        
        // Check if we should insert a video at this position
        foreach ($videos as $video) {
            if (isset($video['position']) && $video['position'] == $gallery_position) {
                $video_html .= $this->generate_video_thumbnail_html($video);
            }
        }
        
        return $video_html . $html;
    }
    
    /**
     * Generate video thumbnail HTML
     */
    private function generate_video_thumbnail_html($video) {
        $thumbnail_id = $video['id'];
        $video_url = $video['url'];
        $unique_id = 'video-' . $thumbnail_id . '-' . uniqid();
        
        // First try to use the custom thumbnail if set
        if (!empty($video['thumbnail_id'])) {
            $thumbnail_url = wp_get_attachment_image_url($video['thumbnail_id'], 'woocommerce_gallery_thumbnail');
        } else {
            // Try to get a thumbnail image from the video attachment
            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'woocommerce_gallery_thumbnail');
        }
        
        // If no thumbnail, use placeholder
        if (!$thumbnail_url) {
            $thumbnail_url = WC_VIDEO_GALLERY_PLUGIN_URL . 'assets/images/video-placeholder.svg';
        }
        
        $html = '<div data-thumb="' . esc_url($thumbnail_url) . '" class="woocommerce-product-gallery__image wc-video-gallery-item">';
        $html .= '<a href="' . esc_url($video_url) . '" class="wc-video-popup" data-video-id="' . esc_attr($unique_id) . '">';
        $html .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr__('Product video', 'wc-video-gallery') . '" />';
        $html .= '<div class="wc-video-play-overlay"><span class="dashicons dashicons-video-alt3"></span></div>';
        $html .= '</a>';
        $html .= '</div>';
        
        return $html;
    }
}

// Initialise the plugin
add_action('plugins_loaded', array('WC_Video_Gallery', 'get_instance'));
