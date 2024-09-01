// admin-user-creation-form.js

document.getElementById('gm-admin-user-creation-form').addEventListener('submit', function(event) {
    var name = document.getElementById('name').value.trim();
    var email = document.getElementById('email').value.trim();
    var role = document.getElementById('role').value;

    if (!name || !email || !role) {
        event.preventDefault();
        showError('Todos los campos son obligatorios.');
    }
});

function showError(message) {
    var errorDiv = document.createElement('div');
    errorDiv.className = 'gm-error';
    errorDiv.textContent = message;
    document.querySelector('.form-container').appendChild(errorDiv);

    setTimeout(function() {
        errorDiv.remove();
    }, 4000);
}