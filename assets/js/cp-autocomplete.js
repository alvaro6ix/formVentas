/**
 * Autocompletado de Códigos Postales - Conectado a BD
 * BDIGITAL Sistema de Ventas
 */

class CPAutocomplete {
    constructor() {
        this.cpInput = document.getElementById('cp');
        this.estadoInput = document.getElementById('estado');
        this.municipioInput = document.getElementById('municipio');
        this.coloniaSelect = document.getElementById('colonia');
        
        this.init();
    }

    init() {
        if (this.cpInput) {
            this.cpInput.addEventListener('input', this.handleCPInput.bind(this));
            // Eliminé el evento blur duplicado para evitar doble petición
        }
    }

    handleCPInput(e) {
        const cp = e.target.value.replace(/\D/g, '');
        
        // Limitar a 5 dígitos visualmente
        if (cp.length > 5) {
            e.target.value = cp.substring(0, 5);
            return;
        }

        // Buscar automáticamente cuando tenga 5 dígitos exactos
        if (cp.length === 5) {
            this.buscarCodigoPostal(cp);
        } else {
            this.limpiarCampos(); // Limpia si borra un número
        }
    }

    // --- AQUÍ ESTÁ EL CAMBIO IMPORTANTE ---
    async buscarCodigoPostal(cp) {
        try {
            this.mostrarLoading();

            // Usamos FETCH para llamar al archivo PHP que creamos

const response = await fetch('/FormVentas/api/api_cp.php?cp=' + cp);
            
            if (!response.ok) throw new Error('Error en la red');
            
            const datos = await response.json();
            
            if (datos.encontrado) {
                this.llenarCampos(datos);
            } else {
                this.mostrarError('Código Postal no encontrado en la Base de Datos');
            }

        } catch (error) {
            console.error('Error:', error);
            // Opcional: silenciar error si es por cancelación de petición
        } finally {
            this.ocultarLoading();
        }
    }

    llenarCampos(datos) {
        // Llenar estado y municipio
        if (this.estadoInput) this.estadoInput.value = datos.estado;
        if (this.municipioInput) this.municipioInput.value = datos.municipio;

        // Llenar colonias
        if (this.coloniaSelect) {
            this.coloniaSelect.innerHTML = '<option value="">Seleccione una colonia</option>';
            
            datos.colonias.forEach(colonia => {
                const option = document.createElement('option');
                option.value = colonia;
                option.textContent = colonia;
                this.coloniaSelect.appendChild(option);
            });

            this.coloniaSelect.disabled = false;
        }
        
        // No mostrar notificación de éxito para no ser invasivo, solo llenar datos
    }

    limpiarCampos() {
        if (this.estadoInput) this.estadoInput.value = '';
        if (this.municipioInput) this.municipioInput.value = '';
        
        if (this.coloniaSelect) {
            this.coloniaSelect.innerHTML = '<option value="">Ingrese CP primero</option>';
            this.coloniaSelect.disabled = true;
        }
    }

    mostrarLoading() {
        // Asumiendo que usas FontAwesome y la estructura previa
        const parent = this.cpInput.parentElement;
        const icon = parent.querySelector('i'); // Si tienes un icono
        if(icon) {
            this.originalIconClass = icon.className; // Guardar clase original
            icon.className = 'fas fa-spinner fa-spin';
        }
    }

    ocultarLoading() {
        const parent = this.cpInput.parentElement;
        const icon = parent.querySelector('i');
        if(icon && this.originalIconClass) {
            icon.className = this.originalIconClass;
        } else if (icon) {
             icon.className = 'fas fa-search'; // Fallback
        }
    }

    mostrarError(mensaje) {
        // Puedes usar tu lógica de notificación anterior aquí si gustas
        // O simplemente limpiar
        this.limpiarCampos();
        console.log(mensaje); 
    }
}

document.addEventListener('DOMContentLoaded', function() {
    new CPAutocomplete();
});