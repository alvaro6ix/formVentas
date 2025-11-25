<?php include '../includes/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark fw-bold m-0"><i class="fas fa-search me-2"></i> Historial de Ventas</h2>
            <p class="text-muted m-0">Consulta, filtra y descarga expedientes.</p>
        </div>
        
        <?php if($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 2): ?>
        <a href="nueva-venta.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nueva Venta
        </a>
        <?php endif; ?>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body bg-light rounded-3">
            <form id="formFiltros" class="row g-3 align-items-end">
                
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary small">Buscar (Folio, Cliente, Dirección)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="busqueda" class="form-control border-start-0 ps-0" placeholder="Escribe para buscar...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold text-secondary small">Estatus</label>
                    <select id="estatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="activa">Activa (En Proceso)</option>
                        <option value="completada">Completada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold text-secondary small">Desde</label>
                    <input type="date" id="fecha_inicio" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-secondary small">Hasta</label>
                    <input type="date" id="fecha_fin" class="form-control">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button>
                    <button type="button" id="btnLimpiar" class="btn btn-outline-secondary" title="Limpiar filtros"><i class="fas fa-undo"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table w-100">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Ubicación</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaResultados">
                        </tbody>
                </table>
            </div>
            
            <div id="loading" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Buscando registros...</p>
            </div>
            <div id="sinResultados" class="text-center py-5" style="display: none;">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No se encontraron ventas</h5>
                <p class="small text-muted">Intenta con otros filtros de búsqueda.</p>
            </div>
        </div>
        
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <small class="text-muted" id="infoResultados">Mostrando resultados</small>
            </div>
    </div>
</div>

<script src="../assets/js/historial.js?v=<?php echo time(); ?>"></script>
<?php include '../includes/footer.php'; ?>