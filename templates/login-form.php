<form method="POST" action="" id="gm-login-form">
    <div class="gm-avatar">
        <i class="fas fa-user-circle"></i> <!-- Icono de usuario de Font Awesome -->
    </div>
    <?php wp_nonce_field('gm_login_action', 'gm_login_nonce'); ?>
    <h2>Iniciar Sesión</h2>
    <p>
        <label for="username">Nombre de usuario</label>
        <input type="text" name="username" id="username" required>
    </p>
    <p>
        <label for="password">Contraseña</label>
        <input type="password" name="password" id="password" required>
    </p>
    <div class="remember-forgot-container">
        <label>
            <input type="checkbox" name="remember" id="remember">
            Recordarme
        </label>
        <a href="#">¿Olvidaste tu contraseña?</a>
    </div>
    <p>
        <input type="submit" name="gm_login" value="Iniciar Sesión">
    </p>
    <?php if (!empty($gm_errors)): ?>
        <div class="gm-error">
            <?php echo $gm_errors; ?>
        </div>
    <?php endif; ?>
</form>

<script>
document.getElementById('gm-login-form').addEventListener('submit', function(event) {
    var username = document.getElementById('username').value;
    var password = document.getElementById('password').value;
    if (username.trim() === '' || password.trim() === '') {
        event.preventDefault();
        alert('Todos los campos son obligatorios.');
    }
});
</script>

<link rel="stylesheet" type="text/css" href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/css/login-form.css'); ?>">