/**
 * Autocompletado de Códigos Postales
 * VERSIÓN FINAL - FUNCIONAL
 */

class CPAutocomplete {
    constructor() {
        this.cpInput = document.getElementById('cp');
        this.estadoInput = document.getElementById('estado');
        this.municipioInput = document.getElementById('municipio');
        this.coloniaSelect = document.getElementById('colonia');
        
        console.log('✅ CPAutocomplete inicializado');
        
        this.init();
    }

    init() {
        if (!this.cpInput) {
            console.error('❌ No se encontró el input con id="cp"');
            return;
        }

        // Evento principal: input en tiempo real
        this.cpInput.addEventListener('input', (e) => this.handleCPInput(e));
        
        // Remover atributo readonly si existe
        if (this.estadoInput) this.estadoInput.removeAttribute('readonly');
        if (this.municipioInput) this.municipioInput.removeAttribute('readonly');
        
        console.log('✅ Eventos configurados correctamente');
    }

    handleCPInput(e) {
        // Solo permitir números
        let cp = e.target.value.replace(/\D/g, '');
        
        // Limitar a 5 dígitos
        if (cp.length > 5) {
            cp = cp.substring(0, 5);
        }
        
        e.target.value = cp;

        // Buscar cuando tenga exactamente 5 dígitos
        if (cp.length === 5) {
            console.log('🔍 Buscando CP:', cp);
            this.buscarCodigoPostal(cp);
        } else {
            // Limpiar campos si no tiene 5 dígitos
            this.limpiarCampos();
        }
    }

    async buscarCodigoPostal(cp) {
        try {
            this.mostrarLoading();
            
            // ⚠️ RUTA FIJA - Ya sabemos que funciona
            const url = `http://localhost/FormVentas/api/api_cp.php?cp=${cp}`;
            console.log('📡 Llamando a:', url);
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const datos = await response.json();
            console.log('📦 Datos recibidos:', datos);
            
            if (datos.encontrado) {
                this.llenarCampos(datos);
                this.mostrarExito();
            } else {
                this.mostrarError('Código Postal no encontrado');
                this.limpiarCampos();
            }

        } catch (error) {
            console.error('❌ Error completo:', error);
            this.mostrarError('Error: ' + error.message);
            this.limpiarCampos();
        } finally {
            this.ocultarLoading();
        }
    }

    llenarCampos(datos) {
        console.log('✏️ Llenando campos...');
        
        // Llenar estado
        if (this.estadoInput) {
            this.estadoInput.value = datos.estado || '';
            this.estadoInput.style.background = '#e8f5e9'; // Verde claro
            console.log('  ✓ Estado:', datos.estado);
        }
        
        // Llenar municipio
        if (this.municipioInput) {
            this.municipioInput.value = datos.municipio || '';
            this.municipioInput.style.background = '#e8f5e9';
            console.log('  ✓ Municipio:', datos.municipio);
        }

        // Llenar colonias
        if (this.coloniaSelect && datos.colonias) {
            this.coloniaSelect.innerHTML = '';
            this.coloniaSelect.style.background = '#e8f5e9';
            
            // Opción por defecto si hay varias colonias
            if (datos.colonias.length > 1) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- Seleccione una colonia --';
                this.coloniaSelect.appendChild(opt);
            }
            
            // Agregar colonias
            datos.colonias.forEach(colonia => {
                const opt = document.createElement('option');
                opt.value = colonia;
                opt.textContent = colonia;
                this.coloniaSelect.appendChild(opt);
            });

            // Si solo hay una, seleccionarla automáticamente
            if (datos.colonias.length === 1) {
                this.coloniaSelect.value = datos.colonias[0];
            }

            this.coloniaSelect.disabled = false;
            console.log('  ✓ Colonias cargadas:', datos.colonias.length);
        }
        
        console.log('✅ Campos llenados exitosamente');
    }

    limpiarCampos() {
        if (this.estadoInput) {
            this.estadoInput.value = '';
            this.estadoInput.style.background = '';
        }
        
        if (this.municipioInput) {
            this.municipioInput.value = '';
            this.municipioInput.style.background = '';
        }
        
        if (this.coloniaSelect) {
            this.coloniaSelect.innerHTML = '<option value="">Ingrese CP primero</option>';
            this.coloniaSelect.disabled = true;
            this.coloniaSelect.style.background = '';
        }
    }

    mostrarLoading() {
        if (this.cpInput) {
            this.cpInput.style.borderColor = '#2196F3';
            this.cpInput.style.background = '#E3F2FD';
        }
        
        // Cambiar icono a spinner si existe
        const parent = this.cpInput?.parentElement;
        const icon = parent?.querySelector('i');
        if (icon) {
            this.originalIcon = icon.className;
            icon.className = 'fas fa-spinner fa-spin';
            icon.style.color = '#2196F3';
        }
    }

    ocultarLoading() {
        if (this.cpInput) {
            this.cpInput.style.borderColor = '';
            this.cpInput.style.background = '';
        }
        
        const parent = this.cpInput?.parentElement;
        const icon = parent?.querySelector('i');
        if (icon && this.originalIcon) {
            icon.className = this.originalIcon;
            icon.style.color = '';
        }
    }

    mostrarError(mensaje) {
        console.warn('⚠️', mensaje);
        
        // Mostrar alerta si SweetAlert2 está disponible
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Código Postal',
                text: mensaje,
                timer: 2500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            // Fallback si no hay SweetAlert
            alert(mensaje);
        }
    }

    mostrarExito() {
        console.log('✅ Datos cargados correctamente');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Listo!',
                text: 'Datos del CP cargados',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    }
}

// ============================================
// INICIALIZACIÓN AUTOMÁTICA
// ============================================
(function() {
    console.log('🚀 Iniciando sistema de CP...');
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('📄 DOM cargado, creando instancia...');
            window.cpAutocomplete = new CPAutocomplete();
        });
    } else {
        // El DOM ya está cargado
        console.log('📄 DOM ya estaba listo, creando instancia...');
        window.cpAutocomplete = new CPAutocomplete();
    }
})();