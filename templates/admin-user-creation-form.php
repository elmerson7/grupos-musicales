<!-- admin-user-creation-form.php -->
<form method="POST" action="" id="gm-admin-user-creation-form">
    <?php wp_nonce_field('gm_create_user_action', 'gm_create_user_nonce'); ?>
    <div class="form-container">
        <div class="form-header">
            <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
        </div>
        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="role">Rol</label>
            <select name="role" id="role" required>
                <option value="gm_group">Grupo Musical</option>
                <option value="gm_contractor">Contratante</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" name="submit_create_user" value="Crear Usuario">
        </div>
        <?php if (!empty($gm_errors)): ?>
            <div class="gm-error">
                <?php echo $gm_errors; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($gm_success)): ?>
            <div class="gm-success">
                <?php echo $gm_success; ?>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- Importar CSS -->
<link rel="stylesheet" type="text/css" href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/css/admin-user-creation-form.css'); ?>">

<!-- Importar JavaScript -->
<script src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/js/admin-user-creation-form.js'); ?>"></script>