# IMMEDIATE FIXES FOR WOOCOMMERCE VIDEO GALLERY

## To Debug The Issues:

1. **Check Browser Console**
   - Open the product edit page
   - Press F12 to open Developer Tools
   - Go to Console tab
   - Click "Select Thumbnail" button
   - See if any errors appear

2. **Test Media Library**
   - In WordPress admin, go to Media > Add New
   - Check if media upload works normally
   - This confirms if media scripts are working

## Quick Fix Option 1: Force Load Media Scripts

Add this to the main plugin file, in the admin_scripts function after wp_enqueue_media():

```php
// Force load media scripts
wp_enqueue_script('media-upload');
wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox');
```

## Quick Fix Option 2: Simple Inline Script

Add this to render_video_meta_box function, right before the closing ?>:

```php
<script>
jQuery(document).ready(function($) {
    $('.wc-select-thumbnail').click(function(e) {
        e.preventDefault();
        var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
            button.siblings('.wc-video-thumbnail-id').val(attachment.id);
            button.siblings('.wc-video-thumbnail-preview').html('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width:100px;">');
        };
        wp.media.editor.open(button);
        return false;
    });
});
</script>
```

## Quick Fix for Video Lightbox:

Replace the CSS in frontend.css with:

```css
/* Video in lightbox - maintain aspect ratio */
.mfp-content video {
    max-width: min(90vw, 1000px) !important;
    max-height: min(80vh, 1000px) !important;
    width: auto !important;
    height: auto !important;
    display: block;
    margin: 0 auto;
}

.mfp-inline-holder .mfp-content {
    width: auto !important;
    text-align: center;
}

.wc-video-popup-content {
    display: inline-block;
    position: relative;
}
```

## Information That Would Help:

1. **WordPress Version**: ?
2. **WooCommerce Version**: ?
3. **Theme Being Used**: ?
4. **Browser Console Errors**: Any errors when clicking thumbnail button?
5. **Other Plugins**: List of active plugins (some might conflict)

Please check the browser console for errors and let me know what you find!
