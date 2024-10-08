<?php
function gm_register_menus() {
    // Menú principal para "Disponibilidades"
    add_menu_page(
        'Disponibilidades',  // Título de la página
        'Disponibilidades',  // Título del menú
        'manage_options',    // Capacidad requerida
        'gm_availabilities', // Slug de la página
        'gm_availabilities_page', // Función de contenido
        'dashicons-calendar-alt', // Icono del menú
        6                     // Posición del menú
    );

    // Menú principal para "Contrataciones"
    add_menu_page(
        'Contrataciones',  // Título de la página
        'Contrataciones',  // Título del menú
        'manage_options',    // Capacidad requerida
        'gm_contrataciones', // Slug de la página
        'gm_contrataciones_page', // Función de contenido
        'dashicons-clipboard', // Icono del menú
        7                     // Posición del menú
    );

    // Menú principal para "Grupo Musical"
    add_menu_page(
        'Grupo Musical',          // Título de la página
        'Grupo Musical',          // Título del menú
        'manage_options',         // Capacidad requerida
        'gm_grupo_musical',       // Slug de la página
        'gm_grupo_musical_page',  // Función de contenido
        'dashicons-groups',       // Icono del menú
        8                         // Posición del menú
    );

    // Menú principal para "Contratante"
    add_menu_page(
        'Contratante',            // Título de la página
        'Contratante',            // Título del menú
        'manage_options',         // Capacidad requerida
        'gm_contratante',         // Slug de la página
        'gm_contratante_page',    // Función de contenido
        'dashicons-businessman',  // Icono del menú
        9                         // Posición del menú
    );
}
add_action('admin_menu', 'gm_register_menus');

// Función de contenido para "Disponibilidades"
function gm_availabilities_page() {
    include plugin_dir_path(__FILE__) . '../templates/gm-availabilities-page.php';
}

// Función de contenido para "Contrataciones"
function gm_contrataciones_page() {
    include plugin_dir_path(__FILE__) . '../templates/gm-contrataciones-page.php';
}

// Función de contenido para "Grupo Musical"
function gm_grupo_musical_page() {
    include plugin_dir_path(__FILE__) . '../templates/gm-grupo-musical-page.php';
}

// Función de contenido para "Contratante"
function gm_contratante_page() {
    include plugin_dir_path(__FILE__) . '../templates/gm-contratante-page.php';
}

