<?php
global $wpdb;

$contrataciones = $wpdb->get_results("
    SELECT 
        a.id as contract_id, 
        b.group_id, 
        g.name as group_name,
        g.email as group_email,
        a.contractor_name, 
        a.contractor_email,
        b.date as availability_date, 
        b.end_time as availability_end_time,
        z.name_zone
    FROM {$wpdb->prefix}gm_contracts a 
    INNER JOIN {$wpdb->prefix}gm_availabilities b ON a.availability_id = b.id
    LEFT JOIN {$wpdb->prefix}gm_groups g ON b.group_id = g.id
    LEFT JOIN {$wpdb->prefix}gm_zones z on b.id_zone = z.id
");

$contractors = $wpdb->get_results("SELECT ID, display_name FROM {$wpdb->prefix}users WHERE ID IN (SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value LIKE '%gm_contractor%')");

// $musical_groups = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}gm_groups");
$musical_groups = $wpdb->get_results("SELECT DISTINCT a.id, a.name FROM {$wpdb->prefix}gm_groups a INNER JOIN {$wpdb->prefix}gm_availabilities b ON a.id = b.group_id");       



?>
<div class="wrap">
    <h1>Gestionar Contrataciones</h1>

    <div class="content-wrapper">
        <div id="availabilityTableContainer">
            <h2>Contrataciones Existentes</h2>
            <table class="wp-list-table widefat fixed striped" id="myTableAvailable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Grupo Musical</th>
                        <th>Contratante</th>
                        <th>Fecha</th>
                        <th>Zona</th>
                        <th>Hora inicio y fin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contrataciones as $contratacion): ?>
                        <tr>
                            <td><?php echo esc_html($contratacion->contract_id); ?></td>
                            <td><?php echo esc_html($contratacion->group_name); ?> (<?php echo esc_html($contratacion->group_email); ?>)</td> <!-- Mostrar nombre y correo del grupo -->
                            <td><?php echo esc_html($contratacion->contractor_name); ?> (<?php echo esc_html($contratacion->contractor_email); ?>)</td>
                            <td><?php echo esc_html(date('Y-m-d', strtotime($contratacion->availability_date))); ?></td>
                            <td><?php echo esc_html($contratacion->name_zone); ?></td>
                            <td><?php echo esc_html(date('H:i', strtotime($contratacion->availability_date))); ?> - <?php echo esc_html(date('H:i', strtotime($contratacion->availability_end_time))); ?></td>
                            <td>
                                <a href="#" class="delete-contract" data-id="<?php echo esc_attr($contratacion->contract_id); ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario para crear nueva Contratación -->
        <div id="createAvailabilityContainer">
            <h2>Crear Nueva Contratación</h2>
            <form id="create-availability-form">
                <div class="form-group">
                    <label for="create_contractor_id">Contratante</label>
                    <select id="create_contractor_id" required>
                        <?php foreach ($contractors as $contractor): ?>
                            <option value="<?php echo esc_attr($contractor->ID); ?>"><?php echo esc_html($contractor->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="create_group_id">Grupo Musical</label>
                    <select id="create_group_id" required>
                        <?php foreach ($musical_groups as $group): ?>
                            <option value="<?php echo esc_attr($group->id); ?>"><?php echo esc_html($group->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="create_availability_id">Disponibilidades</label>
                    <select id="create_availability_id" required>
                    </select>
                </div>
                
                <button type="button" id="createContract">Crear Contratación</button>
            </form>
        </div>

    </div>
</div>

<!-- Modal para editar Contratación -->
<div id="editAvailabilityModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Contratacion</h2>
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

wp_enqueue_script('gm_availabilities_page_js', plugin_dir_url(__FILE__) . '../assets/js/contrataciones-page.js', array('jquery'), null, true);

wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css');
wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js', array('jquery'), null, true);

$gm_contratacion_nonce = wp_create_nonce('gm_contratacion_action');
?>
<script type="text/javascript">
    var gm_contratacion_nonce = '<?php echo $gm_contratacion_nonce; ?>';
    jQuery(document).ready(function($) {
        $('#myTableAvailable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "order": [[0, 'desc']],

        });
    });

</script>