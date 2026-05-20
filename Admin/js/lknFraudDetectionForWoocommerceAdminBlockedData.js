import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

jQuery(document).ready(function ($) {
    'use strict';

    var vars = (typeof lknFsdwBlockedDataVars !== 'undefined') ? lknFsdwBlockedDataVars : {};
    var i18n = vars.i18n || {};
    var $container = $('#block-block-by-data');

    if (!$container.length) {
        return;
    }

    var typeOptions = [
        { key: 'email',           label: i18n.tabEmail          || 'Emails',            placeholder: i18n.placeholderEmail          || 'user@example.com' },
        { key: 'email_domain',    label: i18n.tabEmailDomain    || 'Email Domains',      placeholder: i18n.placeholderEmailDomain    || 'example.com' },
        { key: 'phone',           label: i18n.tabPhone          || 'Phones',             placeholder: i18n.placeholderPhone          || '+5511999999999' },
        { key: 'country',         label: i18n.tabCountry        || 'Countries',          placeholder: i18n.placeholderCountry        || 'BR' },
        { key: 'device_identity', label: i18n.tabDeviceIdentity || 'Device Identities',  placeholder: i18n.placeholderDeviceIdentity || 'Fingerprint hash' },
    ];

    // ── Build UI ──────────────────────────────────────────────────────────
    var selectOptions = typeOptions.map(function (t) {
        return '<option value="' + escAttr(t.key) + '">' + escHtml(t.label) + '</option>';
    }).join('');

    var $wrapper = $(
        '<div class="lkn-fsdw-blocked-data-wrapper">' +
            '<div class="lkn-fsdw-bd-controls">' +
                '<select class="lkn-fsdw-bd-select">' + selectOptions + '</select>' +
                '<input type="text" class="lkn-fsdw-bd-input" />' +
                '<button type="button" class="button button-primary lkn-fsdw-bd-add-btn">' + escHtml(i18n.addBtn || 'Ban') + '</button>' +
            '</div>' +
            '<table class="widefat striped lkn-fsdw-bd-table" style="margin-top:14px;">' +
                '<thead><tr>' +
                    '<th class="lkn-fsdw-bd-col-value"></th>' +
                    '<th style="width:130px;">' + escHtml(i18n.colBannedBy || 'Banned By') + '</th>' +
                    '<th style="width:145px;">' + escHtml(i18n.colBannedAt || 'Banned At') + '</th>' +
                    '<th style="width:145px;">' + escHtml(i18n.colExpiresAt || 'Expires At') + '</th>' +
                    '<th style="width:100px;text-align:center;">' + escHtml(i18n.colActions || 'Actions') + '</th>' +
                '</tr></thead>' +
                '<tbody class="lkn-fsdw-bd-list"></tbody>' +
            '</table>' +
        '</div>'
    );

    $container.append($wrapper);

    // ── Sync placeholder & column header with selected type ───────────────
    function syncType() {
        var key = $container.find('.lkn-fsdw-bd-select').val();
        var opt = typeOptions.find(function (t) { return t.key === key; });
        if (opt) {
            $container.find('.lkn-fsdw-bd-input').attr('placeholder', opt.placeholder);
            $container.find('.lkn-fsdw-bd-col-value').text(opt.label);
        }
    }

    // ── Load list for the current type ────────────────────────────────────
    function loadList() {
        var type   = $container.find('.lkn-fsdw-bd-select').val();
        var $tbody = $container.find('.lkn-fsdw-bd-list');
        $tbody.html('<tr><td colspan="5" style="text-align:center;">' + escHtml(i18n.loading || 'Loading…') + '</td></tr>');

        $.post(vars.ajaxUrl, { action: 'lkn_fsdw_get_blocked_data', nonce: vars.nonceGet, type: type }, function (response) {
            $tbody.empty();
            if (!response.success) {
                $tbody.html('<tr><td colspan="5">' + escHtml(i18n.errorLoad || 'Failed to load.') + '</td></tr>');
                return;
            }
            var items = response.data.items || [];
            if (items.length === 0) {
                $tbody.html('<tr><td colspan="5" style="text-align:center;">' + escHtml(i18n.empty || 'No items.') + '</td></tr>');
                return;
            }
            items.forEach(function (item) {
                $tbody.append(buildRow(type, item));
            });
        }).fail(function () {
            $tbody.html('<tr><td colspan="5">' + escHtml(i18n.errorLoad || 'Failed to load.') + '</td></tr>');
        });
    }

    function buildRow(type, item) {
        var value     = typeof item === 'object' ? (item.value      || '') : item;
        var bannedBy  = typeof item === 'object' ? (item.banned_by  || '—') : '—';
        var bannedAt  = typeof item === 'object' ? (item.banned_at  || '—') : '—';
        var expiresAt = typeof item === 'object' ? (item.expires_at || i18n.forever || 'Forever') : (i18n.forever || 'Forever');
        return $('<tr></tr>').append(
            $('<td></td>').text(value),
            $('<td></td>').text(bannedBy),
            $('<td></td>').text(bannedAt),
            $('<td></td>').text(expiresAt),
            $('<td style="text-align:center;"></td>').append(
                $('<button type="button" class="button button-small lkn-fsdw-bd-remove-btn"></button>')
                    .text(i18n.removeBtn || 'Remove')
                    .data('type', type)
                    .data('value', value)
            )
        );
    }

    // ── Type change ───────────────────────────────────────────────────────
    $container.on('change', '.lkn-fsdw-bd-select', function () {
        syncType();
        loadList();
    });

    // ── Add item → modal confirmation ─────────────────────────────────────
    $container.on('click', '.lkn-fsdw-bd-add-btn', function () {
        var type   = $container.find('.lkn-fsdw-bd-select').val();
        var $input = $container.find('.lkn-fsdw-bd-input');
        var value  = $.trim($input.val());

        if (!value) {
            $input.focus();
            return;
        }

        openAddModal(type, value);
    });

    function openAddModal(type, value) {
        Swal.fire({
            title: i18n.addTitle || 'Ban Item',
            html: '<p>' + escHtml(i18n.addConfirmMsg || 'Do you want to add the following item to the blocked list?') + '</p>' +
                  '<p><strong>' + escHtml(value) + '</strong></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: i18n.addConfirmBtn || 'Confirm Ban',
            cancelButtonText:  i18n.cancel || 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return $.post(vars.ajaxUrl, {
                    action: 'lkn_fsdw_add_blocked_data',
                    nonce:  vars.nonceAdd,
                    type:   type,
                    value:  value,
                }).then(function (response) {
                    if (!response.success) {
                        Swal.showValidationMessage(
                            (response.data && response.data.message) || (i18n.errorAdd || 'Error.')
                        );
                    }
                    return response;
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); },
        }).then(function (result) {
            if (result.isConfirmed && result.value && result.value.success) {
                $container.find('.lkn-fsdw-bd-input').val('');
                loadList();
                Swal.fire({
                    icon: 'success',
                    title: (result.value.data && result.value.data.message) || (i18n.successAdd || 'Item added.'),
                });
            }
        });
    }

    // ── Enter key on input ────────────────────────────────────────────────
    $container.on('keydown', '.lkn-fsdw-bd-input', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $container.find('.lkn-fsdw-bd-add-btn').trigger('click');
        }
    });

    // ── Remove item → modal confirmation ──────────────────────────────────
    $container.on('click', '.lkn-fsdw-bd-remove-btn', function () {
        var type  = $(this).data('type');
        var value = $(this).data('value');
        openRemoveModal(type, value);
    });

    function openRemoveModal(type, value) {
        Swal.fire({
            title: i18n.removeTitle || 'Unban Item',
            html: '<p>' + escHtml(i18n.removeConfirmMsg || 'Do you want to unban the following item?') + '</p>' +
                  '<p><strong>' + escHtml(value) + '</strong></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: i18n.removeConfirmBtn || 'Confirm Unban',
            cancelButtonText:  i18n.cancel || 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return $.post(vars.ajaxUrl, {
                    action: 'lkn_fsdw_remove_blocked_data',
                    nonce:  vars.nonceRemove,
                    type:   type,
                    value:  value,
                }).then(function (response) {
                    if (!response.success) {
                        Swal.showValidationMessage(
                            (response.data && response.data.message) || (i18n.errorRemove || 'Error.')
                        );
                    }
                    return response;
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); },
        }).then(function (result) {
            if (result.isConfirmed && result.value && result.value.success) {
                $container.find('tr').filter(function () {
                    return $(this).find('.lkn-fsdw-bd-remove-btn').data('value') === value;
                }).fadeOut(300, function () {
                    $(this).remove();
                    if ($container.find('.lkn-fsdw-bd-list tr:visible').length === 0) {
                        $container.find('.lkn-fsdw-bd-list').html('<tr><td colspan="5" style="text-align:center;">' + escHtml(i18n.empty || 'No items.') + '</td></tr>');
                    }
                });
                Swal.fire({
                    icon: 'success',
                    title: (result.value.data && result.value.data.message) || (i18n.successRemove || 'Item removed.'),
                });
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    function escAttr(str) { return escHtml(str); }

    // ── Init ──────────────────────────────────────────────────────────────
    syncType();
    loadList();
});

