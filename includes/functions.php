<?php

// Añadir menú "Disponibilidades" al panel de administración
function gm_register_availabilities_menu() {
    add_menu_page(
        'Disponibilidades',  // Título de la página
        'Disponibilidades',  // Título del menú
        'manage_options',    // Capacidad requerida
        'gm_availabilities', // Slug de la página
        'gm_availabilities_page', // Función de contenido
        'dashicons-calendar-alt', // Icono del menú
        6                     // Posición del menú
    );
}
add_action('admin_menu', 'gm_register_availabilities_menu');

function gm_availabilities_page() {
    include plugin_dir_path(__FILE__) . '../templates/gm-availabilities-page.php';
}

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
        $date = sanitize_text_field($_POST['date']);
        $end_time = sanitize_text_field($_POST['end_time']);
        $all_day = isset($_POST['all_day']) ? 1 : 0;

        $updated = $wpdb->update(
            "{$wpdb->prefix}gm_availabilities",
            [
                'date' => $date,
                'end_time' => $end_time,
                'all_day' => $all_day,
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
            region varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}gm_availabilities (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            group_id mediumint(9) NOT NULL,
            date datetime NOT NULL,
            end_time datetime NOT NULL,
            all_day boolean NOT NULL,
            contracted boolean NOT NULL DEFAULT 0,
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

    global $wpdb;
    $date = sanitize_text_field($_POST['date']);
    $start_time = sanitize_text_field($_POST['start_time']);
    $end_time = sanitize_text_field($_POST['end_time']);
    $all_day = isset($_POST['all_day']) ? 1 : 0;
    $user_id = get_current_user_id();

    $group_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}gm_groups WHERE user_id = %d", $user_id));

    if (!$group_id) {
        wp_send_json_error('No se encontró un grupo asociado al usuario actual.');
        return;
    }

    $data = [
        'group_id' => $group_id,
        'date' => $date . ' ' . $start_time,
        'end_time' => $date . ' ' . $end_time,
        'all_day' => $all_day
    ];

    $result = $wpdb->insert("{$wpdb->prefix}gm_availabilities", $data);

    if ($result === false) {
        wp_send_json_error("Error en el registro: No se pudo insertar la disponibilidad en la base de datos.");
    } else {
        wp_send_json_success("Inserción exitosa en la tabla gm_availabilities.");
    }
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
    return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_availabilities WHERE group_id = %d", $user_id));
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
        $availability = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gm_availabilities WHERE id = %d", $availability_id));
        
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

                $group_email = $wpdb->get_var($wpdb->prepare("SELECT email FROM {$wpdb->prefix}gm_groups WHERE id = %d", $availability->group_id));

                wp_mail($group_email, 'Nueva Contratación', 'Tu disponibilidad ha sido contratada por ' . $user_info->display_name . ' para el ' . $availability->date);
                wp_mail($user_info->user_email, 'Confirmación de Contratación', 'Has contratado a ' . gm_get_group_name($availability->group_id) . ' para el ' . $availability->date);
                wp_mail(get_option('admin_email'), 'Nueva Contratación', 'Contratación realizada por ' . $user_info->display_name . ' para ' . gm_get_group_name($availability->group_id) . ' el ' . $availability->date);

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