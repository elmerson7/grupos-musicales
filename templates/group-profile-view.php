<?php
$data_music_group = $wpdb->get_results($wpdb->prepare("SELECT a.id user_id,b.photo,b.name,b.description,b.region, b.email, b.phone, c.name_zone, c.id id_zone FROM {$wpdb->prefix}users a INNER JOIN {$wpdb->prefix}gm_groups b on a.ID = b.user_id INNER JOIN {$wpdb->prefix}gm_zones c on b.id_zone = c.id where a.id = %d", $user_id));
$zones = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gm_zones WHERE status = 1");

if ($wpdb->last_error) {
    echo 'Error en la consulta: ' . $wpdb->last_error;
}
?>
<div class="container">
    <div class="band-info">
        <!-- Imagen del grupo musical -->
        <div class="band-image">
            <img src="<?=$data_music_group[0]->photo?>" alt="Foto del Grupo Musical ">
        </div>

        <!-- Información textual -->
        <div class="band-details">
            <h1 class="band-name" id="name_edit"><?=strtoupper($data_music_group[0]->name)?></h1>
            <p class="band-description" id="description_edit"><?=$data_music_group[0]->description?></p>
            <div class="additional-info">
                <p><strong>Región: </strong><span id="zone_edit"><?=$data_music_group[0]->name_zone?></span></p>
                <p><strong>Email de Contacto: </strong><span id="email_edit"><?=$data_music_group[0]->email?></span></p>
                <p><strong>Teléfono de Contacto: </strong><span  id="phone_edit"><?=$data_music_group[0]->phone?></span></p>
            </div>
        </div>
        
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal">
            Editar Perfil
        </button>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cancelButton()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <!-- Imagen -->
                    <input type="hidden" id="user_id_group" value="<?=$data_music_group[0]->user_id?>">
                    <div class="mb-3 text-center">
                        <label for="profileImage" class="form-label">Imagen de perfil</label><br>
                        <img src="<?=$data_music_group[0]->photo?>" id="profileImagePreview" class="img-thumbnail mb-2" alt="Imagen de perfil" width="150">
                        <input class="form-control" type="file" id="profileImage" accept="image/*" onchange="loadFile(event)">
                    </div>
                    
                    <!-- Nombre -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" placeholder="Ingrese su nombre" value="<?=$data_music_group[0]->name?>">
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripcion</label>
                        <textarea  class="form-control" id="descripcion"><?=$data_music_group[0]->description?></textarea>
                    </div>
                    
                    <!-- Zona -->
                    <div class="mb-3">
                        <label for="zona" class="form-label">Zona</label>
                        <select class="form-select" id="zona">
                            <option value ="">Seleccione su Zona</option>
                            <?php foreach($zones as $zone): ?>
                                <?php if($zone->id == $data_music_group[0]->id_zone): ?>
                                    <option value="<?=$zone->id?>" selected><?=$zone->name_zone?></option>
                                <?php else: ?>
                                    <option value="<?=$zone->id?>"><?=$zone->name_zone?></option>
                                <?php endif ?>
                            <?php endforeach ?>
                        </select>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Ingrese su email" value="<?=$data_music_group[0]->email?>">
                    </div>
                    
                    <!-- Teléfono -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="phone" placeholder="Ingrese su teléfono" value="<?=$data_music_group[0]->phone?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cancelButton()"  data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveChanges()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Importar CSS -->
<link rel="stylesheet" type="text/css" href="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/css/group-profile-view.css'); ?>">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<?php
// Generar el nonce en PHP
$nonce = wp_create_nonce('update_profile_nonce');
?>

<!-- Importar JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    var nonce = "<?php echo $nonce; ?>";

    function loadFile(event) {
        var output = document.getElementById('profileImagePreview');
        output.src = URL.createObjectURL(event.target.files[0]);
        output.onload = function() {
            URL.revokeObjectURL(output.src);
        }
    }
    
    function validateEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }

    function saveChanges() {
        // const image = document.getElementById('profileImage').value;
        const validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const image = document.getElementById('profileImage');
        // const fileName = profileImage.split('\\').pop().split('/').pop().trim();
        const profileImage = image.files[0]; 
        const name = document.getElementById('name').value.trim();
        const descripcion = document.getElementById('descripcion').value.trim();
        const zona = document.getElementById('zona').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const user_id = document.getElementById('user_id_group').value.trim();
        const formData = new FormData();
        
        formData.append('security', nonce);
        formData.append('action', 'update_profile');
        // Aquí puedes procesar los datos, enviarlos al servidor, etc.
        // console.log('Nombre:', name);
        // console.log('Zona:', zona);
        // console.log('Email:', email);
        // console.log('Teléfono:', phone);
      
        if (!validateEmail(email)) {
            alert('Ingrese un email válido');
        }else{
            let arrUpdate = {}; 
            
            // arrUpdate.user_id = user_id;
            formData.append('user_id_group', user_id);

            if (profileImage) {
                if (validImageTypes.includes(profileImage.type)) {
                    formData.append('profileImage', profileImage); 
                }else{
                    alert("Error en el formato del Archivo");
                }
            }
    
            if (name != '<?=$data_music_group[0]->name?>' && name != '') {
                // arrUpdate.name = name;
                formData.append('name', name);
            }

            if (descripcion != '<?=$data_music_group[0]->description?>' && descripcion != '') {
                formData.append('descripcion', descripcion);
            }
        
            if (zona != '<?=$data_music_group[0]->id_zone?>' && zona != '') {
                // arrUpdate.id_zone = zona;
                formData.append('id_zone', zona);
            }
        
            if (email != '<?=$data_music_group[0]->email?>' && email != '') {
                // arrUpdate.email = email;
                formData.append('email', email);
            }
    
            if (phone != '<?=$data_music_group[0]->phone?>' && phone != '') {
                // arrUpdate.phone = phone;
                formData.append('phone', phone);
            }

            formData.forEach((value,key) => {
                console.log(key,value);
                
            });
            // return;
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                }
            });

            // Cerrar el modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            modal.hide();
        }

    }

    function cancelButton(){
        const previewImage = document.getElementById('profileImagePreview');
        const profileImage = document.getElementById('profileImage');
        const name = document.getElementById('name');
        const zona = document.getElementById('zona');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');

        previewImage.setAttribute('src','<?=$data_music_group[0]->photo?>');
        profileImage.value = '';
        name.value = '<?=$data_music_group[0]->name?>';
        zona.value = '<?=$data_music_group[0]->id_zone?>';
        email.value = '<?=$data_music_group[0]->email?>';
        phone.value = '<?=$data_music_group[0]->phone?>';
    }



</script>