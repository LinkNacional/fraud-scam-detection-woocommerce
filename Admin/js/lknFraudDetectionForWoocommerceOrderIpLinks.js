import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

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

        var parts = [];

        if (vars.enableIpLookup === 'yes') {
            var lookupLink = validIp
                ? '<a href="#" class="lkn-fsdw-lookup">lookup</a>'
                : '<a href="#" class="lkn-fsdw-lookup" aria-disabled="true" style="opacity:.4;cursor:not-allowed;pointer-events:none;" title="Invalid IP">lookup</a>';
            parts.push(lookupLink);
        }

        if (vars.enableIpFilter === 'yes') {
            parts.push('<a href="#" class="lkn-fsdw-filter">filter</a>');
        }

        if (vars.enableIpBan === 'yes') {
            parts.push('<a href="#" class="lkn-fsdw-ban">ban</a>');
        }

        if (!parts.length) {
            return;
        }

        var $actions = $('<span class="lkn-fsdw-ip-actions"> [ ' + parts.join(' | ') + ' ]</span>');

        $ipSpan.after($actions);

        // ── Lookup ─────────────────────────────────────────────────────────
        $actions.on('click', '.lkn-fsdw-lookup', function (e) {
            e.preventDefault();
            window.open('https://extreme-ip-lookup.com/' + encodeURIComponent(ip), '_blank');
        });

        // ── Filter ─────────────────────────────────────────────────────────
        $actions.on('click', '.lkn-fsdw-filter', function (e) {
            e.preventDefault();
            lknFsdwOpenFilterModal(ip);
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

    // ── Unban via SweetAlert2 ──────────────────────────────────────────────
    function lknFsdwUnbanFromLink(ip, $link) {
        Swal.fire({
            title: i18n.unbanTitle || 'Unban IP',
            html: '<p>' + (i18n.unbanConfirm || 'Do you want to unban the following IP?') + '</p>'
                + '<p><strong style="font-family:monospace;">' + $('<span>').text(ip).html() + '</strong></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: i18n.unbanConfirmBtn || 'Confirm Unban',
            cancelButtonText: i18n.cancel || 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return jQuery.post(vars.ajaxUrl, {
                    action: 'lkn_fsdw_unban_ip',
                    ip: ip,
                    nonce: vars.nonceUnban,
                }).then(function (response) {
                    if (!response.success) {
                        Swal.showValidationMessage(
                            (response.data && response.data.message) || 'Error.'
                        );
                    }
                    return response;
                });
            },
            allowOutsideClick: function () {
                return !Swal.isLoading();
            },
        }).then(function (result) {
            if (result.isConfirmed && result.value && result.value.success) {
                lknFsdwUpdateBanLink($link, false);
                Swal.fire({
                    icon: 'success',
                    title: result.value.data.message || 'IP unbanned.',
                });
            }
        });
    }

    // ── Filter modal (jQuery) — pedidos do mesmo IP ───────────────────────
    function lknFsdwModal(content) {
        lknFsdwRemoveModal();
        var $overlay = $('<div id="lkn-fsdw-modal-overlay"></div>');
        var $box = $('<div class="lkn-fsdw-modal-box">'
            + '<button class="lkn-fsdw-close lkn-fsdw-modal-close">&times;</button>'
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

    function lknFsdwOpenFilterModal(ip) {
        var $modal = lknFsdwModal(
            '<h3 style="margin-top:0;">'
            + (i18n.filterTitle || 'Order Filter by IP') + ': '
            + '<span style="font-family:monospace;">' + $('<span>').text(ip).html() + '</span>'
            + '</h3>'
            + '<div id="lkn-fsdw-filter-controls" class="lkn-fsdw-filter-controls">'
            + '<span>' + (i18n.showLabel || 'Show:') + '</span>'
            + '<button class="button lkn-fsdw-limit" data-limit="5">5</button>'
            + '<button class="button lkn-fsdw-limit" data-limit="10">10</button>'
            + '<button class="button lkn-fsdw-limit" data-limit="25">25</button>'
            + '</div>'
            + '<div id="lkn-fsdw-filter-loading" style="padding:12px 0;">' + (i18n.loading || 'Loading\u2026') + '</div>'
            + '<div id="lkn-fsdw-filter-empty" style="display:none;padding:12px 0;color:#666;">'
            + (i18n.noOrders || 'No orders found for this IP.') + '</div>'
            + '<div class="lkn-fsdw-table-wrap">'
            + '<table id="lkn-fsdw-order-table" style="width:100%;border-collapse:collapse;display:none;">'
            + '<thead><tr style="border-bottom:2px solid #ddd;">'
            + '<th style="text-align:left;padding:7px 6px;">' + (i18n.colOrder || 'Order') + '</th>'
            + '<th style="text-align:left;padding:7px 6px;">' + (i18n.colValue || 'Value') + '</th>'
            + '<th style="padding:7px 6px;"></th>'
            + '</tr></thead>'
            + '<tbody id="lkn-fsdw-order-tbody"></tbody>'
            + '</table>'
            + '</div>'
            + '<div id="lkn-fsdw-filter-pagination">'
            + '<button class="button" id="lkn-fsdw-page-prev">&laquo; ' + (i18n.prev || 'Prev') + '</button>'
            + '<span id="lkn-fsdw-page-info"></span>'
            + '<button class="button" id="lkn-fsdw-page-next">' + (i18n.next || 'Next') + ' &raquo;</button>'
            + '</div>'
        );

        var allOrders  = [];
        var currentPage = 1;
        var perPage     = 10;

        function renderTable() {
            var $tbody = $('#lkn-fsdw-order-tbody');
            var $table = $('#lkn-fsdw-order-table');
            var $empty = $('#lkn-fsdw-filter-empty');
            var $pag   = $('#lkn-fsdw-filter-pagination');

            $tbody.empty();

            if (!allOrders.length) {
                $table.hide(); $pag.hide(); $empty.show();
                return;
            }

            var totalPages = Math.ceil(allOrders.length / perPage);
            if (currentPage > totalPages) { currentPage = totalPages; }
            var start      = (currentPage - 1) * perPage;
            var pageOrders = allOrders.slice(start, start + perPage);

            $.each(pageOrders, function (idx, order) {
                var safeUrl = $('<a>').attr('href', order.url).prop('href');
                $tbody.append(
                    '<tr style="border-bottom:1px solid #f0f0f0;">'
                    + '<td style="padding:7px 6px;">#' + parseInt(order.id, 10) + '</td>'
                    + '<td style="padding:7px 6px;">' + $('<span>').text(order.total).html() + '</td>'
                    + '<td style="padding:7px 6px;text-align:right;">'
                    + '<a href="' + safeUrl + '" class="button button-small" target="_blank">'
                    + (i18n.viewOrder || 'View Order') + '</a>'
                    + '</td>'
                    + '</tr>'
                );
            });

            $('#lkn-fsdw-page-info').text(currentPage + ' / ' + totalPages);
            $('#lkn-fsdw-page-prev').prop('disabled', currentPage <= 1);
            $('#lkn-fsdw-page-next').prop('disabled', currentPage >= totalPages);

            $empty.hide();
            $table.show();
            $pag.css('display', 'flex');

            $('.lkn-fsdw-limit').removeClass('button-primary');
            $('.lkn-fsdw-limit[data-limit="' + perPage + '"]').addClass('button-primary');
        }

        $.post(
            vars.ajaxUrl,
            { action: 'lkn_fsdw_get_orders_by_ip', ip: ip, nonce: vars.nonceFilterOrders },
            function (response) {
                $('#lkn-fsdw-filter-loading').hide();
                if (response.success) {
                    allOrders = response.data.orders || [];
                    renderTable();
                } else {
                    $('#lkn-fsdw-filter-empty').show();
                }
            }
        );

        $modal.on('click', '.lkn-fsdw-limit', function () {
            perPage = parseInt($(this).data('limit'), 10);
            currentPage = 1;
            renderTable();
        });

        $modal.on('click', '#lkn-fsdw-page-prev', function () {
            if (currentPage > 1) { currentPage--; renderTable(); }
        });

        $modal.on('click', '#lkn-fsdw-page-next', function () {
            if (currentPage < Math.ceil(allOrders.length / perPage)) { currentPage++; renderTable(); }
        });
    }

    // ── Ban via SweetAlert2 ────────────────────────────────────────────────
    function lknFsdwOpenBanModal(ip, $banLink) {
        Swal.fire({
            title: i18n.banTitle || 'Ban IP',
            html: '<p>' + (i18n.banConfirm || 'Do you want to ban the following IP from checkout?') + '</p>'
                + '<p><strong style="font-family:monospace;">' + $('<span>').text(ip).html() + '</strong></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#cc1818',
            confirmButtonText: i18n.banConfirmBtn || 'Confirm Ban',
            cancelButtonText: i18n.cancel || 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return jQuery.post(vars.ajaxUrl, {
                    action: 'lkn_fsdw_ban_ip',
                    ip: ip,
                    nonce: vars.nonce,
                }).then(function (response) {
                    if (!response.success) {
                        Swal.showValidationMessage(
                            (response.data && response.data.message) || 'Error.'
                        );
                    }
                    return response;
                });
            },
            allowOutsideClick: function () {
                return !Swal.isLoading();
            },
        }).then(function (result) {
            if (result.isConfirmed && result.value && result.value.success) {
                lknFsdwUpdateBanLink($banLink, true);
                Swal.fire({
                    icon: 'success',
                    title: result.value.data.message || 'IP banned.',
                });
            }
        });
    }

})(jQuery);
