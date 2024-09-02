<?php
global $wpdb;

// Obtener todas las disponibilidades
$availabilities = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gm_availabilities");

// Obtener todos los grupos musicales
$musical_groups = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}gm_groups");

?>
<div class="wrap">
    <h1>Gestionar Disponibilidades</h1>

    <div class="content-wrapper">
        <!-- Tabla de disponibilidades -->
        <div id="availabilityTableContainer">
            <h2>Disponibilidades Existentes</h2>
            <table class="wp-list-table widefat fixed striped" id="myTableAvailable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Grupo Musical</th>
                        <th>Fecha de Inicio</th>
                        <th>Hora de Fin</th>
                        <th>Todo el Día</th>
                        <th>Contratado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availabilities as $availability): ?>
                        <tr>
                            <td><?php echo esc_html($availability->id); ?></td>
                            <td><?php echo esc_html($availability->group_id); ?></td>
                            <td><?php echo esc_html($availability->date); ?></td>
                            <td><?php echo esc_html($availability->end_time); ?></td>
                            <td><?php echo esc_html($availability->all_day ? 'Sí' : 'No'); ?></td>
                            <td><?php echo esc_html($availability->contracted ? 'Sí' : 'No'); ?></td>
                            <td>
                                <a href="#" class="edit-availability" data-id="<?php echo esc_attr($availability->id); ?>">Editar</a> |
                                <a href="#" class="delete-availability" data-id="<?php echo esc_attr($availability->id); ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario para crear nueva disponibilidad -->
        <div id="createAvailabilityContainer">
            <h2>Crear Nueva Disponibilidad</h2>
            <form id="create-availability-form">
                <div class="form-group">
                    <label for="create_group_id">Grupo Musical</label>
                    <select id="create_group_id" required>
                        <?php foreach ($musical_groups as $group): ?>
                            <option value="<?php echo esc_attr($group->id); ?>"><?php echo esc_html($group->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="create_date">Fecha de Inicio</label>
                    <input type="datetime-local" id="create_date" required>
                </div>
                <div class="form-group">
                    <label for="create_end_time">Hora de Fin</label>
                    <input type="datetime-local" id="create_end_time" required>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="create_all_day">
                    <label for="create_all_day">Todo el día</label>
                </div>
                <button type="button" id="createAvailability">Crear Disponibilidad</button>
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

wp_enqueue_script('gm_availabilities_page_js', plugin_dir_url(__FILE__) . '../assets/js/availabilities-page.js', array('jquery'), null, true);

wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css');
wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js', array('jquery'), null, true);

$gm_availability_nonce = wp_create_nonce('gm_availability_action');
?>
<script type="text/javascript">
    var gm_availability_nonce = '<?php echo $gm_availability_nonce; ?>';
    jQuery(document).ready(function($) {
        $('#myTableAvailable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.1/i18n/Spanish.json"
            }
        });
    });

</script>