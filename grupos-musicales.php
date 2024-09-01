<?php
/*
Plugin Name: Grupos Musicales
Description: Plugin para poner en contacto a grupos de música con contratantes.
Version: 1.0
Author: Admin
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Incluir archivos adicionales
include_once plugin_dir_path(__FILE__) . 'includes/functions.php';
include_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
include_once plugin_dir_path(__FILE__) . 'includes/hooks.php';
include_once plugin_dir_path(__FILE__) . 'includes/user-auth.php';

// Crear páginas automáticamente al activar el plugin
function gm_create_pages() {
    // Crear página de registro
    if (!get_page_by_path('pagina-de-registro')) {
        wp_insert_post(array(
            'post_title'    => 'Grupos Musicales - Página de Registro',
            'post_name'     => 'pagina-de-registro',
            'post_content'  => '[gm_registration_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        ));
    }

    // Crear página de login
    if (!get_page_by_path('pagina-de-login')) {
        wp_insert_post(array(
            'post_title'    => 'Grupos Musicales - Página de Login',
            'post_name'     => 'pagina-de-login',
            'post_content'  => '[gm_login_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        ));
    }

    // Crear página de perfil del grupo musical
    if (!get_page_by_path('perfil-grupo-musical')) {
        wp_insert_post(array(
            'post_title'    => 'Grupos Musicales - Perfil del Grupo Musical',
            'post_name'     => 'perfil-grupo-musical',
            'post_content'  => '[gm_group_profile_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        ));
    }

    // Crear página de formulario de disponibilidades
    if (!get_page_by_path('formulario-de-disponibilidades')) {
        wp_insert_post(array(
            'post_title'    => 'Grupos Musicales - Formulario de Disponibilidades',
            'post_name'     => 'formulario-de-disponibilidades',
            'post_content'  => '[gm_availability_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        ));
    }

    // Crear página de calendario de disponibilidades
    if (!get_page_by_path('calendario-de-disponibilidades')) {
        wp_insert_post(array(
            'post_title'    => 'Grupos Musicales - Calendario de Disponibilidad',
            'post_name'     => 'calendario-de-disponibilidades',
            'post_content'  => '[gm_availability_calendar][gm_contractor_calendar]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        ));
    }
}

register_activation_hook(__FILE__, 'gm_create_pages');
register_activation_hook(__FILE__, 'gm_create_tables');

// cargar Font Awesome
function gm_enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'gm_enqueue_font_awesome');

// Función de desinstalación
function gm_uninstall_plugin() {
    global $wpdb;

    // Eliminar tablas
    $tables = [
        "{$wpdb->prefix}gm_groups",
        "{$wpdb->prefix}gm_availabilities",
        "{$wpdb->prefix}gm_contracts"
    ];

    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }

    // Eliminar páginas
    $pages = [
        'pagina-de-registro',
        'pagina-de-login',
        'perfil-grupo-musical',
        'formulario-de-disponibilidades',
        'calendario-de-disponibilidades'
    ];

    foreach ($pages as $page_slug) {
        $page = get_page_by_path($page_slug);
        if ($page) {
            wp_delete_post($page->ID, true);
        }
    }
}

register_uninstall_hook(__FILE__, 'gm_uninstall_plugin');

function gm_add_roles() {
    // Eliminar el rol si ya existe para evitar duplicados
    remove_role('gm_group');
    remove_role('gm_contractor');

    add_role('gm_group', 'Grupo Musical', [
        'read' => true, // Necesario para que puedan iniciar sesión
        'edit_posts' => false,
        'delete_posts' => false,
        'publish_posts' => false,
        'upload_files' => true, // Necesario para subir imágenes
    ]);

    add_role('gm_contractor', 'Contratante', [
        'read' => true,
        'edit_posts' => false,
        'delete_posts' => false,
        'publish_posts' => false,
    ]);
}
register_activation_hook(__FILE__, 'gm_add_roles');

function gm_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('gm_calendar_contractor', plugin_dir_url(__FILE__) . 'assets/js/calendar-contractor.js', array('jquery'), null, true);
    wp_localize_script('gm_calendar_contractor', 'gm_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gm_contract_action'),
    ));
}
add_action('wp_enqueue_scripts', 'gm_enqueue_scripts');

?>