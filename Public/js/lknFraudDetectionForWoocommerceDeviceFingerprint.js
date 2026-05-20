(function () {
    'use strict';

    /**
     * Generate a simple, stable browser/device fingerprint.
     * Combines user agent, language, screen properties, timezone and hardware info.
     *
     * @returns {string} Hex hash string.
     */
    function lknFsdwFingerprint() {
        var parts = [
            navigator.userAgent        || '',
            navigator.language         || '',
            String(screen.colorDepth   || ''),
            String(screen.width        || '') + 'x' + String(screen.height || ''),
            String(new Date().getTimezoneOffset()),
            String(navigator.hardwareConcurrency || ''),
            navigator.platform         || '',
        ];
        var raw  = parts.join('|');
        var hash = 0;
        for (var i = 0; i < raw.length; i++) {
            hash = Math.imul(31, hash) + raw.charCodeAt(i) | 0;
        }
        return Math.abs(hash).toString(16);
    }

    /**
     * Set a cookie for reading server-side during order processing.
     * Expires in 1 hour — enough for a checkout session.
     *
     * @param {string} name
     * @param {string} value
     */
    function lknFsdwSetCookie(name, value) {
        var expires = new Date(Date.now() + 3600 * 1000).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value) +
            '; expires=' + expires + '; path=/; SameSite=Strict';
    }

    var fingerprint = lknFsdwFingerprint();
    lknFsdwSetCookie('lkn_fsdw_did', fingerprint);
}());
