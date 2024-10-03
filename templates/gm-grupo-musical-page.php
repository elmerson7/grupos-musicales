<?php
global $wpdb;

// $musical_groups = $wpdb->get_results("
//     SELECT a.*, b.name_zone 
//     FROM {$wpdb->prefix}gm_groups a 
//     LEFT JOIN {$wpdb->prefix}gm_zones b ON a.id_zone = b.id
// ");
$musical_groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gm_groups");

$zones = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gm_zones WHERE status = 1");
// echo "<pre>";
// print_r($musical_groups);
// echo "<pre>";
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="wrap">
    <h1>Gestionar Grupos Musicales</h1>

    <div class="content-wrapper">
        <!-- Tabla de disponibilidades -->
        <div id="groupsTableContainer">
            <h2>Grupos Musicales Existentes</h2>
            <table class="wp-list-table widefat fixed striped" id="myTableAvailable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Zona(s)</th>
                        <th>Duración Show</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($musical_groups as $group): ?>
                        <?php
                            $list_ids = $group->id_zone;
                            $name_zones = $wpdb->get_results("SELECT name_zone FROM {$wpdb->prefix}gm_zones WHERE id IN ($list_ids)");
                            $arr_zones = [];
                            foreach ($name_zones as $zone) {
                                $arr_zones[] = $zone->name_zone;
                            }
                            $list_zones = implode(' ,',$arr_zones);                            
                        ?>
                        <tr>
                            <td><?php echo esc_html($group->id); ?></td>
                            <td><?php echo esc_html($group->name); ?></td>
                            <td><?php echo esc_html($group->description); ?></td>
                            <td><?php echo esc_html($group->email); ?></td>
                            <td><?php echo esc_html($group->phone); ?></td>
                            <td><?php echo esc_html($list_zones); ?></td> <!-- Cambiado para mostrar el nombre de la zona -->
                            <td><?php echo esc_html($group->duration). " minutos"; ?></td>
                            <td>
                                <a href="#" class="edit-group" data-id="<?php echo esc_attr($group->id); ?>">Editar</a> |
                                <a href="#" class="delete-group" data-id="<?php echo esc_attr($group->id); ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="createAvailabilityContainer">
            <h2>Crear Nuevo Grupo Musical</h2>
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
                        <label for="name">Nombre artístico</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea name="description" id="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label id="lblRegion" for="zone">Zona geográfica</label>
                        <select name="zone[]" id="zone" multiple="multiple" required>
                        <option value="" disabled>--Selecciona Zona--</option>
                        <?php foreach ($zones as $zone): ?>
                            <option value="<?=$zone->id?>"><?=$zone->name_zone?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="duracion" class="form-label">Duración del Show</label>
                        <select name="duracion" id="duracion" class="form-select" required>
                            <option value="" selected disabled>--Selecciona Duracion--</option>
                            <option value="45">45min</option>
                            <option value="60">1h</option>
                            <option value="75">1h 15min</option>
                            <option value="90">1h 30min</option>
                            <option value="105">1h 45min</option>
                            <option value="120">2h</option>
                            <option value="135">2h 15min</option>
                            <option value="150">2h 30min</option>
                            <option value="165">2h 45min</option>
                            <option value="180">3h</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone">Teléfono</label>
                        <input type="text" name="phone" id="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="photo">Fotografía</label>
                        <input type="file" name="photo" id="photo" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email de contacto</label>
                        <input type="email" name="email_contact" id="email_contact" required>
                    </div>
                    <div class="form-group">
                        <button type="button" id="createGroup">Crear grupo</button>
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

<!-- Modal para editar grupo musical -->
<div id="editGroupModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Grupo Musical</h2>
        <form id="edit-group-form">
            <input type="hidden" id="edit_group_id">
            <div class="form-group">
                <label for="edit_name">Nombre artístico</label>
                <input type="text" id="edit_name" required>
            </div>
            <div class="form-group">
                <label for="edit_description">Descripción</label>
                <textarea id="edit_description" required></textarea>
            </div>

            <div class="form-group">
                <label for="edit_zone">Zona geográfica</label>
                <select id="edit_zone" name="edit_zone[]" multiple="multiple" required>
                    <!-- <option value="" disabled>--Selecciona Zona--</option> -->
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?= esc_attr($zone->id) ?>"><?= esc_html($zone->name_zone) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="edit_duration">Duración</label>
                <select name="edit_duration" id="edit_duration" class="form-select" required>
                    <option value="" selected disabled>--Selecciona Duracion--</option>
                    <option value="45">45min</option>
                    <option value="60">1h</option>
                    <option value="75">1h 15min</option>
                    <option value="90">1h 30min</option>
                    <option value="105">1h 45min</option>
                    <option value="120">2h</option>
                    <option value="135">2h 15min</option>
                    <option value="150">2h 30min</option>
                    <option value="165">2h 45min</option>
                    <option value="180">3h</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit_phone">Teléfono</label>
                <input type="text" id="edit_phone" required>
            </div>
            <div class="form-group">
                <label for="edit_photo">Fotografía</label>
                <input type="file" id="edit_photo" accept="image/*">
                <img id="current_photo" src="" alt="Fotografía actual" style="max-width: 100px; display: block; margin-top: 10px;">
            </div>
            <div class="form-group">
                <label for="edit_email_contact">Email de contacto</label>
                <input type="email" id="edit_email_contact" required>
            </div>
            <button type="button" id="saveGroupChanges">Guardar Cambios</button>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?php
// Incluir los recursos CSS y JS específicos
wp_enqueue_style('gm_availabilities_page_css', plugin_dir_url(__FILE__) . '../assets/css/groups-page.css');
wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css');

wp_enqueue_script('gm_availabilities_page_js', plugin_dir_url(__FILE__) . '../assets/js/groups-page.js', array('jquery'), null, true);
wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js', array('jquery'), null, true);

$gm_group_nonce = wp_create_nonce('gm_group_action');
?>
<script type="text/javascript">
    var gm_group_nonce = '<?php echo $gm_group_nonce; ?>';
    jQuery(document).ready(function($) {
        $('#myTableAvailable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "order": [[0, 'desc']],
        });

        $('#zone').select2({
            placeholder: "--Seleccione Zona--",
        });

        $('#editGroupModal').on('shown.bs.modal', function () {
            $('#edit_zone').select2({
                dropdownParent: $('#editGroupModal'),
                placeholder: "--Seleccione Zona--",
            });
        });

        $('#editGroupModal').on('hidden.bs.modal', function () {
        // Resetear el formulario
        $('#editGroupModal')[0].reset();
    });

    });
</script>