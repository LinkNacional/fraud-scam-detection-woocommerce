/**
 * Support tab (debug & diagnostics) — settings page behaviour.
 *
 * - Keeps the "View Order Log" option usable only while debug logging is on.
 * - Handles the "Clear Order Logs" button (AJAX).
 *
 * Loaded directly (no webpack build step).
 *
 * @package LknFraudDetectionForWoocommerce
 */
(function () {
    'use strict';

    var vars = (typeof lknFsdwSupportVars !== 'undefined') ? lknFsdwSupportVars : {};
    var i18n = vars.i18n || {};

    document.addEventListener('DOMContentLoaded', function () {
        var debug = document.getElementById('lknFraudDetectionForWoocommerceDebug');
        var showLogs = document.getElementById('lknFraudDetectionForWoocommerceShowOrderLogs');
        var clearBtn = document.getElementById('lknFsdwClearOrderLogs');

        // "View Order Log" only makes sense while debug logging is enabled.
        var lastShowLogs = showLogs ? showLogs.checked : false;

        function syncShowLogs() {
            if (!debug || !showLogs) {
                return;
            }
            if (!debug.checked) {
                if (!showLogs.disabled) {
                    lastShowLogs = showLogs.checked;
                }
                showLogs.checked = false;
                showLogs.disabled = true;
            } else {
                showLogs.disabled = false;
                showLogs.checked = lastShowLogs;
            }
        }

        if (debug && showLogs) {
            syncShowLogs();
            debug.addEventListener('change', syncShowLogs);
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (!window.confirm(i18n.confirm || 'Clear the logs stored on all orders?')) {
                    return;
                }

                var originalText = clearBtn.textContent;
                clearBtn.disabled = true;
                clearBtn.textContent = i18n.clearing || 'Clearing…';

                jQuery.ajax({
                    type: 'POST',
                    url: vars.ajaxUrl,
                    data: {
                        action: 'lkn_fsdw_clear_order_logs',
                        nonce: vars.nonce
                    },
                    success: function (response) {
                        clearBtn.disabled = false;
                        clearBtn.textContent = originalText;

                        if (response && response.success) {
                            window.alert((response.data && response.data.message) || i18n.success || 'Logs cleared.');
                        } else {
                            window.alert((response && response.data && response.data.message) || i18n.error || 'Failed to clear logs.');
                        }
                    },
                    error: function () {
                        clearBtn.disabled = false;
                        clearBtn.textContent = originalText;
                        window.alert(i18n.error || 'Failed to clear logs.');
                    }
                });
            });
        }

        // ── "Send settings to support" (WhatsApp) button ──
        var sendBtn = document.getElementById('lknFsdwSendConfigs');
        if (sendBtn && vars.whatsapp) {
            sendBtn.setAttribute('type', 'button');
            sendBtn.textContent = i18n.support || 'Send settings to support';

            sendBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var wa = vars.whatsapp;
                var message = i18n.intro || 'Hello! I need support with the Fraud & Scam Detection plugin. Here are my settings:';
                message += ' Plugin: ' + (wa.plugin || '') + ' v' + (wa.version || '') + ' | Site: ' + (wa.domain || '') + ' | ';

                var report = wa.report || {};
                Object.keys(report).forEach(function (key) {
                    var value = report[key];
                    if (value === undefined || value === null || value === '') {
                        value = 'null';
                    }
                    message += ' ' + key + ': ' + value + ' |';
                });

                message += ' ' + (i18n.outro || 'Waiting for your reply, thank you!');

                window.open(
                    'https://api.whatsapp.com/send/?phone=' + encodeURIComponent(wa.number || '') + '&text=' + encodeURIComponent(message),
                    '_blank'
                );
            });
        }
    });
})();