/* DISPONIILIDADES */
// Endpoint para crear una nueva disponibilidad
function gm_availabilities_page_create_availability() {
    check_ajax_referer('gm_availability_action', '_wpnonce');

    if (isset($_POST['group_id']) && isset($_POST['date']) && isset($_POST['end_time'])) {
        global $wpdb;

        $group_id = intval($_POST['group_id']);
        $date = sanitize_text_field($_POST['date']);
        $end_time = sanitize_text_field($_POST['end_time']);
        $all_day = isset($_POST['all_day']) ? 1 : 0;

        $inserted = $wpdb->insert(
            "{$wpdb->prefix}gm_availabilities",
            [
                'group_id' => $group_id,
                'date' => $date,
                'end_time' => $end_time,
                'all_day' => $all_day,
                'contracted' => 0
            ]
        );

        if ($inserted) {
            wp_send_json_success('Disponibilidad creada correctamente.');
        } else {
            wp_send_json_error('Error al crear la disponibilidad.');
        }
    } else {
        wp_send_json_error('Datos de disponibilidad no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_availabilities_page_create_availability', 'gm_availabilities_page_create_availability');

// Endpoint para obtener los datos de una disponibilidad en la página de disponibilidades
function gm_availabilities_page_get_availability() {
    check_ajax_referer('gm_availability_action', '_wpnonce');

    if (isset($_POST['availability_id'])) {
        global $wpdb;
        $availability_id = intval($_POST['availability_id']);
        $availability = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_availabilities WHERE id = %d", $availability_id));
        if ($availability) {
            wp_send_json_success($availability);
        } else {
            wp_send_json_error('Disponibilidad no encontrada.');
        }
    }
    wp_send_json_error('ID de disponibilidad no especificado.');
}
add_action('wp_ajax_gm_availabilities_page_get_availability', 'gm_availabilities_page_get_availability');

// Endpoint para actualizar una disponibilidad en la página de disponibilidades
function gm_availabilities_page_update_availability() {
    check_ajax_referer('gm_availability_action', '_wpnonce');

    if (isset($_POST['availability_id'])) {
        global $wpdb;

        $availability_id = intval($_POST['availability_id']);
        $id_zone = intval($_POST['id_zone']);
        $date = sanitize_text_field($_POST['date']);
        $end_time = sanitize_text_field($_POST['end_time']);
        $all_day = isset($_POST['all_day']) ? 1 : 0;

        $updated = $wpdb->update(
            "{$wpdb->prefix}gm_availabilities",
            [
                'date' => $date,
                'end_time' => $end_time,
                'all_day' => $all_day,
                'id_zone' => $id_zone,
            ],
            ['id' => $availability_id]
        );

        if ($updated !== false) {
            wp_send_json_success('Disponibilidad actualizada correctamente.');
        } else {
            wp_send_json_error('Error al actualizar la disponibilidad.');
        }
    }
    wp_send_json_error('Datos de disponibilidad no recibidos correctamente.');
}
add_action('wp_ajax_gm_availabilities_page_update_availability', 'gm_availabilities_page_update_availability');

// Función para eliminar una disponibilidad en la página de disponibilidades
function gm_availabilities_page_delete_availability() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gm_availability_action')) {
        wp_send_json_error('Nonce verification failed');
        return;
    }

    if (isset($_POST['availability_id'])) {
        global $wpdb;

        $availability_id = intval($_POST['availability_id']);
        $availability = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_availabilities WHERE id = %d", $availability_id));

        if ($availability && $availability->contracted == 0) {
            $result = $wpdb->delete("{$wpdb->prefix}gm_availabilities", ['id' => $availability_id]);

            if ($result) {
                wp_send_json_success('Disponibilidad eliminada exitosamente');
            } else {
                wp_send_json_error('Error al eliminar disponibilidad: ' . $wpdb->last_error);
            }
        } else {
            wp_send_json_error('No puedes eliminar una disponibilidad contratada.');
        }
    } else {
        wp_send_json_error('Datos no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_availabilities_page_delete_availability', 'gm_availabilities_page_delete_availability');

/* CONTRATACIONES */
// Endpoint para obtener disponibilidades no contratadas de un grupo musical
function gm_get_availabilities_by_group() {
    check_ajax_referer('gm_contratacion_action', '_wpnonce');

    if (isset($_POST['group_id'])) {
        global $wpdb;
        $group_id = intval($_POST['group_id']);
        
        $availabilities = $wpdb->get_results($wpdb->prepare("SELECT a.*,b.name_zone FROM {$wpdb->prefix}gm_availabilities a LEFT JOIN {$wpdb->prefix}gm_zones b ON a.id_zone = b.id WHERE group_id = %d AND contracted = 0", $group_id));

        wp_send_json_success($availabilities);
    } else {
        wp_send_json_error('ID de grupo no recibido.');
    }
}
add_action('wp_ajax_gm_get_availabilities_by_group', 'gm_get_availabilities_by_group');

// Endpoint para crear una nueva contratación
function gm_create_contract() {
    check_ajax_referer('gm_contratacion_action', '_wpnonce');

    if (isset($_POST['contractor_id']) && isset($_POST['group_id']) && isset($_POST['availability_id'])) {
        global $wpdb;

        $contractor_id = intval($_POST['contractor_id']);
        $group_id = intval($_POST['group_id']);
        $availability_id = intval($_POST['availability_id']);

        // Obtener los datos del contratante desde la tabla `users`
        $contractor = get_userdata($contractor_id);
        if (!$contractor) {
            wp_send_json_error('Contratante no encontrado.');
            return;
        }

        $contractor_name = $contractor->display_name;
        $contractor_email = $contractor->user_email;
        $contractor_phone = get_user_meta($contractor_id, 'phone', true); // Asegúrate de que el número de teléfono está guardado en meta como 'phone'

        // Obtener la fecha de la tabla `qYDj7_gm_availabilities`
        $availability = $wpdb->get_row($wpdb->prepare("SELECT date FROM {$wpdb->prefix}gm_availabilities WHERE id = %d", $availability_id));
        if (!$availability) {
            wp_send_json_error('Disponibilidad no encontrada.');
            return;
        }

        $date = $availability->date;

        // Preparar los datos para insertar en `gm_contracts`
        $data = [
            'group_id' => $group_id,
            'availability_id' => $availability_id,
            'contractor_name' => sanitize_text_field($contractor_name),
            'contractor_email' => sanitize_email($contractor_email),
            'contractor_phone' => sanitize_text_field($contractor_phone),
            'date' => sanitize_text_field($date),
        ];

        // Insertar la contratación
        $result = gm_add_contract($data);

        if ($result) {
            wp_send_json_success('Contract created successfully.');
        } else {
            wp_send_json_error('Error al crear el contrato.');
        }
    } else {
        wp_send_json_error('Datos de contratación no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_create_contract', 'gm_create_contract');

/* GRUPOS */
// Endpoint para crear un nuevo grupo
function gm_groups_page_create_group() {
    check_ajax_referer('gm_group_action', '_wpnonce');

    if (
        isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['name'], $_POST['description'], $_POST['zone'],  $_POST['duration'],$_POST['phone'], $_POST['email_contact']) && 
        !empty($_FILES['photo'])
    ) {
        global $wpdb;

        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $zone = sanitize_text_field($_POST['zone']);
        $duration = intval($_POST['duration']);
        $phone = sanitize_text_field($_POST['phone']);
        $email_contact = sanitize_email($_POST['email_contact']);
        $role = 'gm_group';

        // Verificar si el correo electrónico ya existe
        if (email_exists($email)) {
            wp_send_json_error('Lo siento, ¡esa dirección de correo electrónico ya está en uso!');
            return;
        }

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            wp_send_json_error('Error al crear el usuario: ' . $user_id->get_error_message());
            return;
        }

        $user = new WP_User($user_id);
        $user->set_role($role);

        // Manejar la carga de la fotografía
        $upload = wp_handle_upload($_FILES['photo'], array('test_form' => false));
        if (!$upload || isset($upload['error'])) {
            wp_send_json_error('Error al cargar la fotografía: ' . $upload['error']);
            return;
        }

        // Insertar grupo musical en la tabla personalizada
        $result = $wpdb->insert(
            "{$wpdb->prefix}gm_groups",
            array(
                'user_id' => $user_id,
                'name' => $name,
                'description' => $description,
                'id_zone' => $zone,
                'duration' => $duration,
                'phone' => $phone,
                'email' => $email_contact,
                'photo' => $upload['url']
            )
        );

        if ($result === false) {
            wp_send_json_error('Error al crear el grupo musical.');
        } else {
            wp_send_json_success('Grupo creado exitosamente.');
        }
    } else {
        wp_send_json_error('Faltan datos necesarios para crear el grupo.');
    }
}
add_action('wp_ajax_gm_groups_page_create_group', 'gm_groups_page_create_group');

// Endpoint para obtener los datos de un grupo en la página de grupos
function gm_groups_page_get_group() {
    check_ajax_referer('gm_group_action', '_wpnonce');

    if (isset($_POST['group_id'])) {
        global $wpdb;
        $group_id = intval($_POST['group_id']);
        $group = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_groups WHERE id = %d", $group_id));
        if ($group) {
            wp_send_json_success($group);
        } else {
            wp_send_json_error('Grupo no encontrada.');
        }
    }
    wp_send_json_error('ID de grupo no especificado.');
}
add_action('wp_ajax_gm_groups_page_get_group', 'gm_groups_page_get_group');

// Endpoint para actualizar una disponibilidad en la página de disponibilidades
function gm_groups_page_update_group() {
    check_ajax_referer('gm_group_action', '_wpnonce');

    if (isset($_POST['group_id'])) {
        global $wpdb;

        // Obtener y sanitizar los datos del formulario
        $group_id = intval($_POST['group_id']);
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $zone = sanitize_text_field($_POST['zone']);
        $duration = intval($_POST['duration']);
        $phone = sanitize_text_field($_POST['phone']);
        $email_contact = sanitize_email($_POST['email_contact']);

        // Manejar la carga de la fotografía si se ha proporcionado una nueva
        $photo_url = '';
        if (!empty($_FILES['photo']['name'])) {
            $upload = wp_handle_upload($_FILES['photo'], array('test_form' => false));
            if (!$upload || isset($upload['error'])) {
                wp_send_json_error('Error al cargar la fotografía: ' . $upload['error']);
                return;
            }
            $photo_url = $upload['url'];
        }

        // Preparar los datos para la actualización
        $data = [
            'name' => $name,
            'description' => $description,
            'id_zone' => $zone,
            'duration' => $duration,
            'phone' => $phone,
            'email' => $email_contact,
        ];

        // Si hay una nueva fotografía, incluirla en los datos de actualización
        if ($photo_url) {
            $data['photo'] = $photo_url;
        }

        // Realizar la actualización en la base de datos
        $updated = $wpdb->update(
            "{$wpdb->prefix}gm_groups",
            $data,
            ['id' => $group_id]
        );

        if ($updated !== false) {
            wp_send_json_success('Grupo actualizado correctamente.');
        } else {
            wp_send_json_error('Error al actualizar el grupo: ' . $wpdb->last_error);
        }
    } else {
        wp_send_json_error('Datos del grupo no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_groups_page_update_group', 'gm_groups_page_update_group');

// Función para eliminar un grupo
function gm_groups_page_delete_group() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gm_group_action')) {
        wp_send_json_error('Nonce verification failed');
        return;
    }

    if (isset($_POST['groupId'])) {
        global $wpdb;

        $group_id = intval($_POST['groupId']);

        $group = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_groups WHERE id = %d", $group_id));

        if ($group) {
            $result = $wpdb->delete("{$wpdb->prefix}gm_groups", ['id' => $group_id]);

            if ($result) {
                wp_delete_user($group->user_id);

                wp_send_json_success('Grupo eliminado exitosamente');
            } else {
                wp_send_json_error('Error al eliminar el grupo: ' . $wpdb->last_error);
            }
        } else {
            wp_send_json_error('El grupo no existe.');
        }
    } else {
        wp_send_json_error('Datos no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_groups_page_delete_group', 'gm_groups_page_delete_group');

// Función para eliminar un contrato
function gm_groups_page_delete_contract() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gm_contratacion_action')) {
        wp_send_json_error('Nonce verification failed');
        return;
    }

    if (isset($_POST['contractId'])) {
        global $wpdb;

        $contract_id = intval($_POST['contractId']);

        $contract = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_contracts WHERE id = %d", $contract_id));
        
        if ($contract) {
            $result = $wpdb->delete("{$wpdb->prefix}gm_contracts", ['id' => $contract_id]);
            // $result = false;
            if ($result) {
                wp_send_json_success('Contrato eliminado exitosamente');
            }else{
                wp_send_json_error('Error al eliminar Contrato.');
            }
        } else {
            wp_send_json_error('El Contrato no existe.');
        }
    } else {
        wp_send_json_error('Datos no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_groups_page_delete_contract', 'gm_groups_page_delete_contract');

// Función para eliminar un Contratista
function gm_groups_page_delete_contractor() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gm_availability_action')) {
        wp_send_json_error('Nonce verification failed');
        return;
    }

    if (isset($_POST['contractorId'])) {
        global $wpdb;

        $contractor_id = intval($_POST['contractorId']);
        
        $contractor = $wpdb->get_row($wpdb->prepare("SELECT u.ID, u.user_login, u.user_email FROM {$wpdb->users} u INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND um.meta_value LIKE '%gm_contractor%' AND id = %d", $contractor_id));

        if ($contractor) {
            $updated = $wpdb->update(
                $wpdb->users,
                array('user_status' => 1), // Valor de 'user_status', en este caso 1
                array('ID' => $contractor->ID),
                array('%d'),
                array('%d')
            );

            if ($updated) {
                wp_send_json_success('El estado del contratista se ha actualizado correctamente.');
            } else {
                wp_send_json_error('Error al actualizar el estado del Contratista.');
            }
        }else{
            wp_send_json_error('Contratista no encontrado.');
        }
    } else {
        wp_send_json_error('Datos no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_groups_page_delete_contractor', 'gm_groups_page_delete_contractor');

function gm_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tables = [
        "CREATE TABLE {$wpdb->prefix}gm_groups (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            photo varchar(255) DEFAULT '' NOT NULL,
            name varchar(100) NOT NULL,
            description text NOT NULL,
            id_zone varchar(255) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            duration int(11) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}gm_availabilities (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            group_id mediumint(9) NOT NULL,
            date datetime NOT NULL,
            end_time datetime NOT NULL,
            all_day boolean NOT NULL,
            contracted boolean NOT NULL DEFAULT 0,
            id_zone int(11) NOT NULL,
            PRIMARY KEY  (id),
            FOREIGN KEY (group_id) REFERENCES {$wpdb->prefix}gm_groups(id) ON DELETE CASCADE
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}gm_contracts (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            group_id mediumint(9) NOT NULL,
            availability_id mediumint(9) NOT NULL,
            contractor_name varchar(100) NOT NULL,
            contractor_email varchar(100) NOT NULL,
            contractor_phone varchar(20) NOT NULL,
            date datetime NOT NULL,
            PRIMARY KEY  (id),
            FOREIGN KEY (group_id) REFERENCES {$wpdb->prefix}gm_groups(id) ON DELETE CASCADE,
            FOREIGN KEY (availability_id) REFERENCES {$wpdb->prefix}gm_availabilities(id) ON DELETE CASCADE
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}gm_zones (
            id int(11) NOT NULL AUTO_INCREMENT,
            name_zone varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            description_zone text NOT NULL,
            status int(11) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;",

    ];

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $table) {
        dbDelta($table);
    }
}

register_activation_hook(__FILE__, 'gm_create_tables');

function gm_add_group($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gm_groups';
    $result = $wpdb->insert($table_name, $data);

    if ($result === false) {
        error_log('Error en la inserción de grupo musical: ' . $wpdb->last_error);
    } else {
        error_log('Inserción exitosa en la tabla gm_groups.');
    }

    return $result;
}

function gm_get_availability_form() {
    if (!is_user_logged_in() || !current_user_can('gm_group')) {
        wp_send_json_error('No tienes permiso para acceder a esta página.');
    }

    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/availability-form.php'; // Asegúrate de que la ruta sea correcta
    $form = ob_get_clean();
    wp_send_json_success($form);
}
add_action('wp_ajax_gm_get_availability_form', 'gm_get_availability_form');


function gm_handle_availability_ajax() {
    check_ajax_referer('gm_availability_action', 'gm_availability_nonce');
    // wp_send_json_success($_POST);
    global $wpdb;
    $date = sanitize_text_field($_POST['date']);
    $start_time = sanitize_text_field($_POST['start_time']);
    $end_time = sanitize_text_field($_POST['end_time']);
    $all_day = isset($_POST['all_day']) ? 1 : 0;
    $user_id = get_current_user_id();
    $event = isset($_POST['event']) ? sanitize_text_field($_POST['event']) : 0;
    $endDate = sanitize_text_field($_POST['endDate']) ? sanitize_text_field($_POST['endDate']) : null;
    $zone = isset($_POST['zone']) ? $_POST['zone'] : 0;
    $group_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}gm_groups WHERE user_id = %d", $user_id));

    if (!$group_id) {
        wp_send_json_error('No se encontró un grupo asociado al usuario actual.');
        return;
    }

    if ($event != 0) {
        $startDateTime = new DateTime($date . ' ' . $start_time);
        $endDateTime = new DateTime($date . ' ' . $end_time);
        $finalEndDate = new DateTime($endDate);

        $interval = null;

        switch ($event) {
            case 'diario':
                $interval = new DateInterval('P1D'); // 1 día
                break;
            case 'semanal':
                $interval = new DateInterval('P7D'); // 7 días
                break;
            case 'mensual':
                $interval = new DateInterval('P1M'); // 1 mes
                break;
            default:
                $interval = null;
                break;
        }

        $currentDateTime = clone $startDateTime;
        $selectedDay = $startDateTime->format('d');

        while ($currentDateTime <= $finalEndDate) {
            $month = $currentDateTime->format('m');
            $year = $currentDateTime->format('Y');

            if ($event == 'mensual') {
                // Verificar si el mes tiene el día seleccionado
                $lastDayOfMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                if ($selectedDay > $lastDayOfMonth) {
                    // Si el mes no tiene el día seleccionado, avanzar al siguiente mes
                    $currentDateTime->add($interval);
                    continue;
                }
                // Verificar si la fecha calculada excede el rango de endDate
                $calculatedDate = new DateTime($year . '-' . $month . '-' . $selectedDay);
                if ($calculatedDate > $finalEndDate) {
                    break;
                }
            }else {
                // Para los eventos diario y semanal, usamos la fecha actual
                $calculatedDate = clone $currentDateTime;
            }

            // Insertar el registro solo si la fecha es válida
            $data = [
                'group_id' => $group_id,
                'date' => $calculatedDate->format('Y-m-d H:i:s'),
                'end_time' => $calculatedDate->format('Y-m-d') . ' ' . $end_time,
                'all_day' => $all_day,
                'id_zone' => $zone
            ];

            $result = $wpdb->insert("{$wpdb->prefix}gm_availabilities", $data);

            if ($result === false) {
                wp_send_json_error("Error en el registro: No se pudo insertar la disponibilidad en la base de datos.");
                return;
            }

            // Avanzar al próximo Intervalo(dia,semana,mes)
            $currentDateTime->add($interval);
        }
    } else {
        $data = [
            'group_id' => $group_id,
            'date' => $date . ' ' . $start_time,
            'end_time' => $date . ' ' . $end_time,
            'all_day' => $all_day,
            'id_zone' => $zone
        ];
        $result = $wpdb->insert("{$wpdb->prefix}gm_availabilities", $data);
        if ($result === false) {
            wp_send_json_error("Error en el registro: No se pudo insertar la disponibilidad en la base de datos.");
        } else {
            wp_send_json_success("Inserción exitosa en la tabla gm_availabilities.");
        }
    }
    wp_send_json_success("Inserción exitosa en la tabla gm_availabilities.");
}

add_action('wp_ajax_gm_handle_availability_ajax', 'gm_handle_availability_ajax');
add_action('wp_ajax_nopriv_gm_handle_availability_ajax', 'gm_handle_availability_ajax');

function gm_add_contract($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gm_contracts';
    $result = $wpdb->insert($table_name, $data);

    if ($result === false) {
        error_log('Error en la inserción de contrato: ' . $wpdb->last_error);
    } else {
        error_log('Inserción exitosa en la tabla gm_contracts.');
    }

    // Marcar la disponibilidad como contratada
    $wpdb->update(
        "{$wpdb->prefix}gm_availabilities",
        ['contracted' => 1],
        ['id' => $data['availability_id']]
    );

    return $result;
}

function gm_get_groups() {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gm_groups");
}

function gm_get_availabilities() {
    global $wpdb;
    $user_id = get_current_user_id();
    return $wpdb->get_results($wpdb->prepare("SELECT b.* FROM {$wpdb->prefix}gm_availabilities a LEFT JOIN {$wpdb->prefix}gm_zones b WHERE a.group_id = %d", $user_id));
}

function gm_get_availability($availability_id) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_availabilities WHERE id = %d", $availability_id));
}

