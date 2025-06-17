# WooCommerce Video Gallery Plugin

A WordPress plugin that enhances WooCommerce product galleries by adding video support with lightbox functionality.

## Features

- Add multiple videos to product galleries
- Videos appear alongside product images
- Lightbox support for video playback
- Drag-and-drop reordering of videos
- Position control for video placement in gallery
- Mobile responsive
- Compatible with WooCommerce gallery features

## Installation

1. Download the plugin folder
2. **IMPORTANT**: Download Magnific Popup JS library (see MAGNIFIC-POPUP-REQUIRED.txt)
3. Upload the plugin folder to `/wp-content/plugins/`
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Ensure WooCommerce is installed and activated

## Usage

### Adding Videos to Products

1. Edit any WooCommerce product
2. Look for the "Product Gallery Videos" meta box in the sidebar
3. Click "Add Video" to select videos from your Media Library
4. Set the position number to control where videos appear in the gallery
5. Save the product

### Video Requirements

- Videos should be in web-compatible formats (MP4, WebM, OGG)
- Keep videos reasonably sized for web delivery
- Consider using compressed versions for better performance

### Styling and Customisation

The plugin includes CSS classes that can be targeted for custom styling:

- `.wc-video-gallery-item` - Video gallery items
- `.wc-video-play-overlay` - Play button overlay
- `.wc-video-wrapper` - Video container in lightbox

## Technical Details

### Hooks and Filters

The plugin uses these WooCommerce hooks:
- `woocommerce_single_product_image_thumbnail_html` - To inject videos into gallery
- `woocommerce_before_single_product_summary` - To modify gallery wrapper

### File Structure

```
woocommerce-video-gallery/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   ├── frontend.css
│   │   └── magnific-popup.css
│   ├── js/
│   │   ├── admin.js
│   │   ├── frontend.js
│   │   └── jquery.magnific-popup.min.js (must be added)
│   └── images/
│       └── video-placeholder.png (optional)
├── woocommerce-video-gallery.php
└── README.md
```

## Browser Compatibility

- Modern browsers with HTML5 video support
- Internet Explorer 11+
- Chrome, Firefox, Safari, Edge (latest versions)

## Troubleshooting

### Videos not appearing in gallery
- Check that videos are properly uploaded to Media Library
- Ensure video position numbers don't conflict
- Verify WooCommerce gallery is enabled for the product

### Lightbox not working
- Confirm Magnific Popup JS is properly installed
- Check for JavaScript errors in browser console
- Ensure no conflicts with other lightbox plugins

### Video playback issues
- Verify video format is web-compatible
- Check file permissions on video files
- Test with different video formats

## Future Enhancements

Potential improvements for future versions:
- YouTube/Vimeo support
- Video thumbnail generation
- Bulk video management
- Video gallery settings page
- Auto-play options
- Custom video controls

## License

GPL v2 or later

## Credits

- Uses Magnific Popup by Dmitry Semenov
- Built for WooCommerce by Automattic
