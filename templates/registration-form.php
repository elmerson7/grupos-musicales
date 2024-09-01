<form method="POST" action="" id="gm-registration-form">
    <?php wp_nonce_field('gm_register_action', 'gm_register_nonce'); ?>
    <div class="form-container">
        <div class="form-header">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="form-group">
            <label for="username">Nombre de usuario</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <label for="role">Rol</label>
            <select name="role" id="role" required>
                <option value="gm_group">Grupo Musical</option>
                <option value="gm_contractor">Contratante</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" name="gm_register" value="Registrarse">
        </div>
        <?php if (!empty($gm_errors)): ?>
            <div class="gm-error">
                <?php echo $gm_errors; ?>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- Modal de error -->
<div id="error-modal" class="error-modal">
    <div class="error-message" id="error-message"></div>
</div>

<link rel="stylesheet" type="text/css" href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/css/registration-form.css'); ?>">
<script src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/js/registration-form.js'); ?>"></script>