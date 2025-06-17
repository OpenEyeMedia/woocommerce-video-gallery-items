# Video Thumbnail Generation

WordPress doesn't automatically generate thumbnail images for videos. To get proper thumbnails for your videos, you have several options:

## Option 1: Manual Upload
1. Create a screenshot of your video's first frame
2. Upload it as a regular image to Media Library
3. Note its attachment ID
4. Use a custom field to associate it with your video

## Option 2: Use a Plugin
Install a plugin like "Video Thumbnails" or "Automatic Featured Images from Videos" which can generate thumbnails automatically.

## Option 3: Custom Implementation
Add this code to your theme's functions.php to generate video thumbnails:

```php
add_filter('wp_generate_attachment_metadata', 'generate_video_thumbnail', 10, 2);

function generate_video_thumbnail($metadata, $attachment_id) {
    $attachment = get_post($attachment_id);
    $mime_type = get_post_mime_type($attachment_id);
    
    // Check if it's a video
    if (strpos($mime_type, 'video/') !== 0) {
        return $metadata;
    }
    
    // Only works if FFmpeg is installed on server
    if (!function_exists('exec')) {
        return $metadata;
    }
    
    $video_path = get_attached_file($attachment_id);
    $upload_dir = wp_upload_dir();
    $thumbnail_path = $upload_dir['path'] . '/' . $attachment->post_name . '-thumbnail.jpg';
    
    // Generate thumbnail using FFmpeg
    $command = "ffmpeg -i " . escapeshellarg($video_path) . " -ss 00:00:01.000 -vframes 1 " . escapeshellarg($thumbnail_path) . " 2>&1";
    exec($command, $output, $return_var);
    
    if ($return_var === 0 && file_exists($thumbnail_path)) {
        // Create attachment for thumbnail
        $thumbnail_id = wp_insert_attachment(array(
            'post_mime_type' => 'image/jpeg',
            'post_title' => $attachment->post_title . ' Thumbnail',
            'post_content' => '',
            'post_status' => 'inherit'
        ), $thumbnail_path);
        
        // Generate metadata for thumbnail
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $thumbnail_metadata = wp_generate_attachment_metadata($thumbnail_id, $thumbnail_path);
        wp_update_attachment_metadata($thumbnail_id, $thumbnail_metadata);
        
        // Associate thumbnail with video
        update_post_meta($attachment_id, '_thumbnail_id', $thumbnail_id);
    }
    
    return $metadata;
}
```

## Option 4: Video Placeholder
The plugin includes a video placeholder SVG that will be used when no thumbnail is available. You can replace this with your own image.