// funcion para actualizar una disponibilidad

function gm_update_availability() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gm_availability_action')) {
        wp_send_json_error('Nonce verification failed');
        return;
    }

    if (isset($_POST['availability_id']) && isset($_POST['start_time']) && isset($_POST['end_time'])) {
        global $wpdb;

        $availability_id = intval($_POST['availability_id']);
        $start_time = sanitize_text_field($_POST['start_time']);
        $end_time = sanitize_text_field($_POST['end_time']);

        $availability = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_availabilities WHERE id = %d", $availability_id));

        if ($availability && $availability->contracted == 0) {
            $result = $wpdb->update("{$wpdb->prefix}gm_availabilities", [
                'date' => $start_time,
                'end_time' => $end_time
            ], ['id' => $availability_id]);

            if ($result !== false) {
                wp_send_json_success('Disponibilidad actualizada exitosamente');
            } else {
                wp_send_json_error('Error al actualizar disponibilidad: ' . $wpdb->last_error);
            }
        } else {
            wp_send_json_error('No puedes editar una disponibilidad contratada.');
        }
    } else {
        wp_send_json_error('Datos no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_update_availability', 'gm_update_availability');


//funcion para eliminar disponibilidades

function gm_delete_availability() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gm_availability_action')) {
        wp_send_json_error('Nonce verification failed');
        return;
    }

    if (isset($_POST['availability_id'])) {
        global $wpdb;

        $availability_id = intval($_POST['availability_id']);
        $availability = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_availabilities WHERE id = %d", $availability_id));

        if ($availability && $availability->contracted == 0) {
            $result = $wpdb->delete("{$wpdb->prefix}gm_availabilities", ['id' => $availability_id]);

            if ($result) {
                wp_send_json_success('Disponibilidad eliminada exitosamente');
            } else {
                wp_send_json_error('Error al eliminar disponibilidad: ' . $wpdb->last_error);
            }
        } else {
            wp_send_json_error('No puedes eliminar una disponibilidad contratada.');
        }
    } else {
        wp_send_json_error('Datos no recibidos correctamente.');
    }
}
add_action('wp_ajax_gm_delete_availability', 'gm_delete_availability');

