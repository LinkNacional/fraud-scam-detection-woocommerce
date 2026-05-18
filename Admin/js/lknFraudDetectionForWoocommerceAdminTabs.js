jQuery(document).ready(function ($) {
    $('.admin-layout-title-link').on('click', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        $('.admin-layout-title-link').removeClass('active');
        $(this).addClass('active');
        $('.admin-layout-block').removeClass('active');
        $('#' + target).addClass('active');
    });
    // Exibe o primeiro bloco por padrão
    $('.admin-layout-title-link.active').trigger('click');
});