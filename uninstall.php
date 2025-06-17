<?php
/**
 * Uninstall WooCommerce Video Gallery
 *
 * Removes all plugin data when uninstalled
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if we should remove data (optional - you might want to keep data)
$remove_data = apply_filters('wc_video_gallery_remove_data_on_uninstall', false);

if ($remove_data) {
    global $wpdb;
    
    // Remove video meta from all products
    $wpdb->delete(
        $wpdb->postmeta,
        array('meta_key' => '_wc_gallery_videos'),
        array('%s')
    );
    
    // Clear any transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_video_gallery_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wc_video_gallery_%'");
}
