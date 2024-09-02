<?php
global $wpdb;

$gm_contract_nonce = wp_create_nonce('gm_contract_action');

$zones = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gm_zones WHERE status = 1");

?>

<div id="contractor-calendar">
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
        <label id="lblArea" for="zone-filter">Zona:</label>
        <select id="zone-filter">
            <option value="all">Todas</option>
        <?php foreach ($zones as $zone): ?>
            <option value="<?=$zone->id?>"><?=$zone->name_zone?></option>
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
    var gm_contract_nonce = '<?php echo esc_attr($gm_contract_nonce); ?>';
</script>
<script src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/js/calendar-contractor.js'); ?>"></script>