<?php

function calendar_contractor_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/calendar-contractor.php'; // Ajusta la ruta al archivo del calendario
    return ob_get_clean();
}
add_shortcode('calendar-contractor', 'calendar_contractor_shortcode');

// Formulario perfil del grupo
function gm_group_profile_form_shortcode() {
    if (!is_user_logged_in() || !current_user_can('gm_group')) {
        return 'No tienes permiso para acceder a esta página.';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $group_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}gm_groups WHERE user_id = %d", $user_id));

    if ($group_exists) {
        return 'Ya has completado tu perfil de grupo musical.';
    }

    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/group-profile-form.php';
    return ob_get_clean();
}
add_shortcode('gm_group_profile_form', 'gm_group_profile_form_shortcode');

function gm_handle_group_profile_form() {
    if (isset($_POST['submit_group_profile'])) {
        global $wpdb;

        $user_id = get_current_user_id();
        $group_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}gm_groups WHERE user_id = %d", $user_id));

        if ($group_exists) {
            error_log("El usuario ya ha creado un grupo musical.");
            echo '<script>console.error("Ya has completado tu perfil de grupo musical.");</script>';
            return;
        }

        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $region = sanitize_text_field($_POST['region']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);
        $photo = '';

        if (!empty($_FILES['photo']['name'])) {
            $uploaded = wp_upload_bits($_FILES['photo']['name'], null, file_get_contents($_FILES['photo']['tmp_name']));
            if (!$uploaded['error']) {
                $photo = $uploaded['url'];
            }
        }

        $data = [
            'user_id' => $user_id,
            'photo' => $photo,
            'name' => $name,
            'description' => $description,
            'region' => $region,
            'email' => $email,
            'phone' => $phone
        ];

        $result = gm_add_group($data);

        if ($result === false) {
            error_log("Error en el registro: No se pudo insertar el grupo en la base de datos.");
            echo '<script>console.error("Error en el registro: No se pudo insertar el grupo en la base de datos.");</script>';
        } else {
            error_log("Inserción exitosa en la tabla gm_groups.");
            echo '<script>console.log("Inserción exitosa en la tabla gm_groups.");</script>';
            wp_redirect(home_url('/formulario-de-disponibilidades/'));
            exit;
        }
    }
}
add_action('init', 'gm_handle_group_profile_form');


// Formulario crear evento
function gm_availability_form_shortcode() {
    if (!is_user_logged_in() || !current_user_can('gm_group')) {
        return 'No tienes permiso para acceder a esta página.';
    }

    $user_id = get_current_user_id();
    echo '<p>ID del usuario actual: ' . $user_id . '</p>';

    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/availability-form.php';
    return ob_get_clean();
}
add_shortcode('gm_availability_form', 'gm_availability_form_shortcode');

function gm_availability_calendar_shortcode() {
    if (!is_user_logged_in() || !current_user_can('gm_group')) {
      	return '';
    }
    
    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/calendar-musical-group.php';
    return ob_get_clean();
}
add_shortcode('gm_availability_calendar', 'gm_availability_calendar_shortcode');

function gm_contractor_calendar_shortcode() {
    if (!is_user_logged_in() || !current_user_can('gm_contractor')) {
        return '';
    }

    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/calendar-contractor.php';
    return ob_get_clean();
}
add_shortcode('gm_contractor_calendar', 'gm_contractor_calendar_shortcode');


function gm_user_events_shortcode() {
    if (!is_user_logged_in() || !current_user_can('gm_group')) {
        return 'No tienes permiso para acceder a esta página.';
    }

    $current_user_id = get_current_user_id();
    $args = [
        'post_type' => 'tribe_events',
        'author' => $current_user_id,
        'posts_per_page' => -1,
    ];

    $events = new WP_Query($args);

    if ($events->have_posts()) {
        ob_start();
        echo '<ul>';
        while ($events->have_posts()) : $events->the_post();
            echo '<li>' . get_the_title() . ' - ' . tribe_get_start_date() . '</li>';
        endwhile;
        echo '</ul>';
        return ob_get_clean();
    } else {
        return 'No tienes eventos disponibles.';
    }
}
add_shortcode('gm_user_events', 'gm_user_events_shortcode');

function gm_group_calendar_shortcode() {
    if (!is_user_logged_in() || !current_user_can('gm_group')) {
        return 'No tienes permiso para acceder a esta página.';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $availabilities = $wpdb->get_results($wpdb->prepare("
        SELECT a.*, c.contractor_name 
        FROM {$wpdb->prefix}gm_availabilities a 
        LEFT JOIN {$wpdb->prefix}gm_contracts c ON a.id = c.availability_id 
        WHERE a.group_id = %d
    ", $user_id));

    $availabilities_json = json_encode($availabilities);

    ob_start();
    ?>
    <div id="musical-group-calendar" data-availabilities='<?php echo esc_attr($availabilities_json); ?>'>
        <div>
            <label for="availability-filter">Mostrar:</label>
            <select id="availability-filter">
                <option value="all">Todas</option>
                <option value="available">Disponibles</option>
                <option value="contracted">Contratadas</option>
            </select>
        </div>
        <div id="calendar-header">
            <span id="prev-month" role="button" tabindex="0">‹</span>
            <span id="calendar-month-year"></span>
            <span id="next-month" role="button" tabindex="0">›</span>
        </div>
        <div id="calendar-days">
            <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sa</span><span>Do</span>
        </div>
        <div id="calendar-dates"></div>
    </div>

    <div id="availability-popup" class="popup" role="dialog" aria-labelledby="popup-title" aria-hidden="true">
        <div class="popup-content">
            <span class="close-popup" role="button" tabindex="0">&times;</span>
            <div id="popup-content"></div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/css/calendar-musical-group.css'); ?>">
    <script>
        var ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
    </script>
    <script src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/js/calendar-musical-group.js'); ?>"></script>
    <?php
    return ob_get_clean();
}
add_shortcode('gm_group_calendar', 'gm_group_calendar_shortcode');

?>