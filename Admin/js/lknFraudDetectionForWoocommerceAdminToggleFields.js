(function ($) {
    'use strict';

    $(document).ready(function () {
        var $antifraud = $('#lknFraudDetectionForWoocommerceEnableRecaptcha');

        if (!$antifraud.length) {
            return;
        }

        // Field wrappers that depend on the security verification being active.
        // The provider select is a joined child of the enable checkbox, so it
        // is hidden via its joined blocks instead of the whole parent card.
        var $dependents = $(
            '#lknFraudDetectionForWoocommerceEnableIpLookup'
        ).map(function () {
            return $(this).closest('.admin-layout-field-parent-flex').get(0);
        });

        var $providerJoined      = $('#lknFraudDetectionForWoocommerceRecaptchaSelected')
            .closest('.admin-layout-joined-component-bg');
        var $providerJoinedLabel = $providerJoined.prev('.admin-layout-joined-label-desc');

        // Nav tabs that depend on the security verification being active
        var $dependentTabs = $(
            '[data-target="block-google-recaptcha"], ' +
            '[data-target="block-cloudflare-turnstile"]'
        );

        function syncDependents() {
            var active = $antifraud.is(':checked');

            $($dependents).each(function () {
                $(this).toggleClass('lkn-disabled-field', !active);
            });

            $providerJoined.add($providerJoinedLabel).toggleClass('lkn-disabled-field', !active);

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

        // ── Security Version: warn when the selected provider has no creds ──
        var creds = (typeof lknFsdwCredentialsVars !== 'undefined') ? lknFsdwCredentialsVars : null;

        if (creds && creds.providers) {
            var $providerSelect = $('#' + creds.selectId);
            var $enableToggle   = $('#' + creds.enableId);
            var $credsWarning   = $('.lkn-fsdw-credentials-warning');
            var $tabNotices     = $('.lkn-fsdw-tab-notice');

            function providerMissing(providerKey) {
                var provider = creds.providers[providerKey];
                if (!provider) {
                    return false;
                }
                var missing = false;
                $.each(provider.fields, function (_, fieldId) {
                    var $field = $('#' + fieldId);
                    if ($field.length && $.trim($field.val()) === '') {
                        missing = true;
                    }
                });
                return missing;
            }

            function providerLabel(providerKey) {
                return (creds.providers[providerKey] && creds.providers[providerKey].label)
                    ? creds.providers[providerKey].label
                    : providerKey;
            }

            function fill(template, map) {
                return String(template).replace(/\{(\w+)\}/g, function (match, key) {
                    return Object.prototype.hasOwnProperty.call(map, key) ? map[key] : match;
                });
            }

            function syncCredentialsWarning() {
                if (!$credsWarning.length || !$providerSelect.length) {
                    return;
                }

                var provider = $providerSelect.val();
                var enabled  = !$enableToggle.length || $enableToggle.is(':checked');
                var show     = enabled && provider && provider !== 'none' &&
                    creds.providers[provider] && providerMissing(provider);

                if (show) {
                    $credsWarning.find('.lkn-fsdw-credentials-warning-text').text(creds.i18n.missing);
                    $credsWarning.find('.lkn-fsdw-credentials-warning-link')
                        .data('goto-tab', creds.providers[provider].tab)
                        .attr('data-goto-tab', creds.providers[provider].tab)
                        .text(creds.i18n.link);
                    $credsWarning.show();
                } else {
                    $credsWarning.hide();
                }
            }

            // Contextual notice on the Google reCAPTCHA / Cloudflare Turnstile
            // tabs: tells the admin which provider is active (or to enable /
            // select one) and links back to the security settings field.
            function syncTabNotices() {
                if (!$tabNotices.length || !$providerSelect.length) {
                    return;
                }

                var active  = $providerSelect.val();
                var enabled = !$enableToggle.length || $enableToggle.is(':checked');

                $tabNotices.each(function () {
                    var $notice     = $(this);
                    var providerKey = $notice.data('provider');
                    var message     = '';

                    if (!enabled) {
                        message = fill(creds.i18n.tabDisabled, { provider: providerLabel(providerKey) });
                    } else if (!active || active === 'none') {
                        message = fill(creds.i18n.tabNone, { provider: providerLabel(providerKey) });
                    } else if (active !== providerKey) {
                        // Only the non-active provider tab warns; the selected
                        // provider relies on the field-level credentials notice.
                        message = fill(creds.i18n.tabOther, {
                            active: providerLabel(active),
                            provider: providerLabel(providerKey)
                        });
                    }

                    if (message) {
                        $notice.find('.lkn-fsdw-tab-notice-text').text(message);
                        $notice.find('.lkn-fsdw-tab-notice-link').text(creds.i18n.tabAction);
                        $notice.show();
                    } else {
                        $notice.hide();
                    }
                });
            }

            function refresh() {
                syncCredentialsWarning();
                syncTabNotices();
            }

            // Notice button: switch to the Antifraud tab and smooth-scroll to
            // the field that enables the security verification.
            $(document).on('click', '.lkn-fsdw-tab-notice-link', function (e) {
                e.preventDefault();
                var $navLink = $('.admin-layout-title-link[data-target="block-antifraud"]');
                if ($navLink.length) {
                    $navLink.trigger('click');
                }
                var anchor = document.getElementById('lknFraudDetectionForWoocommerceEnableRecaptcha');
                if (anchor) {
                    window.setTimeout(function () {
                        var target = anchor.closest('.admin-layout-field-parent-flex') || anchor;
                        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 80);
                }
            });

            if ($providerSelect.length) {
                $providerSelect.on('change', refresh);
                if ($enableToggle.length) {
                    $enableToggle.on('change', refresh);
                }
                $.each(creds.providers, function (_, provider) {
                    $.each(provider.fields, function (_, fieldId) {
                        $('#' + fieldId).on('input change', refresh);
                    });
                });
                refresh();
            }
        }
    });

}(jQuery));
