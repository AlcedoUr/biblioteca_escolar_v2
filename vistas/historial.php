<?php include 'includes/header.php'; ?>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Gestión de Préstamos</h3>
            <p class="text-muted">Monitoreo de material en circulación</p>
        </div>
        <a href="prestamo_lote.php" class="btn text-white px-4 py-2 fw-bold shadow-sm btn-hover-gold" style="background-color: #8B1538;">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Préstamo
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group border-0 bg-white rounded px-2 py-1 shadow-sm">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar por solicitante, libro o código...">
                    </div>
                </div>
                <div class="col-md-3 ms-auto">
                    <select v-model="filtroEstado" class="form-select border-0 bg-white shadow-sm cursor-pointer py-2">
                        <option value="TODOS">Todos los estados</option>
                        <option value="PENDIENTE">Activos</option>
                        <option value="VENCIDO">Vencidos</option>
                        <option value="FINALIZADO">Finalizados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height: 400px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4 py-3">Solicitante</th>
                            <th>Ubicación</th>
                            <th style="width: 30%;">Libros Prestados</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in prestamosFiltrados" :key="p.id" class="animate-fade">
                            
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ p.solicitante }}</div>
                                <span v-if="p.tipo_solicitante == 'ESTUDIANTE'" class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.65rem;">Estudiante</span>
                                <span v-else class="badge bg-light text-success border border-success rounded-pill" style="font-size: 0.65rem;">Docente</span>
                            </td>

                            <td>
                                <div v-if="p.aula_info" class="d-flex align-items-center fw-bold text-dark small">
                                    <i class="bi bi-easel2-fill text-danger me-2" title="En Aula"></i>
                                    <span>{{ p.aula_info }}</span>
                                </div>
                                <div v-else-if="p.tipo_solicitante == 'ESTUDIANTE'" class="d-flex align-items-center fw-bold text-dark small">
                                    <i class="bi bi-geo-alt-fill text-primary me-2" title="Salón Base"></i>
                                    <span>{{ p.grado }} "{{ p.seccion }}"</span>
                                </div>
                                <div v-else class="d-flex align-items-center fw-bold text-muted small">
                                    <i class="bi bi-house-door-fill text-success me-2" title="Domicilio"></i>
                                    <span>Domicilio</span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-secondary bg-opacity-10 text-dark me-2 border">{{ p.total_libros }}</div>
                                    <span class="text-muted small text-truncate d-inline-block" style="max-width: 250px;" :title="p.resumen_libros">
                                        {{ p.resumen_libros }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark small">{{ p.fecha_devolucion_pactada }}</span>
                                    <small v-if="p.hora_limite" class="text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i>{{ p.hora_limite }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                <div v-if="esVencido(p)">
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 rounded-pill px-3">
                                        Vencido
                                    </span>
                                </div>
                                <div v-else-if="p.estado == 'PENDIENTE'">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-3">
                                        Activo
                                    </span>
                                </div>
                                <div v-else>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 rounded-pill px-3">
                                        Devuelto
                                    </span>
                                </div>
                            </td>

                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm border-0" 
                                            type="button" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Opciones">
                                        <i class="bi bi-three-dots-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow p-2" style="min-width: 160px;">
                                        <li>
                                            <a class="dropdown-item rounded small py-2 mb-1" href="#" @click.prevent="verDetalle(p)">
                                                <i class="bi bi-eye-fill text-primary me-2"></i>Ver Detalles
                                            </a>
                                        </li>
                                        <li v-if="p.estado == 'PENDIENTE'">
                                            <a class="dropdown-item rounded small py-2 fw-bold text-success bg-success bg-opacity-10" 
                                               :href="'detalle_prestamo.php?id=' + p.id">
                                                <i class="bi bi-box-arrow-in-down me-2"></i>Devolver
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="prestamosFiltrados.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
                                No se encontraron registros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div v-if="modal.visible" 
         class="modal fade show d-block" 
         tabindex="-1" 
         role="dialog" 
         aria-modal="true"
         aria-labelledby="modalTitle"
         style="background: rgba(0,0,0,0.5);"
         @keydown.esc="cerrarModal"
         @click.self="cerrarModal">
         
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow rounded-3">
                
                <div class="modal-header text-white border-0" style="background-color: #8B1538;">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalTitle">Préstamo #{{ modal.data.id }}</h5>
                        <small class="opacity-75 d-block" style="font-size: 0.8rem;">
                            <i class="bi bi-calendar-check me-1"></i>Emitido el: {{ modal.data.fecha_prestamo }}
                        </small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" @click="cerrarModal" aria-label="Cerrar"></button>
                </div>
                
                <div class="modal-body p-4">
                    
                    <div v-if="esVencido(modal.data) && modal.data.estado == 'PENDIENTE'" 
                         class="alert alert-danger border-0 d-flex align-items-start mb-4 shadow-sm" 
                         role="alert">
                        <div class="me-3 mt-1">
                            <i class="bi bi-exclamation-octagon-fill fs-1 text-danger"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-danger">⛔ Préstamo vencido hace {{ calcularAtraso(modal.data) }}</h6>
                            <p class="mb-0 small text-dark">
                                ⚠️ Este préstamo debería haberse devuelto el 
                                <strong>{{ modal.data.fecha_devolucion_pactada }}</strong>
                                <span v-if="modal.data.hora_limite"> a las <strong>{{ modal.data.hora_limite }}</strong></span>.
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span v-if="modal.data.aula_info" class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                            <i class="bi bi-easel2-fill me-2"></i>Préstamo en Aula: {{ modal.data.aula_info }}
                        </span>
                        <span v-else class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                            <i class="bi bi-house-door-fill me-2"></i>Préstamo a Domicilio
                        </span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="small text-muted fw-bold text-uppercase">Solicitante</label>
                            <div class="fs-5 fw-bold text-dark">{{ modal.data.solicitante }}</div>
                            <div class="small text-muted">{{ modal.data.grado }} {{ modal.data.seccion }}</div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-muted small text-uppercase mb-2">Material Prestado</h6>
                    <ul class="list-group list-group-flush border rounded bg-light">
                        <li class="list-group-item d-flex align-items-center py-3 bg-transparent" v-for="item in (modal.data.resumen_libros ? modal.data.resumen_libros.split(', ') : [])" :key="item">
                            <div class="me-3 rounded bg-white text-secondary d-flex align-items-center justify-content-center border shadow-sm" style="width: 35px; height: 35px;">
                                <i class="bi bi-book-fill small"></i>
                            </div>
                            <span class="small fw-bold text-dark">{{ item }}</span>
                        </li>
                    </ul>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-outline-secondary fw-bold px-4 btn-close-custom" 
                            @click="cerrarModal" 
                            ref="btnCerrar">
                        Cerrar
                    </button>
                    
                    <a v-if="modal.data.estado == 'PENDIENTE'" 
                       :href="'detalle_prestamo.php?id=' + modal.data.id"
                       class="btn text-white fw-bold shadow-sm px-4 btn-action-primary"
                       style="background-color: #8B1538;">
                        <i class="bi bi-box-arrow-in-down me-2"></i>Registrar devolución
                    </a>
                </div>
            </div>
        </div>
    </div>

</div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .btn-hover-gold:hover { background-color: #c4a030 !important; transform: translateY(-1px); }
    .animate-fade { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    
    /* Dropdown Styles */
    .dropdown-toggle::after { display: none; }
    .dropdown-item:active { background-color: #f8f9fa; color: #000; }

    /* Estilos Mejorados Botones Modal */
    .btn-close-custom {
        border-color: #ccc;
        color: #555;
        transition: all 0.2s;
    }
    .btn-close-custom:hover {
        background-color: #e2e6ea;
        color: #000;
        border-color: #adb5bd;
        cursor: pointer;
    }
    
    .btn-action-primary {
        transition: all 0.2s;
    }
    .btn-action-primary:hover {
        background-color: #a01a45 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
    }
</style>

<script>
    const { createApp, nextTick } = Vue

    createApp({
        data() {
            return {
                prestamos: [],
                busqueda: '',
                filtroEstado: 'TODOS',
                modal: { visible: false, data: {} }
            }
        },
        computed: {
            prestamosFiltrados() {
                return this.prestamos.filter(p => {
                    const textoMatch = 
                        p.solicitante.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                        (p.resumen_libros && p.resumen_libros.toLowerCase().includes(this.busqueda.toLowerCase())) || 
                        p.id.toString().includes(this.busqueda);
                    
                    let estadoMatch = true;
                    const vencido = this.esVencido(p);

                    if (this.filtroEstado === 'PENDIENTE') estadoMatch = p.estado === 'PENDIENTE' && !vencido;
                    else if (this.filtroEstado === 'VENCIDO') estadoMatch = vencido && p.estado === 'PENDIENTE';
                    else if (this.filtroEstado === 'FINALIZADO') estadoMatch = p.estado === 'FINALIZADO';
                    
                    return textoMatch && estadoMatch;
                });
            }
        },
        mounted() {
            this.cargarHistorial();
            setInterval(this.cargarHistorial, 60000);
            
            setTimeout(() => {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }, 1000);
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
                
                const partes = prestamo.fecha_devolucion_pactada.split('/');
                const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                
                const ahora = new Date();
                const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate());
                const fechaVenceSinHora = new Date(fechaVence.getFullYear(), fechaVence.getMonth(), fechaVence.getDate());

                if (fechaVenceSinHora < hoy) return true;

                if (fechaVenceSinHora.getTime() === hoy.getTime() && prestamo.hora_limite) {
                    const [horas, minutos] = prestamo.hora_limite.split(':');
                    const limiteHoy = new Date();
                    limiteHoy.setHours(horas, minutos, 0);
                    if (ahora > limiteHoy) return true;
                }
                return false;
            },
            calcularAtraso(prestamo) {
                const partes = prestamo.fecha_devolucion_pactada.split('/');
                const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                const hoy = new Date();
                
                // Si es hoy y ya pasó la hora
                if (this.esVencido(prestamo) && fechaVence.getDate() === hoy.getDate() && fechaVence.getMonth() === hoy.getMonth()) {
                    return "unas horas";
                }
                
                const diferencia = hoy - fechaVence;
                const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));
                return dias + (dias === 1 ? " día" : " días");
            },
            verDetalle(prestamo) {
                this.modal.data = prestamo;
                this.modal.visible = true;
                // Accesibilidad: Mover foco al botón de cierre al abrir
                nextTick(() => {
                    if(this.$refs.btnCerrar) this.$refs.btnCerrar.focus();
                });
            },
            cerrarModal() {
                this.modal.visible = false;
            }
        }
    }).mount('#app')
</script>
</body>
</html>