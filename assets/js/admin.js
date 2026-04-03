/**
 * WooCommerce Taxonomy SEO - Admin JavaScript
 *
 * @package WooTaxonomySEO
 */

(function($) {
    'use strict';

    /**
     * Character counter for input fields.
     */
    function initCharacterCounters() {
        $('.wts-input[data-maxlength]').each(function() {
            var $input = $(this);
            var maxLength = parseInt($input.data('maxlength'), 10);
            var $counter = $input.siblings('.wts-char-count').find('.wts-current');

            if (!maxLength || !$counter.length) {
                return;
            }

            function updateCounter() {
                var length = $input.val().length;
                $counter.text(length);

                var $parent = $counter.closest('.wts-char-count');
                $parent.removeClass('wts-warning wts-danger');

                if (length > maxLength) {
                    $parent.addClass('wts-danger');
                } else if (length > maxLength * 0.9) {
                    $parent.addClass('wts-warning');
                }
            }

            $input.on('input keyup', updateCounter);
            updateCounter();
        });
    }

    /**
     * Media uploader for image fields.
     */
    function initMediaUploader() {
        var mediaFrame;

        $(document).on('click', '.wts-upload-image', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $field = $button.closest('.wts-image-field');
            var $input = $field.find('.wts-image-id');
            var $preview = $field.find('.wts-image-preview');
            var $removeBtn = $field.find('.wts-remove-image');

            // Create media frame if it doesn't exist.
            if (!mediaFrame) {
                mediaFrame = wp.media({
                    title: wooTaxonomySEO.mediaTitle,
                    button: {
                        text: wooTaxonomySEO.mediaButton
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
            }

            // Handle selection.
            mediaFrame.off('select').on('select', function() {
                var attachment = mediaFrame.state().get('selection').first().toJSON();

                $input.val(attachment.id);

                var imageUrl = attachment.sizes && attachment.sizes.medium
                    ? attachment.sizes.medium.url
                    : attachment.url;

                $preview.find('img').attr('src', imageUrl);
                $preview.show();
                $removeBtn.show();
            });

            mediaFrame.open();
        });

        // Remove image.
        $(document).on('click', '.wts-remove-image', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $field = $button.closest('.wts-image-field');
            var $input = $field.find('.wts-image-id');
            var $preview = $field.find('.wts-image-preview');

            $input.val('');
            $preview.find('img').attr('src', '');
            $preview.hide();
            $button.hide();
        });
    }

    /**
     * SEO Preview (optional enhancement).
     */
    function initSEOPreview() {
        var $titleInput = $('#wts_seo_title');
        var $descInput = $('#wts_seo_description');

        if (!$titleInput.length || !$descInput.length) {
            return;
        }

        // Could add a live preview box here in future versions.
    }

    /**
     * Initialize on document ready.
     */
    $(document).ready(function() {
        initCharacterCounters();
        initMediaUploader();
        initSEOPreview();
    });

})(jQuery);
