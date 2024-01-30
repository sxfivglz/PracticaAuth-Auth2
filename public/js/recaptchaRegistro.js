document.addEventListener('DOMContentLoaded', function () {
    var formReg = document.getElementById('formReg');
    var nameInput = document.getElementById('nombre');
    var passwordInput = document.getElementById('password');
    var confirmPasswordInput = document.getElementById('confirm_password');
    var emailInput = document.getElementById('email');
    var confirmPasswordError = document.getElementById('confirmPasswordError');
    var emailError = document.getElementById('emailError');
    var nameError = document.getElementById('nombreError');
    var submitButton = document.getElementById('btn');
    var recaptchaKey = document.getElementsByName('recaptcha')[0].value;
  

    if (submitButton) {
        
        nameInput.addEventListener('input', validateName);
        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validateFields);
        emailInput.addEventListener('input', validateFields);

        submitButton.addEventListener('click', function (event) {
            event.preventDefault(); 

            resetErrorMessages();
            validateFields(function (isValid) {
                if (isValid) {
                    validateRecaptcha(recaptchaKey, function (isRecaptchaValid) {
                        if (isRecaptchaValid) {
                            formReg.submit();
                        } else {
                            console.error('La validación de reCAPTCHA falló.');
                        }
                    });
                } else {
                    console.error('La validación de campos falló.');
                }
            });
        });
    } else {
        console.error('Botón de envío no encontrado.');
    }

    function validateFields(callback) {
        validatePassword();

        if (passwordInput.value.trim() !== confirmPasswordInput.value.trim()) {
            showError(confirmPasswordError, 'La contraseña y la confirmación de contraseña no coinciden.');
        } else {
            showError(confirmPasswordError, '');
        }

        if (!isValidEmail(emailInput.value.trim())) {
            showError(emailError, 'El correo electrónico no es válido.');
        } else {
            showError(emailError, '');
        }

        validateName();
        updateSubmitButtonState();

       
        if (typeof callback === 'function') {
            callback(submitButton.disabled === false);
        }
    }

    function validateRecaptcha(recaptchaKey, callback) {
        try {
            if (!recaptchaKey) {
                console.error('Clave de reCAPTCHA no encontrada.');
                if (typeof callback === 'function') {
                    callback(false); // Llama al callback con false si no hay clave de reCAPTCHA
                }
                return;
            }

            grecaptcha.ready(function () {
                grecaptcha.execute(recaptchaKey, { action: 'submit' }).then(function (token) {
                    handleRecaptchaSuccess(token, callback);
                }, function (error) {
                    console.error('Error al obtener el token de reCAPTCHA: ', error);
                    if (typeof callback === 'function') {
                        callback(false);
                    }
                });
            });
        } catch (error) {
            console.error('Error al ejecutar reCAPTCHA: ', error);
            if (typeof callback === 'function') {
                callback(false); 
            }
        }
    }

    function handleRecaptchaSuccess(token, callback) {
        var inputToken = document.createElement('input');
        inputToken.type = 'hidden';
        inputToken.name = 'recaptcha';
        inputToken.value = token;
        formReg.appendChild(inputToken);

        var inputAction = document.createElement('input');
        inputAction.type = 'hidden';
        inputAction.name = 'action';
        inputAction.value = 'submit';
        formReg.appendChild(inputAction);

        var event = new CustomEvent('recaptchaValidated');
        formReg.dispatchEvent(event);


        if (typeof callback === 'function') {
            callback(true);
        }
    }

    function resetErrorMessages() {
        showError(confirmPasswordError, '');
        showError(emailError, '');
        showError(nameError, '');
    }

    function showError(element, message) {
        element.textContent = message;
    }

    function isValidEmail(email) {
        var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return emailRegex.test(email);
    }

    function validateName() {
        var nameValue = nameInput.value.trim();
        var nameRegex = /^[a-zA-ZáéíóúüÜ\s]+$/;
        if (!nameRegex.test(nameValue)) {
            showError(nameError, 'Ingresa un nombre válido.');
        } else {
            showError(nameError, '');
        }
    }

    function validatePassword() {
        if (passwordInput.value.trim().length < 8) {
            showError(confirmPasswordError, 'La contraseña debe tener al menos 8 caracteres.');
        } else {
            showError(confirmPasswordError, '');
        }
    }

    function updateSubmitButtonState() {
        var isPasswordValid = passwordInput.value.trim().length >= 8;
        var isPasswordMatch = passwordInput.value.trim() === confirmPasswordInput.value.trim();
        var isEmailValid = isValidEmail(emailInput.value.trim());
        var isNameValid = nameInput.value.trim().length > 0 && !nameError.textContent;

        submitButton.disabled = !(isPasswordValid && isPasswordMatch && isEmailValid && isNameValid);
    }
});
