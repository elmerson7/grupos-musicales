document.addEventListener('DOMContentLoaded', function() {
    const calendarDates = document.getElementById('calendar-dates');
    const calendarMonthYear = document.getElementById('calendar-month-year');
    const prevMonth = document.getElementById('prev-month');
    const nextMonth = document.getElementById('next-month');
    const popup = document.getElementById('availability-popup');
    const popupContent = document.getElementById('popup-content');
    const closePopup = document.querySelector('.close-popup');
    const filterSelect = document.getElementById('availability-filter');
    const areaSelect = document.getElementById('area-filter');

    let currentDate = new Date();
    const availabilities = JSON.parse(document.getElementById('contractor-calendar').dataset.availabilities);

    function loadCalendar() {       
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
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
        const area = areaSelect.value;
        
        for (let date = 1; date <= lastDate; date++) {
            const dateCell = document.createElement('div');
            dateCell.classList.add('calendar-date');
            dateCell.textContent = date;
            
            const dayAvailabilities = getDayAvailabilities(year, month, date, filter, area);
            
            if (dayAvailabilities.length > 0) {
                const isContracted = dayAvailabilities.some(a => a.contractor_name === wp_current_user_name);
                dateCell.classList.add(isContracted ? 'contracted' : 'available');
            }

            dateCell.addEventListener('click', () => showPopup(year, month, date, dayAvailabilities));
            calendarDates.appendChild(dateCell);
        }

        if (lastPaddings !== 0) {
            for (let index = 0; index < lastPaddings; index++) {
                const emptyCell = document.createElement('div');
                emptyCell.classList.add('calendar-date', 'empty');
                calendarDates.appendChild(emptyCell);
            }
        }

    }

    function getDayAvailabilities(year, month, date, filter, area) {
        return availabilities.filter(availability => {
            const availabilityDate = new Date(availability.date);
            const isContractedByMe = availability.contractor_name === wp_current_user_name;
            
            if (
                availabilityDate.getFullYear() === year &&
                availabilityDate.getMonth() === month &&
                availabilityDate.getDate() === date 
            ) {
                if (filter === 'available' && !availability.contractor_name) {
                    if (area === availability.name_area) {
                        return true;
                    }else if(area === 'all'){
                        return true;
                    }
                } else if (filter === 'contracted' && isContractedByMe) {
                    if (area === availability.name_area) {
                        return true;
                    }else if(area === 'all'){
                        return true;
                    }
                } else if (filter === 'all') {
                    if (area === availability.name_area) {
                        return true;
                    }else if(area === 'all'){
                        return true;
                    }
                }
            }
            return false;
        });
    }

    function showPopup(year, month, date, dayAvailabilities) {
        const selectedDate = new Date(year, month, date);
        popupContent.innerHTML = '';

        if (dayAvailabilities.length > 0) {
            popupContent.innerHTML = `<h2>Disponibilidades para ${selectedDate.toLocaleDateString()}</h2>`;
            dayAvailabilities.forEach(availability => {
                const contractButton = availability.contractor_name ? '' : `<button class="contract-button" data-availability-id="${availability.id}">Contratar</button>`;
                popupContent.innerHTML += `
                    <div class="availability-item ${availability.contractor_name ? 'contracted' : ''}">
                        <p><strong>Grupo Musical:</strong> ${availability.group_name}</p>
                        <p><strong>Región:</strong> ${availability.name_area}</p>
                        <p><strong>Inicio:</strong> ${new Date(availability.date).toLocaleTimeString()}</p>
                        <p><strong>Fin:</strong> ${new Date(availability.end_time).toLocaleTimeString()}</p>
                        ${contractButton}
                    </div>
                `;
            });

            document.querySelectorAll('.contract-button').forEach(button => {
                button.addEventListener('click', () => {
                    const availabilityId = button.dataset.availabilityId;
                    contractAvailability(availabilityId);
                });
            });
        } else {
            popupContent.innerHTML = `<h2>No hay disponibilidades para ${selectedDate.toLocaleDateString()}</h2>`;
        }

        popup.style.display = 'block';
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
    areaSelect.addEventListener('change', loadCalendar);

    loadCalendar();
});