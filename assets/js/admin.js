/**
 * WooCommerce Video Gallery Admin JS
 */
(function($) {
    'use strict';
    
    console.log('WC Video Gallery Admin JS loaded');
    
    // Debug info
    if (window.wcVideoGallery && wcVideoGallery.debug) {
        console.log('wcVideoGallery object:', wcVideoGallery);
    }

    // Wait for document ready
    $(document).ready(function() {
        
        // Add video button
        $(document).on('click', '#wc-add-video', function(e) {
            e.preventDefault();
            console.log('Add video clicked');

            var frame = wp.media({
                title: wcVideoGallery.strings.selectVideo,
                button: {
                    text: wcVideoGallery.strings.useVideo
                },
                library: {
                    type: 'video'
                },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                console.log('Video selected:', attachment);
                addVideoItem(attachment);
            });

            frame.open();
        });

        // Select thumbnail button - Fixed version
        $(document).on('click', '.wc-select-thumbnail', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Select thumbnail clicked');
            
            var button = this;
            var $button = $(button);
            var $item = $button.closest('.wc-video-item');
            var index = $button.data('index');
            
            // Create and open media frame
            var thumbnailFrame = wp.media({
                title: wcVideoGallery.strings.selectThumbnail || 'Select Thumbnail',
                button: {
                    text: wcVideoGallery.strings.useThumbnail || 'Use as thumbnail'
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });
            
            // When an image is selected
            thumbnailFrame.on('select', function() {
                var attachment = thumbnailFrame.state().get('selection').first().toJSON();
                console.log('Thumbnail selected:', attachment);
                
                // Update hidden input
                $item.find('.wc-video-thumbnail-id').val(attachment.id);
                
                // Update preview
                var thumbnailUrl = attachment.url;
                if (attachment.sizes && attachment.sizes.thumbnail) {
                    thumbnailUrl = attachment.sizes.thumbnail.url;
                }
                
                var preview = '<img src="' + thumbnailUrl + '" style="max-width: 100px; height: auto;">';
                $item.find('.wc-video-thumbnail-preview').html(preview);
                
                // Add remove button if not exists
                if (!$item.find('.wc-remove-thumbnail').length) {
                    $button.after(' <button type="button" class="button wc-remove-thumbnail" data-index="' + index + '">Remove</button>');
                }
            });
            
            // Open the frame
            thumbnailFrame.open();
            
            return false;
        });

        // Remove thumbnail
        $(document).on('click', '.wc-remove-thumbnail', function(e) {
            e.preventDefault();
            console.log('Remove thumbnail clicked');
            
            var $button = $(this);
            var $item = $button.closest('.wc-video-item');
            
            // Clear hidden input
            $item.find('.wc-video-thumbnail-id').val('');
            
            // Reset preview
            $item.find('.wc-video-thumbnail-preview').html('<span>No thumbnail</span>');
            
            // Remove this button
            $button.remove();
        });

        // Remove video
        $(document).on('click', '.wc-remove-video', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove this video?')) {
                $(this).closest('.wc-video-item').fadeOut(300, function() {
                    $(this).remove();
                    reindexVideos();
                });
            }
        });

        // Function to add video item
        function addVideoItem(attachment) {
            var container = $('.wc-video-items');
            var index = container.find('.wc-video-item').length;
            
            var html = '<div class="wc-video-item" data-index="' + index + '">';
            html += '<video width="100%" controls preload="metadata">';
            html += '<source src="' + attachment.url + '#t=0.1" type="' + attachment.mime + '">';
            html += '</video>';
            html += '<input type="hidden" name="wc_gallery_videos[' + index + '][id]" value="' + attachment.id + '">';
            html += '<input type="hidden" name="wc_gallery_videos[' + index + '][url]" value="' + attachment.url + '">';
            html += '<input type="hidden" name="wc_gallery_videos[' + index + '][type]" value="' + attachment.mime + '">';
            html += '<input type="text" name="wc_gallery_videos[' + index + '][position]" value="' + (index + 1) + '" placeholder="Position in gallery" class="wc-video-position">';
            
            // Thumbnail section
            html += '<div class="wc-video-thumbnail-section">';
            html += '<input type="hidden" name="wc_gallery_videos[' + index + '][thumbnail_id]" value="" class="wc-video-thumbnail-id">';
            html += '<div class="wc-video-thumbnail-preview"><span>No thumbnail</span></div>';
            html += '<button type="button" class="button wc-select-thumbnail" data-index="' + index + '">Select Thumbnail</button>';
            html += '</div>';
            
            html += '<button type="button" class="button wc-remove-video">Remove Video</button>';
            html += '</div>';

            container.append(html);
            
            // Load video metadata
            var $newVideo = container.find('.wc-video-item:last video');
            if ($newVideo.length && $newVideo[0]) {
                $newVideo[0].load();
            }
        }

        // Function to reindex videos
        function reindexVideos() {
            $('.wc-video-items .wc-video-item').each(function(index) {
                var $item = $(this);
                $item.attr('data-index', index);
                $item.find('input').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', name);
                    }
                });
                $item.find('.wc-select-thumbnail').attr('data-index', index);
                $item.find('.wc-remove-thumbnail').attr('data-index', index);
            });
        }

        // Sortable
        if ($.fn.sortable) {
            $('.wc-video-items').sortable({
                items: '.wc-video-item',
                cursor: 'move',
                scrollSensitivity: 40,
                forcePlaceholderSize: true,
                forceHelperSize: false,
                helper: 'clone',
                opacity: 0.65,
                placeholder: 'wc-video-sortable-placeholder',
                start: function(event, ui) {
                    ui.item.css('background-color', '#f6f6f6');
                },
                stop: function(event, ui) {
                    ui.item.removeAttr('style');
                    reindexVideos();
                }
            });
        }
    });

})(jQuery);
