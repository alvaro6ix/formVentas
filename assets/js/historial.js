document.addEventListener('DOMContentLoaded', function() {
    cargarVentas();

    // Evento de filtrado
    document.getElementById('formFiltros').addEventListener('submit', function(e) {
        e.preventDefault();
        cargarVentas();
    });

    // Evento limpiar
    document.getElementById('btnLimpiar').addEventListener('click', function() {
        document.getElementById('formFiltros').reset();
        cargarVentas();
    });
});

function cargarVentas() {
    const formData = new FormData(document.getElementById('formFiltros'));
    const tbody = document.getElementById('tablaResultados');
    const loading = document.getElementById('loading');
    const sinResultados = document.getElementById('sinResultados');
    const info = document.getElementById('infoResultados');

    // UI Reset
    tbody.innerHTML = '';
    loading.style.display = 'block';
    sinResultados.style.display = 'none';

    fetch('../api/ventas/buscar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';

        if (data.success && data.data.length > 0) {
            data.data.forEach(venta => {
                const fila = construirFila(venta);
                tbody.innerHTML += fila;
            });
            info.innerText = `Mostrando ${data.data.length} registros recientes.`;
        } else {
            sinResultados.style.display = 'block';
            info.innerText = '0 resultados.';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loading.style.display = 'none';
        alert('Error al cargar datos');
    });
}

function construirFila(v) {
    // Definir colores de badges
    let badgeClass = 'bg-secondary';
    if (v.estatus === 'activa') badgeClass = 'bg-warning text-dark';
    if (v.estatus === 'completada') badgeClass = 'bg-success';
    if (v.estatus === 'cancelada') badgeClass = 'bg-danger';

    // Definir badge de flujo técnico
    let flujoHTML = '';
    if (v.estatus === 'activa') {
        if (v.asignado_tecnico > 0) {
            flujoHTML = `<span class="badge bg-info text-dark ms-1"><i class="fas fa-running"></i> En Ruta</span>`;
        } else {
            flujoHTML = `<span class="badge bg-secondary ms-1"><i class="fas fa-clock"></i> Pendiente</span>`;
        }
    }

    return `
        <tr>
            <td class="fw-bold text-primary">${v.folio}</td>
            <td>
                <div class="fw-bold text-dark">${v.nombre_titular}</div>
                <a href="tel:${v.telefono}" class="small text-decoration-none text-muted"><i class="fas fa-phone"></i> ${v.telefono}</a>
            </td>
            <td>
                <small class="d-block text-muted"><i class="fas fa-map-marker-alt"></i> ${v.colonia}</small>
                <small class="text-muted">${v.delegacion_municipio}</small>
            </td>
            <td>
                <span class="badge border text-dark bg-light">${v.paquete_contratado}</span>
            </td>
            <td>${new Date(v.fecha_servicio).toLocaleDateString()}</td>
            <td>
                <span class="badge ${badgeClass}">${v.estatus.toUpperCase()}</span>
                ${flujoHTML}
            </td>
            <td class="text-end">
                <a href="generar-pdf.php?id=${v.id}" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver PDF Completo">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </td>
        </tr>
    `;
}