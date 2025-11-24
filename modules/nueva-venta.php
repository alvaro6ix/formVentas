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
                    <option value="addons">Addons</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de Servicio</label>
                <input type="date" name="fecha_servicio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Número de Cuenta</label>
                <input type="text" name="numero_cuenta" class="form-control" placeholder="Opcional">
            </div>
            <div class="form-group">
                <label class="form-label">Puerto</label>
                <input type="text" name="puerto" class="form-control" placeholder="Opcional">
            </div>
            <div class="form-group">
                <label class="form-label">Placa</label>
                <input type="text" name="placa" class="form-control" placeholder="Opcional">
            </div>
        </div>

        <!-- SECCIÓN 2: TITULAR Y UBICACIÓN -->
        <div class="section-title"><i class="fas fa-user"></i> Datos del Titular</div>
        <div class="form-group">
            <label class="form-label">Nombre Completo del Titular</label>
            <input type="text" name="nombre_titular" class="form-control" placeholder="Apellido Paterno, Materno y Nombres" required style="text-transform: uppercase;">
        </div>
        
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Código Postal</label>
                <div style="position: relative;">
                    <input type="text" name="codigo_postal" id="cp" class="form-control" placeholder="Ej: 50000" maxlength="5" required>
                    <i class="fas fa-search" style="position: absolute; right: 10px; top: 12px; color: var(--accent);"></i>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" id="estado" class="form-control" readonly style="background: var(--gray-100);">
            </div>
            <div class="form-group">
                <label class="form-label">Municipio</label>
                <input type="text" name="delegacion_municipio" id="municipio" class="form-control" readonly style="background: var(--gray-100);">
            </div>
            <div class="form-group">
                <label class="form-label">Colonia</label>
                <select name="colonia" id="colonia" class="form-control" required>
                    <option value="">Ingrese CP primero</option>
                </select>
            </div>
        </div>

        <div class="form-grid">
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

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Tipo de Vivienda</label>
                <select name="tipo_vivienda" class="form-control" required>
                    <option value="casa">Casa</option>
                    <option value="departamento">Departamento</option>
                    <option value="negocio">Negocio</option>
                    <option value="empresarial">Empresarial</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="form-group" id="otroTipoVivienda" style="display: none;">
                <label class="form-label">Especificar Tipo de Vivienda</label>
                <input type="text" name="tipo_vivienda_otro" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Referencias de Ubicación</label>
            <textarea name="referencias" class="form-control" rows="3" placeholder="Puntos de referencia para llegar al domicilio"></textarea>
        </div>

        <!-- SECCIÓN 3: SERVICIO CONTRATADO -->
        <div class="section-title"><i class="fas fa-tv"></i> Servicio Contratado</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Paquete Contratado</label>
                <select name="paquete_contratado" class="form-control" required>
                    <option value="Internet 50MB">Internet 50MB</option>
                    <option value="Internet 100MB">Internet 100MB</option>
                    <option value="Internet 200MB">Internet 200MB</option>
                    <option value="Internet 200MB + TV">Internet 200MB + TV</option>
                    <option value="Corporativo Simétrico">Corporativo Simétrico</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Promoción</label>
                <input type="text" name="tipo_promocion" class="form-control" placeholder="Promoción aplicada">
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Identificación</label>
                <select name="identificacion" class="form-control" id="tipoIdentificacion">
                    <option value="">Seleccionar</option>
                    <option value="INE">INE</option>
                    <option value="RFC">RFC</option>
                    <option value="CURP">CURP</option>
                    <option value="Pasaporte">Pasaporte</option>
                </select>
            </div>
            <div class="form-group" id="numeroIdentificacionGroup" style="display: none;">
                <label class="form-label">Número de Identificación</label>
                <input type="text" name="numero_identificacion" class="form-control" placeholder="Número de identificación">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Contrato Entregado</label>
            <div style="display: flex; gap: 15px; margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="radio" name="contrato_entregado" value="1"> Sí
                </label>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="radio" name="contrato_entregado" value="0" checked> No
                </label>
            </div>
        </div>

        <!-- SECCIÓN 4: EQUIPOS -->
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
            <div class="form-group">
                <label class="form-label">Otro Equipo - Modelo</label>
                <input type="text" name="otro_equipo_modelo" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Otro Equipo - Serie</label>
                <input type="text" name="otro_equipo_serie" class="form-control">
            </div>
        </div>

        <div class="form-group">
    <label class="form-label">Materiales Utilizados</label>
    
    <div style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
        <div style="flex: 2;">
            <select id="selMaterial" class="form-control">
                <option value="">Seleccione material...</option>
                <option value="Cable Fibra Óptica (m)">Cable Fibra Óptica (mts)</option>
                <option value="Cable UTP (m)">Cable UTP (mts)</option>
                <option value="Conectores RJ45">Conectores RJ45</option>
                <option value="Conectores Mecánicos">Conectores Mecánicos</option>
                <option value="Tensores">Tensores</option>
                <option value="Grapas">Grapas</option>
                <option value="Roseta Óptica">Roseta Óptica</option>
                <option value="Patch Cord">Patch Cord</option>
                <option value="Fleje">Fleje</option>
                <option value="Hebillas">Hebillas</option>
            </select>
        </div>
        <div style="flex: 1;">
            <input type="number" id="cantMaterial" class="form-control" placeholder="Cant." min="1">
        </div>
        <button type="button" id="btnAgregarMaterial" class="btn btn-primary" style="height: 42px;">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <div id="listaMateriales" style="background: #f8f9fa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; min-height: 50px;">
        <p style="color: #999; text-align: center; margin: 0; font-size: 0.9em;" id="msgVacio">
            No hay materiales agregados
        </p>
        </div>

    <input type="hidden" name="materiales_utilizados" id="inputMaterialesJSON">
