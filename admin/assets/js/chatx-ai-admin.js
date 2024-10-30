var CX = {};

(function($) {
    'use strict';

    CX.init = function() {

        CX.initNotices();

    };

    CX.initNotices = function() {
        jQuery('body').on('click', '.notice-chatx .notice-dismiss', function() {
            jQuery.post(ajaxurl, {
                action: 'dismiss_cx_notice',
                id: jQuery(this).closest('.notice-chatx').data('notice-id')
            });
        })
    }

    $(document).ready(function() {
        CX.init();
    });

})(jQuery);
