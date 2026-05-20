import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

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
                    '<th style="width:130px;">' + escHtml(i18n.colBannedBy || 'Banned By') + '</th>' +
                    '<th style="width:145px;">' + escHtml(i18n.colBannedAt || 'Banned At') + '</th>' +
                    '<th style="width:145px;">' + escHtml(i18n.colExpiresAt || 'Expires At') + '</th>' +
                    '<th style="width:100px;text-align:center;">' + escHtml(i18n.colActions || 'Actions') + '</th>' +
                '</tr></thead>' +
                '<tbody id="lkn-fsdw-banned-ips-list"><tr><td colspan="5" style="text-align:center;">' + escHtml(i18n.loading || 'Loading…') + '</td></tr></tbody>' +
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
        $tbody.html('<tr><td colspan="5" style="text-align:center;">' + escHtml(i18n.loading || 'Loading…') + '</td></tr>');

        $.post(vars.ajaxUrl, { action: 'lkn_fsdw_get_banned_ips', nonce: vars.nonceGet }, function (response) {
            $tbody.empty();
            if (!response.success) {
                $tbody.html('<tr><td colspan="5">' + escHtml(i18n.errorLoad || 'Failed to load IPs.') + '</td></tr>');
                return;
            }
            var ips = response.data.ips || [];
            if (ips.length === 0) {
                $tbody.html('<tr><td colspan="5" style="text-align:center;">' + escHtml(i18n.empty || 'No banned IPs.') + '</td></tr>');
                return;
            }
            ips.forEach(function (item) {
                $tbody.append(buildRow(item));
            });
        }).fail(function () {
            $('#lkn-fsdw-banned-ips-list').html('<tr><td colspan="5">' + escHtml(i18n.errorLoad || 'Failed to load IPs.') + '</td></tr>');
        });
    }

    function buildRow(item) {
        var ip        = typeof item === 'object' ? (item.value      || '') : item;
        var bannedBy  = typeof item === 'object' ? (item.banned_by  || '—') : '—';
        var bannedAt  = typeof item === 'object' ? (item.banned_at  || '—') : '—';
        var expiresAt = typeof item === 'object' ? (item.expires_at || i18n.forever || 'Forever') : (i18n.forever || 'Forever');
        return $('<tr data-ip="' + escAttr(ip) + '">' +
            '<td><code>' + escHtml(ip) + '</code></td>' +
            '<td>' + escHtml(bannedBy) + '</td>' +
            '<td>' + escHtml(bannedAt) + '</td>' +
            '<td>' + escHtml(expiresAt) + '</td>' +
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
        Swal.fire({
            title: i18n.unbanTitle || 'Unban IP',
            html: '<p>' + escHtml(i18n.unbanConfirmMsg || 'Do you want to unban the following IP?') + '</p>' +
                  '<p><strong>' + escHtml(ip) + '</strong></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: i18n.unbanConfirmBtn || 'Confirm Unban',
            cancelButtonText:  i18n.cancel || 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return $.post(vars.ajaxUrl, {
                    action: 'lkn_fsdw_unban_ip',
                    nonce:  vars.nonceUnban,
                    ip:     ip,
                }).then(function (response) {
                    if (!response.success) {
                        Swal.showValidationMessage(
                            (response.data && response.data.message) || (i18n.errorUnban || 'Error.')
                        );
                    }
                    return response;
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); },
        }).then(function (result) {
            if (result.isConfirmed && result.value && result.value.success) {
                $container.find('tr[data-ip="' + escAttr(ip) + '"]').fadeOut(300, function () {
                    $(this).remove();
                    var $tbody = $('#lkn-fsdw-banned-ips-list');
                    if ($tbody.children(':visible').length === 0) {
                        $tbody.html('<tr><td colspan="5" style="text-align:center;">' + escHtml(i18n.empty || 'No banned IPs.') + '</td></tr>');
                    }
                });
                Swal.fire({
                    icon: 'success',
                    title: (result.value.data && result.value.data.message) || (i18n.successUnban || 'IP unbanned.'),
                });
            }
        });
    }

    // ── Ban new IP → modal confirmation ───────────────────────────────────
    $container.on('click', '#lkn-fsdw-add-ip-btn', function () {
        var ip = $('#lkn-fsdw-new-ip').val().trim();
        if (!ip) {
            Swal.fire({ icon: 'warning', title: i18n.errorEmpty || 'Enter an IP address.' });
            return;
        }
        openBanModal(ip);
    });

    $container.on('keydown', '#lkn-fsdw-new-ip', function (e) {
        if (e.key === 'Enter') { $('#lkn-fsdw-add-ip-btn').trigger('click'); }
    });

    function openBanModal(ip) {
        Swal.fire({
            title: i18n.banTitle || 'Ban IP',
            html: '<p>' + escHtml(i18n.banConfirmMsg || 'Do you want to ban the following IP from checkout?') + '</p>' +
                  '<p><strong>' + escHtml(ip) + '</strong></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: i18n.banConfirmBtn || 'Confirm Ban',
            cancelButtonText:  i18n.cancel || 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return $.post(vars.ajaxUrl, {
                    action: 'lkn_fsdw_ban_ip',
                    nonce:  vars.nonceBan,
                    ip:     ip,
                }).then(function (response) {
                    if (!response.success) {
                        Swal.showValidationMessage(
                            (response.data && response.data.message) || (i18n.errorBan || 'Error.')
                        );
                    }
                    return response;
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); },
        }).then(function (result) {
            if (result.isConfirmed && result.value && result.value.success) {
                $('#lkn-fsdw-new-ip').val('');
                loadBannedIps();
                Swal.fire({
                    icon: 'success',
                    title: (result.value.data && result.value.data.message) || (i18n.successBan || 'IP banned.'),
                });
            }
        });
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