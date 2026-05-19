(function ($) {
    'use strict';

    $(function () {
        $(document).on('click', '.lkn-fsdw-update-notice .notice-dismiss', function () {
            $.post(lknFsdwUpdateNotice.ajaxUrl, {
                action: 'lkn_fsdw_dismiss_update_notice',
                nonce: lknFsdwUpdateNotice.nonce,
            });
        });
    });
})(jQuery);
