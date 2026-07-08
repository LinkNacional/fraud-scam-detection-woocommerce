import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

(function ($) {
    'use strict';

    var $container = $('#lkn-fsdw-data-ban-container');
    if (!$container.length) { return; }

    var ajaxUrl = (typeof lknFsdwOrderDataBanVars !== 'undefined' && lknFsdwOrderDataBanVars.ajaxUrl)
        ? lknFsdwOrderDataBanVars.ajaxUrl
        : (window.ajaxurl || '/wp-admin/admin-ajax.php');

    // ── Read all config from server-injected container ─────────────────
    var c = $container[0].dataset;

    var i18n = {
        ban:               c.i18nBan              || 'ban',
        unban:             c.i18nUnban            || 'unban',
        banTitle:          c.i18nBanTitleEmail    || 'Ban Email',
        banConfirm:        c.i18nBanConfirmEmail  || 'Ban this email?',
        banSuccess:        c.i18nBanSuccessEmail  || 'Email banned.',
        unbanTitle:        c.i18nUnbanTitleEmail  || 'Unban Email',
        unbanConfirm:      c.i18nUnbanConfirmEmail|| 'Unban this email?',
        unbanSuccess:      c.i18nUnbanSuccessEmail|| 'Email unbanned.',
        banTitlePhone:     c.i18nBanTitlePhone    || 'Ban Phone',
        banConfirmPhone:   c.i18nBanConfirmPhone  || 'Ban this phone?',
        banSuccessPhone:   c.i18nBanSuccessPhone  || 'Phone banned.',
        unbanTitlePhone:   c.i18nUnbanTitlePhone  || 'Unban Phone',
        unbanConfirmPhone: c.i18nUnbanConfirmPhone|| 'Unban this phone?',
        unbanSuccessPhone: c.i18nUnbanSuccessPhone|| 'Phone unbanned.',
        cancel:            c.i18nCancel           || 'Cancel',
        banConfirmBtn:     c.i18nBanConfirmBtn    || 'Confirm Ban',
        unbanConfirmBtn:   c.i18nUnbanConfirmBtn  || 'Confirm Unban',
    };

    var nonceGet    = c.nonceGet    || '';
    var nonceAdd    = c.nonceAdd    || '';
    var nonceRemove = c.nonceRemove || '';

    // ── Phone helpers ───────────────────────────────────────────────────
    function normalizePhone(raw) {
        var cleaned = raw.replace(/[^\d+]/g, '');
        if (cleaned.indexOf('+') > 0) {
            cleaned = '+' + cleaned.replace(/\+/g, '');
        }
        return cleaned;
    }

    function isValidPhone(raw) {
        var digits = raw.replace(/[^\d]/g, '');
        return raw.indexOf('+') === 0 && digits.length >= 10;
    }

    // ── Shared state ────────────────────────────────────────────────────
    function updateLink($link, isBanned) {
        if (isBanned) {
            $link.text(i18n.unban).data('banned', true).css('color', '#cc1818');
        } else {
            $link.text(i18n.ban).data('banned', false).css('color', '');
        }
    }

    // ── Modals (generic) ────────────────────────────────────────────────
    function openBanModal(type, value, $link) {
        var tk = type === 'phone' ? 'Phone' : '';
        Swal.fire({
            title: i18n['banTitle' + tk] + ': ' + value,
            html: '<p>' + i18n['banConfirm' + tk] + '</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#cc1818',
            confirmButtonText: i18n.banConfirmBtn,
            cancelButtonText: i18n.cancel,
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return $.post(ajaxUrl, {
                    action: 'lkn_fsdw_add_blocked_data',
                    type: type,
                    value: type === 'phone' ? normalizePhone(value) : value,
                    nonce: nonceAdd,
                }).then(function (r) {
                    if (!r.success) { Swal.showValidationMessage((r.data && r.data.message) || 'Error.'); }
                    return r;
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); },
        }).then(function (r) {
            if (r.isConfirmed && r.value && r.value.success) {
                updateLink($link, true);
                Swal.fire({ icon: 'success', title: i18n['banSuccess' + tk] });
            }
        });
    }

    function openUnbanModal(type, value, $link) {
        var tk = type === 'phone' ? 'Phone' : '';
        Swal.fire({
            title: i18n['unbanTitle' + tk] + ': ' + value,
            html: '<p>' + i18n['unbanConfirm' + tk] + '</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: i18n.unbanConfirmBtn,
            cancelButtonText: i18n.cancel,
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return $.post(ajaxUrl, {
                    action: 'lkn_fsdw_remove_blocked_data',
                    type: type,
                    value: type === 'phone' ? normalizePhone(value) : value,
                    nonce: nonceRemove,
                }).then(function (r) {
                    if (!r.success) { Swal.showValidationMessage((r.data && r.data.message) || 'Error.'); }
                    return r;
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); },
        }).then(function (r) {
            if (r.isConfirmed && r.value && r.value.success) {
                updateLink($link, false);
                Swal.fire({ icon: 'success', title: i18n['unbanSuccess' + tk] });
            }
        });
    }

    function checkBanned(type, value, $link) {
        if (!nonceGet) { return; }
        $.post(ajaxUrl, { action: 'lkn_fsdw_get_blocked_data', nonce: nonceGet, type: type }, function (r) {
            if (r.success) {
                var items = r.data.items || [];
                var banned = items.some(function (item) {
                    var v = typeof item === 'object' ? (item.value || '') : item;
                    return v.toLowerCase() === value.toLowerCase();
                });
                updateLink($link, banned);
            }
        });
    }

    function attach(type, value, $target) {
        var cls = 'lkn-fsdw-ban-' + type;
        var $wrap = $('<span class="lkn-fsdw-data-actions"> [ <a href="#" class="' + cls + '">' + i18n.ban + '</a> ]</span>');
        $target.after($wrap);

        $wrap.on('click', '.' + cls, function (e) {
            e.preventDefault();
            var $link = $(this);
            if ($link.data('banned')) { openUnbanModal(type, value, $link); }
            else                     { openBanModal(type, value, $link); }
        });

        checkBanned(type, value, $wrap.find('.' + cls));
    }

    // ═══════════════════════════════════════════════════════════════════
    // INIT
    // ═══════════════════════════════════════════════════════════════════
    $(function () {
        // ── Email ──────────────────────────────────────────────────────
        var email = c.email || '';
        if (email) {
            var $mailto = $('.order_data_column a[href^="mailto:"]').first();
            if ($mailto.length) { attach('email', email, $mailto); }
        }

        // ── Phone ──────────────────────────────────────────────────────
        var rawPhone = c.phone || '';
        if (rawPhone && isValidPhone(rawPhone)) {
            var phone = normalizePhone(rawPhone);
            var $tel = $('a[href^="tel:"]').first();
            if ($tel.length) {
                attach('phone', phone, $tel);
            }
        }
    });

})(jQuery);
