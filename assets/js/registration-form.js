document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('gm-registration-form').addEventListener('submit', function (event) {
        var username = document.getElementById('username').value.trim();
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;

        // Validaciones
        if (!emailPattern.test(email)) {
            showError('Por favor, introduce un correo electrónico válido.');
            event.preventDefault();
            return;
        }

        if (!passwordPattern.test(password)) {
            showError('La contraseña debe tener al menos 8 caracteres, incluyendo una letra mayúscula, una letra minúscula y un número.');
            event.preventDefault();
            return;
        }

        if (username.length === 0) {
            showError('El nombre de usuario no puede estar vacío.');
            event.preventDefault();
            return;
        }
    });

    function showError(message) {
        var errorModal = document.getElementById('error-modal');
        var errorMessage = document.getElementById('error-message');
        errorMessage.textContent = message;
        errorModal.classList.add('visible');

        setTimeout(function () {
            errorModal.classList.remove('visible');
        }, 4000); // Duración de la visibilidad del mensaje de error
    }
});