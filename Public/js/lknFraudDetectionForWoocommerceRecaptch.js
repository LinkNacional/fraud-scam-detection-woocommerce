(function ($) {
    var vars        = (typeof lknFsdwFraudScamDetectionVars !== 'undefined') ? lknFsdwFraudScamDetectionVars : {};
    var tokenButton = '';

    // ── Blocks checkout: middleware wp.apiFetch ────────────────────────────
    // `wp-api-fetch` é declarado como dependência no PHP, então wp.apiFetch
    // está garantidamente disponível aqui (nível do módulo).
    // Imune ao ciclo de vida React (ex.: cleanup do gateway Rede).
    if (window.wp && window.wp.apiFetch) {
        window.wp.apiFetch.use(function (options, next) {
            var path = options.path || options.url || '';
            if (path.includes('/wc/store/v1/checkout') &&
                options.data && Array.isArray(options.data.payment_data)) {
                var hasToken = options.data.payment_data.some(function (d) {
                    return d.key === 'grecaptchav3response';
                });
                if (!hasToken) {
                    options.data.payment_data.push({ key: 'grecaptchav3response', value: tokenButton });
                }
            }
            return next(options);
        });
    }

    $(window).load(function () {

        // Exibe texto de termos no rodapé do checkout
        var termsText = vars.termsText || vars.googleTermsText || '';
        if (termsText) {
            var formDesc = document.querySelector('.wc-block-checkout__terms.wc-block-checkout__terms--with-separator.wp-block-woocommerce-checkout-terms-block')
                || document.querySelector('.woocommerce-privacy-policy-text');
            if (formDesc) {
                var spanElement = document.createElement('span');
                spanElement.innerHTML = termsText;
                formDesc.appendChild(spanElement);
            }
        }

        // ── Blocks checkout ────────────────────────────────────────────────
        // O token é injetado pelo middleware wp.apiFetch (acima).
        // Aqui apenas mantemos o token atualizado via executeRecaptcha().
        var placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
        if (placeOrderButton) {
            grecaptcha.ready(function () {
                placeOrderButton.addEventListener('click', function () { executeRecaptcha(); });
                executeRecaptcha();

                function executeRecaptcha() {
                    grecaptcha.execute(vars.googleKey, { action: 'submit' }).then(function (token) {
                        tokenButton = token;
                    });
                }
            });
        }

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
                    var xhr = this;
                    grecaptcha.ready(async function () {
                        var tokenButton = await grecaptcha.execute(vars.googleKey, { action: 'submit' });
                        var newBody = new URLSearchParams(body);
                        newBody.append('grecaptchav3response', tokenButton);
                        newBody.append('lknFraudNonce', vars.nonce);
                        originalXHRSend.call(xhr, newBody.toString());
                    });
                } else {
                    originalXHRSend.apply(this, arguments);
                }
            };
        }
    });
})(jQuery);