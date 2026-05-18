(function ($) {
    'use strict';

    var vars = (typeof lknFsdwOrderIpVars !== 'undefined') ? lknFsdwOrderIpVars : {};
    var i18n = vars.i18n || {};

    $(function () {
        var $ipSpan = $('span.woocommerce-Order-customerIP');

        if (!$ipSpan.length) {
            return;
        }

        var ip = $ipSpan.text().trim();
        var validIp = lknFsdwIsLookupable(ip);

        var lookupLink = validIp
            ? '<a href="#" class="lkn-fsdw-lookup">lookup</a>'
            : '<a href="#" class="lkn-fsdw-lookup" aria-disabled="true" style="opacity:.4;cursor:not-allowed;pointer-events:none;" title="Invalid IP">lookup</a>';

        var $actions = $('<span class="lkn-fsdw-ip-actions">'
            + ' [ ' + lookupLink
            + ' | <a href="#" class="lkn-fsdw-filter">filter</a>'
            + ' | <a href="#" class="lkn-fsdw-ban">ban</a> ]'
            + '</span>');

        $ipSpan.after($actions);

        // ── Lookup ─────────────────────────────────────────────────────────
        $actions.on('click', '.lkn-fsdw-lookup', function (e) {
            e.preventDefault();
            window.open('https://extreme-ip-lookup.com/' + encodeURIComponent(ip), '_blank');
        });

        // ── Filter ─────────────────────────────────────────────────────────
        $actions.on('click', '.lkn-fsdw-filter', function (e) {
            e.preventDefault();
            lknFsdwOpenFilterModal();
        });

        // ── Ban ────────────────────────────────────────────────────────────
        $actions.on('click', '.lkn-fsdw-ban', function (e) {
            e.preventDefault();
            var $link = $(this);
            if ($link.data('banned') === true) {
                lknFsdwUnbanFromLink(ip, $link);
            } else {
                lknFsdwOpenBanModal(ip, $link);
            }
        });

        // Check initial ban state on page load
        if (vars.nonceGet) {
            $.post(vars.ajaxUrl, { action: 'lkn_fsdw_get_banned_ips', nonce: vars.nonceGet }, function (response) {
                if (response.success) {
                    var isBanned = (response.data.ips || []).indexOf(ip) !== -1;
                    lknFsdwUpdateBanLink($actions.find('.lkn-fsdw-ban'), isBanned);
                }
            });
        }
    });

    // ── Helpers ────────────────────────────────────────────────────────────
    function lknFsdwIsValidIp(ip) {
        var ipv4 = /^(\d{1,3}\.){3}\d{1,3}$/;
        var ipv6 = /^[\da-fA-F]{0,4}(:[\da-fA-F]{0,4}){2,7}$/;
        return ipv4.test(ip) || ipv6.test(ip);
    }

    function lknFsdwIsLookupable(ip) {
        if (!lknFsdwIsValidIp(ip)) { return false; }

        // Loopback / private IPv4
        if (/^127\./.test(ip)) { return false; }
        if (/^10\./.test(ip)) { return false; }
        if (/^192\.168\./.test(ip)) { return false; }
        if (/^172\.(1[6-9]|2\d|3[01])\./.test(ip)) { return false; }
        if (/^169\.254\./.test(ip)) { return false; } // link-local

        // Loopback / private IPv6
        if (/^::1$/.test(ip)) { return false; }           // loopback
        if (/^fe[89ab]/i.test(ip)) { return false; }      // link-local fe80::/10
        if (/^f[cd]/i.test(ip)) { return false; }         // unique-local fc00::/7

        return true;
    }

    function lknFsdwUpdateBanLink($link, isBanned) {
        if (isBanned) {
            $link.text(i18n.unban || 'unban').data('banned', true).css('color', '#cc1818');
        } else {
            $link.text(i18n.ban || 'ban').data('banned', false).css('color', '');
        }
    }

    function lknFsdwUnbanFromLink(ip, $link) {
        var $modal = lknFsdwModal(
            '<h3 style="margin-top:0;">' + (i18n.unbanTitle || 'Unban IP') + '</h3>'
            + '<p>' + (i18n.unbanConfirm || 'Do you want to unban the following IP?') + '</p>'
            + '<p><strong>' + $('<span>').text(ip).html() + '</strong></p>'
            + '<div id="lkn-fsdw-unban-feedback" style="margin:6px 0 10px;min-height:20px;"></div>'
            + '<button id="lkn-fsdw-unban-confirm" class="button button-primary">'
            + (i18n.unbanConfirmBtn || 'Confirm Unban') + '</button>'
            + ' <button class="button lkn-fsdw-close">' + (i18n.cancel || 'Cancel') + '</button>'
        );

        $modal.on('click', '#lkn-fsdw-unban-confirm', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).text(i18n.unbanning || '…');
            $.post(vars.ajaxUrl, { action: 'lkn_fsdw_unban_ip', ip: ip, nonce: vars.nonceUnban }, function (response) {
                var $feedback = $('#lkn-fsdw-unban-feedback');
                if (response.success) {
                    $feedback.html('<span style="color:#007a00;">'
                        + (response.data.message || 'IP unbanned.') + '</span>');
                    lknFsdwUpdateBanLink($link, false);
                    setTimeout(lknFsdwRemoveModal, 1800);
                } else {
                    $feedback.html('<span style="color:#cc1818;">'
                        + ((response.data && response.data.message) || 'Error.') + '</span>');
                    $btn.prop('disabled', false).text(i18n.unbanConfirmBtn || 'Confirm Unban');
                }
            });
        });
    }

    function lknFsdwModal(content) {
        lknFsdwRemoveModal();
        var $overlay = $('<div id="lkn-fsdw-modal-overlay" style="'
            + 'position:fixed;top:0;left:0;width:100%;height:100%;'
            + 'background:rgba(0,0,0,.6);z-index:99999;'
            + 'display:flex;align-items:center;justify-content:center;"></div>');
        var $box = $('<div style="'
            + 'background:#fff;border-radius:4px;padding:24px 28px;'
            + 'max-width:460px;width:90%;position:relative;box-shadow:0 4px 20px rgba(0,0,0,.25);">'
            + '<button class="lkn-fsdw-close" style="'
            + 'position:absolute;top:10px;right:14px;background:none;'
            + 'border:none;font-size:22px;cursor:pointer;line-height:1;">&times;</button>'
            + content
            + '</div>');
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

    // ── Filter modal — lista de IPs banidos paginada ──────────────────────
    function lknFsdwOpenFilterModal() {
        var $modal = lknFsdwModal(
            '<h3 style="margin-top:0;">' + (i18n.filterTitle || 'Banned IPs') + '</h3>'
            + '<div id="lkn-fsdw-filter-controls" style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">'
            + '<span>' + (i18n.showLabel || 'Show:') + '</span>'
            + '<button class="button lkn-fsdw-limit" data-limit="5">5</button>'
            + '<button class="button lkn-fsdw-limit" data-limit="10">10</button>'
            + '<button class="button lkn-fsdw-limit" data-limit="25">25</button>'
            + '</div>'
            + '<div id="lkn-fsdw-filter-loading" style="padding:12px 0;">' + (i18n.loading || 'Loading…') + '</div>'
            + '<table id="lkn-fsdw-ip-table" style="width:100%;border-collapse:collapse;display:none;">'
            + '<thead><tr>'
            + '<th style="text-align:left;padding:6px 4px;border-bottom:1px solid #ddd;">' + (i18n.ipCol || 'IP Address') + '</th>'
            + '<th style="padding:6px 4px;border-bottom:1px solid #ddd;"></th>'
            + '</tr></thead>'
            + '<tbody id="lkn-fsdw-ip-tbody"></tbody>'
            + '</table>'
            + '<div id="lkn-fsdw-filter-empty" style="display:none;padding:12px 0;color:#666;">' + (i18n.noIps || 'No banned IPs.') + '</div>'
            + '<div id="lkn-fsdw-filter-pagination" style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;">'
            + '<button class="button" id="lkn-fsdw-page-prev">&laquo; ' + (i18n.prev || 'Prev') + '</button>'
            + '<span id="lkn-fsdw-page-info"></span>'
            + '<button class="button" id="lkn-fsdw-page-next">' + (i18n.next || 'Next') + ' &raquo;</button>'
            + '</div>'
        );

        var allIps = [];
        var currentPage = 1;
        var perPage = 10;

        function renderTable() {
            var $tbody = $('#lkn-fsdw-ip-tbody');
            var $table = $('#lkn-fsdw-ip-table');
            var $empty = $('#lkn-fsdw-filter-empty');
            var $pag   = $('#lkn-fsdw-filter-pagination');

            $tbody.empty();

            if (!allIps.length) {
                $table.hide(); $pag.hide(); $empty.show();
                return;
            }

            var totalPages = Math.ceil(allIps.length / perPage);
            if (currentPage > totalPages) { currentPage = totalPages; }
            var start = (currentPage - 1) * perPage;
            var pageIps = allIps.slice(start, start + perPage);

            $.each(pageIps, function (idx, ip) {
                var $tr = $('<tr data-ip="' + $('<span>').text(ip).html() + '" style="border-bottom:1px solid #f0f0f0;">'
                    + '<td style="padding:6px 4px;font-family:monospace;">' + $('<span>').text(ip).html() + '</td>'
                    + '<td style="padding:6px 4px;text-align:right;">'
                    + '<button class="button button-small lkn-fsdw-unban" style="color:#cc1818;">'
                    + (i18n.unban || 'Unban') + '</button></td>'
                    + '</tr>');
                $tbody.append($tr);
            });

            $('#lkn-fsdw-page-info').text(currentPage + ' / ' + totalPages);
            $('#lkn-fsdw-page-prev').prop('disabled', currentPage <= 1);
            $('#lkn-fsdw-page-next').prop('disabled', currentPage >= totalPages);

            $empty.hide(); $table.show(); $pag.show();

            // Highlight active limit button
            $('.lkn-fsdw-limit').removeClass('button-primary');
            $('.lkn-fsdw-limit[data-limit="' + perPage + '"]').addClass('button-primary');
        }

        // Fetch IPs
        $.post(vars.ajaxUrl, { action: 'lkn_fsdw_get_banned_ips', nonce: vars.nonceGet }, function (response) {
            $('#lkn-fsdw-filter-loading').hide();
            if (response.success) {
                allIps = response.data.ips || [];
                renderTable();
            }
        });

        // Limit buttons
        $modal.on('click', '.lkn-fsdw-limit', function () {
            perPage = parseInt($(this).data('limit'), 10);
            currentPage = 1;
            renderTable();
        });

        // Pagination
        $modal.on('click', '#lkn-fsdw-page-prev', function () {
            if (currentPage > 1) { currentPage--; renderTable(); }
        });
        $modal.on('click', '#lkn-fsdw-page-next', function () {
            var totalPages = Math.ceil(allIps.length / perPage);
            if (currentPage < totalPages) { currentPage++; renderTable(); }
        });

        // Unban
        $modal.on('click', '.lkn-fsdw-unban', function () {
            var $btn = $(this);
            var ip = $btn.closest('tr').data('ip');
            $btn.prop('disabled', true).text(i18n.unbanning || '…');
            $.post(vars.ajaxUrl, { action: 'lkn_fsdw_unban_ip', ip: ip, nonce: vars.nonceUnban }, function (response) {
                if (response.success) {
                    allIps = allIps.filter(function (x) { return x !== ip; });
                    renderTable();
                } else {
                    $btn.prop('disabled', false).text(i18n.unban || 'Unban');
                }
            });
        });
    }

    // ── Ban modal ──────────────────────────────────────────────────────────
    function lknFsdwOpenBanModal(ip, $banLink) {
        var $modal = lknFsdwModal(
            '<h3 style="margin-top:0;">' + (i18n.banTitle || 'Ban IP') + '</h3>'
            + '<p>' + (i18n.banConfirm || 'Do you want to ban the following IP from checkout?') + '</p>'
            + '<p><strong>' + $('<span>').text(ip).html() + '</strong></p>'
            + '<div id="lkn-fsdw-ban-feedback" style="margin:6px 0 10px;min-height:20px;"></div>'
            + '<button id="lkn-fsdw-ban-confirm" class="button button-primary">'
            + (i18n.banConfirmBtn || 'Confirm Ban') + '</button>'
            + ' <button class="button lkn-fsdw-close">' + (i18n.cancel || 'Cancel') + '</button>'
        );

        $modal.on('click', '#lkn-fsdw-ban-confirm', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).text(i18n.banning || 'Banning…');

            $.post(vars.ajaxUrl, {
                action: 'lkn_fsdw_ban_ip',
                ip:     ip,
                nonce:  vars.nonce
            }, function (response) {
                var $feedback = $('#lkn-fsdw-ban-feedback');
                if (response.success) {
                    $feedback.html('<span style="color:#007a00;">'
                        + (response.data.message || 'IP banned.') + '</span>');
                    lknFsdwUpdateBanLink($banLink, true);
                    setTimeout(lknFsdwRemoveModal, 1800);
                } else {
                    $feedback.html('<span style="color:#cc1818;">'
                        + ((response.data && response.data.message) || 'Error.') + '</span>');
                    $btn.prop('disabled', false).text(i18n.banConfirmBtn || 'Confirm Ban');
                }
            });
        });
    }

})(jQuery);
