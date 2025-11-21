/**
 * Autocompletado de Códigos Postales - Toluca, Estado de México
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
            this.cpInput.addEventListener('blur', this.handleCPBlur.bind(this));
        }
    }

    handleCPInput(e) {
        const cp = e.target.value.replace(/\D/g, '');
        
        // Limitar a 5 dígitos
        if (cp.length > 5) {
            e.target.value = cp.substring(0, 5);
            return;
        }

        // Buscar automáticamente cuando tenga 5 dígitos
        if (cp.length === 5) {
            this.buscarCodigoPostal(cp);
        } else {
            this.limpiarCampos();
        }
    }

    handleCPBlur(e) {
        const cp = e.target.value.replace(/\D/g, '');
        if (cp.length === 5) {
            this.buscarCodigoPostal(cp);
        }
    }

    async buscarCodigoPostal(cp) {
        try {
            // Mostrar loading
            this.mostrarLoading();

            // Buscar en nuestra base de datos local de Toluca
            const resultado = await this.buscarEnBaseLocal(cp);
            
            if (resultado) {
                this.llenarCampos(resultado);
            } else {
                this.mostrarError('Código Postal no encontrado en Toluca');
            }
        } catch (error) {
            console.error('Error al buscar código postal:', error);
            this.mostrarError('Error al buscar el código postal');
        } finally {
            this.ocultarLoading();
        }
    }

    buscarEnBaseLocal(cp) {
        return new Promise((resolve) => {
            // Simulamos una búsqueda con setTimeout
            setTimeout(() => {
                const datos = this.obtenerDatosToluca()[cp];
                resolve(datos || null);
            }, 500);
        });
    }

    obtenerDatosToluca() {
        // Base de datos local de códigos postales de Toluca, Estado de México
        return {
            '50010': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Centro',
                    'Barrio de San Miguel',
                    'Barrio de la Merced',
                    'Barrio de Santa Clara'
                ]
            },
            '50020': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Barrio de San Bernardino',
                    'Barrio de San Juan',
                    'Barrio de San Sebastián'
                ]
            },
            '50100': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Barrio de San Mateo',
                    'Barrio de San Nicolás',
                    'Barrio de San Pedro'
                ]
            },
            '50110': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Barrio de Santiago',
                    'Barrio de San Antonio',
                    'Barrio de San Bartolo'
                ]
            },
            '50120': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Barrio de San Lorenzo',
                    'Barrio de San Marcos',
                    'Barrio de San Martín'
                ]
            },
            '50130': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Doctores',
                    'Barrio de San Felipe',
                    'Barrio de San Isidro'
                ]
            },
            '50140': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Barrio de San Pablo',
                    'Barrio de San Rafael',
                    'Barrio de San Roque'
                ]
            },
            '50150': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Barrio de San Lucas',
                    'Barrio de San Pedro',
                    'Barrio de San Tomás'
                ]
            },
            '50160': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Barrio de Santa María',
                    'Barrio de Santa Ana',
                    'Barrio de Santa Cruz'
                ]
            },
            '50170': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Barrio de Santo Domingo',
                    'Barrio de San José',
                    'Barrio de San Carlos'
                ]
            },
            '50200': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Pedro Totoltepec',
                    'Totoltepec de Arriba',
                    'Totoltepec de Abajo'
                ]
            },
            '50250': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Buenaventura',
                    'Buenavista',
                    'Lomas de San Buenaventura'
                ]
            },
            '50260': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Mateo Oxtotitlán',
                    'Oxtotitlán Centro',
                    'Oxtotitlán Norte'
                ]
            },
            '50270': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Felipe Tlalmimilolpan',
                    'Tlalmimilolpan Centro',
                    'Tlalmimilolpan Sur'
                ]
            },
            '50280': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Pablo Autopan',
                    'Autopan Norte',
                    'Autopan Sur'
                ]
            },
            '50290': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San José Guadalupe Otzacatipan',
                    'Otzacatipan Centro',
                    'Otzacatipan Oriente'
                ]
            },
            '50300': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Andrés Cuexcontitlán',
                    'Cuexcontitlán Centro',
                    'Cuexcontitlán Norte'
                ]
            },
            '50350': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Santiago Tlacotepec',
                    'Tlacotepec Centro',
                    'Tlacotepec Norte'
                ]
            },
            '50400': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Juan Tilapa',
                    'Tilapa Centro',
                    'Tilapa Sur'
                ]
            },
            '50450': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Cristóbal Huichochitlán',
                    'Huichochitlán Centro',
                    'Huichochitlán Norte'
                ]
            },
            '50500': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Pedro Tlanixco',
                    'Tlanixco Centro',
                    'Tlanixco Sur'
                ]
            },
            '50550': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Santa Ana Tlapaltitlán',
                    'Tlapaltitlán Centro',
                    'Tlapaltitlán Norte'
                ]
            },
            '50600': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Santa María Totoltepec',
                    'Totoltepec Centro',
                    'Totoltepec Norte'
                ]
            },
            '50650': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Diego Linares',
                    'Linares Centro',
                    'Linares Sur'
                ]
            },
            '50700': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Lorenzo Tepaltitlán',
                    'Tepaltitlán Centro',
                    'Tepaltitlán Norte'
                ]
            },
            '50750': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Marcos Yachihuacaltepec',
                    'Yachihuacaltepec Centro',
                    'Yachihuacaltepec Sur'
                ]
            },
            '50800': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Bartolo del Llano',
                    'Del Llano Centro',
                    'Del Llano Norte'
                ]
            },
            '50850': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San José Jajalpa',
                    'Jajalpa Centro',
                    'Jajalpa Norte'
                ]
            },
            '50900': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Santa María de las Rosas',
                    'Las Rosas Centro',
                    'Las Rosas Norte'
                ]
            },
            '50950': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Miguel Totoltepec',
                    'Totoltepec Centro',
                    'Totoltepec Norte'
                ]
            },
            '51000': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Mateo Atenco',
                    'Atenco Centro',
                    'Atenco Norte'
                ]
            },
            '51100': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'San Pedro Tlaltizapan',
                    'Tlaltizapan Centro',
                    'Tlaltizapan Norte'
                ]
            },
            '51200': {
                estado: 'Estado de México',
                municipio: 'Toluca',
                colonias: [
                    'Villa de Allende',
                    'Allende Centro',
                    'Allende Norte'
                ]
            }
        };
    }

    llenarCampos(datos) {
        // Llenar estado y municipio
        if (this.estadoInput) {
            this.estadoInput.value = datos.estado;
        }
        
        if (this.municipioInput) {
            this.municipioInput.value = datos.municipio;
        }

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

        // Mostrar mensaje de éxito
        this.mostrarExito('Código Postal encontrado');
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
        // Agregar clase de loading al input
        this.cpInput.classList.add('loading');
        
        // Cambiar icono a loading
        const icon = this.cpInput.parentElement.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-spinner fa-spin';
        }
    }

    ocultarLoading() {
        // Remover clase de loading
        this.cpInput.classList.remove('loading');
        
        // Restaurar icono original
        const icon = this.cpInput.parentElement.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-search';
        }
    }

    mostrarError(mensaje) {
        this.mostrarNotificacion(mensaje, 'error');
        this.limpiarCampos();
    }

    mostrarExito(mensaje) {
        this.mostrarNotificacion(mensaje, 'success');
    }

    mostrarNotificacion(mensaje, tipo) {
        // Crear notificación
        const notificacion = document.createElement('div');
        notificacion.className = `alert alert-${tipo}`;
        notificacion.innerHTML = `
            <i class="fas fa-${tipo === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i>
            ${mensaje}
        `;

        // Insertar después del input de CP
        const cpGroup = this.cpInput.parentElement.parentElement;
        cpGroup.appendChild(notificacion);

        // Remover después de 3 segundos
        setTimeout(() => {
            if (notificacion.parentElement) {
                notificacion.parentElement.removeChild(notificacion);
            }
        }, 3000);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    new CPAutocomplete();
});

// También exportar para uso global
window.CPAutocomplete = CPAutocomplete;