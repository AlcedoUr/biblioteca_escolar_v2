<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Gestión de Reservas de Material</h3>
            <p class="text-muted mb-0">Programación de uso de recursos bibliográficos.</p>
        </div>
        <button class="btn text-white shadow-sm fw-bold btn-hover-gold" style="background-color: #8B1538;" @click="abrirModal">
            <i class="bi bi-calendar-plus me-2"></i>Nueva Reserva
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Fecha Uso</th>
                            <th>Horario</th>
                            <th>Libro</th>
                            <th class="text-center">Cant.</th>
                            <th>Destino</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in reservas" :key="r.id" :class="{'bg-light opacity-75': r.estado == 'VENCIDA' || r.estado == 'CANCELADA'}">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ r.fecha_fmt }}</div>
                                <div class="small text-muted">{{ getDiaSemana(r.fecha_uso) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock me-1"></i> {{ r.h_inicio }} - {{ r.h_fin }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ r.titulo }}</div>
                                <div class="small text-muted" v-if="rolUsuario == 'ADMINISTRADOR'">Solicita: {{ r.solicitante }}</div>
                            </td>
                            <td class="text-center fw-bold">{{ r.cantidad }}</td>
                            <td>{{ r.grado }} "{{ r.seccion }}"</td>
                            <td>
                                <span v-if="r.estado == 'PENDIENTE'" class="badge bg-warning bg-opacity-10 text-dark border border-warning">Pendiente</span>
                                <span v-else-if="r.estado == 'ENTREGADA'" class="badge bg-success text-white shadow-sm">Confirmada</span>
                                <span v-else-if="r.estado == 'CANCELADA'" class="badge bg-secondary text-white">Cancelada</span>
                                <span v-else class="badge bg-danger text-white shadow-sm">No Entregada</span>
                            </td>
                            <td class="text-end pe-4">
                                <button v-if="r.estado == 'PENDIENTE'" @click="cancelarReserva(r.id)" class="btn btn-sm btn-outline-danger border-0" title="Cancelar Reserva">
                                    <i class="bi bi-x-circle-fill me-1"></i> Cancelar
                                </button>
                                <span v-else class="text-muted small">-</span>
                            </td>
                        </tr>
                        <tr v-if="reservas.length === 0">
                            <td colspan="7" class="text-center py-5 text-muted">No hay reservas registradas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div v-if="modalVisible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #8B1538;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar2-week me-2"></i>Programar Reserva</h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrarModal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    
                    <div class="card border-0 shadow-sm mb-3 transition-step">
                        <div class="card-body position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold small text-muted mb-0">1. ¿Cuándo lo necesita?</label>
                                <i v-if="paso1OK" class="bi bi-check-circle-fill text-success animate-pop"></i>
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <input type="date" v-model="form.fecha" class="form-control fw-bold" :min="minDate" @change="validarDiaSemana">
                                </div>
                                <div class="col-md-7 d-flex align-items-center">
                                    <small class="text-muted" v-if="form.fecha"><i class="bi bi-calendar-check me-1"></i> {{ getDiaSemana(form.fecha) }}</small>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                <div v-for="bloque in bloquesHorarios" 
                                     :key="bloque.id"
                                     @click="toggleBloque(bloque)"
                                     class="card hora-card cursor-pointer text-center p-2"
                                     :class="{'selected': bloquesSeleccionados.includes(bloque.id)}"
                                     style="width: 100px; transition: all 0.2s;">
                                    <div class="fw-bold small">{{ bloque.label }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ bloque.inicio }} - {{ bloque.fin }}</div>
                                    <div class="mt-1" v-if="bloquesSeleccionados.includes(bloque.id)">
                                        <i class="bi bi-check-circle-fill text-white"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3 alert alert-light border d-inline-block w-100 mb-0 py-2" v-if="rangoHorarioTexto">
                                <span class="text-muted small me-2">Horario Solicitado:</span>
                                <strong class="text-vino">{{ rangoHorarioTexto }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3 transition-step" :class="{'disabled-module': !paso1OK}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold small text-muted mb-0">2. Seleccionar Material</label>
                                <i v-if="paso2OK" class="bi bi-check-circle-fill text-success animate-pop"></i>
                            </div>
                            
                            <div v-if="!libroSeleccionado">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0" 
                                           v-model="busquedaLibro" 
                                           placeholder="Buscar libro disponible..." 
                                           @input="buscarLibros">
                                </div>
                                
                                <div v-if="librosEncontrados.length > 0" class="list-group mt-2 shadow-sm border-0" style="max-height: 250px; overflow-y: auto;">
                                    <button v-for="l in librosEncontrados" @click="seleccionarLibro(l)" 
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 border-start-0 border-end-0">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center me-3 border text-secondary fw-bold" style="width: 40px; height: 50px;">
                                                {{ l.titulo.charAt(0) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ l.titulo }}</div>
                                                <div class="small text-muted"><i class="bi bi-person me-1"></i>{{ l.autor }}</div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span v-if="l.stock_disponible_real > 0" class="badge bg-success bg-opacity-10 text-success border border-success mb-1 d-block">
                                                Disp: {{ l.stock_disponible_real }}
                                            </span>
                                            <span v-else class="badge bg-danger bg-opacity-10 text-danger border border-danger mb-1 d-block">
                                                Agotado
                                            </span>
                                            
                                            <div v-if="l.reservados_info > 0" class="text-warning small fw-bold">
                                                <i class="bi bi-exclamation-triangle"></i> {{ l.reservados_info }} Reservados
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div v-else class="alert alert-success d-flex justify-content-between align-items-center mb-0 shadow-sm bg-success bg-opacity-10 border-success">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white rounded-circle p-2 me-3 text-success shadow-sm">
                                        <i class="bi bi-book-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-success mb-0">{{ libroSeleccionado.titulo }}</h6>
                                        <small class="text-success text-opacity-75 fw-bold">Disponible para este horario: {{ libroSeleccionado.stock_disponible_real }}</small>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-light text-danger fw-bold shadow-sm" @click="libroSeleccionado = null">
                                    <i class="bi bi-pencil me-1"></i>Cambiar
                                </button>
                            </div>
                            
                            <div v-if="!paso1OK" class="overlay-lock"><i class="bi bi-lock-fill fs-3 text-muted"></i></div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm transition-step" :class="{'disabled-module': !paso2OK}">
                        <div class="card-body position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold small text-muted mb-0">3. Cantidad y Aula</label>
                                <i v-if="paso3OK" class="bi bi-check-circle-fill text-success animate-pop"></i>
                            </div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="number" v-model="form.cantidad" 
                                           class="form-control text-center fw-bold" 
                                           placeholder="Cant." min="1" 
                                           :max="libroSeleccionado ? libroSeleccionado.stock_disponible_real : 1">
                                </div>
                                <div class="col-4">
                                    <select v-model="form.grado" class="form-select text-center fw-bold p-1">
                                        <option value="" disabled>Grd</option>
                                        <option v-for="g in listaGrados" :value="g">{{ g }}</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select v-model="form.seccion" class="form-select text-center fw-bold p-1">
                                        <option value="" disabled>Sec</option>
                                        <option v-for="s in listaSecciones" :value="s">{{ s }}</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div v-if="libroSeleccionado && form.cantidad > libroSeleccionado.stock_disponible_real" class="text-danger small mt-2 fw-bold animate-pop">
                                <i class="bi bi-x-circle me-1"></i> Supera el stock disponible ({{ libroSeleccionado.stock_disponible_real }})
                            </div>

                            <div v-if="!paso2OK" class="overlay-lock"><i class="bi bi-lock-fill fs-3 text-muted"></i></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 bg-light">
                    <button class="btn btn-outline-secondary fw-bold" @click="cerrarModal">Cancelar</button>
                    <button class="btn text-white fw-bold btn-gold shadow-sm px-4 transition-step" 
                            @click="guardarReserva" 
                            :disabled="cargando || !paso3OK || (libroSeleccionado && form.cantidad > libroSeleccionado.stock_disponible_real)">
                        <span v-if="cargando" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check-lg me-2"></i>Confirmar Reserva
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .btn-hover-gold:hover { background-color: #c4a030 !important; transform: translateY(-1px); }
    .btn-gold { background-color: #D4AF37; border: none; }
    .btn-gold:hover { background-color: #c4a030; box-shadow: 0 4px 8px rgba(212, 175, 55, 0.3); }
    .btn-gold:disabled { background-color: #ccc; box-shadow: none; transform: none; cursor: not-allowed; }
    
    .text-vino { color: #8B1538; }
    
    .hora-card { border: 1px solid #dee2e6; background: white; user-select: none; }
    .hora-card:hover { transform: translateY(-3px); border-color: #8B1538; }
    .hora-card.selected { background-color: #8B1538; color: white; border-color: #8B1538; box-shadow: 0 4px 8px rgba(139, 21, 56, 0.3); }
    .hora-card.selected .text-muted { color: rgba(255,255,255,0.8) !important; }

    .disabled-module { opacity: 0.5; pointer-events: none; filter: grayscale(1); }
    .overlay-lock { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.4); z-index: 10; border-radius: 0.375rem; }
    .transition-step { transition: all 0.3s ease; }
    .animate-pop { animation: pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    @keyframes pop { 0% { transform: scale(0); } 100% { transform: scale(1); } }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                rolUsuario: '<?php echo $_SESSION['user_rol'] ?? ''; ?>',
                reservas: [],
                modalVisible: false,
                cargando: false,
                
                busquedaLibro: '',
                librosEncontrados: [],
                libroSeleccionado: null,
                
                form: { fecha: '', cantidad: 1, grado: '', seccion: '' },
                
                listaGrados: ['1ro', '2do', '3ro', '4to', '5to', '6to'],
                listaSecciones: ['A', 'B', 'C', 'D', 'E', 'F', 'G'],

                // HORARIOS OFICIALES ACTUALIZADOS
                bloquesHorarios: [
                    { id: 1, label: '1° Hora', inicio: '07:30', fin: '08:15' },
                    { id: 2, label: '2° Hora', inicio: '08:15', fin: '09:00' },
                    { id: 3, label: '3° Hora', inicio: '09:00', fin: '09:45' },
                    { id: 4, label: 'Recreo',  inicio: '09:45', fin: '10:15' },
                    { id: 5, label: '4° Hora', inicio: '10:30', fin: '11:15' }, // Ajustado inicio
                    { id: 6, label: '5° Hora', inicio: '11:15', fin: '12:00' },
                    { id: 7, label: '6° Hora', inicio: '12:00', fin: '12:45' },
                    { id: 8, label: 'Salida',  inicio: '12:45', fin: '13:05' }
                ],
                bloquesSeleccionados: []
            }
        },
        computed: {
            minDate() { return new Date().toISOString().split('T')[0]; },
            
            rangoHorarioTexto() {
                if (this.bloquesSeleccionados.length === 0) return '';
                const seleccion = this.bloquesHorarios
                    .filter(b => this.bloquesSeleccionados.includes(b.id))
                    .sort((a,b) => a.id - b.id);
                return `${seleccion[0].inicio} - ${seleccion[seleccion.length - 1].fin}`;
            },
            
            // PASOS WIZARD
            paso1OK() { return this.form.fecha !== '' && this.bloquesSeleccionados.length > 0; },
            paso2OK() { return this.paso1OK && this.libroSeleccionado != null; },
            paso3OK() { return this.paso2OK && this.form.cantidad > 0 && this.form.grado !== '' && this.form.seccion !== ''; }
        },
        mounted() {
            this.cargarReservas();
        },
        methods: {
            async cargarReservas() {
                try {
                    const res = await fetch('../api/reservar.php');
                    this.reservas = await res.json();
                } catch(e) { console.error(e); }
            },
            
            async buscarLibros() {
                if(this.busquedaLibro.length < 3) { this.librosEncontrados = []; return; }
                
                // Calcular horas para filtro
                const seleccion = this.bloquesHorarios
                    .filter(b => this.bloquesSeleccionados.includes(b.id))
                    .sort((a,b) => a.id - b.id);
                
                const hIni = seleccion[0].inicio;
                const hFin = seleccion[seleccion.length - 1].fin;

                // Consulta Inteligente a la API (Stock Real)
                const params = new URLSearchParams({
                    q: this.busquedaLibro,
                    limit: 5,
                    fecha: this.form.fecha,
                    hora_ini: hIni,
                    hora_fin: hFin
                });

                const res = await fetch(`../api/libros.php?${params}`);
                const data = await res.json();
                this.librosEncontrados = data.data;
            },

            seleccionarLibro(l) {
                if (l.stock_disponible_real <= 0) {
                    Swal.fire('Agotado', 'No hay unidades disponibles en ese horario.', 'warning');
                    return;
                }
                this.libroSeleccionado = l;
                this.librosEncontrados = [];
                this.busquedaLibro = '';
                this.form.cantidad = 1; 
            },
            
            validarDiaSemana() {
                if(!this.form.fecha) return;
                const d = new Date(this.form.fecha + 'T00:00:00');
                const dia = d.getDay();
                if (dia === 0 || dia === 6) {
                    Swal.fire({ icon: 'warning', title: 'Día no permitido', text: 'Solo Lunes a Viernes.', confirmButtonColor: '#8B1538' });
                    this.form.fecha = '';
                }
                // Resetear libro si cambia fecha
                this.libroSeleccionado = null;
            },
            
            toggleBloque(bloque) {
                const index = this.bloquesSeleccionados.indexOf(bloque.id);
                if (index === -1) {
                    this.bloquesSeleccionados.push(bloque.id);
                } else {
                    this.bloquesSeleccionados.splice(index, 1);
                }
                // Resetear libro si cambia hora
                this.libroSeleccionado = null;
            },

            abrirModal() { 
                this.modalVisible = true; 
                this.form = { fecha: '', cantidad: 1, grado: '', seccion: '' }; 
                this.libroSeleccionado = null; 
                this.bloquesSeleccionados = []; 
            },
            cerrarModal() { this.modalVisible = false; },
            
            getDiaSemana(fechaStr) {
                const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                return dias[new Date(fechaStr + 'T00:00:00').getDay()];
            },

            async guardarReserva() {
                if (!this.paso3OK) return;

                const seleccion = this.bloquesHorarios.filter(b => this.bloquesSeleccionados.includes(b.id)).sort((a,b) => a.id - b.id);
                const payload = {
                    id_libro: this.libroSeleccionado.id,
                    fecha: this.form.fecha,
                    hora_inicio: seleccion[0].inicio,
                    hora_fin: seleccion[seleccion.length - 1].fin,
                    cantidad: this.form.cantidad,
                    grado: this.form.grado,
                    seccion: this.form.seccion
                };

                this.cargando = true;
                try {
                    const res = await fetch('../api/reservar.php', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    
                    if (data.exito) {
                        Swal.fire({ icon: 'success', title: 'Reserva Exitosa', text: 'El material ha sido separado.', confirmButtonColor: '#198754' });
                        this.cerrarModal();
                        this.cargarReservas();
                    } else {
                        Swal.fire({ icon: 'error', title: 'No disponible', text: data.mensaje, confirmButtonColor: '#dc3545' });
                    }
                } catch(e) {
                    Swal.fire('Error', 'Error de conexión', 'error');
                } finally {
                    this.cargando = false;
                }
            },

            async cancelarReserva(id) {
                const result = await Swal.fire({
                    title: '¿Cancelar reserva?',
                    text: "Esta acción liberará los libros para otros usuarios.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No'
                });

                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`../api/reservar.php?id=${id}`, { method: 'DELETE' });
                        const data = await res.json();
                        if (data.exito) {
                            Swal.fire('Cancelada', 'La reserva ha sido cancelada.', 'success');
                            this.cargarReservas();
                        } else {
                            Swal.fire('Error', data.mensaje, 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Fallo de conexión', 'error');
                    }
                }
            }
        }
    }).mount('#app')
</script>