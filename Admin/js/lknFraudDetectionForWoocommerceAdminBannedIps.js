jQuery(document).ready(function ($) {
    'use strict';

    var vars = (typeof lknFsdwBannedIpsVars !== 'undefined') ? lknFsdwBannedIpsVars : {};
    var i18n = vars.i18n || {};
    var $container = $('#block-banned-ips');

    if (!$container.length) {
        return;
    }

    // ── Build UI ───────────────────────────────────────────────────────────
    var $wrapper = $(
        '<div class="lkn-fsdw-banned-ips-wrapper">' +
            '<div class="lkn-fsdw-banned-ips-add">' +
                '<input type="text" id="lkn-fsdw-new-ip" maxlength="39" placeholder="' + escAttr(i18n.placeholder || 'IPv4 or IPv6') + '" />' +
                '<button type="button" id="lkn-fsdw-add-ip-btn" class="button button-primary">' + escHtml(i18n.banBtn || 'Ban IP') + '</button>' +
            '</div>' +
            '<table class="widefat striped lkn-fsdw-banned-ips-table" style="margin-top:16px;">' +
                '<thead><tr>' +
                    '<th>' + escHtml(i18n.colIp || 'IP Address') + '</th>' +
                    '<th style="width:120px;text-align:center;">' + escHtml(i18n.colActions || 'Actions') + '</th>' +
                '</tr></thead>' +
                '<tbody id="lkn-fsdw-banned-ips-list"><tr><td colspan="2" style="text-align:center;">' + escHtml(i18n.loading || 'Loading…') + '</td></tr></tbody>' +
            '</table>' +
        '</div>'
    );

    $container.append($wrapper);

    // ── IP input: auto-format (IPv4 and IPv6) ─────────────────────────────
    $container.on('input', '#lkn-fsdw-new-ip', function (e) {
        var isDeleting = e.originalEvent && (
            e.originalEvent.inputType === 'deleteContentBackward' ||
            e.originalEvent.inputType === 'deleteContentForward'
        );
        var raw = this.value;

        // Detect IPv6: has colon or a hex letter (a-f)
        if (/[:a-fA-F]/.test(raw)) {
            lknFormatIPv6(this, raw, isDeleting);
        } else {
            lknFormatIPv4(this, raw, isDeleting);
        }
    });

    function lknFormatIPv4(input, raw, isDeleting) {
        raw = raw.replace(/[^0-9.]/g, '');
        var parts  = raw.split('.').slice(0, 4).map(function (p) { return p.substring(0, 3); });
        var result = parts.join('.');

        // Auto-insert dot after 3 digits — only when typing, not deleting,
        // and result doesn't already end with a dot (parts.join preserves trailing dots)
        if (!isDeleting && !result.endsWith('.') && parts.length < 4 && parts[parts.length - 1].length === 3) {
            result += '.';
        }
        input.value = result;
    }

    function lknFormatIPv6(input, raw, isDeleting) {
        raw = raw.replace(/[^0-9a-fA-F:]/g, '').toLowerCase();

        // Preserve compressed notation (::) – don't auto-group, just limit length
        if (raw.indexOf('::') !== -1) {
            input.value = raw.substring(0, 39);
            return;
        }

        var parts  = raw.split(':').slice(0, 8).map(function (p) { return p.substring(0, 4); });
        var result = parts.join(':');

        // Auto-insert colon after 4 hex digits — only when typing, not deleting
        if (!isDeleting && !result.endsWith(':') && parts.length < 8 && parts[parts.length - 1].length === 4) {
            result += ':';
        }
        input.value = result;
    }

    // ── Fetch & render list ────────────────────────────────────────────────
    function loadBannedIps() {
        var $tbody = $('#lkn-fsdw-banned-ips-list');
        $tbody.html('<tr><td colspan="2" style="text-align:center;">' + escHtml(i18n.loading || 'Loading…') + '</td></tr>');

        $.post(vars.ajaxUrl, { action: 'lkn_fsdw_get_banned_ips', nonce: vars.nonceGet }, function (response) {
            $tbody.empty();
            if (!response.success) {
                $tbody.html('<tr><td colspan="2">' + escHtml(i18n.errorLoad || 'Failed to load IPs.') + '</td></tr>');
                return;
            }
            var ips = response.data.ips || [];
            if (ips.length === 0) {
                $tbody.html('<tr><td colspan="2" style="text-align:center;">' + escHtml(i18n.empty || 'No banned IPs.') + '</td></tr>');
                return;
            }
            ips.forEach(function (ip) {
                $tbody.append(buildRow(ip));
            });
        }).fail(function () {
            $('#lkn-fsdw-banned-ips-list').html('<tr><td colspan="2">' + escHtml(i18n.errorLoad || 'Failed to load IPs.') + '</td></tr>');
        });
    }

    function buildRow(ip) {
        return $('<tr data-ip="' + escAttr(ip) + '">' +
            '<td><code>' + escHtml(ip) + '</code></td>' +
            '<td style="text-align:center;">' +
                '<button type="button" class="button button-small lkn-fsdw-unban-btn" data-ip="' + escAttr(ip) + '">' +
                    escHtml(i18n.unbanBtn || 'Unban') +
                '</button>' +
            '</td>' +
        '</tr>');
    }

    // ── Unban click → modal ────────────────────────────────────────────────
    $container.on('click', '.lkn-fsdw-unban-btn', function () {
        var ip = $(this).data('ip');
        openUnbanModal(ip);
    });

    function openUnbanModal(ip) {
        var $modal = lknFsdwModal(
            '<h3 style="margin-top:0;">' + escHtml(i18n.unbanTitle || 'Unban IP') + '</h3>' +
            '<p>' + escHtml(i18n.unbanConfirmMsg || 'Do you want to unban the following IP?') + '</p>' +
            '<p><strong>' + escHtml(ip) + '</strong></p>' +
            '<div id="lkn-fsdw-bi-feedback" style="margin:6px 0 10px;min-height:20px;"></div>' +
            '<button id="lkn-fsdw-unban-confirm" class="button button-primary">' +
                escHtml(i18n.unbanConfirmBtn || 'Confirm Unban') +
            '</button>' +
            ' <button class="button lkn-fsdw-close">' + escHtml(i18n.cancel || 'Cancel') + '</button>'
        );

        $modal.on('click', '#lkn-fsdw-unban-confirm', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).text(i18n.loading || '…');

            $.post(vars.ajaxUrl, { action: 'lkn_fsdw_unban_ip', nonce: vars.nonceUnban, ip: ip }, function (response) {
                var $fb = $('#lkn-fsdw-bi-feedback');
                if (response.success) {
                    $fb.html('<span style="color:#007a00;">' + escHtml(response.data.message || 'IP unbanned.') + '</span>');
                    $container.find('tr[data-ip="' + escAttr(ip) + '"]').fadeOut(300, function () {
                        $(this).remove();
                        var $tbody = $('#lkn-fsdw-banned-ips-list');
                        if ($tbody.children(':visible').length === 0) {
                            $tbody.html('<tr><td colspan="2" style="text-align:center;">' + escHtml(i18n.empty || 'No banned IPs.') + '</td></tr>');
                        }
                    });
                    setTimeout(lknFsdwRemoveModal, 1800);
                } else {
                    $fb.html('<span style="color:#cc1818;">' + escHtml((response.data && response.data.message) || i18n.errorUnban || 'Error.') + '</span>');
                    $btn.prop('disabled', false).text(i18n.unbanConfirmBtn || 'Confirm Unban');
                }
            }).fail(function () {
                $('#lkn-fsdw-bi-feedback').html('<span style="color:#cc1818;">' + escHtml(i18n.errorUnban || 'Error unbanning IP.') + '</span>');
                $btn.prop('disabled', false).text(i18n.unbanConfirmBtn || 'Confirm Unban');
            });
        });
    }

    // ── Ban new IP → modal confirmation ───────────────────────────────────
    $container.on('click', '#lkn-fsdw-add-ip-btn', function () {
        var ip = $('#lkn-fsdw-new-ip').val().trim();
        if (!ip) {
            openInfoModal(i18n.errorEmpty || 'Enter an IP address.', 'error');
            return;
        }
        openBanModal(ip);
    });

    $container.on('keydown', '#lkn-fsdw-new-ip', function (e) {
        if (e.key === 'Enter') { $('#lkn-fsdw-add-ip-btn').trigger('click'); }
    });

    function openBanModal(ip) {
        var $modal = lknFsdwModal(
            '<h3 style="margin-top:0;">' + escHtml(i18n.banTitle || 'Ban IP') + '</h3>' +
            '<p>' + escHtml(i18n.banConfirmMsg || 'Do you want to ban the following IP from checkout?') + '</p>' +
            '<p><strong>' + escHtml(ip) + '</strong></p>' +
            '<div id="lkn-fsdw-bi-feedback" style="margin:6px 0 10px;min-height:20px;"></div>' +
            '<button id="lkn-fsdw-ban-confirm" class="button button-primary">' +
                escHtml(i18n.banConfirmBtn || 'Confirm Ban') +
            '</button>' +
            ' <button class="button lkn-fsdw-close">' + escHtml(i18n.cancel || 'Cancel') + '</button>'
        );

        $modal.on('click', '#lkn-fsdw-ban-confirm', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).text(i18n.loading || '…');

            $.post(vars.ajaxUrl, { action: 'lkn_fsdw_ban_ip', nonce: vars.nonceBan, ip: ip }, function (response) {
                var $fb = $('#lkn-fsdw-bi-feedback');
                if (response.success) {
                    $fb.html('<span style="color:#007a00;">' + escHtml(response.data.message || 'IP banned.') + '</span>');
                    $('#lkn-fsdw-new-ip').val('');
                    loadBannedIps();
                    setTimeout(lknFsdwRemoveModal, 1800);
                } else {
                    $fb.html('<span style="color:#cc1818;">' + escHtml((response.data && response.data.message) || i18n.errorBan || 'Error.') + '</span>');
                    $btn.prop('disabled', false).text(i18n.banConfirmBtn || 'Confirm Ban');
                }
            }).fail(function () {
                $('#lkn-fsdw-bi-feedback').html('<span style="color:#cc1818;">' + escHtml(i18n.errorBan || 'Error banning IP.') + '</span>');
                $btn.prop('disabled', false).text(i18n.banConfirmBtn || 'Confirm Ban');
            });
        });
    }

    function openInfoModal(message, type) {
        var color = type === 'error' ? '#cc1818' : '#007a00';
        lknFsdwModal(
            '<p style="margin:0;color:' + color + ';">' + escHtml(message) + '</p>' +
            '<p style="text-align:right;margin:12px 0 0;">' +
                '<button class="button lkn-fsdw-close">OK</button>' +
            '</p>'
        );
    }

    // ── Modal helpers (same pattern as OrderIpLinks) ───────────────────────
    function lknFsdwModal(content) {
        lknFsdwRemoveModal();
        var $overlay = $('<div id="lkn-fsdw-modal-overlay" style="' +
            'position:fixed;top:0;left:0;width:100%;height:100%;' +
            'background:rgba(0,0,0,.6);z-index:99999;' +
            'display:flex;align-items:center;justify-content:center;"></div>');
        var $box = $('<div style="' +
            'background:#fff;border-radius:4px;padding:24px 28px;' +
            'max-width:460px;width:90%;position:relative;box-shadow:0 4px 20px rgba(0,0,0,.25);">' +
            '<button class="lkn-fsdw-close" style="' +
            'position:absolute;top:10px;right:14px;background:none;' +
            'border:none;font-size:22px;cursor:pointer;line-height:1;">&times;</button>' +
            content +
            '</div>');
        $overlay.append($box);
        $('body').append($overlay);
        $overlay.on('click', '.lkn-fsdw-close', lknFsdwRemoveModal);
        $overlay.on('click', function (e) {
            if ($(e.target).is('#lkn-fsdw-modal-overlay')) { lknFsdwRemoveModal(); }
        });
        return $overlay;
    }

    function lknFsdwRemoveModal() {
        $('#lkn-fsdw-modal-overlay').remove();
    }

    // ── Escape helpers ─────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escAttr(str) {
        return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // ── Init ───────────────────────────────────────────────────────────────
    loadBannedIps();
});