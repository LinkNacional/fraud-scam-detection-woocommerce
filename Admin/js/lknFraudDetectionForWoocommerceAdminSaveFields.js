jQuery(document).on('click', '.admin-layout-submit-wrapper button', function (e) {
    // Primeiro verifica validação HTML5
    var form = jQuery('form#mainform')[0];
    if (form.checkValidity && !form.checkValidity()) {
        var firstInvalidField = form.querySelector(':invalid');
        document.documentElement.style.scrollBehavior = 'smooth';
        if (firstInvalidField) {
            toggleBlockVisibility(firstInvalidField);
            firstInvalidField.scrollIntoView({ block: 'center' });

            setTimeout(function () {
                firstInvalidField.reportValidity();
                document.documentElement.style.scrollBehavior = 'auto';
            }, 300);
        }
        return;
    }

    // Se chegou até aqui, a validação HTML5 passou
    e.preventDefault();

    var settings = {};
    var formData = new FormData();


    // Coleta todos os campos de input, select e textarea que tenham name/id começando com lknFraudDetectionForWoocommerce
    jQuery('form input[name^="lknFraudDetectionForWoocommerce"], form select[name^="lknFraudDetectionForWoocommerce"], form textarea[name^="lknFraudDetectionForWoocommerce"]').each(function () {
        var name = jQuery(this).attr('name');
        var type = jQuery(this).attr('type');
        if (type === 'checkbox') {
            settings[name] = jQuery(this).is(':checked') ? 'yes' : 'no';
        } else if (type === 'radio') {
            if (jQuery(this).is(':checked')) {
                settings[name] = jQuery(this).val();
            }
        } else if (type === 'file') {
            if (this.files && this.files.length > 0) {
                formData.append(name, this.files[0]);
                settings[name] = this.files[0].name;
                var fileName = this.files[0].name;
                var $wrapper = jQuery(this).closest('.admin-gateway-field-input-wrapper, .admin-layout-field-input-wrapper');
                var $fileCurrent = $wrapper.find('.admin-gateway-file-current, .admin-layout-file-current');
                if ($fileCurrent.length) {
                    $fileCurrent.find('strong').text(fileName);
                } else {
                    var $newFileCurrent = jQuery('<div class="admin-gateway-file-current"><span>Last file uploaded: <strong>' + fileName + '</strong></span></div>');
                    jQuery(this).after($newFileCurrent);
                }
            }
        } else {
            settings[name] = jQuery(this).val();
        }
    });


    // Add settings as JSON
    formData.append('settings', JSON.stringify(settings));

    // Use nonce and action from localized settings
    var nonce = lknAntiFraudSettings.settingsNonce;
    var ajaxAction = lknAntiFraudSettings.ajaxAction;
    var ajaxUrl = lknAntiFraudSettings.ajaxUrl || window.ajaxurl;
    if (!nonce || !ajaxAction) {
        alert('Security error: nonce or action not found!');
        return;
    }
    formData.append('_ajax_nonce', nonce);
    formData.append('action', ajaxAction);

    // Salva os dados e arquivos usando o nonce seguro
    jQuery.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false
    })
    .success(function(response) {
        // WordPress retornou success: true
        if (response.success) {
            alert(response.data.message || 'Configurações salvas com sucesso!');
        } else {
            alert('Erro: ' + (response.data.message || 'Ocorreu um erro ao salvar as configurações.'));
        }
    })
    .error(function(xhr) {
        const response = xhr.responseJSON;
        const message = response?.data?.message || 'Ocorreu um erro inesperado.';
        
        console.error('Erro AJAX:', xhr.status, message);
        alert('Erro: ' + message);
    });
});

// Bloqueia submit via Enter
jQuery('form').on('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        return false;
    }
});


jQuery('form').on('change input', function () {
    window.onbeforeunload = null;
    window.removeEventListener('beforeunload', function () { });
    jQuery(window).off('beforeunload');
});

jQuery(document).ready(function ($) {
    $('#pix_expiration_minutes').on('input', function () {
        var val = $(this).val();

        // Se for letra, retorna para 1440
        if (isNaN(val) || /[a-zA-Z]/.test(val)) {
            $(this).val(1440);
            return;
        }

        // Se for menor que 1 ou vazio, retorna para 1
        if (parseInt(val) < 1 || val === '') {
            $(this).val(1);
        }
    });
});

function toggleBlockVisibility(input) {
    // Encontra o bloco que contém o campo vazio
    var $fieldBlock = jQuery(input).closest('.admin-gateway-block');
    var blockId = $fieldBlock.attr('id');

    // Se o bloco não estiver ativo, ativa ele
    if (!$fieldBlock.hasClass('active')) {
        // Remove active de todos os blocos e links
        jQuery('.admin-gateway-block').removeClass('active');
        jQuery('.admin-gateway-title-link').removeClass('active');

        // Ativa o bloco correto
        $fieldBlock.addClass('active');

        // Ativa o link correspondente
        jQuery('.admin-gateway-title-link[data-target="' + blockId + '"]').addClass('active');
    }
}