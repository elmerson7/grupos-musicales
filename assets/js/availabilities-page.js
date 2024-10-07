document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editAvailabilityModal');
    const closeModal = document.querySelector('.modal .close');
    const saveChangesButton = document.getElementById('saveAvailabilityChanges');
    const createAvailabilityButton = document.getElementById('createAvailability');
    const selectGroup = document.getElementById('create_group_id');
    const form = document.getElementById('edit-availability-form');
    let editingAvailabilityId = null;

    document.querySelectorAll('.edit-availability').forEach(button => {
        button.addEventListener('click', function() {
            editingAvailabilityId = this.dataset.id;
            fetchAvailabilityData(editingAvailabilityId);
            editModal.style.display = 'block';
        });
    });

    document.querySelectorAll('.delete-availability').forEach(button => {
        button.addEventListener('click', function() {
            const availabilityId = this.dataset.id;
            if (confirm('¿Estás seguro de eliminar esta disponibilidad?')) {
                deleteAvailability(availabilityId);
            }
        });
    });

    closeModal.addEventListener('click', function() {
        editModal.style.display = 'none';
        saveChangesButton.style.display = "none";
        form.reset();
    });

    saveChangesButton.addEventListener('click', function() {
        const date = document.getElementById('edit_date').value;
        const zone = document.getElementById('zone_edit').value;
        const endTime = document.getElementById('edit_end_time').value;
        const allDay = document.getElementById('edit_all_day').checked ? 1 : 0;
        // console.log(zone,date,endTime,allDay);
        // return
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_availabilities_page_update_availability',
                _wpnonce: gm_availability_nonce,
                availability_id: editingAvailabilityId,
                date: date,
                end_time: endTime,
                all_day: allDay,
                id_zone: zone
            },
            function(response) {
                if (response.success) {
                    alert('Disponibilidad actualizada exitosamente.');
                    location.reload();
                } else {
                    alert('Error al actualizar la disponibilidad: ' + response.data);
                }
            }
        );
    });

    selectGroup.addEventListener('change', function() {
        const groupId = selectGroup.value;
        const contendorOptions = document.getElementById('create_zone');
        contendorOptions.innerHTML = "";
        // console.log(groupId);
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_extract_zones_per_group',
                _wpnonce: gm_availability_nonce,
                group_id: groupId,
            },
            function(response) {
                // console.log(response.data);
                if (response.success) {
                    // console.log(response.data);
                    let template = "<option value='' disabled selected>--Seleccione Zona--</option>";
                    let data = response.data
                    data.forEach(element => {                       
                        template += `<option value="${element.id}">${element.name_zone}</option>`
                    });
                    contendorOptions.innerHTML = template;
                } else {
                    alert('Error al cargar zonas: ' + response.data);
                }
            }
        );
    });

    createAvailabilityButton.addEventListener('click', function() {
        const groupId = document.getElementById('create_group_id').value;
        const date = document.getElementById('create_date').value;
        const endTime = document.getElementById('create_end_time').value;
        const allDay = document.getElementById('create_all_day').checked ? 1 : 0;

        jQuery.post(
            ajaxurl,
            {
                action: 'gm_availabilities_page_create_availability',
                _wpnonce: gm_availability_nonce,
                group_id: groupId,
                date: date,
                end_time: endTime,
                all_day: allDay
            },
            function(response) {
                if (response.success) {
                    alert('Disponibilidad creada exitosamente.');
                    location.reload();
                } else {
                    alert('Error al crear la disponibilidad: ' + response.data);
                }
            }
        );
    });

    function fetchAvailabilityData(availabilityId) {
        const zoneEdit = document.getElementById('zone_edit');
        zoneEdit.innerHTML = "";+
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_availabilities_page_get_availability',
                _wpnonce: gm_availability_nonce,
                availability_id: availabilityId
            },
            function(response) {
                if (response.success) {
                    const availability = response.data;
                    // console.log(availability);
                    jQuery.post(
                        ajaxurl,
                        {
                            action: 'gm_extract_zones_per_group',
                            _wpnonce: gm_availability_nonce,
                            group_id: availability.group_id,
                        },
                        function(response) {
                            // console.log(response.data);
                            if (response.success) {
                                let template = "<option value='' disabled>--Seleccione Zona--</option>";
                                let data = response.data
                                data.forEach(element => {    
                                    if (element.id === availability.id_zone) {
                                        template += `<option selected value="${element.id}">${element.name_zone}</option>`
                                    }else{
                                        template += `<option value="${element.id}">${element.name_zone}</option>`
                                    }                   
                                });
                                zoneEdit.innerHTML = template;
                                document.getElementById('edit_availability_id').value = availability.id;
                                document.getElementById('edit_date').value = availability.date.replace(' ', 'T');
                                document.getElementById('edit_end_time').value = availability.end_time.replace(' ', 'T');
                                document.getElementById('edit_all_day').checked = availability.all_day == 1;
                                saveChangesButton.style.display = "";
                            } else {
                                alert('Error al cargar zonas: ' + response.data);
                            }
                        }
                    );
                    // console.log(availability.id_zone,zoneEdit.value);
                    // zoneEdit.value = availability.id_zone;
                    // const event = new Event('change', { bubbles: true });
                    // zoneEdit.dispatchEvent(event);

                } else {
                    alert('Error al obtener la disponibilidad: ' + response.data);
                }
            }
        );
    }

    function deleteAvailability(availabilityId) {
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_availabilities_page_delete_availability',
                _wpnonce: gm_availability_nonce,
                availability_id: availabilityId
            },
            function(response) {
                if (response.success) {
                    alert('Disponibilidad eliminada exitosamente.');
                    location.reload();
                } else {
                    alert('Error al eliminar la disponibilidad: ' + response.data);
                }
            }
        );
    }
});