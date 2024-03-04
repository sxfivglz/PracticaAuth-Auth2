// Captura y maneja los errores de JavaScript
window.addEventListener('error', function (event) {
    // Muestra el mensaje de error en una alerta
    alert('Hubo un error al validar el reCaptcha, recarga la página o verifica tu conexión a internet');
});

document.addEventListener('DOMContentLoaded', function () {
    var formLog = document.getElementById('formLog');
    var submitButton = document.getElementById('btn');
    var recaptchaKey = document.getElementById('recaptchaKey').value;
    if (submitButton) {
        submitButton.addEventListener('click', function (event) {
            event.preventDefault(); // Evita que el formulario se envíe automáticamente

            // Realiza la validación de reCAPTCHA
            try {
                validateRecaptcha(recaptchaKey, function (isRecaptchaValid) {
                    if (isRecaptchaValid) {
                        // Si reCAPTCHA es válido, puedes enviar el formulario
                        formLog.submit();
                    }
                });
            } catch (error) {
                handleValidationFailure(function () {
                    alert('Hubo un error al validar el reCaptcha, recarga la página o verifica tu conexión a internet');
                });
            }
        });
    }

    function validateRecaptcha(recaptchaKey, callback) {
        if (!recaptchaKey) {
            throw new Error("Clave de reCAPTCHA no válida");
        }

        grecaptcha.ready(function () {
            try {
                grecaptcha.execute(recaptchaKey, { action: 'submit' }).then(function (token) {
                    handleRecaptchaSuccess(token, callback);
                }).catch(function (error) {
                    handleValidationFailure(callback, error.message);
                });
            } catch (error) {
                handleValidationFailure(callback, error.message);
            }
        });
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
