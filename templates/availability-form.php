<?php
    global $wpdb;
    // echo '<pre>';
    // print_r(get_defined_vars());
    // echo '</pre>';
    $user_id = get_current_user_id();
    $group_zones = $wpdb->get_results($wpdb->prepare("SELECT id_zone,duration FROM {$wpdb->prefix}gm_groups WHERE user_id = %d", $user_id));

    $id_zone = $group_zones[0]->id_zone;
    $duration = $group_zones[0]->duration;

    $zones = $wpdb   ->get_results($wpdb->prepare("SELECT id,name_zone FROM {$wpdb->prefix}gm_zones WHERE id IN ($id_zone)"));
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Incluye SweetAlert desde una CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Incluye los estilos del formulario -->
    <link rel="stylesheet" type="text/css" href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/css/availability-form.css'); ?>">
</head>
<body>
<form method="POST" action="#" id="gm-availability-form" class="form-container">
    <?php wp_nonce_field('gm_availability_action', 'gm_availability_nonce'); ?>
    <input type="hidden" name="action" value="gm_handle_availability_ajax">
    
    <div class="form-header">
        <i class="fas fa-calendar-alt"></i>
    </div>
    
    <div class="form-group">
        <label for="date">Fecha</label>
        <input type="date" name="date" id="date" required aria-describedby="date-error">
        <span id="date-error" class="gm-error">Este campo es obligatorio.</span>
    </div>

    <div class="form-group">
        <label for="zone">Zona geográfica</label>
            <select name="zone" id="zone" required>
            <option value="" selected disabled>--Selecciona Zona--</option>
            <?php foreach ($zones as $zone): ?>
                <option value="<?=$zone->id?>"><?=$zone->name_zone?></option>
            <?php endforeach; ?>
        </select>
        <span id="date-error" class="gm-error">Este campo es obligatorio.</span>
    </div>
    
    <div class="form-group availabilitiDiv">
        <input type="checkbox" name="all_day" id="all_day" class="inputAvailibility">
        <label for="all_day">Todo el día</label>
    </div>

    <div class="form-group" id="horaInicio">
        <label for="start_time">Hora de inicio</label>
        <input type="time" name="start_time" id="start_time" required aria-describedby="start-time-error">
        <span id="start-time-error" class="gm-error">Este campo es obligatorio.</span>
    </div>
    
    <div class="form-group" id="horaFin">
        <label for="end_time">Hora de fin</label>
        <input type="time" name="end_time" id="end_time" required aria-describedby="end-time-error">
        <span id="end-time-error" class="gm-error">Este campo es obligatorio.</span>
        <span id="time-error" class="gm-error">La hora de inicio debe ser anterior a la hora de fin.</span>
    </div>

    <div id="contenedor-eventos">
        <div class="form-group">
            <div class="radio-group" id="listaEventos">
                <label for="daily">
                    <input class="radioEvent" type="radio" id="daily" name="event" value="diario">
                    Diario
                </label>
                <label for="weekly">
                    <input class="radioEvent" type="radio" id="weekly" name="event" value="semanal">
                    Semanal
                </label>
                <label for="monthly">
                    <input class="radioEvent" type="radio" id="monthly" name="event" value="mensual">
                    Mensual
                </label>
            </div>
        </div>
    
        <div class="form-group" id="divFechaFin">
            <label for="endDate">Fecha Fin</label>
            <input type="date" name="endDate" id="endDate" disabled aria-describedby="date-error">
            <span id="date-error" class="gm-error">Este campo es obligatorio.</span>
        </div>
        
        <div class="form-group" id="contentEvent">
            <input type="button" name="cancelEvent" id="cancelEvent" value="Cancelar Evento">
        </div>
    </div>

    <div class="form-group">
        <input type="submit" name="submit_availability" value="Guardar Disponibilidad">
    </div>
</form>

<script>

let btnAllDay = document.getElementById('all_day');
let startTime = document.getElementById('start_time');
let endTime = document.getElementById('end_time');

let divFechaFin = document.getElementById('divFechaFin');
let cancelEvent = document.getElementById('cancelEvent');
let radioButtons = document.querySelectorAll('.radioEvent');
let endDate = document.getElementById('endDate');

cancelEvent.addEventListener('click', function() {
    radioButtons.forEach(function(radio) {
        radio.checked = false;
    });

    if (divFechaFin) {
        divFechaFin.style.display = 'none';
        divFechaFin.value = '';
    }
    contentEvent.style.display = 'none';
    endDate.removeAttribute('required');
    endDate.setAttribute('disabled','disabled');
    endDate.value = '';
});

document.querySelectorAll('.radioEvent').forEach(function(radio) {
    radio.addEventListener('click', function() {
        contentEvent.style.display = 'flex';
        divFechaFin.style.display = 'block';
        endDate.setAttribute('required','required');
        endDate.removeAttribute('disabled');

    });
});

btnAllDay.addEventListener('change', function(){
    if(this.checked){
        startTime.value = '00:00';
        endTime.value = '23:59';
        startTime.disabled = true;
        endTime.disabled = true;
    }else{
        startTime.value = '';
        endTime.value = '';
        startTime.disabled = false;
        endTime.disabled = false;
    }
});

function convertirAMinutos(hora) {
    const [horas, minutos] = hora.split(':').map(Number);
    return horas * 60 + minutos;
}

jQuery(document).ready(function($) {
    // let divFechaFin = document.getElementById('divFechaFin');
    // let startTime = document.getElementById('start_time');
    // let endTime = document.getElementById('end_time');
    $('#gm-availability-form').submit(function(e) {
        e.preventDefault();
        // console.log(startTime.value,endTime.value);
        const minutosHora1 = convertirAMinutos(startTime.value);
        const minutosHora2 = convertirAMinutos(endTime.value);
        const diferenciaMinutos = minutosHora2 - minutosHora1;
        const duration = <?=$duration?>;
        // console.log(duration);
        if (duration > diferenciaMinutos) {
            alert("El tiempo seleccionado excede a la duración del Show")
            return;
        }
        // console.log(`Han transcurrido ${diferenciaMinutos} minutos entre ${startTime.value} y ${endTime.value}`);

        // return
        var formData = $(this).serialize(); // Captura todos los datos del formulario
            
        if(btnAllDay.checked){
            let dates = `&start_time=${startTime.value}&end_time=${endTime.value}`;
            formData += dates;
        }
        
        // return console.log(formData);
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    var date = $('#date').val();
                    var startTime = $('#start_time').val();
                    var endTime = $('#end_time').val();

                    Swal.fire({
                        title: 'Disponibilidad guardada con éxito',
                        html: `<p>Fecha: ${date}</p><p>Hora de inicio: ${startTime}</p><p>Hora de fin: ${endTime}</p>`,
                        icon: 'success'
                    }).then(function() {
                        location.reload(); // O redirigir a otra página
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.data,
                        icon: 'error'
                    });
                }
            }
        });
    });
});


</script>
</body>
</html>