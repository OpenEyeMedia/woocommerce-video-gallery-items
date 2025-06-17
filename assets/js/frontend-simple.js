/**
 * WooCommerce Video Gallery Frontend JS - Simplified
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize video galleries
        initVideoGallery();
        
        // Reinitialize after WooCommerce gallery loads
        $(document).on('wc-product-gallery-after-init', '.woocommerce-product-gallery', function() {
            initVideoGallery();
        });
    });
    
    function initVideoGallery() {
        // Remove any existing bindings
        $('.wc-video-popup').off('click.wcvideo');
        
        // Simple click handler for videos
        $('.wc-video-popup').on('click.wcvideo', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var videoUrl = $(this).attr('href');
            
            // Create video HTML
            var videoHtml = '<video controls autoplay style="max-width: 100%; max-height: 80vh; width: auto; height: auto;">' +
                           '<source src="' + videoUrl + '">' +
                           'Your browser does not support the video tag.' +
                           '</video>';
            
            // Open in Magnific Popup
            $.magnificPopup.open({
                items: {
                    src: '<div class="wc-video-popup-content" style="max-width: 90vw; margin: 0 auto; text-align: center;">' + videoHtml + '</div>',
                    type: 'inline'
                },
                closeBtnInside: true,
                mainClass: 'mfp-fade',
                callbacks: {
                    open: function() {
                        // Prevent body scroll
                        $('body').css('overflow', 'hidden');
                    },
                    close: function() {
                        // Restore body scroll
                        $('body').css('overflow', '');
                    }
                }
            });
            
            return false;
        });
        
        // Prevent PhotoSwipe from capturing video clicks
        $('.wc-video-gallery-item').on('click', 'a', function(e) {
            e.stopPropagation();
        });
    }

})(jQuery);
