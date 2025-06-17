/**
 * WooCommerce Video Gallery Frontend JS
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        console.log('WC Video Gallery Frontend JS loaded');
        
        // Prevent PhotoSwipe on video items
        $('.wc-video-gallery-item').each(function() {
            $(this).attr('data-thumb-alt', 'video');
            // Remove all PhotoSwipe attributes
            var $link = $(this).find('a');
            $link.attr('data-rel', 'video')
                 .removeAttr('data-size')
                 .removeAttr('data-large_image')
                 .removeAttr('data-large_image_width')
                 .removeAttr('data-large_image_height');
        });
        
        // Initialize video popups
        $('.wc-video-popup').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var videoUrl = $(this).attr('href');
            var videoId = $(this).data('video-id') || 'video-' + Date.now();
            
            // Create video HTML with proper wrapper
            var videoHtml = '<div id="' + videoId + '" class="white-popup">' +
                            '<div class="video-container">' +
                            '<video controls autoplay>' +
                            '<source src="' + videoUrl + '" type="video/mp4">' +
                            '<source src="' + videoUrl + '" type="video/webm">' +
                            '<source src="' + videoUrl + '" type="video/ogg">' +
                            'Your browser does not support the video tag.' +
                            '</video>' +
                            '</div>' +
                            '</div>';
            
            // Open popup
            $.magnificPopup.open({
                items: {
                    src: videoHtml,
                    type: 'inline'
                },
                closeBtnInside: true,
                fixedContentPos: true,
                mainClass: 'mfp-video-popup',
                callbacks: {
                    open: function() {
                        var $video = this.content.find('video');
                        if ($video.length) {
                            // Ensure video plays
                            $video[0].play().catch(function(e) {
                                console.log('Autoplay prevented:', e);
                            });
                            
                            // Log video dimensions when loaded
                            $video.on('loadedmetadata', function() {
                                console.log('Video dimensions:', this.videoWidth + 'x' + this.videoHeight);
                            });
                        }
                    },
                    close: function() {
                        var $video = this.content.find('video');
                        if ($video.length && $video[0]) {
                            $video[0].pause();
                        }
                    }
                }
            });
            
            return false;
        });
        
        // Prevent gallery from intercepting video clicks
        $('.woocommerce-product-gallery').on('click', '.wc-video-gallery-item', function(e) {
            if ($(e.target).closest('.wc-video-popup').length) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
        });
    });
    
})(jQuery);
