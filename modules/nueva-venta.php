<?php include '../includes/header.php'; ?>

<div class="card">
    <h2 style="color: var(--primary-dark); margin-bottom: 20px;">
        <i class="fas fa-file-signature"></i> Nuevo Contrato de Servicio
    </h2>
    
    <form id="formVenta" action="../api/save-venta.php" method="POST">
        
        <!-- SECCIÓN 1: DATOS DEL SERVICIO -->
        <div class="section-title"><i class="fas fa-info-circle"></i> Datos del Servicio</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Tipo de Servicio</label>
                <select name="tipo_servicio" class="form-control" required>
                    <option value="instalacion">Instalación</option>
                    <option value="soporte">Soporte Técnico</option>
                    <option value="cambio_domicilio">Cambio de Domicilio</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de Servicio</label>
                <input type="date" name="fecha_servicio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Paquete Contratado</label>
                <select name="paquete_contratado" class="form-control" required>
                    <option value="Internet 50MB">Internet 50MB</option>
                    <option value="Internet 100MB">Internet 100MB</option>
                    <option value="Internet 200MB + TV">Internet 200MB + TV</option>
                    <option value="Corporativo Simétrico">Corporativo Simétrico</option>
                </select>
            </div>
        </div>

        <!-- SECCIÓN 2: TITULAR Y UBICACIÓN (AUTOCOMPLETADO) -->
        <div class="section-title"><i class="fas fa-map-marker-alt"></i> Datos del Titular y Ubicación</div>
        <div class="form-group">
            <label class="form-label">Nombre Completo del Titular</label>
            <input type="text" name="nombre_titular" class="form-control" placeholder="Apellido Paterno, Materno y Nombres" required style="text-transform: uppercase;">
        </div>
        
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Código Postal (Toluca)</label>
                <div style="position: relative;">
                    <input type="text" name="codigo_postal" id="cp" class="form-control" placeholder="Ej: 50000" maxlength="5" required>
                    <i class="fas fa-search" style="position: absolute; right: 10px; top: 12px; color: var(--accent);"></i>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" id="estado" class="form-control" readonly style="background: #f8f9fa;">
            </div>
            <div class="form-group">
                <label class="form-label">Municipio</label>
                <input type="text" name="delegacion_municipio" id="municipio" class="form-control" readonly style="background: #f8f9fa;">
            </div>
            <div class="form-group">
                <label class="form-label">Colonia</label>
                <select name="colonia" id="colonia" class="form-control" required>
                    <option value="">Ingrese CP primero</option>
                </select>
            </div>
        </div>

        <div class="form-grid" style="margin-top: 15px;">
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Calle</label>
                <input type="text" name="calle" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Núm. Ext.</label>
                <input type="text" name="numero_exterior" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Núm. Int.</label>
                <input type="text" name="numero_interior" class="form-control">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Teléfono Casa</label>
                <input type="tel" name="telefono" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Celular</label>
                <input type="tel" name="celular" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="correo_electronico" class="form-control">
            </div>
        </div>

        <!-- SECCIÓN 3: EQUIPOS Y TÉCNICA -->
        <div class="section-title"><i class="fas fa-server"></i> Equipos Instalados</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">ONT Modelo</label>
                <input type="text" name="ont_modelo" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">ONT Serie (Mac)</label>
                <input type="text" name="ont_serie" class="form-control">
            </div>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;">
                <i class="fas fa-save"></i> Guardar Venta y Generar PDF
            </button>
        </div>
    </form>
</div>

<script src="../assets/js/cp-autocomplete.js"></script>
<script>
// Manejo del envío del formulario con fetch
document.getElementById('formVenta').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    Swal.fire({
        title: 'Procesando...',
        text: 'Guardando datos y generando QR',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    fetch('../api/save-venta.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Venta Exitosa!',
                text: 'El folio es: ' + data.folio,
                showCancelButton: true,
                confirmButtonText: 'Ver PDF',
                cancelButtonText: 'Cerrar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('generar-pdf.php?id=' + data.id, '_blank');
                    window.location.href = 'dashboard.php';
                } else {
                    window.location.href = 'dashboard.php';
                }
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
    });
});
</script>

</main>
</div>
</body>
</html>