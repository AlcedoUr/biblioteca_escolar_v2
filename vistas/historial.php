<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Gestión de Préstamos y Reservas</h3>
            <p class="text-muted mb-0">Control unificado de circulación de material.</p>
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
                        <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar por solicitante, libro...">
                    </div>
                </div>
                <div class="col-md-3 ms-auto">
                    <select v-model="filtroEstado" class="form-select border-0 bg-white shadow-sm cursor-pointer py-2">
                        <option value="TODOS">Todos los registros</option>
                        <option value="RESERVADO">📅 Reservas Pendientes</option>
                        <option value="PENDIENTE">📖 Préstamos Activos</option>
                        <option value="VENCIDO">⚠️ Préstamos Vencidos</option>
                        <option value="FINALIZADO">✅ Historial Finalizado</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height: 400px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Solicitante</th>
                            <th>Tipo / Origen</th>
                            <th style="width: 30%;">Material</th>
                            <th>Vencimiento / Horario</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in listaFiltrada" :key="p.tipo_registro + '-' + p.id" class="animate-fade">
                            
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ p.solicitante }}</div>
                                <span v-if="p.tipo_solicitante == 'ESTUDIANTE'" class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.65rem;">Estudiante</span>
                                <span v-else class="badge bg-light text-success border border-success rounded-pill" style="font-size: 0.65rem;">Docente</span>
                            </td>

                            <td>
                                <div v-if="p.tipo_registro == 'RESERVA'">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill mb-1">
                                        <i class="bi bi-calendar-check me-1"></i> Reserva
                                    </span>
                                    <div class="small text-muted">Aula {{ p.grado }} "{{ p.seccion }}"</div>
                                </div>
                                <div v-else>
                                    <div v-if="p.es_reserva_convertida" class="mb-1">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill" style="font-size: 0.6rem;">
                                            <i class="bi bi-bookmark-star-fill me-1"></i> Origen Reserva
                                        </span>
                                    </div>
                                    <div v-if="p.aula_info" class="small text-dark fw-bold"><i class="bi bi-easel2 me-1 text-danger"></i> En Aula: {{ p.aula_info }}</div>
                                    <div v-else class="small text-muted"><i class="bi bi-house-door me-1 text-success"></i> Domicilio</div>
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
                                    <span class="fw-bold text-dark small">{{ p.fecha_fin_fmt }}</span>
                                    <small v-if="p.hora_limite" class="text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i> Límite: {{ p.hora_limite }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                <div v-if="p.tipo_registro == 'RESERVA'">
                                    <span class="badge bg-info text-white shadow-sm">Por Entregar</span>
                                </div>
                                <div v-else-if="esVencido(p)">
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 rounded-pill px-3">Vencido</span>
                                </div>
                                <div v-else-if="p.estado == 'PENDIENTE'">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-3">Activo</span>
                                </div>
                                <div v-else>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 rounded-pill px-3">Devuelto</span>
                                </div>
                            </td>

                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm border-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow p-2">
                                        
                                        <li v-if="p.tipo_registro == 'RESERVA'">
                                            <a class="dropdown-item rounded small py-2 fw-bold text-success bg-success bg-opacity-10 mb-1" href="#" @click.prevent="entregarReserva(p)">
                                                <i class="bi bi-box-seam me-2"></i>Entregar Material
                                            </a>
                                            <hr class="dropdown-divider">
                                            <a class="dropdown-item rounded small py-2 text-danger" href="#" @click.prevent="cancelarReserva(p.id)">
                                                <i class="bi bi-x-circle me-2"></i>Cancelar
                                            </a>
                                        </li>

                                        <li v-else>
                                            <a class="dropdown-item rounded small py-2 mb-1" href="#" @click.prevent="verDetalle(p)">
                                                <i class="bi bi-eye-fill text-primary me-2"></i>Ver Detalles
                                            </a>
                                            <a v-if="p.estado == 'PENDIENTE'" class="dropdown-item rounded small py-2 fw-bold text-success" 
                                               :href="'detalle_prestamo.php?id=' + p.id">
                                                <i class="bi bi-box-arrow-in-down me-2"></i>Devolver
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="listaFiltrada.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div v-if="cargando" class="spinner-border text-secondary" role="status"></div>
                                <div v-else>
                                    <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
                                    No se encontraron registros.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div v-if="modal.visible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);" @click.self="cerrarModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-3">
                <div class="modal-header text-white border-0" style="background-color: #8B1538;">
                    <h5 class="modal-title fw-bold">Préstamo #{{ modal.data.id }}</h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrarModal"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <div v-if="modal.data.es_reserva_convertida" class="alert alert-info border-0 d-flex align-items-center mb-3 shadow-sm">
                        <i class="bi bi-bookmark-star-fill me-3 fs-4 text-info"></i>
                        <div>
                            <strong class="d-block text-info">Origen: Reserva</strong>
                            <small class="text-muted">Generado desde una reserva confirmada.</small>
                        </div>
                    </div>

                    <div v-if="esVencido(modal.data) && modal.data.estado == 'PENDIENTE'" class="alert alert-danger border-0 d-flex align-items-start mb-3">
                        <i class="bi bi-exclamation-octagon-fill me-2 mt-1"></i>
                        <div>
                            <strong>Vencido</strong><br>Debió devolverse: {{ modal.data.fecha_fin_fmt }}
                        </div>
                    </div>

                    <ul class="list-group list-group-flush border rounded bg-light">
                        <li class="list-group-item d-flex align-items-center py-3 bg-transparent" v-for="item in (modal.data.resumen_libros ? modal.data.resumen_libros.split(', ') : [])">
                            <i class="bi bi-book-fill me-2 text-secondary"></i> <span class="small fw-bold text-dark">{{ item }}</span>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button class="btn btn-outline-secondary" @click="cerrarModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const { createApp, nextTick } = Vue
    createApp({
        data() { 
            return { 
                items: [], 
                busqueda: '', 
                filtroEstado: 'TODOS', 
                modal: { visible: false, data: {} },
                cargando: false
            } 
        },
        computed: {
            listaFiltrada() {
                return this.items.filter(p => {
                    const textoMatch = p.solicitante.toLowerCase().includes(this.busqueda.toLowerCase()) || 
                                       (p.resumen_libros && p.resumen_libros.toLowerCase().includes(this.busqueda.toLowerCase()));
                    
                    let estadoMatch = true;
                    const vencido = this.esVencido(p);

                    if (this.filtroEstado === 'PENDIENTE') estadoMatch = p.estado === 'PENDIENTE' && !vencido && p.tipo_registro === 'PRESTAMO';
                    else if (this.filtroEstado === 'RESERVADO') estadoMatch = p.tipo_registro === 'RESERVA';
                    else if (this.filtroEstado === 'VENCIDO') estadoMatch = vencido && p.estado === 'PENDIENTE' && p.tipo_registro === 'PRESTAMO';
                    else if (this.filtroEstado === 'FINALIZADO') estadoMatch = p.estado === 'FINALIZADO';
                    
                    return textoMatch && estadoMatch;
                });
            }
        },
        mounted() { this.cargarDatos(); setInterval(this.cargarDatos, 30000); },
        methods: {
            async cargarDatos() {
                this.cargando = true;
                try {
                    const res = await fetch('../api/historial.php');
                    const data = await res.json();
                    
                    if(data.error) {
                        console.error("Error SQL:", data.mensaje);
                        // Si hay error SQL, la lista se vacía
                        this.items = [];
                    } else {
                        this.items = data;
                    }
                } catch(e) { 
                    console.error("Error JS:", e); 
                } finally {
                    this.cargando = false;
                }
            },
            esVencido(p) {
                if (p.estado !== 'PENDIENTE' || p.tipo_registro === 'RESERVA') return false;
                
                const partes = p.fecha_fin_fmt.split('/');
                if(partes.length < 3) return false;
                
                const fVence = new Date(partes[2], partes[1]-1, partes[0]);
                const hoy = new Date(); hoy.setHours(0,0,0,0);
                
                if (fVence < hoy) return true;
                if (fVence.getTime() === hoy.getTime() && p.hora_limite) {
                    const [h, m] = p.hora_limite.split(':');
                    const lim = new Date(); lim.setHours(h, m, 0);
                    if (new Date() > lim) return true;
                }
                return false;
            },
            verDetalle(p) { this.modal.data = p; this.modal.visible = true; },
            cerrarModal() { this.modal.visible = false; },
            
            async entregarReserva(r) {
                const res = await Swal.fire({
                    title: '¿Entregar Material?',
                    text: `Confirmar entrega de "${r.resumen_libros}"`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'Sí, Entregar'
                });
                if (res.isConfirmed) {
                    try {
                        const api = await fetch('../api/entregar_reserva.php', {
                            method: 'POST', headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_reserva: r.id })
                        });
                        const resp = await api.json();
                        if (resp.exito) { 
                            Swal.fire('Entregado', 'El préstamo ha sido creado.', 'success'); 
                            this.cargarDatos(); 
                        } else { 
                            Swal.fire('Error', resp.mensaje, 'error'); 
                        }
                    } catch(e) { Swal.fire('Error', 'Fallo de conexión', 'error'); }
                }
            },
            async cancelarReserva(id) {
               Swal.fire('Info', 'Función de cancelar no implementada en backend aún.', 'info');
            }
        }
    }).mount('#app')
</script>