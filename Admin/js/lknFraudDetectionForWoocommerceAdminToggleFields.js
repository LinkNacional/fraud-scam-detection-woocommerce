(function ($) {
    'use strict';

    $(document).ready(function () {
        var $antifraud = $('#lknFraudDetectionForWoocommerceEnableRecaptcha');

        if (!$antifraud.length) {
            return;
        }

        // Field wrappers that depend on Enable Antifraud being active
        var $dependents = $(
            '#lknFraudDetectionForWoocommerceEnableIpLookup, ' +
            '#lknFraudDetectionForWoocommerceRecaptchaSelected'
        ).map(function () {
            return $(this).closest('.admin-layout-field-parent-flex').get(0);
        });

        // Nav tabs that depend on Enable Antifraud being active
        var $dependentTabs = $(
            '[data-target="block-google-recaptcha"], ' +
            '[data-target="block-cloudflare-turnstile"]'
        );

        function syncDependents() {
            var active = $antifraud.is(':checked');

            $($dependents).each(function () {
                $(this).toggleClass('lkn-disabled-field', !active);
            });

            $dependentTabs.toggleClass('lkn-disabled-tab', !active);
        }

        syncDependents();
        $antifraud.on('change', syncDependents);

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
