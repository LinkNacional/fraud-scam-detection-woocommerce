(function ($) {
    var vars = (typeof lknFsdwCredentialsNoticeVars !== 'undefined') ? lknFsdwCredentialsNoticeVars : {};

    function buildBox() {
        var box = document.createElement('div');
        box.id        = 'lkn-fsdw-credentials-notice';
        box.className = 'lkn-fsdw-credentials-notice';

        if (vars.iconUrl) {
            var img = document.createElement('img');
            img.className = 'lkn-fsdw-credentials-notice-icon';
            img.src       = vars.iconUrl;
            img.alt       = vars.providerLabel || '';
            box.appendChild(img);
        }

        var text = document.createElement('span');
        text.className   = 'lkn-fsdw-credentials-notice-text';
        text.textContent = vars.message || '';
        box.appendChild(text);

        return box;
    }

    function paymentSection() {
        return document.querySelector('#payment.woocommerce-checkout-payment')
            || document.querySelector('fieldset.wc-block-checkout__payment-method');
    }

    function inject() {
        if (document.getElementById('lkn-fsdw-credentials-notice')) {
            return true;
        }

        var section = paymentSection();
        var box     = buildBox();

        if (section && section.parentNode) {
            section.parentNode.insertBefore(box, section);
        } else {
            // Fallback: fixed above the footer until the payment section exists
            box.style.cssText = 'max-width:420px;margin:12px auto;';
            document.body.appendChild(box);
        }
        return true;
    }

    $(window).on('load', function () {
        inject();

        // Blocks checkout may render the payment section later; keep watching
        // briefly and reposition the placeholder once it appears.
        if (!paymentSection()) {
            var attempts = 0;
            var timer = window.setInterval(function () {
                attempts++;
                var section = paymentSection();
                if (section && section.parentNode) {
                    var box = document.getElementById('lkn-fsdw-credentials-notice');
                    if (box) {
                        section.parentNode.insertBefore(box, section);
                    }
                    window.clearInterval(timer);
                } else if (attempts >= 20) {
                    window.clearInterval(timer);
                }
            }, 500);
        }
    });
})(jQuery);
