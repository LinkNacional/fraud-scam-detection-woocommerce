(function ($) {
    $(window).load(function () {
        // Script para exibir texto com url da documentação do google
        const formTable = document.querySelector('.form-table');
        const urlParams = new URLSearchParams(window.location.search);
        if(formTable && urlParams.get('tab') == 'lkn_anti_fraud') {
            const secondRow = formTable.querySelectorAll('tr')[1];
    
            // Cria o elemento <tr>
            const newRow = document.createElement('tr');
            const thElement = document.createElement('th');
            thElement.setAttribute('scope', 'row');
            thElement.className = 'titledesc';
            newRow.appendChild(thElement);
    
            const tdElement = document.createElement('td');
            tdElement.style.paddingBottom = '0';
    
            const aElement = document.createElement('a');
            aElement.href = 'https://www.google.com/recaptcha/admin/';
            aElement.target = '_blank';
            aElement.textContent = lknFraudDetectionVariables.googleRecaptchaText;
            aElement.style.fontSize = '15px';
    
            tdElement.appendChild(aElement);
            newRow.appendChild(tdElement);
            secondRow.insertAdjacentElement('afterend', newRow);
            // Criação do segundo <tr> baseado no valor do GoogleRecaptchaV3Score
            const scoreInput = document.querySelector('#lknFraudDetectionForWoocommerceGoogleRecaptchaV3Score');
            const newScoreRow = document.createElement('tr');
            if (scoreInput) {
                const pElement = document.createElement('p');
                setPElementText(scoreInput.value, pElement);
                pElement.style.fontSize = '15px';
                scoreInput.parentElement.appendChild(pElement);

                // Adiciona evento para atualizar o texto do <p> ao alterar o valor do campo
                scoreInput.addEventListener('input', function () {
                    setPElementText(this.value, pElement);
                });

                function setPElementText(inputValue, pElement) {
                    inputValue = parseFloat(scoreInput.value)
                    if (inputValue <= 0.3) {
                        pElement.textContent = lknFraudDetectionVariables.scoreBetween0and3;
                    } else if (inputValue > 0.3 && inputValue < 0.6) {
                        pElement.textContent = lknFraudDetectionVariables.scoreBetween4and5;
                    } else if (inputValue >= 0.6 && inputValue <= 0.7) {
                        pElement.textContent = lknFraudDetectionVariables.scoreBetween6and7;
                    } else {
                        pElement.textContent = lknFraudDetectionVariables.scoreBetween8and10;
                    }
                }
            }


            // Script para fazer os campos ficarem display none
            enableRecaptchaInput = document.querySelector('#lknFraudDetectionForWoocommerceEnableRecaptcha');
            enableRecaptchaSelectInput = document.querySelector('#lknFraudDetectionForWoocommerceRecaptchaSelected');
            enableGoogleV3KeyInput = document.querySelector('#lknFraudDetectionForWoocommerceGoogleRecaptchaV3Key');
            enableGoogleV3SecretInput = document.querySelector('#lknFraudDetectionForWoocommerceGoogleRecaptchaV3Secret');
            enableGoogleV3ScoreInput = document.querySelector('#lknFraudDetectionForWoocommerceGoogleRecaptchaV3Score');
    
            if(enableRecaptchaInput && enableRecaptchaSelectInput && enableGoogleV3KeyInput && enableGoogleV3SecretInput && enableGoogleV3ScoreInput) {
                enableRecaptchaInputTr = enableRecaptchaInput.closest('tr')
                enableRecaptchaSelectInputTr = enableRecaptchaSelectInput.closest('tr')
                enableGoogleV3KeyInputTr = enableGoogleV3KeyInput.closest('tr')
                enableGoogleV3SecretInputTr = enableGoogleV3SecretInput.closest('tr')
                enableGoogleV3ScoreInputTr = enableGoogleV3ScoreInput.closest('tr')
                
                if(lknFraudDetectionVariables.enableRecaptcha == 'no') {
                    hideRecaptchaFields()
                }
    
                enableRecaptchaSelectInput.addEventListener('change', function() {
                    if(this.value == 'googleRecaptchaV3') {
                        enableGoogleV3KeyInputTr.style.display = 'table-row';
                        enableGoogleV3SecretInputTr.style.display = 'table-row';
                        enableGoogleV3ScoreInputTr.style.display = 'table-row';
                        newRow.style.display = 'table-row';
                        newScoreRow.style.display = 'table-row';
                    } else {
                        enableGoogleV3KeyInputTr.style.display = 'none';
                        enableGoogleV3SecretInputTr.style.display = 'none';
                        enableGoogleV3ScoreInputTr.style.display = 'none';
                        newRow.style.display = 'none';
                        newScoreRow.style.display = 'none';
                    }
                
                })
    
                enableRecaptchaInput.addEventListener('change', function() {
                    if(this.checked) {
                        showRecaptchaFields()
                    } else {
                        hideRecaptchaFields()
                    }
                
                })
    
                function showRecaptchaFields() {
                    enableRecaptchaSelectInputTr.style.display = 'table-row';
                    enableGoogleV3KeyInputTr.style.display = 'table-row';
                    enableGoogleV3SecretInputTr.style.display = 'table-row';
                    enableGoogleV3ScoreInputTr.style.display = 'table-row';
                    newRow.style.display = 'table-row';
                    newScoreRow.style.display = 'table-row';
                }
    
                function hideRecaptchaFields() {
                    enableRecaptchaSelectInputTr.style.display = 'none';
                    enableGoogleV3KeyInputTr.style.display = 'none';
                    enableGoogleV3SecretInputTr.style.display = 'none';
                    enableGoogleV3ScoreInputTr.style.display = 'none';
                    newRow.style.display = 'none';
                    newScoreRow.style.display = 'none';
                }
            }
        }
    })
})(jQuery)