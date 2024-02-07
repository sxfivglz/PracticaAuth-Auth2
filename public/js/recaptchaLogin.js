document.addEventListener('DOMContentLoaded', function () {
    var formLog = document.getElementById('formLog');
    var submitButton = document.getElementById('btn');
    var recaptchaKey = document.getElementById('recaptchaKey').value;

    if (submitButton) {
        submitButton.addEventListener('click', function (event) {
            event.preventDefault(); // Evita que el formulario se envíe automáticamente

            // Realiza la validación de reCAPTCHA
            validateRecaptcha(recaptchaKey, function (isRecaptchaValid) {
                if (isRecaptchaValid) {
                    // Si reCAPTCHA es válido, puedes enviar el formulario
                    formLog.submit();
                }
            });
        });
    }

    function validateRecaptcha(recaptchaKey, callback) {
        try {
            if (!recaptchaKey) {
                handleValidationFailure(callback);
                return;
            }

            grecaptcha.ready(function () {
                grecaptcha.execute(recaptchaKey, { action: 'submit' }).then(function (token) {
                    handleRecaptchaSuccess(token, callback);
                }, function (error) {
                    handleValidationFailure(callback);
                });
            });
        } catch (error) {
            handleValidationFailure(callback);
        }
    }

    function handleRecaptchaSuccess(token, callback) {
        var inputToken = document.createElement('input');
        inputToken.type = 'hidden';
        inputToken.name = 'recaptcha';
        inputToken.value = token;
        formLog.appendChild(inputToken);

        var inputAction = document.createElement('input');
        inputAction.type = 'hidden';
        inputAction.name = 'action';
        inputAction.value = 'submit';
        formLog.appendChild(inputAction);

        var event = new CustomEvent('recaptchaValidated');
        formLog.dispatchEvent(event);

        // Llama al callback con true, indicando que reCAPTCHA es válido
        if (typeof callback === 'function') {
            callback(true);
        }
    }

    function handleValidationFailure(callback) {
        
        if (typeof callback === 'function') {
            callback(false);
        }
    }
});
