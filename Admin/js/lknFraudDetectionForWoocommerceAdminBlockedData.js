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
                '<button type="button" class="button button-primary lkn-fsdw-bd-add-btn">' + escHtml(i18n.addBtn || 'Add') + '</button>' +
            '</div>' +
            '<table class="widefat striped lkn-fsdw-bd-table" style="margin-top:14px;">' +
                '<thead><tr>' +
                    '<th class="lkn-fsdw-bd-col-value"></th>' +
                    '<th style="width:120px;text-align:center;">' + escHtml(i18n.colActions || 'Actions') + '</th>' +
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
        $tbody.html('<tr><td colspan="2" style="text-align:center;">' + escHtml(i18n.loading || 'Loading…') + '</td></tr>');

        $.post(vars.ajaxUrl, { action: 'lkn_fsdw_get_blocked_data', nonce: vars.nonceGet, type: type }, function (response) {
            $tbody.empty();
            if (!response.success) {
                $tbody.html('<tr><td colspan="2">' + escHtml(i18n.errorLoad || 'Failed to load.') + '</td></tr>');
                return;
            }
            var items = response.data.items || [];
            if (items.length === 0) {
                $tbody.html('<tr><td colspan="2" style="text-align:center;">' + escHtml(i18n.empty || 'No items.') + '</td></tr>');
                return;
            }
            items.forEach(function (item) {
                $tbody.append(buildRow(type, item));
            });
        }).fail(function () {
            $tbody.html('<tr><td colspan="2">' + escHtml(i18n.errorLoad || 'Failed to load.') + '</td></tr>');
        });
    }

    function buildRow(type, value) {
        return $('<tr></tr>').append(
            $('<td></td>').text(value),
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

    // ── Add item ──────────────────────────────────────────────────────────
    $container.on('click', '.lkn-fsdw-bd-add-btn', function () {
        var type   = $container.find('.lkn-fsdw-bd-select').val();
        var $input = $container.find('.lkn-fsdw-bd-input');
        var value  = $.trim($input.val());

        if (!value) {
            $input.focus();
            return;
        }

        var $btn = $(this).prop('disabled', true);
        $.post(vars.ajaxUrl, { action: 'lkn_fsdw_add_blocked_data', nonce: vars.nonceAdd, type: type, value: value }, function (response) {
            $btn.prop('disabled', false);
            if (!response.success) {
                alert(response.data && response.data.message ? response.data.message : (i18n.errorAdd || 'Error adding item.'));
                return;
            }
            $input.val('');
            loadList();
        }).fail(function () {
            $btn.prop('disabled', false);
            alert(i18n.errorAdd || 'Error adding item.');
        });
    });

    // ── Enter key on input ────────────────────────────────────────────────
    $container.on('keydown', '.lkn-fsdw-bd-input', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $container.find('.lkn-fsdw-bd-add-btn').trigger('click');
        }
    });

    // ── Remove item ───────────────────────────────────────────────────────
    $container.on('click', '.lkn-fsdw-bd-remove-btn', function () {
        var type  = $(this).data('type');
        var value = $(this).data('value');
        var $row  = $(this).closest('tr');

        $row.css('opacity', '0.5');
        $.post(vars.ajaxUrl, { action: 'lkn_fsdw_remove_blocked_data', nonce: vars.nonceRemove, type: type, value: value }, function (response) {
            if (!response.success) {
                $row.css('opacity', '1');
                alert(i18n.errorRemove || 'Error removing item.');
                return;
            }
            $row.remove();
            if ($container.find('.lkn-fsdw-bd-list tr').length === 0) {
                $container.find('.lkn-fsdw-bd-list').html('<tr><td colspan="2" style="text-align:center;">' + escHtml(i18n.empty || 'No items.') + '</td></tr>');
            }
        }).fail(function () {
            $row.css('opacity', '1');
            alert(i18n.errorRemove || 'Error removing item.');
        });
    });

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

