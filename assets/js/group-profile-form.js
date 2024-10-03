document.getElementById('gm-group-profile-form').addEventListener('submit', function(event) {
    var phone = document.getElementById('phone').value.trim();
    var email = document.getElementById('email').value.trim();
    var fileInput = document.getElementById('photo');
    var zona = document.getElementById('zona');
    var duracion = document.getElementById('duracion');
    var file = fileInput.files[0];
    var fileType = file ? file.type.split('/')[0] : '';

    var errors = [];

    if (!phone) {
        errors.push('El teléfono es obligatorio.');
    }

    if (!email) {
        errors.push('El email es obligatorio.');
    } else {
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            errors.push('El formato del email es incorrecto.');
        }
    }

    if (fileType !== 'image') {
        errors.push('La fotografía debe ser una imagen válida.');
    }

    if (errors.length > 0) {
        event.preventDefault();
        showError(errors.join('<br>'));
    }

    if (zona === "") {
        errors.push('Seleccione Zona Geográfica.');
    }
    if (duracion === "") {
        errors.push('Seleccione Duración del Show.');
    }
});

function showError(message) {
    var errorDiv = document.createElement('div');
    errorDiv.className = 'gm-error';
    errorDiv.innerHTML = message;
    document.querySelector('.form-container').appendChild(errorDiv);

    setTimeout(function() {
        errorDiv.remove();
    }, 4000); // Ocultar después de 4 segundos
}