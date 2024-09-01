document.addEventListener('DOMContentLoaded', function() {
    const calendarDates = document.getElementById('calendar-dates');
    const calendarMonthYear = document.getElementById('calendar-month-year');
    const prevMonth = document.getElementById('prev-month');
    const nextMonth = document.getElementById('next-month');
    const popup = document.getElementById('availability-popup');
    const popupContent = document.getElementById('popup-content');
    const closePopup = document.querySelector('.close-popup');
    const availabilityFilter = document.getElementById('availability-filter');

    let currentDate = new Date();
    const availabilityData = document.getElementById('musical-group-calendar').dataset.availabilities;
    if (!availabilityData) {
        console.error('Data availabilities no encontrada');
        return;
    }
    console.log('Disponibilidades:', availabilityData);
    const availabilities = JSON.parse(availabilityData);

    availabilityFilter.addEventListener('change', loadCalendar);


 // funcion para mostrar el popup en el calendario de grupos musicales
 function showPopup(year, month, date, dayAvailabilities) {
    const selectedDate = new Date(year, month, date);
    popupContent.innerHTML = '';

    if (dayAvailabilities.length > 0) {
        // Crear la tabla de disponibilidades
        popupContent.innerHTML = `<h3>Disponibilidades para ${selectedDate.toLocaleDateString()}</h3>`;
        popupContent.innerHTML += `
            <table class="availability-table">
                <thead>
                    <tr>
                        <th>Grupo Musical</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${dayAvailabilities.map(availability => `
                        <tr>
                            <td>${availability.group_name}</td>
                            <td>${new Date(availability.date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</td>
                            <td>${new Date(availability.end_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</td>
                            <td>
                                <button class="edit-availability" data-id="${availability.id}">
                                    <i class="fas fa-edit"></i> <!-- Icono de edición -->
                                </button>
                                <button class="delete-availability" data-id="${availability.id}">
                                    <i class="fas fa-trash-alt"></i> <!-- Icono de eliminación -->
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            <div id="add-availability-container" style="text-align: center; margin-top: 10px;">
                <span id="add-availability" style="color: green; font-size: 30px; cursor: pointer;">+</span>
            </div>
        `;
    } else {
        // Cargar el formulario de disponibilidad si no hay ninguna
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_get_availability_form',
                date: `${year}-${(month + 1).toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`
            },
            function(response) {
                if (response.success) {
                    popupContent.innerHTML = `<h3>Crear Disponibilidad para ${selectedDate.toLocaleDateString()}</h3>`;
                    popupContent.innerHTML += response.data;
                    const availabilityForm = document.getElementById('gm-availability-form');
                    if (availabilityForm) {
                        availabilityForm.addEventListener('submit', handleFormSubmit);
                    }
                } else {
                    popupContent.innerHTML = `<p>${response.data}</p>`;
                }
            }
        );
    }

    popup.style.display = 'block';

    document.querySelectorAll('.edit-availability').forEach(button => {
        button.addEventListener('click', handleEditAvailability);
    });

    document.querySelectorAll('.delete-availability').forEach(button => {
        button.addEventListener('click', handleDeleteAvailability);
    });

    // Añadir el evento al botón de añadir disponibilidad
    const addAvailabilityButton = document.getElementById('add-availability');
    if (addAvailabilityButton) {
        addAvailabilityButton.addEventListener('click', () => {
            jQuery.post(
                ajaxurl,
                {
                    action: 'gm_get_availability_form',
                    date: `${year}-${(month + 1).toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`
                },
                function(response) {
                    if (response.success) {
                        popupContent.innerHTML += `<h4>Crear Nueva Disponibilidad para ${selectedDate.toLocaleDateString()}</h4>`;
                        popupContent.innerHTML += response.data;
                        const availabilityForm = document.getElementById('gm-availability-form');
                        if (availabilityForm) {
                            availabilityForm.addEventListener('submit', handleFormSubmit);
                        }
                    } else {
                        popupContent.innerHTML += `<p>${response.data}</p>`;
                    }
                }
            );
        });
    }
}

    // eliminar disponibilidades en el calendario de grupos musicales
    function handleDeleteAvailability(event) {
        const availabilityId = event.target.closest('button').dataset.id;
        if (availabilityId && confirm('¿Estás seguro de que deseas eliminar esta disponibilidad?')) {
            console.log(`Delete availability with ID: ${availabilityId}`);
            jQuery.post(
                ajaxurl,
                {
                    action: 'gm_delete_availability',
                    availability_id: availabilityId,
                    _wpnonce: gm_availability_nonce // Asegúrate de que esta variable está correctamente definida
                },
                function(response) {
                    if (response.success) {
                        alert('Disponibilidad eliminada exitosamente');
                        location.reload();
                    } else {
                        alert('Error al eliminar disponibilidad: ' + response.data);
                    }
                }
            );
        }
    }    
    
    // editar funcionalidades en el calendario de grupos musicales
    function handleEditAvailability(event) {
        const availabilityId = event.target.closest('button').dataset.id;
        console.log(`Editing availability with ID: ${availabilityId}`); // Añadido para depuración
    
        const availability = availabilities.find(a => a.id == availabilityId);
        if (availability) {
            const newStartTime = prompt("Ingrese la nueva hora de inicio (HH:mm)", new Date(availability.date).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }));
            const newEndTime = prompt("Ingrese la nueva hora de fin (HH:mm)", new Date(availability.end_time).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }));
            if (newStartTime && newEndTime) {
                const datePart = availability.date.split(' ')[0];
                const newStartDateTime = `${datePart} ${newStartTime}:00`;
                const newEndDateTime = `${datePart} ${newEndTime}:00`;
    
                jQuery.post(
                    ajaxurl,
                    {
                        action: 'gm_update_availability',
                        availability_id: availabilityId,
                        start_time: newStartDateTime,
                        end_time: newEndDateTime,
                        _wpnonce: gm_availability_nonce // Asegúrate de que esta variable está correctamente definida
                    },
                    function(response) {
                        if (response.success) {
                            alert('Disponibilidad actualizada exitosamente');
                            location.reload();
                        } else {
                            alert('Error al actualizar disponibilidad: ' + response.data);
                        }
                    }
                );
            }
        }
    }
    

    function loadCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const today = new Date(); // Obtener la fecha y hora actuales del dispositivo

        calendarMonthYear.textContent = currentDate.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });

        calendarDates.innerHTML = '';

        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();

        const paddingDays = firstDay === 0 ? 6 : firstDay - 1;

        for (let i = 0; i < paddingDays; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.classList.add('calendar-date', 'empty');
            calendarDates.appendChild(emptyCell);
        }

        for (let date = 1; date <= lastDate; date++) {
            const dateCell = document.createElement('div');
            dateCell.classList.add('calendar-date');
            dateCell.textContent = date;

            const dayAvailabilities = getDayAvailabilities(year, month, date, availabilityFilter.value);

            const currentDateObj = new Date(year, month, date);

            // Deshabilitar días vencidos
            if (currentDateObj < today.setHours(0, 0, 0, 0)) {
                dateCell.classList.add('past');
                dateCell.style.pointerEvents = 'none'; // Desactivar interacción
                dateCell.style.opacity = '0.2'; // Atenuar visualmente
            } else {
                // Añadir clases en función de las disponibilidades
                if (dayAvailabilities.length > 0) {
                    const hasAvailable = dayAvailabilities.some(avail => avail.contractor_name === null);
                    const hasContracted = dayAvailabilities.some(avail => avail.contractor_name !== null);
                    if (hasAvailable && hasContracted) {
                        dateCell.classList.add('mixed');
                    } else if (hasContracted) {
                        dateCell.classList.add('contracted');
                    } else {
                        dateCell.classList.add('available');
                    }
                }

                // Permitir la interacción en días válidos
                dateCell.addEventListener('click', () => showPopup(year, month, date, dayAvailabilities));
            }

            calendarDates.appendChild(dateCell);
        }
    }


    function getDayAvailabilities(year, month, date, filter) {
        return availabilities.filter(availability => {
            const availabilityDate = new Date(availability.date);
            const isSameDay = (
                availabilityDate.getFullYear() === year &&
                availabilityDate.getMonth() === month &&
                availabilityDate.getDate() === date
            );
            const isContracted = availability.contractor_name !== null;

            if (filter === 'available' && isSameDay) return !isContracted;
            if (filter === 'contracted' && isSameDay) return isContracted;
            if (filter === 'all' && isSameDay) return true;
            return false;
        });
    }

    function handleFormSubmit(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        jQuery.post(
            ajaxurl,
            data,
            function(response) {
                if (response.success) {
                    alert('Disponibilidad agregada exitosamente');
                    location.reload();
                } else {
                    alert('Error al agregar disponibilidad: ' + response.data);
                }
            }
        );
    }


    closePopup.addEventListener('click', () => {
        popup.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === popup) {
            popup.style.display = 'none';
        }
    });

    prevMonth.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        loadCalendar();
    });

    nextMonth.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        loadCalendar();
    });

    loadCalendar();
});