<?php
function gm_registration_form_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/registration-form.php';
    return ob_get_clean();
}
add_shortcode('gm_registration_form', 'gm_registration_form_shortcode');

function gm_login_form_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/login-form.php';
    return ob_get_clean();
}
add_shortcode('gm_login_form', 'gm_login_form_shortcode');

function gm_handle_registration() {
    global $gm_errors;
    $gm_errors = '';

    if (isset($_POST['gm_register'])) {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        $role = sanitize_text_field($_POST['role']);

        if (email_exists($email)) {
            $gm_errors = 'Lo siento, ¡esa dirección de correo electrónico ya está en uso!';
            return;
        }

        $user_id = wp_create_user($username, $password, $email);
        if (!is_wp_error($user_id)) {
            $user = get_user_by('id', $user_id);
            $user->set_role($role);

            wp_new_user_notification($user_id, null, 'both');

            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);

            if ($role == 'gm_group') {
                wp_redirect(home_url('/perfil-grupo-musical/'));
            } elseif ($role == 'gm_contractor') {
                wp_redirect(home_url('/calendario-de-disponibilidades/'));
            }
            exit;
        } else {
            $gm_errors = $user_id->get_error_message();
        }
    }
}
add_action('init', 'gm_handle_registration');

function gm_handle_login() {
    global $gm_errors;
    $gm_errors = '';

    if (isset($_POST['gm_login'])) {
        $credentials = array(
            'user_login' => sanitize_user($_POST['username']),
            'user_password' => sanitize_text_field($_POST['password']),
            'remember' => isset($_POST['remember']),
        );

        $user = wp_signon($credentials, is_ssl());
        if (!is_wp_error($user)) {
            wp_redirect(home_url());
            exit;
        } else {
            $gm_errors = $user->get_error_message();
        }
    }
}
add_action('init', 'gm_handle_login');

// crear usuario desde el perfil de admin
function gm_create_user() {
    if (isset($_POST['submit_create_user'])) {
        check_admin_referer('gm_create_user_action', 'gm_create_user_nonce');

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $role = sanitize_text_field($_POST['role']);

        if (!email_exists($email)) {
            $password = wp_generate_password();
            $user_id = wp_create_user($name, $password, $email);

            if (!is_wp_error($user_id)) {
                wp_update_user([
                    'ID' => $user_id,
                    'role' => $role,
                    'display_name' => $name,
                ]);

                wp_mail($email, 'Bienvenido', "Tu cuenta ha sido creada. Inicia sesión aquí: " . wp_login_url() . " con la contraseña temporal: $password");

                echo '<div class="gm-success">Usuario creado exitosamente. Se ha enviado un correo con las credenciales.</div>';
            } else {
                echo '<div class="gm-error">Error al crear el usuario: ' . $user_id->get_error_message() . '</div>';
            }
        } else {
            echo '<div class="gm-error">El correo electrónico ya está registrado.</div>';
        }
    }
}
add_action('admin_post_create_user', 'gm_create_user');


?>