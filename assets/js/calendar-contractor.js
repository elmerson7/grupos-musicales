document.addEventListener('DOMContentLoaded', function() {
    const calendarDates = document.getElementById('calendar-dates');
    const calendarMonthYear = document.getElementById('calendar-month-year');
    const prevMonth = document.getElementById('prev-month');
    const nextMonth = document.getElementById('next-month');
    const popup = document.getElementById('availability-popup');
    const popupContent = document.getElementById('popup-content');
    const closePopup = document.querySelector('.close-popup');
    const filterSelect = document.getElementById('availability-filter');
    const zoneSelect = document.getElementById('zone-filter');

    let currentDate = new Date();
    let availabilities = [];

    // Función para cargar disponibilidades desde el servidor
    function loadAvailabilities() {
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_get_contractor_availabilities',
                security: gm_contract_nonce
            },
            function(response) {
                if (response.success) {
                    availabilities = response.data;

                    loadCalendar(); // Cargar el calendario con las disponibilidades obtenidas
                } else {
                    alert('Error al cargar disponibilidades: ' + response.data);
                }
            }
        );
    }

    function loadCalendar() {       
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        let contadorContracted = 0;
        
        calendarMonthYear.textContent = currentDate.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
        
        calendarDates.innerHTML = '';
        
        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();
        const lastDay = new Date(year, month + 1, 0).getDay();
        
        const paddingDays = firstDay === 0 ? 6 : firstDay - 1;
        const lastPaddings = lastDay !== 0 ? 7-lastDay : 0;        

        for (let i = 0; i < paddingDays; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.classList.add('calendar-date', 'empty');
            calendarDates.appendChild(emptyCell);
        }
        
        const filter = filterSelect.value;
        const id_zone = zoneSelect.value;

        for (let date = 1; date <= lastDate; date++) {
            const dateCell = document.createElement('div');
            dateCell.classList.add('calendar-date');
            dateCell.textContent = date;
            
            const dayAvailabilities = getDayAvailabilities(year, month, date, filter, id_zone);
            
            if (dayAvailabilities.length > 0) {
                const isContracted = dayAvailabilities.some(a => a.contractor_name === wp_current_user_name);
                if (isContracted) {
                    contadorContracted++;
                }
            
                dateCell.classList.add(isContracted ? 'contracted' : 'available');
            }

            dateCell.addEventListener('click', () => showPopupC(year, month, date, dayAvailabilities, contadorContracted));
            calendarDates.appendChild(dateCell);
        }

        if (lastPaddings !== 0) {
            for (let index = 0; index < lastPaddings; index++) {
                const emptyCell = document.createElement('div');
                emptyCell.classList.add('calendar-date', 'empty');
                calendarDates.appendChild(emptyCell);
            }
        }
        if (contadorContracted>=2) {
            console.log('Has realizado el máximo de contrataciones este mes');
        }
    }

    function getDayAvailabilities(year, month, date, filter, id_zone) {
        return availabilities.filter(availability => {
            const availabilityDate = new Date(availability.date);
            const isContractedByMe = availability.contractor_name === wp_current_user_name;
            
            if (
                availabilityDate.getFullYear() === year &&
                availabilityDate.getMonth() === month &&
                availabilityDate.getDate() === date 
            ) {

                if (filter === 'available' && !availability.contractor_name) {
                    if (id_zone === availability.id_zone) {
                        return true;
                    }else if(id_zone === 'all'){
                        return true;
                    }
                } else if (filter === 'contracted' && isContractedByMe) {
                    if (id_zone === availability.id_zone) {
                        return true;
                    }else if(id_zone === 'all'){
                        return true;
                    }
                } else if (filter === 'all') {
                    if (id_zone === availability.id_zone) {
                        return true;
                    }else if(id_zone === 'all'){
                        return true;
                    }
                }
            }
            return false;
        });
    }

    function showPopupC(year, month, date, dayAvailabilities, contadorContracted) {
        const selectedDate = new Date(year, month, date);
        popupContent.innerHTML = ''; 
        
        if (dayAvailabilities.length > 0) {
            popupContent.innerHTML = `<h2>Disponibilidades para ${selectedDate.toLocaleDateString()}</h2>`;
            dayAvailabilities.forEach(availability => {
                let contractButton = '';
                let deleteContract = '';
                if (contadorContracted >= 2 && !availability.contractor_name) {
                    contractButton = ``;
                }else{
                    contractButton = `<button class="contract-button" data-availability-id="${availability.id}">Contratar</button>`;
                }
                
                if (availability.contractor_name && availability.contractor_name === wp_current_user_name) {
                    deleteContract = `<button class="delete-availabilityC" data-contracted-id="${availability.id}"><i class="fas fa-trash-alt"></i></button>`;
                }
    
                popupContent.innerHTML += `
                    <div class="availability-item ${availability.contractor_name ? 'contracted' : ''}">
                        <div class="availability-header">
                            <img src="${availability.photo}" alt="${availability.group_name}" class="availability-photo"/>
                            <div class="availability-info">
                                <p><strong>Grupo Musical:</strong> ${availability.group_name}</p>
                                <p><strong>Descripción:</strong> ${availability.description}</p>
                                <p><strong>Región:</strong> ${availability.name_zone}</p>
                            </div>
                        </div>
                        <div class="availability-details"> <!-- Contenedor flexible -->
                            <div class="availability-action">
                                ${contractButton}
                                ${deleteContract}
                            </div>
                            <div class="availability-time">
                                <p><strong>Inicio:</strong> ${new Date(availability.date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                                <p><strong>Fin:</strong> ${new Date(availability.end_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
    
            // Agregar eventos a los botones de contratación
            document.querySelectorAll('.contract-button').forEach(button => {
                button.addEventListener('click', () => {
                    const availabilityId = button.dataset.availabilityId;
                    contractAvailability(availabilityId);
                });
            });
            // Agregar eventos a los botones de contratación
            document.querySelectorAll('.delete-availabilityC').forEach(button => {
                button.addEventListener('click', () => {
                    const contract = button.dataset.contractedId;
                    deleteContract(contract);
                });
            });
        } else {
            popupContent.innerHTML = `<h2>No hay disponibilidades para ${selectedDate.toLocaleDateString()}</h2>`;
        }
        popup.style.display = 'block';
    }

    function deleteContract(contractId) {
        if (confirm('¿Estás seguro de que deseas eliminar este contrato?')) {
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'gm_delete_contract',
                    contract_id: contractId,
                    _ajax_nonce: gm_contract_nonce // Añadir el nonce para seguridad
                },
                success: function(response) {
                    if (response.success) {
                        alert('Contrato eliminado exitosamente.');
                        location.reload();
                        // Actualizar el calendario o la UI aquí si es necesario
                    } else {
                        alert('Error al eliminar el contrato: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Ocurrió un error al intentar eliminar el contrato.');
                }
            });
        }
    }
    
    
    function contractAvailability(availabilityId) {
        if (confirm('¿Estás seguro de que quieres contratar esta disponibilidad?')) {
            jQuery.post(
                gm_ajax.ajaxurl,
                {
                    action: 'gm_contract',
                    availability_id: availabilityId,
                    _wpnonce: gm_ajax.nonce
                },
                function(response) {
                    if (response.success) {
                        alert('Contratación exitosa');
                        location.reload();
                    } else {
                        alert('Error en la contratación');
                    }
                }
            );
        }
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

    filterSelect.addEventListener('change', loadCalendar);
    zoneSelect.addEventListener('change', loadCalendar);

    // loadCalendar();
    loadAvailabilities();
});