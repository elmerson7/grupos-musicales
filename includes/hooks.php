<?php
function gm_add_custom_roles() {
    add_role('gm_group', 'Grupo Musical', array(
        'read' => true,
        'edit_posts' => false,
    ));

    add_role('gm_contractor', 'Contratante', array(
        'read' => true,
        'edit_posts' => false,
    ));
}

register_activation_hook(__FILE__, 'gm_add_custom_roles');

function gm_remove_custom_roles() {
    remove_role('gm_group');
    remove_role('gm_contractor');
}

register_deactivation_hook(__FILE__, 'gm_remove_custom_roles');

add_action('wp_ajax_gm_contract', 'gm_contract');
add_action('wp_ajax_nopriv_gm_contract', 'gm_contract');

function gm_contract() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'gm_contract')) {
        wp_send_json_error('Nonce verification failed');
    }

    $data = [
        'group_id' => intval($_POST['group_id']),
        'contractor_name' => sanitize_text_field($_POST['contractor_name']),
        'contractor_email' => sanitize_email($_POST['contractor_email']),
        'contractor_phone' => sanitize_text_field($_POST['contractor_phone']),
        'date' => sanitize_text_field($_POST['date']),
    ];

    gm_add_contract($data);

    wp_send_json_success('Contract created successfully');
}

function gm_delete_contract() {
    // Verifica el nonce para seguridad
    check_ajax_referer('gm_contract_action');

    // Asegúrate de que el usuario tenga los permisos necesarios para eliminar contratos
    // if (!current_user_can('manage_options')) {
    //     wp_send_json_error('No tienes permisos suficientes para realizar esta acción.');
    //     return;
    // }

    // Obtén el ID del contrato desde la solicitud AJAX
    $contract_id = isset($_POST['contract_id']) ? intval($_POST['contract_id']) : 0;

    if ($contract_id > 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gm_contracts';

        // Elimina el contrato de la base de datos
        $deleted = $wpdb->delete($table_name, array('availability_id' => $contract_id), array('%d'));
        // $deleted = true;
        
        if ($deleted) {
            wp_send_json_success('Contrato eliminado correctamente.');
        } else {
            wp_send_json_error('No se pudo eliminar el contrato. Puede que el contrato no exista.');
        }
    } else {
        wp_send_json_error('ID de contrato inválido.');
    }
}

// Agrega la acción para manejar la solicitud AJAX
add_action('wp_ajax_gm_delete_contract', 'gm_delete_contract');

function gm_send_email_notifications($post_id, $post) {
    // Verificar si es una revisión para evitar duplicados
    if (wp_is_post_revision($post_id)) {
        return;
    }

    if ($post->post_type == 'tribe_events') {
        $group_email = get_the_author_meta('user_email', $post->post_author);
        $admin_email = get_option('admin_email');

        // Enviar email al grupo musical
        wp_mail(
            $group_email,
            'Nueva Disponibilidad Creada',
            'Se ha creado una nueva disponibilidad para tu grupo musical.'
        );

        // Enviar email al administrador
        wp_mail(
            $admin_email,
            'Nueva Disponibilidad Creada',
            'Se ha creado una nueva disponibilidad para el grupo musical: ' . get_the_author_meta('display_name', $post->post_author)
        );
    }
}
add_action('save_post', 'gm_send_email_notifications', 10, 2);
?>