</div>

        <div class="form-group">
            <label class="form-label">Notas de Instalación</label>
            <textarea name="notas_instalacion" class="form-control" rows="3" placeholder="Observaciones durante la instalación"></textarea>
        </div>

        <!-- SECCIÓN 5: INSTALADOR -->
        <div class="section-title"><i class="fas fa-user-cog"></i> Datos del Instalador</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nombre del Instalador</label>
                <input type="text" name="instalador_nombre" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Número del Instalador</label>
                <input type="text" name="instalador_numero" class="form-control">
            </div>
        </div>

        <!-- SECCIÓN 6: EVALUACIÓN -->
        <div class="section-title"><i class="fas fa-clipboard-check"></i> Evaluación del Servicio</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">¿Se explicaron los servicios?</label>
                <select name="eval_servicios_explicados" class="form-control">
                    <option value="">Seleccionar</option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">¿Se entregó manual?</label>
                <select name="eval_manual_entregado" class="form-control">
                    <option value="">Seleccionar</option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Trato recibido</label>
                <select name="eval_trato_recibido" class="form-control">
                    <option value="">Seleccionar</option>
                    <option value="excelente">Excelente</option>
                    <option value="bueno">Bueno</option>
                    <option value="regular">Regular</option>
                    <option value="malo">Malo</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Eficiencia en instalación</label>
                <select name="eval_eficiencia" class="form-control">
                    <option value="">Seleccionar</option>
                    <option value="excelente">Excelente</option>
                    <option value="bueno">Bueno</option>
                    <option value="regular">Regular</option>
                    <option value="malo">Malo</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;">
                <i class="fas fa-save"></i> Guardar Venta y Generar PDF
            </button>
        </div>
    </form>
</div>

<script src="../assets/js/cp-autocomplete.js?v=5000"></script>
<script>
// ==========================================
// 1. LÓGICA DE MATERIALES (Nueva)
// ==========================================
const materialesList = [];
const selMaterial = document.getElementById('selMaterial');
const cantMaterial = document.getElementById('cantMaterial');
const listaVisual = document.getElementById('listaMateriales');
const inputHidden = document.getElementById('inputMaterialesJSON');
const msgVacio = document.getElementById('msgVacio');

// Botón Agregar Material
const btnAgregar = document.getElementById('btnAgregarMaterial');
if(btnAgregar){
    btnAgregar.addEventListener('click', function() {
        const material = selMaterial.value;
        const cantidad = cantMaterial.value;

        if (!material || !cantidad || cantidad <= 0) {
            Swal.fire('Atención', 'Seleccione un material y una cantidad válida', 'warning');
            return;
        }

        // Agregar al array
        materialesList.push({ material: material, cantidad: cantidad });

        // Actualizar vista y input oculto
        actualizarListaMateriales();
        
        // Limpiar campos
        selMaterial.value = "";
        cantMaterial.value = "";
        cantMaterial.focus();
    });
}

function actualizarListaMateriales() {
    // Limpiar lista visual
    listaVisual.innerHTML = '';

    if (materialesList.length === 0) {
        listaVisual.appendChild(msgVacio);
        msgVacio.style.display = 'block';
    } else {
        msgVacio.style.display = 'none'; // Aseguramos ocultar el mensaje
        
        // Renderizar items
        materialesList.forEach((item, index) => {
            const div = document.createElement('div');
            div.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-bottom: 1px solid #eee; background: white; margin-bottom: 5px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);';
            div.innerHTML = `
                <span style="font-size: 0.95rem;">
                    <i class="fas fa-tools" style="color: var(--accent); margin-right: 8px;"></i>
                    <strong>${item.material}</strong>: ${item.cantidad}
                </span>
                <button type="button" onclick="eliminarMaterial(${index})" style="background: none; border: none; color: #dc3545; cursor: pointer; padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            `;
            listaVisual.appendChild(div);
        });
    }

    // Actualizar el INPUT HIDDEN con el JSON String
    inputHidden.value = JSON.stringify(materialesList);
}

// Función global para eliminar (necesaria para el onclick inline)
window.eliminarMaterial = function(index) {
    materialesList.splice(index, 1);
    actualizarListaMateriales();
};

// ==========================================
// 2. LÓGICA DE FORMULARIO (Existente)
// ==========================================

// Mostrar/ocultar campo "otro tipo de vivienda"
const selectVivienda = document.querySelector('select[name="tipo_vivienda"]');
if(selectVivienda){
    selectVivienda.addEventListener('change', function() {
        const otroDiv = document.getElementById('otroTipoVivienda');
        otroDiv.style.display = this.value === 'otro' ? 'block' : 'none';
    });
}

// Mostrar/ocultar campo "número de identificación"
const selectIdent = document.getElementById('tipoIdentificacion');
if(selectIdent){
    selectIdent.addEventListener('change', function() {
        const numeroGroup = document.getElementById('numeroIdentificacionGroup');
        numeroGroup.style.display = this.value ? 'block' : 'none';
    });
}

// ==========================================
// 3. ENVÍO DEL FORMULARIO (Fetch)
// ==========================================
document.getElementById('formVenta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);

    // Validación extra: Si hay materiales seleccionados pero el input hidden está vacío (caso raro)
    if(materialesList.length > 0 && inputHidden.value === "") {
        inputHidden.value = JSON.stringify(materialesList);
    }

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
            Swal.fire('Error', data.message || 'Error desconocido', 'error');
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