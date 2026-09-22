(function ($) {
    'use strict';

    $(document).ready(function () {
        // ── Captcha: the provider select drives the hidden enable checkbox and
        //    reveals the matching provider credential fields. ────────────────
        var captcha = (typeof lknFsdwCaptchaVars !== 'undefined') ? lknFsdwCaptchaVars : null;

        if (captcha) {
            var $select = $('#' + captcha.selectId);
            var $enable = $('#' + captcha.enableId);
            var $groups = $('[data-captcha-provider]');

            function syncProviderFields() {
                var provider = $select.val();
                $groups.each(function () {
                    var $group = $(this);
                    $group.toggle($group.attr('data-captcha-provider') === provider);
                });
            }

            // Legacy installs enabled the (previously Google-only) feature before
            // the provider option existed. Default them to Google so the stored
            // "enabled" state stays visible and consistent with the select.
            if ($enable.length && $enable.is(':checked') &&
                (!$select.val() || $select.val() === captcha.noneValue)) {
                $select.val(captcha.googleProvider);
            }

            // The select is the single source of truth for the hidden enable
            // checkbox: a provider means "on", "None" means "off". On load this
            // keeps the stored checkbox consistent with the selected provider
            // (fresh stores keep "none", so they stay disabled).
            function syncEnableFromProvider() {
                var provider = $select.val();
                $enable.prop('checked', !!provider && provider !== captcha.noneValue);
            }

            syncEnableFromProvider();
            syncProviderFields();

            $select.on('change', function () {
                syncEnableFromProvider();
                syncProviderFields();
            });
        }

        // ── Ban duration: disable number field when unit = "forever" ───────
        var $durationUnit  = $('#lknFraudDetectionForWoocommerceBanDurationUnit');
        var $durationValue = $('#lknFraudDetectionForWoocommerceBanDuration');

        function syncBanDuration() {
            var isForever = $durationUnit.val() === 'forever';
            $durationValue.prop('disabled', isForever);
            if (isForever) {
                $durationValue.val('0');
            } else if ($durationValue.val() === '0' || $durationValue.val() === '') {
                $durationValue.val('1');
            }
        }

        if ($durationUnit.length && $durationValue.length) {
            syncBanDuration();
            $durationUnit.on('change', syncBanDuration);
        }

        // ── Inline tab links (data-goto-tab) ────────────────────────────────
        $(document).on('click', '[data-goto-tab]', function (e) {
            e.preventDefault();
            var target = $(this).data('goto-tab');
            $('.admin-layout-title-link[data-target="block-' + target + '"]').trigger('click');
            var navLink = document.getElementById('nav-' + target);
            if (navLink) {
                navLink.scrollIntoView({ block: 'center' });
            }
        });
    });

}(jQuery));
