<?php include 'includes/header.php'; ?>

<div id="app">
    
    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0" style="color: #8B1538;">Nuevo Préstamo</h4>
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="historial.php" class="text-decoration-none text-muted">Gestión</a></li>
                    <li class="breadcrumb-item active text-dark fw-bold">Solicitud</li>
                </ol>
            </nav>
        </div>
        <a href="historial.php" class="btn btn-light border text-muted shadow-sm btn-sm">
            <i class="bi bi-x-lg me-1"></i> Cancelar
        </a>
    </div>

    <div class="row g-3">
        
        <!-- COLUMNA IZQUIERDA: BÚSQUEDAS -->
        <div class="col-md-8">
            
            <!-- 1. BUSCADOR DE SOLICITANTE -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-0">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary me-2">PASO 1</span>
                        <h6 class="fw-bold mb-0 text-dark">Buscar Solicitante</h6>
                    </div>
                </div>
                <div class="card-body px-3 pb-3">
                    <div class="position-relative">
                        <div class="input-group input-group-lg border rounded bg-white">
                            <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                   class="form-control border-0 shadow-none fs-6" 
                                   placeholder="Escriba nombre, apellido o DNI..." 
                                   v-model="filtroPersona"
                                   @focus="mostrarListaPersonas = true"
                                   :disabled="personaSeleccionada != null">
                            
                            <button v-if="personaSeleccionada" class="btn btn-link text-danger text-decoration-none border-0" @click="limpiarPersona">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div v-if="mostrarListaPersonas && personasFiltradas.length > 0 && !personaSeleccionada" 
                             class="list-group position-absolute w-100 shadow-lg mt-1 overflow-auto custom-scrollbar" 
                             style="z-index: 1000; max-height: 250px; border-radius: 8px; border: 1px solid #eee;">
                            <button type="button" class="list-group-item list-group-item-action p-2 border-start-0 border-end-0" 
                                    v-for="p in personasFiltradas" @click="seleccionarPersona(p)">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark small">{{ p.apellidos }}, {{ p.nombres }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ p.dni }} <span v-if="p.grado">• {{ p.grado }} "{{ p.seccion }}"</span></div>
                                    </div>
                                    <span class="badge rounded-pill" :class="p.tipo == 'ESTUDIANTE' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success'">{{ p.tipo }}</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div v-if="personaSeleccionada" class="mt-2 p-2 rounded d-flex align-items-center animate-fade-in" style="background-color: #e8f5e9; border: 1px solid #c8e6c9;">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 35px; height: 35px;"><i class="bi bi-person-check-fill fs-5"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold text-success small">{{ personaSeleccionada.apellidos }}, {{ personaSeleccionada.nombres }}</h6>
                            <div class="text-success text-opacity-75" style="font-size: 0.7rem;">
                                <span class="fw-bold">{{ personaSeleccionada.tipo }}</span> 
                                <span v-if="personaSeleccionada.grado">• {{ personaSeleccionada.grado }} "{{ personaSeleccionada.seccion }}"</span>
                                <span v-if="personaSeleccionada.especialidad">• {{ personaSeleccionada.especialidad }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="personaSeleccionada && personaSeleccionada.tipo == 'DOCENTE'" class="mt-2 animate-fade-in">
                        <div class="bg-light p-2 rounded border">
                            <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.7rem;"><i class="bi bi-easel me-1"></i> ¿Para qué aula es?</label>
                            <div class="d-flex gap-2">
                                <select v-model="docenteGrado" class="form-select form-select-sm py-0" style="height: 30px;"><option value="">Grado</option><option>1ro</option><option>2do</option><option>3ro</option><option>4to</option><option>5to</option></select>
                                <select v-model="docenteSeccion" class="form-select form-select-sm py-0" style="height: 30px;"><option value="">Sección</option><option>A</option><option>B</option><option>C</option><option>D</option><option>E</option></select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. SELECCIÓN DE LIBROS -->
            <div class="card border-0 shadow-sm" style="height: calc(100vh - 280px); min-height: 400px;">
                <div class="card-header bg-white border-bottom border-light pt-3 px-3 pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center"><span class="badge bg-secondary bg-opacity-10 text-secondary me-2">PASO 2</span><h6 class="fw-bold mb-0 text-dark">Seleccionar Material</h6></div>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ librosFiltrados.length }} disp.</small>
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" v-model="busquedaLibro" class="form-control border-start-0 bg-light" placeholder="Buscar título o código...">
                    </div>
                    <div class="d-flex gap-1 overflow-auto pb-1 custom-scrollbar" style="white-space: nowrap;">
                        <button class="btn btn-xs rounded-pill px-2" :class="filtroCategoria === '' ? 'btn-dark' : 'btn-light border'" @click="filtroCategoria = ''" style="font-size: 0.7rem;">Todos</button>
                        <button v-for="cat in categoriasUnicas" class="btn btn-xs rounded-pill px-2" :class="filtroCategoria === cat ? 'btn-dark' : 'btn-light border'" @click="filtroCategoria = cat" style="font-size: 0.7rem;">{{ cat }}</button>
                    </div>
                </div>
                
                <div class="card-body p-0 overflow-auto custom-scrollbar">
                    <div class="list-group list-group-flush">
                        <button v-for="libro in librosFiltrados.slice(0, 30)" 
                                @click="agregarAlCarrito(libro)" 
                                class="list-group-item list-group-item-action p-2 border-bottom-0 border-top d-flex align-items-center hover-bg-light"
                                :disabled="libro.stock_disponible < 1"
                                :class="{'opacity-50': libro.stock_disponible < 1}">
                            
                            <div class="me-2 rounded bg-light text-secondary d-flex align-items-center justify-content-center fs-5 fw-bold shadow-sm" style="width: 35px; height: 45px; border: 1px solid #eee;">{{ libro.titulo.charAt(0) }}</div>
                            <div class="flex-grow-1" style="line-height: 1.1;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate small" style="max-width: 200px;">{{ libro.titulo }}</h6>
                                    <span v-if="libro.stock_disponible > 0" class="badge bg-light text-success border border-success" style="font-size: 0.6rem;">{{ libro.stock_disponible }}</span>
                                    <span v-else class="badge bg-danger" style="font-size: 0.6rem;">0</span>
                                </div>
                                <div class="text-muted d-flex gap-2" style="font-size: 0.65rem;"><span>{{ libro.autor }}</span><span v-if="libro.editorial">• {{ libro.editorial }}</span></div>
                            </div>
                            <div class="ms-2 text-success" v-if="libro.stock_disponible > 0"><i class="bi bi-plus-circle-fill"></i></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: TICKET -->
        <div class="col-md-4">
            <div class="card border-0 shadow h-100 position-sticky" style="top: 10px; border-radius: 12px; overflow: hidden; height: calc(100vh - 100px) !important;">
                
                <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background-color: #8B1538;">
                    <div class="fw-bold"><i class="bi bi-basket me-2"></i>Resumen</div>
                    <span class="badge bg-white text-danger fw-bold rounded-pill">{{ totalLibrosCarrito }}</span>
                </div>

                <!-- Lista Carrito (Se expande para llenar el espacio) -->
                <div class="card-body p-0 d-flex flex-column bg-white flex-grow-1 overflow-hidden">
                    <div class="flex-grow-1 overflow-auto p-3 custom-scrollbar">
                        <div v-if="carrito.length === 0" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted text-center opacity-50">
                            <i class="bi bi-cart-plus display-4 mb-2"></i>
                            <p class="small mb-0">Agregue libros para<br>iniciar el préstamo.</p>
                        </div>

                        <div v-for="(item, index) in carrito" :key="index" class="d-flex align-items-center mb-2 pb-2 border-bottom animate-slide-in">
                            <button @click="removerDelCarrito(index)" class="btn btn-sm text-danger me-1 p-0"><i class="bi bi-trash-fill"></i></button>
                            <div class="flex-grow-1 me-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 140px;">{{ item.titulo }}</div>
                                    <small class="text-muted fw-bold" style="font-size: 0.6rem;">DISP. {{ item.max_stock }}</small>
                                </div>
                                <!-- INPUT EDITABLE COMPACTO -->
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary py-0 px-2" @click="actualizarCantidad(item, -1)">-</button>
                                    <input type="number" v-model.number="item.cantidad" @change="validarCantidad(item)" class="form-control text-center fw-bold py-0" min="1" :max="item.max_stock">
                                    <button class="btn btn-outline-secondary py-0 px-2" @click="actualizarCantidad(item, 1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: FECHA Y CONFIRMACIÓN -->
                    <div class="p-3 bg-light border-top">
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-dark mb-1">Tipo de Préstamo</label>
                            <div class="btn-group w-100 mb-2">
                                <button class="btn btn-sm" :class="tipoPrestamo === 'AULA' ? 'btn-vino text-white' : 'btn-outline-secondary bg-white'" @click="setTipoPrestamo('AULA')" :disabled="horarioAulaCerrado">En Aula</button>
                                <button class="btn btn-sm" :class="tipoPrestamo === 'DOMICILIO' ? 'btn-vino text-white' : 'btn-outline-secondary bg-white'" @click="setTipoPrestamo('DOMICILIO')">Domicilio</button>
                            </div>

                            <!-- Aviso Aula Cerrada -->
                            <div v-if="horarioAulaCerrado && tipoPrestamo !== 'DOMICILIO'" class="alert alert-secondary py-1 px-2 mb-1 d-flex align-items-center border-0">
                                <i class="bi bi-lock-fill me-1 small"></i><small style="font-size: 0.65rem;">Aula cerrada (> 1:05 PM).</small>
                            </div>

                            <!-- AULA: Horarios -->
                            <div v-if="tipoPrestamo === 'AULA' && !horarioAulaCerrado">
                                <select v-model="horaDevolucion" class="form-select form-select-sm fw-bold mb-1" style="font-size: 0.75rem;">
                                    <option value="08:15">07:30 - 08:15 (1° Hora)</option>
                                    <option value="09:00">08:15 - 09:00 (2° Hora)</option>
                                    <option value="09:45">09:00 - 09:45 (3° Hora)</option>
                                    <option value="10:15">09:45 - 10:15 (Recreo)</option>
                                    <option value="11:15">10:30 - 11:15 (4° Hora)</option>
                                    <option value="12:00">11:15 - 12:00 (5° Hora)</option>
                                    <option value="12:45">12:00 - 12:45 (6° Hora)</option>
                                    <option value="13:05">12:45 - 13:05 (Salida)</option>
                                </select>
                                <div class="text-end text-warning small fw-bold" style="font-size: 0.65rem;">
                                    Límite: {{ calcularHoraMax(horaDevolucion) }}
                                </div>
                            </div>

                            <!-- DOMICILIO: Fecha Y HORA -->
                            <div v-if="tipoPrestamo === 'DOMICILIO'">
                                <div class="row g-1">
                                    <div class="col-7">
                                        <input type="date" v-model="fechaDevolucion" class="form-control form-control-sm fw-bold text-center border-primary" @change="validarFechaDomicilio" :min="minDate" :max="maxDate" style="font-size: 0.75rem;">
                                    </div>
                                    <div class="col-5">
                                        <select v-model="horaDomicilio" class="form-select form-select-sm fw-bold" style="font-size: 0.75rem;">
                                            <option value="07:30">7:30 AM (Ingreso)</option>
                                            <option value="09:45">9:45 AM (Recreo)</option>
                                            <option value="13:05">1:05 PM (Salida)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div v-if="errorFecha" class="mt-1 text-danger fw-bold text-end" style="font-size: 0.65rem;">
                                    <i class="bi bi-exclamation-circle"></i> {{ errorFecha }}
                                </div>
                                <div v-else class="text-end text-muted mt-1" style="font-size: 0.65rem;">Máx 3 días</div>
                            </div>
                        </div>

                        <button @click="guardarPrestamo" class="btn w-100 py-2 text-white fw-bold shadow-sm btn-gold btn-sm" 
                                :disabled="carrito.length === 0 || !personaSeleccionada || procesando || errorFecha || (tipoPrestamo==='AULA' && horarioAulaCerrado)">
                            <span v-if="procesando"><span class="spinner-border spinner-border-sm me-1"></span>...</span>
                            <span v-else><i class="bi bi-check-circle-fill me-1"></i> CONFIRMAR</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div> 

