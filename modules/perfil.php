<?php include '../includes/header.php'; ?>

<?php
// LÓGICA DE ROLES (Solo visual para el perfil)
$rol_texto_perfil = 'USUARIO';
switch($_SESSION['rol_id']) {
    case 1: $rol_texto_perfil = 'ADMINISTRADOR'; break;
    case 2: $rol_texto_perfil = 'VENTAS'; break;
    case 3: $rol_texto_perfil = 'DESPACHO'; break;
    case 4: $rol_texto_perfil = 'TÉCNICO'; break;
    default: $rol_texto_perfil = 'USUARIO'; break;
}
?>

<div class="container-fluid px-4">
    <h2 class="mt-4">Mi Perfil</h2>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-header bg-primary text-white">
                    Foto de Perfil
                </div>
                <div class="card-body">
                    <div style="position: relative; display: inline-block;">
                        <img id="imgPreview" 
                             src="<?php echo $avatar_final; ?>" 
                             alt="Avatar" 
                             class="rounded-circle img-thumbnail" 
                             style="width: 150px; height: 150px; object-fit: cover;"
                             onerror="this.src='../assets/img/avatars/default.png'">
                        
                        <label for="inputAvatar" style="position: absolute; bottom: 0; right: 0; background: #0d6efd; color: white; padding: 8px; border-radius: 50%; cursor: pointer; border: 2px solid white;">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>

                    <h5 class="mt-3"><?php echo htmlspecialchars($_SESSION['nombre']); ?></h5>
                    <p class="text-muted mb-1"><?php echo ucfirst($_SESSION['usuario']); ?></p>
                    
                    <span class="badge bg-success" style="font-size: 0.9rem;">
                        <?php echo $rol_texto_perfil; ?>
                    </span>

                    <form id="formAvatar" class="mt-4">
                        <input type="file" id="inputAvatar" name="nuevo_avatar" accept="image/*" style="display: none;">
                        <button type="submit" id="btnGuardar" class="btn btn-outline-primary btn-sm w-100" disabled>
                            <i class="fas fa-save"></i> Guardar Nueva Foto
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Detalles de la Cuenta
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Usuario:</label>
                            <p class="form-control-static"><?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Rol de Sistema:</label>
                            <p class="form-control-static"><?php echo $rol_texto_perfil; ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                         <div class="col-md-6">
                            <label class="fw-bold">ID Interno:</label>
                            <p class="text-muted">#<?php echo $_SESSION['user_id']; ?></p>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Para cambiar tu contraseña o datos personales, contacta al administrador del sistema.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Previsualizar imagen al seleccionar
const input = document.getElementById('inputAvatar');
const preview = document.getElementById('imgPreview');
const btn = document.getElementById('btnGuardar');

input.addEventListener('change', function(e) {
    if(this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result; // Mostrar imagen seleccionada
            btn.disabled = false; // Habilitar botón guardar
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
            btn.innerHTML = '<i class="fas fa-save"></i> Confirmar Cambio';
        }
        reader.readAsDataURL(this.files[0]);
    }
});

// 2. Enviar formulario con AJAX
document.getElementById('formAvatar').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Subiendo...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    // Usamos la ruta relativa correcta hacia la API
    fetch('../api/cambiar-avatar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Foto Actualizada!',
                text: 'Tu perfil se ha actualizado correctamente.',
                confirmButtonText: 'Genial'
            }).then(() => {
                // Recargar para ver el cambio en el Header también
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Hubo un problema con el servidor', 'error');
    });
});
</script>

<?php include '../includes/footer.php'; ?>