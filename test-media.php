<?php
/**
 * Test page for WooCommerce Video Gallery
 * Upload this to your WordPress root and access directly
 */

// Load WordPress
require_once('wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('Admin access required');
}

// Load media scripts
wp_enqueue_media();
wp_enqueue_script('jquery');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Video Gallery Media Test</title>
    <?php wp_head(); ?>
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
        #result { margin-top: 20px; padding: 10px; background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>WooCommerce Video Gallery - Media Library Test</h1>
    
    <div class="test-section">
        <h2>Test 1: Basic Media Library</h2>
        <button id="test-basic">Open Media Library</button>
        <div id="result-basic"></div>
    </div>
    
    <div class="test-section">
        <h2>Test 2: Image Selection</h2>
        <button id="test-image">Select Image</button>
        <div id="result-image"></div>
    </div>
    
    <div class="test-section">
        <h2>Test 3: Video Selection</h2>
        <button id="test-video">Select Video</button>
        <div id="result-video"></div>
    </div>

    <?php wp_footer(); ?>
    
    <script>
    jQuery(document).ready(function($) {
        console.log('Test page loaded');
        console.log('wp.media available:', typeof wp.media !== 'undefined');
        
        // Test 1: Basic media library
        $('#test-basic').on('click', function(e) {
            e.preventDefault();
            console.log('Basic test clicked');
            
            try {
                var frame = wp.media({
                    title: 'Select Media',
                    button: { text: 'Select' },
                    multiple: false
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#result-basic').html('<p>Selected: ' + attachment.filename + '</p>');
                    console.log('Selected:', attachment);
                });
                
                frame.open();
                $('#result-basic').html('<p style="color: green;">Media library opened successfully!</p>');
            } catch (error) {
                $('#result-basic').html('<p style="color: red;">Error: ' + error.message + '</p>');
                console.error('Error:', error);
            }
        });
        
        // Test 2: Image selection
        $('#test-image').on('click', function(e) {
            e.preventDefault();
            console.log('Image test clicked');
            
            try {
                var imageFrame = wp.media({
                    title: 'Select Image',
                    button: { text: 'Use this image' },
                    library: { type: 'image' },
                    multiple: false
                });
                
                imageFrame.on('select', function() {
                    var attachment = imageFrame.state().get('selection').first().toJSON();
                    var imgUrl = attachment.sizes && attachment.sizes.thumbnail ? 
                        attachment.sizes.thumbnail.url : attachment.url;
                    $('#result-image').html('<img src="' + imgUrl + '" style="max-width: 200px;">');
                });
                
                imageFrame.open();
            } catch (error) {
                $('#result-image').html('<p style="color: red;">Error: ' + error.message + '</p>');
                console.error('Error:', error);
            }
        });
        
        // Test 3: Video selection
        $('#test-video').on('click', function(e) {
            e.preventDefault();
            console.log('Video test clicked');
            
            try {
                var videoFrame = wp.media({
                    title: 'Select Video',
                    button: { text: 'Use this video' },
                    library: { type: 'video' },
                    multiple: false
                });
                
                videoFrame.on('select', function() {
                    var attachment = videoFrame.state().get('selection').first().toJSON();
                    $('#result-video').html('<p>Selected video: ' + attachment.filename + '</p>');
                });
                
                videoFrame.open();
            } catch (error) {
                $('#result-video').html('<p style="color: red;">Error: ' + error.message + '</p>');
                console.error('Error:', error);
            }
        });
    });
    </script>
</body>
</html>
