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
                            <th>Ubicación</th>
                            <th style="width: 25%;">Libros Prestados</th>
                            <th>Vencimiento</th>
                            <th>Horario</th> <!-- NUEVA COLUMNA -->
                            <th>Estado</th>
                            <th>Atraso</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in prestamosFiltrados" :key="p.id" :class="{'bg-danger bg-opacity-10': esVencido(p)}">
                            
                            <!-- Solicitante -->
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ p.solicitante }}</div>
                                <div class="d-flex align-items-center gap-2">
                                    <span v-if="p.tipo_solicitante == 'ESTUDIANTE'" class="badge bg-light text-secondary border" style="font-size: 0.6rem;">Estudiante</span>
                                    <span v-else class="badge bg-light text-success border border-success" style="font-size: 0.6rem;">Docente</span>
                                </div>
                            </td>

                            <!-- Ubicación -->
                            <td>
                                <div v-if="p.aula_info" class="fw-bold text-dark small">
                                    <i class="bi bi-easel2-fill text-warning me-1"></i> {{ p.aula_info }}
                                </div>
                                <div v-else-if="p.tipo_solicitante == 'ESTUDIANTE'" class="small text-dark">
                                    {{ p.grado }} "{{ p.seccion }}"
                                </div>
                                <div v-else class="text-muted small fst-italic">-</div>
                            </td>

                            <!-- Libros -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">{{ p.total_libros }}</span>
                                    <span class="text-muted small text-truncate d-inline-block" style="max-width: 200px;" :title="p.resumen_libros">
                                        {{ p.resumen_libros }}
                                    </span>
                                </div>
                            </td>

                            <!-- Fecha Vencimiento -->
                            <td>
                                <div class="fw-bold small" :class="esVencido(p) ? 'text-danger' : 'text-dark'">
                                    {{ p.fecha_devolucion_pactada }}
                                </div>
                            </td>

                            <!-- Horario Límite (NUEVA COLUMNA VISIBLE) -->
                            <td>
                                <span v-if="p.hora_limite" class="fw-bold" :class="esVencido(p) ? 'text-danger' : 'text-primary'">
                                    <i class="bi bi-clock me-1"></i> {{ p.hora_limite }}
                                </span>
                                <span v-else class="text-muted small">-</span>
                            </td>

                            <!-- Estado -->
                            <td>
                                <span v-if="esVencido(p)" class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">Vencido</span>
                                <span v-else-if="p.estado == 'PENDIENTE'" class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2">Activo</span>
                                <span v-else class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">Devuelto</span>
                                
                                <!-- Texto de atraso si venció hoy por hora -->
                                <div v-if="esVencido(p) && p.estado === 'PENDIENTE'" class="text-danger fw-bold" style="font-size: 0.65rem; margin-top: 2px;">
                                    ¡ATRASADO!
                                </div>
                            </td>

                            <!-- Atraso -->
                            <td>
                                <span v-if="esVencido(p)" class="text-danger fw-bold small">
                                    {{ calcularAtraso(p) }}
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
                            <td colspan="8" class="text-center py-5 text-muted">No se encontraron registros.</td>
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
                    </div>
                    <div class="mb-3" v-if="modal.data.hora_limite">
                        <label class="small text-danger fw-bold">Hora Límite de Entrega:</label>
                        <div class="text-danger fw-bold fs-5"><i class="bi bi-alarm"></i> {{ modal.data.hora_limite }}</div>
                    </div>
                    <ul class="list-group mb-3">
                        <li class="list-group-item" v-for="item in (modal.data.resumen_libros ? modal.data.resumen_libros.split(', ') : [])" :key="item">
                            <i class="bi bi-book me-2 text-secondary"></i> {{ item }}
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="modal.visible = false">Cerrar</button>
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
                        (p.resumen_libros && p.resumen_libros.toLowerCase().includes(this.busqueda.toLowerCase())) || 
                        p.id.toString().includes(this.busqueda);
                    
                    let estadoMatch = true;
                    const estaVencido = this.esVencido(p); // Calculamos una sola vez

                    if (this.filtroEstado === 'PENDIENTE') estadoMatch = p.estado === 'PENDIENTE' && !estaVencido;
                    else if (this.filtroEstado === 'VENCIDO') estadoMatch = estaVencido; // Ahora incluye vencidos por hora
                    else if (this.filtroEstado === 'FINALIZADO') estadoMatch = p.estado === 'FINALIZADO';
                    
                    return textoMatch && estadoMatch;
                });
            }
        },
        mounted() {
            this.cargarHistorial();
            // Recargar cada minuto para actualizar vencimientos por hora en tiempo real
            setInterval(this.cargarHistorial, 60000);
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
                
                // Parsear Fecha Vencimiento
                const partes = prestamo.fecha_devolucion_pactada.split('/');
                const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                
                const ahora = new Date();
                const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate()); // Hoy 00:00:00
                const fechaVenceSinHora = new Date(fechaVence.getFullYear(), fechaVence.getMonth(), fechaVence.getDate());

                // 1. Si la fecha ya pasó (ayer o antes)
                if (fechaVenceSinHora < hoy) return true;

                // 2. Si es HOY, verificar HORA
                if (fechaVenceSinHora.getTime() === hoy.getTime() && prestamo.hora_limite) {
                    const [horas, minutos] = prestamo.hora_limite.split(':');
                    // Crear objeto fecha con la hora límite de hoy
                    const limiteHoy = new Date();
                    limiteHoy.setHours(horas, minutos, 0);

                    // Si la hora actual es mayor a la límite, está vencido
                    if (ahora > limiteHoy) {
                        return true; // ¡Ya pasó la hora!
                    }
                }

                return false;
            },
            calcularAtraso(prestamo) {
                const partes = prestamo.fecha_devolucion_pactada.split('/');
                const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                const hoy = new Date();
                
                // Si es hoy y venció por hora
                if (this.esVencido(prestamo) && fechaVence.getDate() === hoy.getDate()) {
                    return "Horas";
                }
                
                // Si venció por días
                const diferencia = hoy - fechaVence;
                const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));
                return dias > 0 ? dias + " días" : 0;
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