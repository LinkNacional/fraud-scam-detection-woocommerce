(function ($) {
    $(window).load(function () {
        const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');

        if (placeOrderButton) {
            grecaptcha.ready(() => {
                var tokenButton = '';
                placeOrderButton.addEventListener('click', (e) => {
                    executeRecaptcha()
                });
                executeRecaptcha()
                function executeRecaptcha() {
                    grecaptcha.execute(lknFraudDetectionVariables.googleKey, { action: 'submit' }).then((token) => {
                        tokenButton = token;
                    });
                }
                // Intercepta o fetch para /wc/store/v1/checkout
                const originalFetch = window.fetch;
                
                window.fetch = async (input, init) => {
                    if (typeof input === 'string' && input.includes('/wc/store/v1/checkout')) {
                        // Clona o payload existente
                        const body = JSON.parse(init.body);
                        
                        // Adiciona o token do reCAPTCHA
                        body['payment_data'].push({
                            'key': 'gRecaptchaV3Response',
                            'value': tokenButton
                        })

                        // Recria o init com o payload modificado
                        init.body = JSON.stringify(body);
                    }
                    return originalFetch(input, init);
                };
            });
        }

        formDesc = document.querySelector('.wc-block-checkout__terms.wc-block-checkout__terms--with-separator.wp-block-woocommerce-checkout-terms-block')
        if(!formDesc){
            formDesc = document.querySelector('.woocommerce-privacy-policy-text')
        }
        if(formDesc){
            const spanElement = document.createElement('span');
            spanElement.innerHTML = lknFraudDetectionVariables.googleTermsText;
            formDesc.appendChild(spanElement);
        }

        legacyForm = document.querySelector('.checkout.woocommerce-checkout')
        if(legacyForm){
            let originalXHROpen = XMLHttpRequest.prototype.open;
            let originalXHRSend = XMLHttpRequest.prototype.send;
          
            XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
              this._requestURL = url; // Armazena a URL da requisição
              originalXHROpen.apply(this, arguments);
            };
          
            XMLHttpRequest.prototype.send = function (body) {
              if (this._requestURL && this._requestURL.includes('?wc-ajax=checkout')) {
                let xhr = this; // Armazena referência ao objeto XMLHttpRequest
          
                grecaptcha.ready(async () => {
                  let tokenButton = await grecaptcha.execute(lknFraudDetectionVariables.googleKey, { action: 'submit' });
          
                  // Adiciona o token reCAPTCHA ao corpo da requisição
                  let newBody = new URLSearchParams(body);
                  newBody.append('grecaptchav3response', tokenButton);
                  body = newBody.toString();
          
                  originalXHRSend.call(xhr, body);
                });
              } else {
                originalXHRSend.apply(this, arguments);
              }
            };
        }

    })
})(jQuery)