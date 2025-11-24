<?php 
require_once '../../includes/session.php';
require_once '../../config/database.php';

// Verificar que sea administrador
if(!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: ../dashboard.php");
    exit();
}

$db = (new Database())->getConnection();

// Obtener todos los usuarios
$query = $db->query("SELECT * FROM vista_usuarios_completa ORDER BY fecha_creacion DESC");
$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);

// Obtener roles para el formulario
$queryRoles = $db->query("SELECT * FROM roles ORDER BY id");
$roles = $queryRoles->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<style>
/* Estilos adicionales para la gestión de usuarios */
.user-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.user-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.user-card.admin { border-left-color: #8B5CF6; }
.user-card.ventas { border-left-color: #3B82F6; }
.user-card.despacho { border-left-color: #10B981; }
.user-card.tecnico { border-left-color: #F59E0B; }

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #E5E7EB;
}

.user-info {
    flex: 1;
    margin-left: 20px;
}

.user-actions {
    display: flex;
    gap: 8px;
}

.badge-role {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-admin { background: #EDE9FE; color: #7C3AED; }
.badge-ventas { background: #DBEAFE; color: #1E40AF; }
.badge-despacho { background: #D1FAE5; color: #065F46; }
.badge-tecnico { background: #FEF3C7; color: #92400E; }

.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    margin: 2% auto; /* Margen ajustado */
    padding: 30px;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalSlide 0.3s ease;
    max-height: 90vh; 
    overflow-y: auto;
}

@keyframes modalSlide {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.close-modal {
    float: right;
    font-size: 28px;
    font-weight: bold;
    color: #999;
    cursor: pointer;
    line-height: 20px;
}

.close-modal:hover { color: #333; }

.filtro-activo {
    background: var(--primary-color, #4F46E5) !important; 
    color: white !important;
}
</style>

<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 30px;">
    <h1 style="color: white; margin-bottom: 10px;">
        <i class="fas fa-users-cog"></i> Gestión de Usuarios
    </h1>
    <p style="opacity: 0.9;">Administra los usuarios del sistema y sus permisos</p>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h3 style="margin: 0; color: var(--primary-dark);">
                <i class="fas fa-list"></i> Usuarios Registrados
            </h3>
            <p style="color: #6c757d; font-size: 0.9rem; margin-top: 5px;">
                Total: <?php echo count($usuarios); ?> usuarios
            </p>
        </div>
        <button onclick="abrirModalNuevo()" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </button>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <button onclick="filtrarRol('todos')" class="btn btn-secondary btn-sm filtro-activo" data-filtro="todos">
            <i class="fas fa-users"></i> Todos
        </button>
        <button onclick="filtrarRol('admin')" class="btn btn-secondary btn-sm" data-filtro="admin">
            <i class="fas fa-crown"></i> Admin
        </button>
        <button onclick="filtrarRol('ventas')" class="btn btn-secondary btn-sm" data-filtro="ventas">
            <i class="fas fa-shopping-cart"></i> Ventas
        </button>
        <button onclick="filtrarRol('despacho')" class="btn btn-secondary btn-sm" data-filtro="despacho">
            <i class="fas fa-boxes"></i> Despacho
        </button>
        <button onclick="filtrarRol('tecnico')" class="btn btn-secondary btn-sm" data-filtro="tecnico">
            <i class="fas fa-tools"></i> Técnicos
        </button>
    </div>

    <div id="listaUsuarios">
        <?php foreach($usuarios as $user): 
            // LÓGICA DE IMAGEN CORREGIDA PARA LA LISTA
            $avatar_file = $user['avatar'];
            
            // Si la BD tiene ruta completa, la limpiamos para quedarnos solo con el nombre
            // O si ya es solo nombre, lo usamos.
            $avatar_name = basename($avatar_file);
            
            // Construimos la ruta relativa correcta desde admin hacia assets
            // Ruta: ../../assets/img/avatars/nombre.jpg
            $avatar_path = "../../assets/img/avatars/" . $avatar_name;
        ?>
        <div class="user-card <?php echo $user['rol_nombre']; ?>" data-rol="<?php echo $user['rol_nombre']; ?>">
            <div style="display: flex; align-items: center;">
                <img src="<?php echo $avatar_path; ?>" 
                     alt="Avatar" 
                     class="user-avatar"
                     onerror="this.src='../../assets/img/avatars/default.png'">
                
                <div class="user-info">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <h4 style="margin: 0; color: var(--primary-dark);">
                            <?php echo $user['nombre_completo']; ?>
                        </h4>
                        <span class="badge-role badge-<?php echo $user['rol_nombre']; ?>">
                            <?php echo $user['rol_nombre']; ?>
                        </span>
                        <?php if($user['activo'] == 0): ?>
                        <span style="background: #FEE2E2; color: #991B1B; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem;">
                            <i class="fas fa-ban"></i> Inactivo
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; gap: 20px; color: #6c757d; font-size: 0.85rem;">
                        <span><i class="fas fa-user"></i> <?php echo $user['usuario']; ?></span>
                        <span><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></span>
                        <?php if($user['telefono']): ?>
                        <span><i class="fas fa-phone"></i> <?php echo $user['telefono']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 5px; font-size: 0.8rem; color: #6c757d;">
                        <i class="fas fa-clock"></i> Último acceso: 
                        <?php echo $user['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($user['ultimo_acceso'])) : 'Nunca'; ?>
                    </div>
                </div>
                
                <div class="user-actions">
                    <button onclick="editarUsuario(<?php echo $user['id']; ?>)" 
                            class="btn btn-secondary btn-sm"
                            title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    
                    <?php if($user['activo'] == 1): ?>
                    <button onclick="toggleEstado(<?php echo $user['id']; ?>, 0)" 
                            class="btn btn-secondary btn-sm"
                            style="background: #FEE2E2; color: #991B1B;"
                            title="Desactivar">
                        <i class="fas fa-ban"></i>
                    </button>
                    <?php else: ?>
                    <button onclick="toggleEstado(<?php echo $user['id']; ?>, 1)" 
                            class="btn btn-secondary btn-sm"
                            style="background: #D1FAE5; color: #065F46;"
                            title="Activar">
                        <i class="fas fa-check"></i>
                    </button>
                    <?php endif; ?>
                    
                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                    <button onclick="eliminarUsuario(<?php echo $user['id']; ?>)" 
                            class="btn btn-secondary btn-sm"
                            style="background: #991B1B; color: white;"
                            title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="modalUsuario" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="cerrarModal()">&times;</span>
        <h2 id="modalTitulo" style="color: var(--primary-dark); margin-bottom: 25px;">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </h2>
        
        <form id="formUsuario" enctype="multipart/form-data">
            <input type="hidden" id="usuario_id" name="usuario_id">
            
            <div class="text-center mb-4">
                <div style="position: relative; display: inline-block;">
                    <img id="previewAvatar" src="../../assets/img/avatars/default.png" 
                         class="rounded-circle border shadow-sm" 
                         style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;"
                         onclick="document.getElementById('inputFoto').click()"
                         onerror="this.src='../../assets/img/avatars/default.png'">
                    
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 35px; height: 35px; position: absolute; bottom: 0; right: 0; cursor: pointer; border: 2px solid white;"
                         onclick="document.getElementById('inputFoto').click()">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Click en la imagen para cambiar</small>
                </div>
                <input type="file" id="inputFoto" name="avatar" accept="image/*" style="display: none;" onchange="mostrarPrevisualizacion(this)">
            </div>
            <div class="form-grid grid-2">
                <div class="form-group">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control" required>
                </div>
            </div>
            
            <div class="form-grid grid-2">
                <div class="form-group">
                    <label class="form-label">Apellido Materno</label>
                    <input type="text" name="apellido_materno" id="apellido_materno" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Usuario (Login)</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" required>
                </div>
            </div>
            
            <div class="form-grid grid-2">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control">
                </div>
            </div>
            
            <div class="form-grid grid-2">
                <div class="form-group">
                    <label class="form-label">Rol</label>
                    <select name="rol_id" id="rol_id" class="form-control" required>
                        <?php foreach($roles as $rol): ?>
                        <option value="<?php echo $rol['id']; ?>">
                            <?php echo ucfirst($rol['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="activo" id="activo" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group" id="passwordGroup">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" minlength="6">
                <small style="color: #6c757d;">Mínimo 6 caracteres. Déjalo vacío para mantener la contraseña actual (en edición)</small>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Guardar Usuario
                </button>
                <button type="button" onclick="cerrarModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Función para previsualizar imagen en el modal
function mostrarPrevisualizacion(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewAvatar').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Filtrar por rol
function filtrarRol(rol) {
    const cards = document.querySelectorAll('.user-card');
    const botones = document.querySelectorAll('[data-filtro]');
    
    botones.forEach(btn => btn.classList.remove('filtro-activo'));
    document.querySelector(`[data-filtro="${rol}"]`).classList.add('filtro-activo');
    
    cards.forEach(card => {
        if(rol === 'todos' || card.dataset.rol === rol) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Abrir modal para nuevo usuario
function abrirModalNuevo() {
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Usuario';
    document.getElementById('formUsuario').reset();
    document.getElementById('usuario_id').value = '';
    document.getElementById('password').required = true;
    // Resetear imagen a default
    document.getElementById('previewAvatar').src = "../../assets/img/avatars/default.png";
    document.getElementById('modalUsuario').style.display = 'block';
}

// Editar usuario
function editarUsuario(id) {
    fetch(`../../api/admin/get-usuario.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const u = data.usuario;
            document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit"></i> Editar Usuario';
            document.getElementById('usuario_id').value = u.id;
            document.getElementById('nombre').value = u.nombre;
            document.getElementById('apellido_paterno').value = u.apellido_paterno;
            document.getElementById('apellido_materno').value = u.apellido_materno || '';
            document.getElementById('usuario').value = u.usuario;
            document.getElementById('email').value = u.email;
            document.getElementById('telefono').value = u.telefono || '';
            document.getElementById('rol_id').value = u.rol_id;
            document.getElementById('activo').value = u.activo;
            document.getElementById('password').required = false;
            document.getElementById('password').value = '';
            
            // LOGICA CORREGIDA PARA LA IMAGEN EN EL MODAL
            if(u.avatar) {
                // Limpiamos la ruta por si viene con ../ o assets/ duplicados
                // Simplemente tomamos el nombre del archivo
                let avatarName = u.avatar.split('/').pop();
                
                if (!avatarName || avatarName === 'default-avatar.png') {
                     avatarName = 'default.png';
                }

                document.getElementById('previewAvatar').src = "../../assets/img/avatars/" + avatarName;
            } else {
                document.getElementById('previewAvatar').src = "../../assets/img/avatars/default.png";
            }

            document.getElementById('modalUsuario').style.display = 'block';
        }
    });
}

function cerrarModal() {
    document.getElementById('modalUsuario').style.display = 'none';
}

// Enviar formulario
document.getElementById('formUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const userId = document.getElementById('usuario_id').value;
    const url = userId ? '../../api/admin/actualizar-usuario.php' : '../../api/admin/crear-usuario.php';
    
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    fetch(url, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Éxito', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Error en la petición', 'error');
    });
});

// Funciones auxiliares (Toggle y Eliminar)
function toggleEstado(id, nuevoEstado) {
    const texto = nuevoEstado == 1 ? 'activar' : 'desactivar';
    Swal.fire({
        title: '¿Confirmar acción?',
        text: `¿Deseas ${texto} este usuario?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../../api/admin/toggle-estado.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id, activo: nuevoEstado})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Éxito', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

function eliminarUsuario(id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../../api/admin/eliminar-usuario.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Eliminado', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

window.onclick = function(event) {
    const modal = document.getElementById('modalUsuario');
    if (event.target == modal) {
        cerrarModal();
    }
}
</script>

</main>
</div>
</body>
</html>