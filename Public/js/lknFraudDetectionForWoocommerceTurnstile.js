(function ($) {
    var vars     = (typeof lknFsdwFraudScamDetectionVars !== 'undefined') ? lknFsdwFraudScamDetectionVars : {};
    var cfToken  = '';
    var widgetId = null;

    // ── Blocks checkout: middleware wp.apiFetch ────────────────────────────
    // `wp-api-fetch` é declarado como dependência deste script no PHP,
    // portanto wp.apiFetch está garantidamente disponível aqui (nível do módulo).
    // O middleware persiste durante toda a sessão — imune ao ciclo de vida de
    // componentes React (ex.: cleanup do gateway Rede que restaura window.fetch).
    if (window.wp && window.wp.apiFetch) {
        window.wp.apiFetch.use(function (options, next) {
            var path = options.path || options.url || '';
            if (path.includes('/wc/store/v1/checkout') &&
                options.data && Array.isArray(options.data.payment_data)) {
                var hasToken = options.data.payment_data.some(function (d) {
                    return d.key === 'lkncfturnstileresponse';
                });
                if (!hasToken) {
                    console.log('[LKN Turnstile] apiFetch intercepted, cfToken length:', cfToken.length);
                    options.data.payment_data.push({ key: 'lkncfturnstileresponse', value: cfToken });
                    if (widgetId !== null) { turnstile.reset(widgetId); }
                }
            }
            return next(options);
        });
        console.log('[LKN Turnstile] apiFetch middleware registered');
    } else {
        console.log('[LKN Turnstile] wp.apiFetch not available — middleware not registered');
    }

    $(window).load(function () {
        // Exibe texto de termos no rodapé do checkout
        var termsText = vars.termsText || '';
        if (termsText) {
            var formDesc = document.querySelector('.wc-block-checkout__terms.wc-block-checkout__terms--with-separator.wp-block-woocommerce-checkout-terms-block')
                || document.querySelector('.woocommerce-privacy-policy-text');
            if (formDesc) {
                var spanElement = document.createElement('span');
                spanElement.innerHTML = termsText;
                formDesc.appendChild(spanElement);
            }
        }

        if (typeof turnstile === 'undefined') {
            console.log('[LKN Turnstile] turnstile not defined — script not loaded yet');
            return;
        }

        // Container fixo no canto inferior direito, igual ao badge do Google reCAPTCHA
        var container = document.createElement('div');
        container.id  = 'lkn-cf-turnstile';
        container.style.cssText = 'position:fixed;bottom:14px;right:25px;z-index:9999;';
        document.body.appendChild(container);
        console.log('[LKN Turnstile] rendering widget, sitekey:', vars.cfSiteKey);

        widgetId = turnstile.render('#lkn-cf-turnstile', {
            sitekey:            vars.cfSiteKey,
            appearance:         'always',
            size:               'normal',
            callback:           function (token) { cfToken = token; console.log('[LKN Turnstile] token received, length:', token.length); },
            'expired-callback': function () { cfToken = ''; console.log('[LKN Turnstile] token expired'); turnstile.reset(widgetId); },
            'error-callback':   function (code) { cfToken = ''; console.log('[LKN Turnstile] error-callback code:', code); }
        });
        console.log('[LKN Turnstile] widgetId:', widgetId);

        // ── Classic checkout (XHR) ─────────────────────────────────────────
        var legacyForm = document.querySelector('.checkout.woocommerce-checkout');
        if (legacyForm) {
            var originalXHROpen = XMLHttpRequest.prototype.open;
            var originalXHRSend = XMLHttpRequest.prototype.send;

            XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
                this._requestURL = url;
                originalXHROpen.apply(this, arguments);
            };

            XMLHttpRequest.prototype.send = function (body) {
                if (this._requestURL && this._requestURL.includes('?wc-ajax=checkout')) {
                    console.log('[LKN Turnstile] XHR intercepted, cfToken length:', cfToken.length);
                    var newBody = new URLSearchParams(body);
                    newBody.append('lknCfTurnstileResponse', cfToken);
                    newBody.append('lknFraudNonce', vars.nonce);
                    if (widgetId !== null) { turnstile.reset(widgetId); }
                    originalXHRSend.call(this, newBody.toString());
                } else {
                    originalXHRSend.apply(this, arguments);
                }
            };
        }
    });
})(jQuery);
