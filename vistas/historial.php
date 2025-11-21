<?php include 'includes/header.php'; ?>

<div id="app">
    
    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Gestión de Préstamos</h3>
            <p class="text-muted">Monitoreo de material en circulación</p>
        </div>
        <a href="prestamo_lote.php" class="btn text-white px-4 py-2 fw-bold shadow-sm btn-hover-gold" style="background-color: #8B1538;">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Préstamo
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="input-group border-0 bg-white rounded px-2">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar por solicitante, libro o código...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select v-model="filtroEstado" class="form-select border-0 bg-white shadow-none cursor-pointer">
                        <option value="TODOS">Todos los estados</option>
                        <option value="PENDIENTE">Activos</option>
                        <option value="VENCIDO">Vencidos</option>
                        <option value="FINALIZADO">Finalizados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETAS KPI -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-3" style="background-color: #E3F2FD; border-left: 4px solid #2196F3 !important;">
                <h6 class="text-primary fw-bold mb-1">Préstamos Activos</h6>
                <h3 class="fw-bold text-dark mb-0">{{ contadores.activos }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-3" style="background-color: #FFEBEE; border-left: 4px solid #F44336 !important;">
                <h6 class="text-danger fw-bold mb-1">Préstamos Vencidos</h6>
                <h3 class="fw-bold text-dark mb-0">{{ contadores.vencidos }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-3" style="background-color: #E8F5E9; border-left: 4px solid #4CAF50 !important;">
                <h6 class="text-success fw-bold mb-1">Devoluciones Hoy</h6>
                <h3 class="fw-bold text-dark mb-0">{{ contadores.finalizados }}</h3>
            </div>
        </div>
    </div>

    <!-- TABLA DE SEGUIMIENTO -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4">Solicitante</th>
                            <th>Ubicación / Rol</th>
                            <th style="width: 30%;">Libros Prestados</th> <!-- Columna Ancha -->
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Atraso</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in prestamosFiltrados" :key="p.id">
                            <!-- Solicitante -->
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ p.solicitante }}</div>
                                <small class="text-muted">ID: #{{ p.id }}</small>
                            </td>

                            <!-- Ubicación (Salón) -->
                            <td>
                                <span v-if="p.tipo_solicitante == 'ESTUDIANTE'" class="badge bg-light text-dark border">
                                    <i class="bi bi-backpack me-1"></i> {{ p.grado }} "{{ p.seccion }}"
                                </span>
                                <span v-else class="badge bg-light text-primary border border-primary">
                                    <i class="bi bi-person-video3 me-1"></i> Docente
                                </span>
                            </td>

                            <!-- Resumen de Libros (NUEVO) -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">{{ p.total_libros }}</span>
                                    <span class="text-muted small text-truncate d-inline-block" style="max-width: 250px;" :title="p.resumen_libros">
                                        {{ p.resumen_libros }}
                                    </span>
                                </div>
                            </td>

                            <!-- Fechas -->
                            <td>
                                <div class="fw-bold text-dark small">{{ p.fecha_devolucion_pactada }}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Prestado: {{ p.fecha_prestamo }}</small>
                            </td>

                            <!-- Estado -->
                            <td>
                                <span v-if="esVencido(p)" class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Vencido</span>
                                <span v-else-if="p.estado == 'PENDIENTE'" class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Activo</span>
                                <span v-else class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Devuelto</span>
                            </td>

                            <!-- Atraso -->
                            <td>
                                <span v-if="esVencido(p)" class="text-danger fw-bold small">
                                    {{ calcularAtraso(p.fecha_devolucion_pactada) }} días
                                </span>
                                <span v-else class="text-muted small">-</span>
                            </td>

                            <!-- Acciones -->
                            <td class="text-end pe-4">
                                <button @click="verDetalle(p)" class="btn btn-sm btn-light border me-1" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </button>
                                
                                <a :href="'detalle_prestamo.php?id=' + p.id" 
                                   class="btn btn-sm fw-bold border hover-green"
                                   :class="p.estado == 'PENDIENTE' ? 'btn-outline-success' : 'btn-light text-muted disabled'">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                </a>
                            </td>
                        </tr>
                        <tr v-if="prestamosFiltrados.length === 0">
                            <td colspan="7" class="text-center py-5 text-muted">No se encontraron registros.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE DETALLE RÁPIDO -->
    <div v-if="modal.visible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #8B1538;">
                    <h5 class="modal-title">Detalle del Préstamo #{{ modal.data.id }}</h5>
                    <button type="button" class="btn-close btn-close-white" @click="modal.visible = false"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small text-muted fw-bold">Solicitante:</label>
                        <div class="fs-5">{{ modal.data.solicitante }}</div>
                        <div class="text-muted small" v-if="modal.data.tipo_solicitante == 'ESTUDIANTE'">
                            {{ modal.data.grado }} "{{ modal.data.seccion }}"
                        </div>
                        <div class="text-muted small" v-else>Docente / Personal</div>
                    </div>
                    
                    <hr>
                    
                    <label class="small text-muted fw-bold mb-2">Material Prestado:</label>
                    <ul class="list-group">
                        <!-- Parseamos el string de resumen para mostrarlo en lista -->
                        <li class="list-group-item d-flex justify-content-between align-items-center" 
                            v-for="item in (modal.data.resumen_libros ? modal.data.resumen_libros.split(', ') : [])" :key="item">
                            <span>{{ item }}</span>
                            <i class="bi bi-check-circle text-success" v-if="modal.data.estado == 'FINALIZADO'"></i>
                            <i class="bi bi-clock text-warning" v-else></i>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="modal.visible = false">Cerrar</button>
                    <a :href="'detalle_prestamo.php?id=' + modal.data.id" class="btn btn-success" v-if="modal.data.estado == 'PENDIENTE'">
                        Ir a Devolución
                    </a>
                </div>
            </div>
        </div>
    </div>

