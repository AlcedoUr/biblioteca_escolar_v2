<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="app">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Gestión de Incidencias</h3>
            <p class="text-muted small mb-0">Control de material dañado, extraviado y sus resoluciones.</p>
        </div>
        <a href="historial.php" class="btn text-white px-4 py-2 fw-bold shadow-sm" style="background-color: #8B1538;">
            <i class="bi bi-plus-lg me-2"></i>Reportar Nueva Incidencia
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group border rounded bg-light">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" v-model="filtros.busqueda" @input="cargarDatos" class="form-control border-0 bg-transparent shadow-none" placeholder="Buscar por título, código o alumno...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select v-model="filtros.estado" @change="cargarDatos" class="form-select border-0 bg-light cursor-pointer">
                        <option value="TODOS">Todos los Estados</option>
                        <option value="PENDIENTE">⚠️ Pendientes</option>
                        <option value="RESUELTO">✅ Resueltos / Archivados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100 border-0 bg-light text-start" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Imprimir Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 d-print-none">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning bg-warning bg-opacity-10">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="me-3"><i class="bi bi-hourglass-split fs-3 text-warning"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-0">Casos Pendientes</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ stats.pendientes }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success bg-success bg-opacity-10">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="me-3"><i class="bi bi-check-circle fs-3 text-success"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-0">Casos Resueltos</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ stats.resueltos }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary bg-primary bg-opacity-10">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="me-3"><i class="bi bi-cash-coin fs-3 text-primary"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-0">Compensaciones (S/)</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ stats.dinero.toFixed(2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-muted fw-bold">
                        <tr>
                            <th class="ps-4">Libro / Código</th>
                            <th>Solicitante</th>
                            <th>Causante</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Resolución</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in incidencias" :key="item.id_detalle">
                            
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ item.titulo }}</div>
                                <small class="text-muted font-monospace">ISBN: {{ item.isbn || 'S/N' }}</small>
                            </td>

                            <td>
                                <div class="small text-dark fw-bold">{{ item.solicitante }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ item.rol_solicitante }}</div>
                            </td>

                            <td>
                                <span v-if="item.causante_display !== '-'" class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2">
                                    {{ item.causante_display }}
                                </span>
                                <span v-else class="text-muted small">-</span>
                            </td>

                            <td class="small text-muted">{{ item.fecha_incidente }}</td>

                            <td>
                                <span v-if="item.tipo_incidente == 'PERDIDO'" class="badge bg-dark text-white"><i class="bi bi-x-circle me-1"></i> Extravío</span>
                                <span v-else class="badge bg-warning text-dark"><i class="bi bi-bandaid me-1"></i> Dañado</span>
                            </td>

                            <td>
                                <span v-if="item.estado_resolucion == 'PENDIENTE'" class="badge bg-warning bg-opacity-10 text-dark border border-warning">Pendiente</span>
                                <span v-else class="badge bg-success bg-opacity-10 text-success border border-success">Regularizado</span>
                            </td>

                            <td>
                                <div v-if="item.estado_resolucion == 'RESUELTO'" class="small">
                                    <div class="fw-bold text-success">{{ item.tipo_resolucion }}</div>
                                    <div v-if="item.monto_compensacion > 0" class="text-muted">S/ {{ item.monto_compensacion }}</div>
                                </div>
                                <div v-else class="text-muted small fst-italic">-</div>
                            </td>

                            <td class="text-end pe-4">
                                <button v-if="item.estado_resolucion == 'PENDIENTE'" 
                                        @click="abrirResolver(item)" 
                                        class="btn btn-sm btn-primary shadow-sm fw-bold" 
                                        title="Resolver Caso">
                                    <i class="bi bi-check2-square me-1"></i>Resolver
                                </button>
                                <button v-else 
                                        class="btn btn-sm btn-light border text-muted" disabled>
                                    <i class="bi bi-archive-fill"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="incidencias.length === 0">
                            <td colspan="8" class="text-center py-5 text-muted">No se encontraron incidencias.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div v-if="modal.visible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-3">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">Resolver Incidencia</h5>
                    <button type="button" class="btn-close btn-close-white" @click="modal.visible = false"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3 small">
                        <strong>Libro:</strong> {{ modal.data.titulo }}<br>
                        <strong>Incidente:</strong> {{ modal.data.tipo_incidente }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Forma de Resolución <span class="text-danger">*</span></label>
                        <select v-model="formResolucion.tipo" class="form-select">
                            <option value="">-- Seleccione --</option>
                            <option value="REPOSICION">Reposición (Trajo libro nuevo)</option>
                            <option value="PAGO">Pago Económico (Compensación)</option>
                            <option value="REPARACION">Reparación (Arreglado por alumno)</option>
                            <option value="CONDONADO">Condonado (Sin sanción)</option>
                        </select>
                    </div>

                    <div class="mb-3" v-if="formResolucion.tipo == 'PAGO'">
                        <label class="form-label fw-bold small text-muted">Monto Pagado (S/)</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 bg-light">S/</span>
                            <input type="number" v-model="formResolucion.monto" class="form-control border-start-0" min="0" step="0.10">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Observaciones / Detalles</label>
                        <textarea v-model="formResolucion.observaciones" class="form-control" rows="3" placeholder="Ej: El alumno trajo una edición 2024 nueva..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button class="btn btn-light text-secondary" @click="modal.visible = false">Cancelar</button>
                    <button class="btn btn-primary fw-bold px-4" @click="guardarResolucion">
                        <i class="bi bi-save-fill me-2"></i>Guardar y Archivar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                incidencias: [],
                filtros: { busqueda: '', estado: 'PENDIENTE' }, // Por defecto ver pendientes
                stats: { pendientes: 0, resueltos: 0, dinero: 0 },
                modal: { visible: false, data: {} },
                formResolucion: { tipo: '', monto: 0, observaciones: '' }
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                try {
                    const params = new URLSearchParams({
                        q: this.filtros.busqueda,
                        estado: this.filtros.estado
                    });
                    const res = await fetch(`../api/incidencias.php?${params}`);
                    const data = await res.json();
                    this.incidencias = data.data;
                    this.stats = data.stats;
                } catch(e) { console.error(e); }
            },
            
            abrirResolver(item) {
                this.modal.data = item;
                this.formResolucion = { tipo: '', monto: 0, observaciones: '' };
                this.modal.visible = true;
            },

            async guardarResolucion() {
                if (!this.formResolucion.tipo) return Swal.fire('Error', 'Seleccione un tipo de resolución', 'warning');

                const payload = {
                    id_detalle: this.modal.data.id_detalle,
                    tipo_resolucion: this.formResolucion.tipo,
                    monto: this.formResolucion.monto,
                    observaciones: this.formResolucion.observaciones
                };

                try {
                    const res = await fetch('../api/incidencias.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const r = await res.json();
                    if (r.exito) {
                        Swal.fire('Resuelto', 'La incidencia ha sido regularizada.', 'success');
                        this.modal.visible = false;
                        this.cargarDatos();
                    } else {
                        Swal.fire('Error', r.mensaje, 'error');
                    }
                } catch(e) { Swal.fire('Error', 'Fallo de red', 'error'); }
            }
        }
    }).mount('#app')
</script>