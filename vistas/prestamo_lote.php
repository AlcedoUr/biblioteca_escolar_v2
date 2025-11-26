<?php include 'includes/header.php'; ?>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Nuevo Préstamo</h3>
            <p class="text-muted mb-0">Siga los pasos para registrar la salida.</p>
        </div>
        <a href="historial.php" class="btn btn-light border text-muted shadow-sm">Cancelar</a>
    </div>

    <div class="mb-4">
        <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-success transition-width" role="progressbar" :style="{ width: progresoPorcentaje + '%' }"></div>
        </div>
        <div class="d-flex justify-content-between mt-2 small fw-bold text-uppercase text-muted">
            <span :class="{'text-success': pasoActual >= 1}">1. Quién</span>
            <span :class="{'text-success': pasoActual >= 2}">2. Cuándo</span>
            <span :class="{'text-success': pasoActual >= 3}">3. Qué (Libros)</span>
            <span :class="{'text-success': pasoActual >= 4}">4. Confirmar</span>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-md-8">
            
            <div class="card border-0 shadow-sm mb-3 transition-step" :class="{'border-success': pasoActual > 1}">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center cursor-pointer" @click="irAPaso(1)">
                    <h6 class="mb-0 fw-bold" :class="pasoActual > 1 ? 'text-success' : 'text-dark'">1. Datos del Solicitante</h6>
                    <i v-if="pasoActual > 1" class="bi bi-check-circle-fill text-success fs-5"></i>
                </div>
                
                <div class="card-body" v-show="pasoActual === 1">
                    <div class="input-group input-group-lg border rounded mb-3">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-0 shadow-none" 
                               placeholder="Buscar por nombre o DNI..." 
                               v-model="filtroPersona"
                               @input="buscarPersona">
                    </div>

                    <div class="list-group shadow-sm" v-if="personasFiltradas.length > 0">
                        <button v-for="p in personasFiltradas" class="list-group-item list-group-item-action p-3" @click="seleccionarPersona(p)">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-bold">{{ p.apellidos }}, {{ p.nombres }}</div>
                                    <small class="text-muted">{{ p.tipo }} - {{ p.dni }}</small>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="card-body bg-light py-2" v-if="pasoActual > 1">
                    <div class="d-flex align-items-center text-success">
                        <i class="bi bi-person-fill me-2"></i>
                        <strong>{{ personaSeleccionada.apellidos }}, {{ personaSeleccionada.nombres }}</strong>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3 transition-step" :class="{'opacity-50': pasoActual < 2, 'border-success': pasoActual > 2}">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center cursor-pointer" @click="pasoActual > 1 ? irAPaso(2) : null">
                    <h6 class="mb-0 fw-bold" :class="pasoActual > 2 ? 'text-success' : 'text-dark'">2. Horario y Ubicación</h6>
                    <i v-if="pasoActual > 2" class="bi bi-check-circle-fill text-success fs-5"></i>
                </div>

                <div class="card-body" v-show="pasoActual === 2">
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Modalidad de Préstamo</label>
                        <div class="btn-group w-100">
                            <button class="btn btn-lg fw-bold" 
                                    :class="tipoPrestamo === 'HORAS' ? 'btn-vino text-white' : 'btn-outline-secondary'"
                                    @click="setTipo('HORAS')">
                                <i class="bi bi-clock me-2"></i>Por Horas (Aula)
                            </button>
                            <button class="btn btn-lg fw-bold" 
                                    :class="tipoPrestamo === 'DIAS' ? 'btn-vino text-white' : 'btn-outline-secondary'"
                                    @click="setTipo('DIAS')">
                                <i class="bi bi-calendar-range me-2"></i>Por Días (Casa)
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Grado</label>
                            <select v-model="form.grado" class="form-select">
                                <option>1ro</option><option>2do</option><option>3ro</option><option>4to</option><option>5to</option><option>6to</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sección</label>
                            <select v-model="form.seccion" class="form-select">
                                <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option><option>F</option><option>G</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="tipoPrestamo === 'DIAS'" class="mb-4">
                        <label class="form-label small fw-bold text-muted">Fecha de Devolución</label>
                        <input type="date" v-model="form.fecha" class="form-control fw-bold text-center" :min="minDate">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-muted mb-2">
                            {{ tipoPrestamo === 'HORAS' ? 'Seleccione Bloques de Uso' : 'Seleccione Horario de Devolución' }}
                        </label>
                        
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <div v-for="bloque in bloquesHorarios" 
                                 :key="bloque.id"
                                 @click="toggleBloque(bloque)"
                                 class="card hora-card cursor-pointer text-center p-2 shadow-sm"
                                 :class="{'selected': bloquesSeleccionados.includes(bloque.id)}"
                                 style="width: 110px; transition: all 0.2s;">
                                
                                <div class="fw-bold small">{{ bloque.label }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    {{ bloque.inicio }} - {{ bloque.fin }}
                                </div>
                                
                                <div class="mt-1" v-if="bloquesSeleccionados.includes(bloque.id)">
                                    <i class="bi bi-check-circle-fill text-white"></i>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3 alert alert-light border py-2" v-if="rangoHorarioTexto">
                            <small class="text-muted me-2">
                                {{ tipoPrestamo === 'HORAS' ? 'Horario Solicitado:' : 'Hora de Devolución:' }}
                            </small>
                            <strong class="text-vino">
                                {{ tipoPrestamo === 'HORAS' ? rangoHorarioTexto : horaFinTexto }}
                            </strong>
                            <div class="small text-success mt-1 fw-bold">
                                <i class="bi bi-info-circle me-1"></i> Se sumarán 10 min de tolerancia
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-success w-100 mt-4 fw-bold py-2" @click="confirmarHorario" :disabled="!horarioValido">
                        Continuar a Libros <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>

                <div class="card-body bg-light py-2" v-if="pasoActual > 2">
                    <div class="d-flex gap-3 small text-dark">
                        <span v-if="tipoPrestamo == 'HORAS'">
                            <i class="bi bi-clock me-1"></i> Hoy: {{ rangoHorarioTexto }}
                        </span>
                        <span v-else>
                            <i class="bi bi-calendar-event me-1"></i> Hasta {{ form.fecha }} {{ horaFinTexto }}
                        </span>
                        <span><i class="bi bi-geo-alt me-1"></i> {{ form.grado }} "{{ form.seccion }}"</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3 transition-step" :class="{'opacity-50': pasoActual < 3}">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">3. Selección de Material</h6>
                    <div v-if="cargandoLibros" class="spinner-border spinner-border-sm text-primary"></div>
                    <span v-else class="badge bg-light text-dark border">{{ librosFiltrados.length }} Disp.</span>
                </div>

                <div class="card-body p-0" v-show="pasoActual >= 3">
                    <div class="p-3 border-bottom">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" v-model="busquedaLibro" class="form-control border-start-0" placeholder="Filtrar libros disponibles...">
                        </div>
                    </div>

                    <div class="list-group list-group-flush custom-scrollbar" style="max-height: 400px; overflow-y: auto;">
                        <div v-for="libro in librosFiltrados.slice(0, 50)" class="list-group-item p-3 d-flex align-items-center">
                            <div class="me-3 rounded bg-light border d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 40px; height: 50px;">
                                {{ libro.titulo.charAt(0) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-truncate" style="max-width: 350px;">{{ libro.titulo }}</div>
                                
                                <div class="d-flex align-items-center gap-3 mt-1" style="font-size: 0.75rem;">
                                    <div class="text-muted">Físico: <strong>{{ libro.stock_disponible_fisico }}</strong></div>
                                    
                                    <div v-if="libro.reservados_info > 0" class="text-warning fw-bold">
                                        <i class="bi bi-exclamation-circle"></i> {{ libro.reservados_info }} Reservados
                                    </div>

                                    <div v-if="libro.stock_disponible_real > 0" class="text-success fw-bold border border-success px-2 rounded bg-success bg-opacity-10">
                                        Disp: {{ libro.stock_disponible_real }}
                                    </div>
                                    <div v-else class="text-danger fw-bold border border-danger px-2 rounded bg-danger bg-opacity-10">
                                        Agotado
                                    </div>
                                </div>
                            </div>

                            <button v-if="estaEnCarrito(libro)" @click="toggleCarrito(libro)" class="btn btn-sm btn-outline-danger fw-bold">Quitar</button>
                            <button v-else-if="libro.stock_disponible_real > 0" @click="toggleCarrito(libro)" class="btn btn-sm btn-outline-success fw-bold">Agregar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow position-sticky" style="top: 20px;">
                <div class="card-header text-white py-3 bg-vino">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-basket me-2"></i>Resumen</h6>
                </div>
                <div class="card-body bg-light d-flex flex-column" style="height: 500px;">
                    
                    <div class="flex-grow-1 overflow-auto p-2 bg-white rounded border mb-3">
                        <div v-if="carrito.length === 0" class="text-center py-5 text-muted opacity-50">
                            <i class="bi bi-book display-4"></i>
                            <p class="mt-2 small">Seleccione libros en el Paso 3</p>
                        </div>
                        <div v-for="(item, idx) in carrito" class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                            <div class="small fw-bold text-truncate" style="max-width: 160px;">{{ item.titulo }}</div>
                            <div class="d-flex align-items-center">
                                <input type="number" v-model.number="item.cantidad" 
                                       class="form-control form-control-sm text-center me-1 fw-bold" 
                                       style="width: 60px;" min="1" :max="item.max_real"
                                       @change="validarMaximo(item)">
                                <button @click="removerDelCarrito(idx)" class="btn btn-sm text-danger"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-success w-100 py-3 fw-bold shadow-sm" 
                            @click="finalizarPrestamo" 
                            :disabled="carrito.length === 0 || procesando">
                        <span v-if="procesando" class="spinner-border spinner-border-sm me-2"></span>
                        <span v-else>CONFIRMAR PRÉSTAMO</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .btn-vino { background-color: #8B1538; color: white; border: none; }
    .btn-vino:hover { background-color: #6b0f2a; color: white; }
    .bg-vino { background-color: #8B1538; }
    .text-vino { color: #8B1538; }
    .transition-step { transition: all 0.3s ease; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

    /* Estilos Tarjetas de Hora */
    .hora-card { border: 1px solid #dee2e6; background: white; user-select: none; }
    .hora-card:hover { transform: translateY(-2px); border-color: #8B1538; }
    .hora-card.selected { background-color: #8B1538; color: white; border-color: #8B1538; }
    .hora-card.selected .text-muted { color: rgba(255,255,255,0.8) !important; }
    .hora-card.selected .text-dark { color: white !important; }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                pasoActual: 1,
                personas: [], libros: [], carrito: [],
                filtroPersona: '', personaSeleccionada: null,
                busquedaLibro: '', cargandoLibros: false,
                procesando: false,

                tipoPrestamo: 'HORAS',
                form: { fecha: '', hora_inicio: '', hora_fin: '', grado: '', seccion: '' },

                bloquesHorarios: [
                    { id: 1, label: '1° Hora', inicio: '07:30', fin: '08:15' },
                    { id: 2, label: '2° Hora', inicio: '08:15', fin: '09:00' },
                    { id: 3, label: '3° Hora', inicio: '09:00', fin: '09:45' },
                    { id: 4, label: 'Recreo',  inicio: '09:45', fin: '10:15' },
                    { id: 5, label: '4° Hora', inicio: '10:15', fin: '11:00' }, 
                    { id: 6, label: '5° Hora', inicio: '11:15', fin: '12:00' },
                    { id: 7, label: '6° Hora', inicio: '12:00', fin: '12:45' },
                    { id: 8, label: 'Salida',  inicio: '12:45', fin: '13:05' }
                ],
                bloquesSeleccionados: []
            }
        },
        computed: {
            progresoPorcentaje() { return (this.pasoActual / 3) * 100; },
            
            // Cálculo para el rango (MODO AULA)
            rangoHorarioTexto() {
                if (this.bloquesSeleccionados.length === 0) return '';
                const seleccion = this.bloquesHorarios.filter(b => this.bloquesSeleccionados.includes(b.id)).sort((a,b) => a.id - b.id);
                return `${seleccion[0].inicio} - ${seleccion[seleccion.length - 1].fin}`;
            },
            
            // Cálculo para hora fin (MODO DÍAS)
            horaFinTexto() {
                if (this.bloquesSeleccionados.length === 0) return '';
                // Para días tomamos el FIN del último bloque seleccionado
                const seleccion = this.bloquesHorarios.filter(b => this.bloquesSeleccionados.includes(b.id)).sort((a,b) => a.id - b.id);
                return seleccion[seleccion.length - 1].fin;
            },
            
            personasFiltradas() {
                if (this.filtroPersona.length < 2) return [];
                const t = this.filtroPersona.toLowerCase();
                return this.personas.filter(p => p.nombres.toLowerCase().includes(t) || p.dni.includes(t)).slice(0,5);
            },
            
            horarioValido() {
                if(!this.form.grado || !this.form.seccion) return false;
                if(this.bloquesSeleccionados.length === 0) return false; // Ahora obligatorio para ambos
                if(this.tipoPrestamo === 'DIAS' && !this.form.fecha) return false;
                return true;
            },
            
            librosFiltrados() {
                const t = this.busquedaLibro.toLowerCase();
                return this.libros.filter(l => l.titulo.toLowerCase().includes(t) || l.isbn.includes(t));
            },
            minDate() { return new Date().toISOString().split('T')[0]; }
        },
        mounted() {
            this.cargarPersonas();
            this.setTipo('HORAS');
        },
        methods: {
            async cargarPersonas() {
                const res = await fetch('../api/personas.php');
                this.personas = await res.json();
            },
            seleccionarPersona(p) { this.personaSeleccionada = p; this.pasoActual = 2; },
            irAPaso(n) { this.pasoActual = n; },
            
            setTipo(t) { 
                this.tipoPrestamo = t; 
                this.form.fecha = (t === 'HORAS') ? new Date().toISOString().split('T')[0] : '';
                this.bloquesSeleccionados = []; // Limpiar al cambiar
            },
            
            toggleBloque(bloque) {
                const index = this.bloquesSeleccionados.indexOf(bloque.id);
                if (index === -1) this.bloquesSeleccionados.push(bloque.id); else this.bloquesSeleccionados.splice(index, 1);
            },

            async confirmarHorario() {
                this.pasoActual = 3;
                this.cargandoLibros = true;
                
                // Obtener selección de tarjetas
                const seleccion = this.bloquesHorarios.filter(b => this.bloquesSeleccionados.includes(b.id)).sort((a,b) => a.id - b.id);
                
                // Lógica de horas para la consulta de disponibilidad
                let hIni, hFin;
                if (this.tipoPrestamo === 'HORAS') {
                    hIni = seleccion[0].inicio;
                    hFin = seleccion[seleccion.length - 1].fin;
                } else {
                    // Si es por días, la hora de inicio de hoy no importa tanto para el stock,
                    // pero la hora de fin del día de devolución SÍ importa para ver si hay reservas ESE día.
                    hIni = '07:00'; 
                    hFin = seleccion[seleccion.length - 1].fin; 
                }

                // Guardamos para el envío final
                this.form.hora_inicio = hIni;
                this.form.hora_fin = hFin;

                const params = new URLSearchParams({
                    limit: 1000,
                    fecha: this.form.fecha,
                    hora_ini: hIni,
                    hora_fin: hFin
                });
                
                const res = await fetch(`../api/libros.php?${params}`);
                const data = await res.json();
                this.libros = data.data;
                this.cargandoLibros = false;
            },

            buscarPersona() { /* Reactivo */ },

            estaEnCarrito(l) { return this.carrito.some(i => i.id === l.id); },
            
            toggleCarrito(l) {
                if(this.estaEnCarrito(l)) {
                    this.carrito = this.carrito.filter(i => i.id !== l.id);
                } else {
                    this.carrito.push({ 
                        id: l.id, 
                        titulo: l.titulo, 
                        cantidad: 1, 
                        max_real: l.stock_disponible_real 
                    });
                }
            },
            
            validarMaximo(item) {
                if (item.cantidad > item.max_real) {
                    item.cantidad = item.max_real;
                    alert(`Solo hay ${item.max_real} unidades disponibles para este horario.`);
                }
            },
            
            removerDelCarrito(i) { this.carrito.splice(i, 1); },

            async finalizarPrestamo() {
                if(!confirm('¿Registrar préstamo?')) return;
                this.procesando = true;
                
                const payload = {
                    persona_id: this.personaSeleccionada.id,
                    libros: this.carrito,
                    tipo_prestamo: this.tipoPrestamo,
                    fecha_devolucion: this.form.fecha,
                    
                    // Enviamos las horas calculadas desde las tarjetas
                    hora_inicio: (this.tipoPrestamo === 'HORAS') ? this.form.hora_inicio : new Date().toTimeString().slice(0,5),
                    hora_fin: this.form.hora_fin, // Esta viene de las tarjetas en ambos casos
                    
                    aula_grado: this.form.grado,
                    aula_seccion: this.form.seccion
                };

                try {
                    const res = await fetch('../api/guardar_prestamo.php', {
                        method: 'POST', headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(payload)
                    });
                    const r = await res.json();
                if(r.exito) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Préstamo registrado correctamente.',
                        confirmButtonText: 'Ir al Historial',
                        confirmButtonColor: '#8B1538',
                        allowOutsideClick: false, // Obliga al usuario a dar clic en el botón
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'historial.php';
                        }
                    });
                } else {
                    Swal.fire('Error', r.mensaje, 'error');
                }
                } catch(e) { Swal.fire('Error', 'Fallo de red', 'error'); } 
                finally { this.procesando = false; }
            }
        }
    }).mount('#app')
</script>