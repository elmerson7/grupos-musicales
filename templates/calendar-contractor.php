<?php
global $wpdb;

// Obtener todas las disponibilidades junto con la información del grupo musical y las contrataciones
$availabilities = $wpdb->get_results($wpdb->prepare("
    SELECT a.*, g.name as group_name, c.contractor_name, name_area 
    FROM {$wpdb->prefix}gm_availabilities a 
    JOIN {$wpdb->prefix}gm_groups g ON a.group_id = g.id
    LEFT JOIN {$wpdb->prefix}gm_contracts c ON a.id = c.availability_id
    LEFT JOIN {$wpdb->prefix}areas d ON g.region = d.name_area WHERE d.status = 1
"));
$availabilities_json = json_encode($availabilities);
$areas = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}areas WHERE status = 1"));

?>

<div id="contractor-calendar" data-availabilities='<?php echo esc_attr($availabilities_json); ?>'>
    <div id="calendar-header">
        <span id="prev-month" role="button" tabindex="0">‹</span>
        <span id="calendar-month-year"></span>
        <span id="next-month" role="button" tabindex="0">›</span>
    </div>
    <div id="calendar-filters">
        <label id="lblAvailability" for="availability-filter">Mostrar:</label>
        <select id="availability-filter">
            <option value="all">Todas</option>
            <option value="available">Disponibles</option>
            <option value="contracted">Contratadas por mí</option>
        </select>
        <label id="lblArea" for="area-filter">Area:</label>
        <select id="area-filter">
            <option value="all">Todas</option>
        <?php foreach ($areas as $area): ?>
            <option value="<?=$area->name_area?>"><?=$area->name_area?></option>
        <?php endforeach; ?>
        </select>
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

<?php wp_nonce_field('gm_contract_action', 'gm_contract_nonce'); ?>

<link rel="stylesheet" type="text/css" href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/css/calendar-contractor.css'); ?>">
<script>
    var ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
    var wp_current_user_name = '<?php echo esc_js(wp_get_current_user()->display_name); ?>';
</script>
<script src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/js/calendar-contractor.js'); ?>"></script>