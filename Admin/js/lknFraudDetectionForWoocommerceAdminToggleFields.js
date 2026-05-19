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
    });

}(jQuery));
