<?php
global $wpdb;

$contractors = $wpdb->get_results("
    SELECT u.ID, u.user_login, u.user_email
    FROM {$wpdb->users} u
    INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE um.meta_key = '{$wpdb->prefix}capabilities'
    AND um.meta_value LIKE '%gm_contractor%' AND u.user_status = 0");

?>
<div class="wrap">
    <h1>Gestionar Contratantes</h1>

    <div class="content-wrapper">
        <!-- Tabla de disponibilidades -->
        <div id="availabilityTableContainer">
            <h2>Contratantes Existentes</h2>
            <table class="wp-list-table widefat fixed striped" id="myTableAvailable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contractors as $contractor): ?>
                        <tr>
                            <td><?php echo esc_html($contractor->ID); ?></td>
                            <td><?php echo esc_html($contractor->user_login); ?></td>
                            <td><?php echo esc_html($contractor->user_email); ?></td>
                            <td>
                                <a href="#" class="delete-contractor" data-id="<?php echo esc_attr($contractor->ID); ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="createAvailabilityContainer">
            <h2>Crear Nuevo Cnotratante</h2>
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
                        <button type="button" id="createGroup">Crear contratante</button>
                    </div>
                    <?php if (!empty($gm_errors)): ?>
                        <div class="gm-error">
                            <?php echo $gm_errors; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar disponibilidad -->
<div id="editAvailabilityModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Disponibilidad</h2>
        <form id="edit-availability-form">
            <input type="hidden" id="edit_availability_id">
            <div class="form-group">
                <label for="edit_date">Fecha de Inicio</label>
                <input type="datetime-local" id="edit_date" required>
            </div>
            <div class="form-group">
                <label for="edit_end_time">Hora de Fin</label>
                <input type="datetime-local" id="edit_end_time" required>
            </div>
            <div class="form-group">
                <input type="checkbox" id="edit_all_day">
                <label for="edit_all_day">Todo el día</label>
            </div>
            <button type="button" id="saveAvailabilityChanges">Guardar Cambios</button>
        </form>
    </div>
</div>

<?php
// Incluir los recursos CSS y JS específicos
wp_enqueue_style('gm_availabilities_page_css', plugin_dir_url(__FILE__) . '../assets/css/availabilities-page.css');

wp_enqueue_script('gm_availabilities_page_js', plugin_dir_url(__FILE__) . '../assets/js/contractors-page.js', array('jquery'), null, true);

wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css');
wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js', array('jquery'), null, true);

$gm_availability_nonce = wp_create_nonce('gm_availability_action');
?>
<script type="text/javascript">
    var gm_group_nonce = '<?php echo $gm_availability_nonce; ?>';
    jQuery(document).ready(function($) {
        $('#myTableAvailable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "order": [[0, 'desc']],
        });
    });

</script>