<style>
    .hover-bg-light:hover { background-color: #f8f9fa !important; }
    .btn-vino { background-color: #8B1538 !important; border-color: #8B1538 !important; }
    .btn-gold { background-color: #D4AF37; border: none; }
    .btn-gold:hover { background-color: #c4a030; transform: translateY(-1px); }
    .btn-gold:disabled { background-color: #e0e0e0; color: #999; transform: none; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .animate-slide-in { animation: slideIn 0.2s ease-out; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(5px); } to { opacity: 1; transform: translateX(0); } }
    /* Ajuste para altura dinámica del contenido */
    .card-body { display: flex; flex-direction: column; }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                personas: [], libros: [], carrito: [],
                filtroPersona: '', personaSeleccionada: null, mostrarListaPersonas: false,
                docenteGrado: '', docenteSeccion: '',
                busquedaLibro: '', filtroCategoria: '', cargandoLibros: false,
                
                tipoPrestamo: 'DOMICILIO', 
                fechaDevolucion: '',
                horaDevolucion: '09:00', // Hora Aula
                horaDomicilio: '07:30',  // Hora Domicilio
                
                procesando: false, horarioAulaCerrado: false, errorFecha: '' 
            }
        },
        computed: {
            personasFiltradas() {
                if (this.filtroPersona.length < 2) return []; 
                const texto = this.filtroPersona.toLowerCase();
                return this.personas.filter(p => p.nombres.toLowerCase().includes(texto) || p.apellidos.toLowerCase().includes(texto) || p.dni.includes(texto)).slice(0, 8); 
            },
            librosFiltrados() {
                return this.libros.filter(l => {
                    const textoMatch = l.titulo.toLowerCase().includes(this.busquedaLibro.toLowerCase()) || l.id.toString().includes(this.busquedaLibro) || (l.editorial && l.editorial.toLowerCase().includes(this.busquedaLibro.toLowerCase()));
                    const catMatch = this.filtroCategoria === '' || l.categoria === this.filtroCategoria;
                    return textoMatch && catMatch;
                });
            },
            categoriasUnicas() { return [...new Set(this.libros.map(l => l.categoria).filter(c => c))]; },
            totalLibrosCarrito() { return this.carrito.reduce((sum, item) => sum + item.cantidad, 0); },
            minDate() { return new Date().toISOString().split('T')[0]; },
            maxDate() { const d = new Date(); d.setDate(d.getDate() + 3); return d.toISOString().split('T')[0]; }
        },
        mounted() {
            this.cargarDatos();
            this.verificarHorarioAula(); 
            this.setTipoPrestamo('DOMICILIO'); 
        },
        methods: {
            async cargarDatos() {
                try {
                    const resPer = await fetch('../api/personas.php'); this.personas = await resPer.json();
                    this.cargandoLibros = true;
                    const resLib = await fetch('../api/libros.php?limit=1000'); const dataLib = await resLib.json();
                    this.libros = dataLib.data || []; this.cargandoLibros = false;
                } catch(e) { console.error(e); this.cargandoLibros = false; }
            },
            seleccionarPersona(p) { this.personaSeleccionada = p; this.mostrarListaPersonas = false; this.filtroPersona = ''; this.docenteGrado = ''; this.docenteSeccion = ''; },
            limpiarPersona() { this.personaSeleccionada = null; this.filtroPersona = ''; setTimeout(() => document.querySelector('input[placeholder="Escriba nombre, apellido o DNI..."]').focus(), 100); },
            ocultarListaConRetraso() { setTimeout(() => { this.mostrarListaPersonas = false; }, 200); },
            agregarAlCarrito(libro) {
                const existe = this.carrito.find(item => item.id === libro.id);
                if (existe) { if(existe.cantidad < libro.stock_disponible) existe.cantidad++; else alert("Stock máximo alcanzado"); }
                else { this.carrito.push({ id: libro.id, titulo: libro.titulo, cantidad: 1, max_stock: libro.stock_disponible }); }
            },
            actualizarCantidad(item, delta) {
                let nueva = item.cantidad + delta;
                if (nueva >= 1 && nueva <= item.max_stock) item.cantidad = nueva;
                else if (nueva > item.max_stock) { alert("Solo hay " + item.max_stock + " disponibles."); item.cantidad = item.max_stock; }
            },
            validarCantidad(item) { if (item.cantidad > item.max_stock) { item.cantidad = item.max_stock; alert("Stock máximo superado."); } if (item.cantidad < 1) item.cantidad = 1; },
            removerDelCarrito(index) { this.carrito.splice(index, 1); },
            verificarHorarioAula() {
                const ahora = new Date();
                if (ahora.getHours() > 13 || (ahora.getHours() === 13 && ahora.getMinutes() > 5)) {
                    this.horarioAulaCerrado = true;
                    if (this.tipoPrestamo === 'AULA') this.setTipoPrestamo('DOMICILIO'); 
                }
            },
            setTipoPrestamo(tipo) {
                this.errorFecha = '';
                if (tipo === 'AULA') {
                    if (this.horarioAulaCerrado) { alert("Horario aula cerrado."); return; }
                    this.tipoPrestamo = 'AULA';
                    this.fechaDevolucion = new Date().toISOString().split('T')[0]; 
                } else {
                    this.tipoPrestamo = 'DOMICILIO';
                    const d = new Date(); d.setDate(d.getDate() + 1);
                    this.fechaDevolucion = d.toISOString().split('T')[0];
                    this.validarFechaDomicilio();
                }
            },
            validarFechaDomicilio() {
                this.errorFecha = '';
                if (!this.fechaDevolucion) return;
                const fecha = new Date(this.fechaDevolucion + 'T00:00:00'); 
                if (fecha.getDay() === 0 || fecha.getDay() === 6) { this.errorFecha = "No sábados/domingos."; return; }
                const limite = new Date(); limite.setDate(limite.getDate() + 3); limite.setHours(23,59,59);
                if (fecha > limite) this.errorFecha = "Máx 3 días.";
            },
            calcularHoraMax(horaFin) {
                if (!horaFin) return '';
                const fecha = new Date(`2000-01-01T${horaFin}:00`);
                fecha.setMinutes(fecha.getMinutes() + 10);
                return `${fecha.getHours().toString().padStart(2, '0')}:${fecha.getMinutes().toString().padStart(2, '0')}`;
            },
            async guardarPrestamo() {
                if(!this.personaSeleccionada || this.carrito.length === 0) return;
                if(this.personaSeleccionada.tipo == 'DOCENTE' && (!this.docenteGrado || !this.docenteSeccion)) { alert("Indique Aula."); return; }
                if(this.tipoPrestamo === 'DOMICILIO') { this.validarFechaDomicilio(); if(this.errorFecha) { alert(this.errorFecha); return; } }
                if(!confirm(`¿Confirmar préstamo?`)) return;
                this.procesando = true;
                
                // Determinar hora límite según el tipo
                let hLimite = null;
                if(this.tipoPrestamo === 'AULA') hLimite = this.horaDevolucion;
                if(this.tipoPrestamo === 'DOMICILIO') hLimite = this.horaDomicilio;

                const datos = { 
                    persona_id: this.personaSeleccionada.id, 
                    libros: this.carrito,
                    fecha_devolucion: this.fechaDevolucion,
                    tipo_prestamo: this.tipoPrestamo,
                    hora_limite: hLimite,
                    aula_grado: this.docenteGrado, aula_seccion: this.docenteSeccion
                };
                try {
                    const respuesta = await fetch('../api/guardar_prestamo.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) });
                    const r = await respuesta.json();
                    if (r.exito) { alert("✅ Registrado"); window.location.href = 'historial.php'; } 
                    else { alert("❌ Error: " + r.mensaje); }
                } catch(e) { alert("Error conexión"); } finally { this.procesando = false; }
            }
        }
    }).mount('#app')
</script>
</body>
</html>