function gm_get_contracts() {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gm_contracts");
}

function gm_get_group_name($group_id) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}gm_groups WHERE id = %d", $group_id));
}

// funcion para contratar grupos musicales
function gm_handle_contract() {
    check_ajax_referer('gm_contract_action', '_wpnonce');

    if (isset($_POST['availability_id']) && current_user_can('gm_contractor')) {
        global $wpdb;
        $availability_id = intval($_POST['availability_id']);
        $availability = $wpdb->get_row($wpdb->prepare("
            SELECT a.*, z.name_zone, z.email FROM {$wpdb->prefix}gm_availabilities a 
            LEFT JOIN {$wpdb->prefix}gm_zones z ON a.id_zone = z.id 
            WHERE a.id = %d", $availability_id));
        if ($availability) {
            $current_user_id = get_current_user_id();
            $user_info = get_userdata($current_user_id);

            $data = [
                'group_id' => $availability->group_id,
                'availability_id' => $availability_id,
                'contractor_name' => $user_info->display_name,
                'contractor_email' => $user_info->user_email,
                'contractor_phone' => get_user_meta($current_user_id, 'phone', true),
                'date' => $availability->date,
            ];

            $result = gm_add_contract($data);

            if ($result !== false) {
                // Marcar la disponibilidad como contratada
                $wpdb->update(
                    "{$wpdb->prefix}gm_availabilities",
                    ['contracted' => 1],
                    ['id' => $availability_id]
                );

                $group = $wpdb->get_row($wpdb->prepare("SELECT name, email, phone FROM {$wpdb->prefix}gm_groups WHERE id = %d", $availability->group_id), ARRAY_A);

                // Crear lista con información del contratista
                $contractor_info_list = '<ul>';
                $contractor_info_list .= '<li>Nombre: ' . esc_html($user_info->display_name) . '</li>';
                $contractor_info_list .= '<li>Email: ' . esc_html($user_info->user_email) . '</li>';
                $contractor_info_list .= '<li>Zona: ' . esc_html($availability->name_zone) . '</li>';
                // $contractor_info_list .= '<li>Teléfono: ' . esc_html(get_user_meta($user_info->ID, 'phone', true)) . '</li>';
                $contractor_info_list .= '</ul>';
                
                // Crear lista con información del grupo
                $group_info_list = '<ul>';
                $group_info_list .= '<li>Nombre del Grupo: ' . esc_html($group['name']) . '</li>';
                $group_info_list .= '<li>Email del Grupo: ' . esc_html($group['email']) . '</li>';
                $group_info_list .= '<li>Teléfono del Grupo: ' . esc_html($group['phone']) . '</li>';
                $group_info_list .= '<li>Zona: ' . esc_html($availability->name_zone) . '</li>';
                $group_info_list .= '</ul>';
                
                // Crear mensaje para el grupo con información del contratista
                $message_for_group = 'Tu disponibilidad ha sido contratada en tu zona ' . esc_html($availability->name_zone) . ' para el ' . esc_html($availability->date) . '.<br>';
                $message_for_group .= 'Información del contratista:<br>' . $contractor_info_list;
                
                // Crear mensaje para el contratista con información del grupo
                $message_for_contractor = 'Has contratado a ' . esc_html($group['name']) . ' para el ' . esc_html($availability->date) . '.<br>';
                $message_for_contractor .= 'Información del grupo contratado:<br>' . $group_info_list;
                
                // Crear mensaje para el administrador con información de ambos
                $message_for_admin = 'Contratación realizada por ' . esc_html($user_info->display_name) . ' para el grupo ' . esc_html($group['name']) . ' el ' . esc_html($availability->date) . '.<br>';
                $message_for_admin .= 'Información del contratista:<br>' . $contractor_info_list;
                $message_for_admin .= 'Información del grupo:<br>' . $group_info_list;
                
                // Enviar correos
                // Enviar correo al grupo
                wp_mail($group['email'], 'Nueva Contratación', $message_for_group, ['Content-Type: text/html; charset=UTF-8']);
                // Enviar correo al contratista
                wp_mail($user_info->user_email, 'Confirmación de Contratación', $message_for_contractor, ['Content-Type: text/html; charset=UTF-8']);
                // Enviar correo al administrador
                wp_mail(get_option('admin_email'), 'Nueva Contratación', $message_for_admin, ['Content-Type: text/html; charset=UTF-8']);
                // Comprobar si la zona tiene un email y enviar correo
                if (!empty($availability->email)) {
                    $message_for_zone = 'Nueva contratación en la zona ' . esc_html($availability->name_zone) . ' para el ' . esc_html($availability->date) . '.<br>';
                    $message_for_zone .= 'Información del grupo contratado:<br>' . $group_info_list;

                    wp_mail($availability->email, 'Nueva Contratación en tu Zona', $message_for_zone, ['Content-Type: text/html; charset=UTF-8']);
                }

                wp_send_json_success(['message' => 'Contratación exitosa']);
            } else {
                wp_send_json_error(['message' => 'Error al insertar el contrato en la base de datos']);
            }
        } else {
            wp_send_json_error(['message' => 'Disponibilidad no encontrada']);
        }
    } else {
        wp_send_json_error(['message' => 'Datos inválidos o falta de permisos']);
    }
}
add_action('wp_ajax_gm_contract', 'gm_handle_contract');


// Función para manejar la solicitud AJAX
function gm_get_availabilities_ajax() {
    check_ajax_referer('gm_availability_action', 'security'); // Verifica el nonce para seguridad

    if (!is_user_logged_in() || !current_user_can('gm_group')) {
        wp_send_json_error('No tienes permiso para acceder a esta información.');
        return;
    }

    global $wpdb;
    $current_user_id = get_current_user_id();
    $group_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}gm_groups WHERE user_id = %d", $current_user_id));

    if (!$group_id) {
        wp_send_json_error('No se encontró un grupo asociado al usuario actual.');
        return;
    }

    // Realizar la consulta para obtener todas las disponibilidades
    $availabilities = $wpdb->get_results($wpdb->prepare("
        SELECT a.*, c.contractor_name, z.name_zone
        FROM {$wpdb->prefix}gm_availabilities a 
        LEFT JOIN {$wpdb->prefix}gm_contracts c ON a.id = c.availability_id 
        LEFT JOIN {$wpdb->prefix}gm_zones z ON a.id_zone = z.id 
        WHERE a.group_id = %d
    ", $group_id));

    if (empty($availabilities)) {
        wp_send_json_error('No se encontraron disponibilidades para tu grupo musical.');
        return;
    }

    wp_send_json_success($availabilities);
}

// Registrar las acciones para AJAX
add_action('wp_ajax_gm_get_availabilities', 'gm_get_availabilities_ajax');
add_action('wp_ajax_nopriv_gm_get_availabilities', 'gm_get_availabilities_ajax');


function gm_get_contractor_availabilities_ajax() {
    check_ajax_referer('gm_contract_action', 'security'); // Verifica el nonce para seguridad

    global $wpdb;

    // Realizar la consulta para obtener todas las disponibilidades junto con la información del grupo musical y las contrataciones
    $availabilities = $wpdb->get_results("
        SELECT a.*, c.contractor_name, g.photo, g.description, g.duration, a.id_zone, g.name AS group_name, d.name_zone 
        FROM {$wpdb->prefix}gm_availabilities a 
        JOIN {$wpdb->prefix}gm_groups g ON a.group_id = g.id
        LEFT JOIN {$wpdb->prefix}gm_contracts c ON a.id = c.availability_id
        LEFT JOIN {$wpdb->prefix}gm_zones d ON d.id = a.id_zone
        WHERE d.status = 1
    ");
    // wp_send_json_success($wpdb->last_query);


    if (empty($availabilities)) {
        wp_send_json_error('No se encontraron disponibilidades.');
        return;
    }

    wp_send_json_success($availabilities);
}

// Registrar las acciones para AJAX
add_action('wp_ajax_gm_get_contractor_availabilities', 'gm_get_contractor_availabilities_ajax');
add_action('wp_ajax_nopriv_gm_get_contractor_availabilities', 'gm_get_contractor_availabilities_ajax');

function gm_filter_block_navigation($block_content, $block) {
    if ($block['blockName'] === 'core/navigation') {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();

            $pages_to_hide = ['pagina-de-login', 'pagina-de-registro'];

            foreach ($pages_to_hide as $slug) {
                $block_content = preg_replace('/<li[^>]*>\s*<a[^>]*href="[^"]*\/' . preg_quote($slug, '/') . '[^"]*".*?<\/a>.*?<\/li>/is', '', $block_content);
            }

            if (in_array('gm_contractor', $current_user->roles)) {
                $pages_to_hide_contractor = ['formulario-de-disponibilidades', 'perfil-grupo-musical'];

                foreach ($pages_to_hide_contractor as $slug) {
                    $block_content = preg_replace('/<li[^>]*>\s*<a[^>]*href="[^"]*\/' . preg_quote($slug, '/') . '[^"]*".*?<\/a>.*?<\/li>/is', '', $block_content);
                }
            }

            $logout_url = wp_logout_url(home_url());
            $logout_link = '<li class="wp-block-pages-list__item wp-block-navigation-item"><a href="' . esc_url($logout_url) . '" style="color: red;">Salir</a></li>';
            $block_content = preg_replace('/(<ul class="wp-block-page-list">.*?)(<\/ul>)/is', '$1' . $logout_link . '$2', $block_content);
        }else {
            $pages_to_hide_logged_out = ['calendario-de-disponibilidad', 'formulario-de-disponibilidades', 'perfil-grupo-musical'];

            foreach ($pages_to_hide_logged_out as $slug) {
                $block_content = preg_replace('/<li[^>]*>\s*<a[^>]*href="[^"]*\/' . preg_quote($slug, '/') . '[^"]*".*?<\/a>.*?<\/li>/is', '', $block_content);
            }
        }
    }
    return $block_content;
}
add_filter('render_block', 'gm_filter_block_navigation', 10, 2);

// Ocultar la barra de administración para usuarios que no son administradores
function gm_ocultar_barra_admin_para_no_admins() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'gm_ocultar_barra_admin_para_no_admins');


add_action('wp_ajax_update_profile', 'handle_update_profile');
add_action('wp_ajax_nopriv_update_profile', 'handle_update_profile'); // Para usuarios no logueados, si es necesario
function handle_update_profile() {
    global $wpdb;

    check_ajax_referer('update_profile_nonce', 'security');

    $data = isset($_POST) ? $_POST : array();
    $update_data = array();
    $user_id = isset($data['user_id_group']) ? intval($data['user_id_group']) : 0;

    // Procesar imagen si se sube
    if (!empty($_FILES['profileImage']['name'])) {
        $uploaded_file = $_FILES['profileImage'];
        $original_filename = $uploaded_file['name'];
        $pathinfo = pathinfo($original_filename);
        $unique_filename = md5(uniqid()) . '.' . $pathinfo['extension']; // Nombre único

        // Definir la ruta donde guardarás el archivo
        $upload_dir = wp_upload_dir(); // Obtener la carpeta de uploads de WordPress
        $target_path = $upload_dir['path'] . '/' . $unique_filename; // Ruta completa del archivo

        // Mover el archivo a la carpeta de uploads de WordPress con el nombre único
        if (move_uploaded_file($uploaded_file['tmp_name'], $target_path)) {
            $file_url = $upload_dir['url'] . '/' . $unique_filename; // URL pública del archivo subido

            // Almacenar la URL de la imagen en la base de datos
            $update_data['photo'] = esc_url_raw($file_url);

            // Para depuración, puedes enviar una respuesta JSON
            // wp_send_json_success(array(
            //     'message' => 'Subida exitosa',
            //     'file_path' => $target_path, // Ruta completa en el servidor
            //     'file_url' => $file_url,     // URL pública
            // ));
        } else {
            // Subida fallida, enviar mensaje de error
            wp_send_json_error('Error al mover la imagen.');
        }
    }

    if (isset($data['name']) && $data['name'] !== '') {
        $update_data['name'] = sanitize_text_field($data['name']);
    }

    if (isset($data['descripcion']) && $data['descripcion'] !== '') {
        $update_data['description'] = sanitize_text_field($data['descripcion']);
    }

    if (isset($data['id_zone']) && $data['id_zone'] !== '') {
        // $update_data['id_zone'] = intval($data['id_zone']);
        $update_data['id_zone'] = sanitize_text_field($data['id_zone']);
    }

    if (isset($data['email']) && $data['email'] !== '') {
        if (is_email($data['email'])) {
            $update_data['email'] = sanitize_email($data['email']);
        } else {
            wp_send_json_error('Email no válido.');
        }
    }

    if (isset($data['phone']) && $data['phone'] !== '') {
        $update_data['phone'] = sanitize_text_field($data['phone']);
    }

    if (isset($data['duration']) && $data['duration'] !== '') {
        $update_data['duration'] = intval($data['duration']);
    }

    // Actualizar la base de datos solo si hay datos para actualizar
    if (!empty($update_data) && $user_id > 0) {
        $wpdb->update(
            "{$wpdb->prefix}gm_groups",
            $update_data,
            array('user_id' => $user_id),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d'), 
            array('%d')
        );
        // wp_send_json_success($wpdb->last_query);
        wp_send_json_success('Grupo actualizado correctamente.');
    } else {
        wp_send_json_error('No se recibieron datos para actualizar o el ID de usuario es inválido.');
    }
}

function gm_extract_zones_per_group() {
    // Verificar el nonce para mayor seguridad
    check_ajax_referer('gm_availability_action', '_wpnonce');

    // Obtener el ID del grupo enviado por AJAX
    $group_id = intval($_POST['group_id']);

    global $wpdb;

    // Realizar la consulta a la base de datos (ajusta la consulta según tus necesidades)
    $zones = $wpdb->get_results($wpdb->prepare("
        SELECT id_zone FROM {$wpdb->prefix}gm_groups
        WHERE id = %d
    ", $group_id));
    $ids_zones = $zones[0]->id_zone;
    // wp_send_json_success($zones[0]->id_zone);
    
    // Verificar si se encontraron zonas
    if ($zones) {
        // Retornar respuesta exitosa con los datos
        $availables_zones =  $wpdb->get_results($wpdb->prepare("
        SELECT id,name_zone FROM {$wpdb->prefix}gm_zones
        WHERE id IN ($ids_zones)"));
        wp_send_json_success($availables_zones);
        // wp_send_json_success($wpdb->last_query);
    } else {
        // Retornar respuesta de error
        wp_send_json_error('No se encontraron zonas para el grupo especificado.');
    }
}

// Registrar la acción AJAX para usuarios logueados y no logueados
add_action('wp_ajax_gm_extract_zones_per_group', 'gm_extract_zones_per_group');
