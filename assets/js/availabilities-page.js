document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editAvailabilityModal');
    const closeModal = document.querySelector('.modal .close');
    const saveChangesButton = document.getElementById('saveAvailabilityChanges');
    const createAvailabilityButton = document.getElementById('createAvailability');
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
    });

    saveChangesButton.addEventListener('click', function() {
        const date = document.getElementById('edit_date').value;
        const endTime = document.getElementById('edit_end_time').value;
        const allDay = document.getElementById('edit_all_day').checked ? 1 : 0;

        jQuery.post(
            ajaxurl,
            {
                action: 'gm_availabilities_page_update_availability',
                _wpnonce: gm_availability_nonce,
                availability_id: editingAvailabilityId,
                date: date,
                end_time: endTime,
                all_day: allDay
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
                    document.getElementById('edit_availability_id').value = availability.id;
                    document.getElementById('edit_date').value = availability.date.replace(' ', 'T');
                    document.getElementById('edit_end_time').value = availability.end_time.replace(' ', 'T');
                    document.getElementById('edit_all_day').checked = availability.all_day == 1;
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