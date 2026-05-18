jQuery(function($) {
    // Use localized score messages from PHP
    var scoreMessages = window.lknAntiFraudScoreMessages;

    function getScoreMessage(score) {
        var val = parseFloat(score);
        if (isNaN(val)) return '';
        if (val < 0.4) return scoreMessages.scoreBetween0and3;
        if (val < 0.6) return scoreMessages.scoreBetween4and5;
        if (val < 0.8) return scoreMessages.scoreBetween6and7;
        return scoreMessages.scoreBetween8and10;
    }

    // Para cada input de score, vincula evento
    $('input[type="number"][name="lknFraudDetectionForWoocommerceGoogleRecaptchaV3Score"]').each(function() {
        var $input = $(this);
        var $desc = $input.closest('.admin-layout-field-input-wrapper').find('.admin-layout-input-description');
        function updateDesc() {
            var msg = getScoreMessage($input.val());
            $desc.text(msg);
        }
        $input.on('input change', updateDesc);
        updateDesc(); // inicial
    });
});