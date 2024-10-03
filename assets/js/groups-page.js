document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editGroupModal');
    const closeModal = document.querySelector('.modal .close');
    const saveChangesButton = document.getElementById('saveGroupChanges');
    const createGroupButton = document.getElementById('createGroup');
    let editingAvailabilityId = null;

    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const zoneInput = document.getElementById('zone');
    
    const phoneInput = document.getElementById('phone');
    const durationInput = document.getElementById('duracion');
    const photoInput = document.getElementById('photo');
    const emailContactInput = document.getElementById('email_contact');
    const gmRegisterNonce = gm_group_nonce;

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;

    document.querySelectorAll('.edit-group').forEach(button => {
        button.addEventListener('click', function() {
            editingGroupId = this.dataset.id;
            fetchGroupData(editingGroupId);
            editModal.style.display = 'block';
            
            $('#edit_zone').select2({
                dropdownParent: $('#editGroupModal'),
                placeholder: "--Seleccione Zona--",
            });

        });
        
    });

    document.querySelectorAll('.delete-group').forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.dataset.id;
            if (confirm('¿Estás seguro de eliminar este grupo?')) {
                deleteGroup(groupId);
            }
        });
    });

    closeModal.addEventListener('click', function() {
        editModal.style.display = 'none';
    });

    saveChangesButton.addEventListener('click', function() {
        const groupId = document.getElementById('edit_group_id').value;
        const name = document.getElementById('edit_name').value.trim();
        const description = document.getElementById('edit_description').value.trim();
        
        const zone = document.getElementById('edit_zone');
        const zoneOptions = Array.from(zone.selectedOptions);
        const zoneSelectedValues = zoneOptions.map(option => option.value);
        const stringZones = zoneSelectedValues.join(',');

        const duration = document.getElementById('edit_duration').value;
        const phone = document.getElementById('edit_phone').value.trim();
        const emailContact = document.getElementById('edit_email_contact').value.trim();
        const photo = document.getElementById('edit_photo').files[0];
        // console.log(stringZones,duration);
        
        // Validaciones
        if (name.length === 0) {
            showError('El nombre artístico no puede estar vacío.');
            return;
        }
    
        if (description.length === 0) {
            showError('La descripción no puede estar vacía.');
            return;
        }
    
        if (stringZones.length === 0) {
            showError('Por favor, selecciona Zona geográfica.');
            return;
        }

        if (duration.length === 0) {
            showError('Por favor, seleccione duración del Show.');
            return;
        }
    
        if (phone.length === 0) {
            showError('El teléfono no puede estar vacío.');
            return;
        }
    
        if (!emailPattern.test(emailContact)) {
            showError('Por favor, introduce un correo electrónico de contacto válido.');
            return;
        }
    
        // Crear objeto FormData para enviar archivos
        const formData = new FormData();
        formData.append('action', 'gm_groups_page_update_group');
        formData.append('_wpnonce', gm_group_nonce);
        formData.append('group_id', groupId);
        formData.append('name', name);
        formData.append('description', description);
        formData.append('zone', stringZones);
        formData.append('duration', duration);
        formData.append('phone', phone);
        if (photo) {
            formData.append('photo', photo);
        }
        formData.append('email_contact', emailContact);
    
        // Enviar solicitud AJAX
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Grupo actualizado exitosamente.');
                    location.reload();
                } else {
                    showError('Error al actualizar el grupo: ' + response.data);
                }
            },
            error: function() {
                showError('Error en la solicitud. Por favor, inténtalo de nuevo.');
            }
        });
    });

    createGroupButton.addEventListener('click', function(event) {
        const username = usernameInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const name = nameInput.value.trim();
        const description = descriptionInput.value.trim();
        // const zone = zoneInput.value;
        const zoneOptions = Array.from(zoneInput.selectedOptions);
        const zoneSelectedValues = zoneOptions.map(option => option.value);
        const stringZones = zoneSelectedValues.join(',');

        const phone = phoneInput.value.trim();
        const duration = durationInput.value.trim();
        const photo = photoInput.files[0];
        const emailContact = emailContactInput.value.trim();

        // Validaciones
        if (username.length === 0) {
            showError('El nombre de usuario no puede estar vacío.');
            return;
        }

        if (!emailPattern.test(email)) {
            showError('Por favor, introduce un correo electrónico válido.');
            return;
        }

        if (!passwordPattern.test(password)) {
            showError('La contraseña debe tener al menos 8 caracteres, incluyendo una letra mayúscula, una letra minúscula y un número.');
            return;
        }

        if (name.length === 0) {
            showError('El nombre artístico no puede estar vacío.');
            return;
        }

        if (description.length === 0) {
            showError('La descripción no puede estar vacía.');
            return;
        }

        if (stringZones.length === 0) {
            showError('Por favor, selecciona una Zona geográfica.');
            return;
        }

        
        if (phone.length === 0) {
            showError('El teléfono no puede estar vacío.');
            return;
        }
        
        if (duration.length === 0) {
            showError('Por favor, selecciona una Zona geográfica.');
            return;
        }

        if (!photo) {
            showError('Por favor, selecciona una fotografía.');
            return;
        }

        if (!emailPattern.test(emailContact)) {
            showError('Por favor, introduce un correo electrónico de contacto válido.');
            return;
        }
        // return;

        // Crear objeto FormData para enviar archivos
        const formData = new FormData();
        formData.append('action', 'gm_groups_page_create_group');
        formData.append('_wpnonce', gmRegisterNonce);
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('name', name);
        formData.append('description', description);
        formData.append('zone', stringZones);
        formData.append('phone', phone);
        formData.append('duration', duration);
        formData.append('photo', photo);
        formData.append('email_contact', emailContact);

        
        // Enviar solicitud AJAX
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Grupo creado exitosamente.');
                    location.reload();
                } else {
                    showError('Error al crear el grupo: ' + response.data);
                }
            },
            error: function() {
                showError('Error en la solicitud. Por favor, inténtalo de nuevo.');
            }
        });
    });

    function fetchGroupData(groupId) {
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_groups_page_get_group',
                _wpnonce: gm_group_nonce,
                group_id: groupId
            },
            function(response) {
                if (response.success) {
                    const group = response.data;
                    
                    document.getElementById('edit_group_id').value = group.id;
                    document.getElementById('edit_name').value = group.name;
                    document.getElementById('edit_description').value = group.description;
                    let zonesSelected = document.getElementById('edit_zone');
                    zonesSelected.selectedIndex = -1;
                    // var valoresPredeterminados = "";
                    valoresPredeterminados = group.id_zone.split(',').map(id => id.trim());
                    // console.log(valoresPredeterminados);

                    for (let i = 0; i < zonesSelected.options.length; i++) {
                        if (valoresPredeterminados.includes(zonesSelected.options[i].value)) {
                            zonesSelected.options[i].selected = true; // Marca la opción como seleccionada
                        }
                    }
                
                    $(zonesSelected).trigger('change');

                    document.getElementById('edit_duration').value = group.duration;
                    document.getElementById('edit_phone').value = group.phone;
                    document.getElementById('edit_email_contact').value = group.email;
    
                    // Mostrar la foto actual
                    const currentPhoto = document.getElementById('current_photo');
                    currentPhoto.src = group.photo;
                    currentPhoto.style.display = 'block';
    
                    // Mostrar el modal
                    document.getElementById('editGroupModal').style.display = 'block';
                } else {
                    alert('Error al obtener los datos del grupo: ' + response.data);
                }
            }
        );
    }

    function deleteGroup(groupId) {
        jQuery.post(
            ajaxurl,
            {
                action: 'gm_groups_page_delete_group',
                _wpnonce: gm_group_nonce,
                groupId: groupId
            },
            function(response) {
                if (response.success) {
                    alert('Grupo eliminada exitosamente.');
                    location.reload();
                } else {
                    alert('Error al eliminar el grupo: ' + response.data);
                }
            }
        );
    }

    function showError(message) {
        alert(message);
    }
});
