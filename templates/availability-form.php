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
    
    
    <div class="form-group">
        <input type="submit" name="submit_availability" value="Guardar Disponibilidad">
    </div>
</form>

<script>

let btnAllDay = document.getElementById('all_day');
let startTime = document.getElementById('start_time');
let endTime = document.getElementById('end_time');

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


jQuery(document).ready(function($) {
    $('#gm-availability-form').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize(); // Captura todos los datos del formulario
            
        if(btnAllDay.checked){
            let dates = `&start_time=${startTime.value}&end_time=${endTime.value}`;
            formData += dates;
        }
        
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