<?php
if (!is_user_logged_in() || !current_user_can('gm_group')) {
    echo '<p>No tienes permiso para acceder a esta página.</p>';
    return;
}

// Generar nonce para seguridad AJAX
$gm_availability_nonce = wp_create_nonce('gm_availability_action');
?>

<div id="musical-group-calendar">
    <div>
        <label for="availability-filterGM">Mostrar:</label>
        <select id="availability-filterGM">
            <option value="all">Todas</option>
            <option value="available">Disponibles</option>
            <option value="contracted">Contratadas</option>
        </select>
    </div>

    <div id="calendar-header">
        <span id="prev-monthGM" role="button" tabindex="0">‹</span>
        <span id="calendar-month-year"></span>
        <span id="next-monthGM" role="button" tabindex="0">›</span>
    </div>
    <div id="calendar-days">
        <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sa</span><span>Do</span>
    </div>
    <div id="calendar-dates"></div>
</div>

<!-- Leyenda -->
<div class="leyenda-container">
    <h6 class="leyenda-title">Leyenda</h6>
    <ul class="leyenda-list">
        <li class="leyenda-item">
            Día contratado parcialmente: <span class="parcial leyenda-icon">■</span>
        </li>
        <li class="leyenda-item">
            Día contratado completamente: <span class="allComplet leyenda-icon">■</span>
        </li>
        <li class="leyenda-item">
            Día sin contratación: <span class="allAvailable leyenda-icon">■</span>
        </li>
    </ul>
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
    var gm_availability_nonce = '<?php echo esc_attr($gm_availability_nonce); ?>'; // Genera el nonce
</script>
<script src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/js/calendar-musical-group.js'); ?>"></script>
