(function ($) {
    'use strict';

    $(function () {
        var $ipSpan = $('span.woocommerce-Order-customerIP');

        if (!$ipSpan.length) {
            return;
        }

        var links = ' <span class="lkn-fsdw-ip-actions">'
            + '[ <a href="#">lookup</a>'
            + ' | <a href="#">filter</a>'
            + ' | <a href="#">ban</a> ]'
            + '</span>';

        $ipSpan.after(links);
    });

})(jQuery);
