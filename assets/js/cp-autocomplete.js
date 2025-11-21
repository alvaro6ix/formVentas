// assets/js/cp-autocomplete.js

document.addEventListener('DOMContentLoaded', function() {
    const cpInput = document.getElementById('cp');
    const coloniaSelect = document.getElementById('colonia');
    const municipioInput = document.getElementById('municipio');
    const estadoInput = document.getElementById('estado');
    // Puedes agregar ciudadInput si tu HTML lo tiene
    
    if(cpInput) { 
        cpInput.addEventListener('input', function() {
            const cp = this.value;
            
            if(cp.length === 5) {
                // Mostrar loader o indicador visual si deseas
                fetch(`../api/get-cp-info.php?cp=${cp}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Rellenar campos fijos
                            municipioInput.value = data.data.municipio;
                            estadoInput.value = data.data.estado;
                            
                            // Rellenar selector de colonias
                            coloniaSelect.innerHTML = '<option value="">Seleccione una colonia</option>';
                            data.colonias.forEach(colonia => {
                                const option = document.createElement('option');
                                option.value = colonia;
                                option.textContent = colonia;
                                coloniaSelect.appendChild(option);
                            });

                            // Efecto visual de completado
                            municipioInput.style.borderColor = '#00D4FF';
                            setTimeout(() => municipioInput.style.borderColor = '', 1000);
                        } else {
                            // SweetAlert si lo tienes, sino alert
                            alert('Código Postal no encontrado en la zona de cobertura.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    }
});