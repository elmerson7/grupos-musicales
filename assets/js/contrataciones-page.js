document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editAvailabilityModal');
    const closeModal = document.querySelector('.modal .close');
    const saveChangesButton = document.getElementById('saveAvailabilityChanges');
    const createAvailabilityButton = document.getElementById('createAvailability');

    const createGroupSelect = document.getElementById('create_group_id');
    const createAvailabilitySelect = document.getElementById('create_availability_id');

    let editingAvailabilityId = null;

    createGroupSelect.addEventListener('change', function() {
        const groupId = this.value;

        jQuery.post(
            ajaxurl,
            {
                action: 'gm_get_availabilities_by_group',
                _wpnonce: gm_contratacion_nonce,
                group_id: groupId
            },
            function(response) {
                // console.log(response.data);
                if (response.success) {
                    
                    // Limpiar opciones actuales
                    createAvailabilitySelect.innerHTML = '';

                    // Agregar nuevas opciones de disponibilidades
                    response.data.forEach(availability => {
                        const option = document.createElement('option');
                        option.value = availability.id;
                        option.textContent = `${availability.name_zone} ::: ${availability.date} - ${availability.end_time}`;
                        createAvailabilitySelect.appendChild(option);
                    });
                } else {
                    alert('Error al cargar disponibilidades: ' + response.data);
                }
            }
        );
    });

    document.getElementById('createContract').addEventListener('click', function() {
        const contractorId = document.getElementById('create_contractor_id').value;
        const groupId = createGroupSelect.value;
        const availabilityId = createAvailabilitySelect.value;

        if (!contractorId || !groupId || !availabilityId) {
            alert('Por favor, selecciona todos los campos.');
            return;
        }

        jQuery.post(
            ajaxurl,
            {
                action: 'gm_create_contract',
                _wpnonce: gm_contratacion_nonce,
                contractor_id: contractorId,
                group_id: groupId,
                availability_id: availabilityId
            },
            function(response) {
                if (response.success) {
                    alert('Contratación creada exitosamente.');
                    location.reload();
                } else {
                    alert('Error al crear la contratación: ' + response.data);
                }
            }
        );
    });

    document.querySelectorAll('.delete-contract').forEach(button => {
        button.addEventListener('click', function() {
            const contractId = this.dataset.id;
            console.log(contractId);
            if (confirm('¿Estás seguro de eliminar este grupo?')) {
                deleteContract(contractId);
            }
        });
    });
    
    function deleteContract(contractId) {
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_groups_page_delete_contract',
                _wpnonce: gm_contratacion_nonce,
                contractId: contractId
            },
            function(response) {
                if (response.success) {
                    alert('Contrato eliminado exitosamente.');
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        );
    }

});