</div> <!-- Fin App -->
</div> <!-- Fin Wrapper -->

<style>
    .btn-hover-gold:hover { background-color: #c4a030 !important; transform: translateY(-1px); }
    .hover-green:hover { background-color: #e8f5e9 !important; color: #198754 !important; border-color: #198754 !important; }
    .cursor-pointer { cursor: pointer; }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                prestamos: [],
                busqueda: '',
                filtroEstado: 'TODOS',
                modal: { visible: false, data: { resumen_libros: '' } }
            }
        },
        computed: {
            contadores() {
                return {
                    activos: this.prestamos.filter(p => p.estado === 'PENDIENTE' && !this.esVencido(p)).length,
                    vencidos: this.prestamos.filter(p => this.esVencido(p)).length,
                    finalizados: this.prestamos.filter(p => p.estado === 'FINALIZADO').length
                }
            },
            prestamosFiltrados() {
                return this.prestamos.filter(p => {
                    const textoMatch = 
                        p.solicitante.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                        (p.resumen_libros && p.resumen_libros.toLowerCase().includes(this.busqueda.toLowerCase())) || // Ahora busca también por nombre de libro
                        p.id.toString().includes(this.busqueda);
                    
                    let estadoMatch = true;
                    if (this.filtroEstado === 'PENDIENTE') estadoMatch = p.estado === 'PENDIENTE' && !this.esVencido(p);
                    else if (this.filtroEstado === 'VENCIDO') estadoMatch = this.esVencido(p);
                    else if (this.filtroEstado === 'FINALIZADO') estadoMatch = p.estado === 'FINALIZADO';
                    
                    return textoMatch && estadoMatch;
                });
            }
        },
        mounted() {
            this.cargarHistorial();
        },
        methods: {
            async cargarHistorial() {
                try {
                    const res = await fetch('../api/historial.php');
                    this.prestamos = await res.json();
                } catch(e) { console.error(e); }
            },
            esVencido(prestamo) {
                if (prestamo.estado !== 'PENDIENTE') return false;
                
                // Convertir fecha d/m/Y a objeto Date
                const partes = prestamo.fecha_devolucion_pactada.split('/');
                const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                
                const hoy = new Date();
                hoy.setHours(0,0,0,0);
                
                return fechaVence < hoy;
            },
            calcularAtraso(fechaStr) {
                const partes = fechaStr.split('/');
                const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                const hoy = new Date();
                
                const diferencia = hoy - fechaVence;
                const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));
                return dias > 0 ? dias : 0;
            },
            verDetalle(prestamo) {
                this.modal.data = prestamo;
                this.modal.visible = true;
            }
        }
    }).mount('#app')
</script>
</body>
</html>