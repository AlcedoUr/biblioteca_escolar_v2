<?php include 'includes/header.php'; ?>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Nuevo Préstamo</h3>
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="historial.php" class="text-decoration-none text-muted">Gestión</a></li>
                    <li class="breadcrumb-item active text-dark fw-bold">Solicitud</li>
                </ol>
            </nav>
        </div>
        <a href="historial.php" class="btn btn-light border text-muted shadow-sm d-none d-md-inline-block">
            <i class="bi bi-x-lg me-2"></i> Cancelar
        </a>
    </div>

    <div class="mb-4">
        <div class="progress" style="height: 4px;">
            <div class="progress-bar bg-success transition-width" role="progressbar" :style="{ width: progresoPorcentaje + '%' }"></div>
        </div>
        <div class="d-flex justify-content-between mt-2 small fw-bold text-uppercase text-muted">
            <span :class="{'text-success': pasoActual >= 1}">1. Solicitante</span>
            <span :class="{'text-success': pasoActual >= 2}">2. Selección</span>
            <span :class="{'text-success': pasoActual >= 3}">3. Confirmación</span>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-md-8">
            
            <div class="card border-0 shadow-sm mb-4 transition-opacity" :class="{'opacity-50': pasoActual > 1 && !mostrarListaPersonas}">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge rounded-pill me-2" :class="personaSeleccionada ? 'bg-success' : 'bg-secondary'">PASO 1</span>
                        <h6 class="fw-bold mb-0 text-dark">Buscar Solicitante</h6>
                    </div>
                    <i class="bi bi-check-circle-fill text-success fs-5 animate-pop" v-if="personaSeleccionada"></i>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="position-relative">
                        <div class="input-group input-group-lg border rounded bg-white" :class="{'border-success': personaSeleccionada}">
                            <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                   class="form-control border-0 shadow-none" 
                                   placeholder="Escriba nombre, apellido o DNI..." 
                                   v-model="filtroPersona"
                                   @focus="mostrarListaPersonas = true"
                                   :disabled="personaSeleccionada != null">
                            
                            <button v-if="personaSeleccionada" class="btn btn-link text-danger text-decoration-none border-0" @click="limpiarPersona" title="Cambiar usuario">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div v-if="mostrarListaPersonas && personasFiltradas.length > 0 && !personaSeleccionada" 
                             class="list-group position-absolute w-100 shadow-lg mt-1 overflow-auto custom-scrollbar" 
                             style="z-index: 1000; max-height: 300px; border-radius: 8px; border: 1px solid #eee;">
                            
                            <button type="button" 
                                    class="list-group-item list-group-item-action p-3 border-start-0 border-end-0" 
                                    v-for="p in personasFiltradas" 
                                    @click="seleccionarPersona(p)">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark">{{ p.apellidos }}, {{ p.nombres }}</div>
                                        <div class="small text-muted d-flex gap-2">
                                            <span><i class="bi bi-card-heading me-1"></i>{{ p.dni }}</span>
                                            <span v-if="p.grado">• {{ p.grado }} "{{ p.seccion }}"</span>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill" 
                                          :class="p.tipo == 'ESTUDIANTE' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success'">
                                        {{ p.tipo }}
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div v-if="personaSeleccionada" class="mt-3 p-3 rounded d-flex align-items-center animate-fade-in" style="background-color: #e8f5e9; border: 1px solid #c8e6c9;">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px;">
                            <i class="bi bi-person-check-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-success">{{ personaSeleccionada.apellidos }}, {{ personaSeleccionada.nombres }}</h6>
                            <div class="small text-success text-opacity-75">
                                <span class="fw-bold">{{ personaSeleccionada.tipo }}</span> 
                                <span v-if="personaSeleccionada.grado">• {{ personaSeleccionada.grado }} "{{ personaSeleccionada.seccion }}"</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="personaSeleccionada && personaSeleccionada.tipo == 'DOCENTE'" class="mt-3 animate-fade-in">
                        <div class="bg-light p-3 rounded border">
                            <label class="form-label small fw-bold text-muted mb-2"><i class="bi bi-easel me-1"></i> ¿Para qué aula es el material?</label>
                            <div class="d-flex gap-2">
                                <select v-model="docenteGrado" class="form-select form-select-sm"><option value="">-- Grado --</option><option>1ro</option><option>2do</option><option>3ro</option><option>4to</option><option>5to</option></select>
                                <select v-model="docenteSeccion" class="form-select form-select-sm"><option value="">-- Sección --</option><option>A</option><option>B</option><option>C</option><option>D</option><option>E</option></select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card border-0 shadow-sm transition-opacity" 
                 :class="{'disabled-section': !personaSeleccionada}"
                 style="min-height: 500px;">
                 
                <div class="card-header bg-white border-bottom border-light pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <span class="badge rounded-pill me-2" :class="carrito.length > 0 ? 'bg-success' : 'bg-secondary'">PASO 2</span>
                            <h6 class="fw-bold mb-0 text-dark">Seleccionar Material</h6>
                        </div>
                        <small class="text-muted">{{ librosFiltrados.length }} disponibles</small>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" v-model="busquedaLibro" class="form-control border-start-0 bg-light" placeholder="Buscar por título, autor o código..." :disabled="!personaSeleccionada">
                    </div>

                    <div class="d-flex gap-2 overflow-auto pb-2" style="white-space: nowrap;">
                        <button class="btn btn-sm rounded-pill" 
                                :class="filtroCategoria === '' ? 'btn-dark' : 'btn-light border'"
                                @click="filtroCategoria = ''" :disabled="!personaSeleccionada">Todos</button>
                        <button v-for="cat in categoriasUnicas" 
                                class="btn btn-sm rounded-pill" 
                                :class="filtroCategoria === cat ? 'btn-dark' : 'btn-light border'"
                                @click="filtroCategoria = cat" :disabled="!personaSeleccionada">
                            {{ cat }}
                        </button>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div v-if="!personaSeleccionada" class="text-center py-5 text-muted opacity-75">
                        <i class="bi bi-lock-fill display-4 d-block mb-3"></i>
                        <p>Complete el Paso 1 para desbloquear.</p>
                    </div>

                    <div v-else class="list-group list-group-flush custom-scrollbar" style="max-height: 450px; overflow-y: auto;">
                        <div v-for="libro in librosFiltrados.slice(0, 30)" 
                             class="list-group-item p-3 border-bottom-0 border-top d-flex align-items-center hover-bg-light">
                            
                            <div class="me-3 rounded bg-light text-secondary d-flex align-items-center justify-content-center fs-3 fw-bold shadow-sm" style="width: 45px; height: 60px; border: 1px solid #eee;">
                                {{ libro.titulo.charAt(0) }}
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1 fw-bold text-dark text-truncate" style="max-width: 300px;">{{ libro.titulo }}</h6>
                                    <span v-if="libro.stock_disponible > 0" class="badge bg-light text-success border border-success">Disp: {{ libro.stock_disponible }}</span>
                                    <span v-else class="badge bg-danger">Agotado</span>
                                </div>
                                <div class="text-muted small">{{ libro.autor }}</div>
                            </div>

                            <div class="ms-3">
                                <button v-if="estaEnCarrito(libro)" 
                                        @click="toggleCarrito(libro)" 
                                        class="btn btn-sm btn-outline-danger fw-bold px-3 animate-pop">
                                    <i class="bi bi-x-lg me-1"></i> Quitar
                                    <span class="badge bg-danger text-white ms-1">Agregado</span>
                                </button>
                                
                                <button v-else-if="libro.stock_disponible > 0" 
                                        @click="toggleCarrito(libro)" 
                                        class="btn btn-sm btn-light border text-success fw-bold px-3 hover-scale">
                                    <i class="bi bi-plus-lg me-1"></i> Agregar
                                </button>
                                
                                <button v-else class="btn btn-sm btn-light text-muted disabled border-0">
                                    No disponible
                                </button>
                            </div>
                        </div>

                        <div v-if="!cargandoLibros && librosFiltrados.length === 0" class="text-center py-5 text-muted">
                            <p>No se encontraron libros.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow h-100 position-sticky" style="top: 20px; border-radius: 12px; overflow: hidden;">
                
                <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background-color: #8B1538;">
                    <div class="fw-bold"><i class="bi bi-basket me-2"></i>Resumen</div>
                    <span class="badge bg-white text-danger fw-bold rounded-pill animate-pop" :key="totalLibrosCarrito">{{ totalLibrosCarrito }}</span>
                </div>

                <div class="card-body p-0 d-flex flex-column bg-white" style="height: 600px;">
                    
                    <div class="flex-grow-1 overflow-auto p-3 custom-scrollbar">
                        <div v-if="carrito.length === 0" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted text-center opacity-50">
                            <i class="bi bi-cart-plus display-4 mb-3"></i>
                            <p class="small">Seleccione libros para<br>iniciar el préstamo.</p>
                        </div>

                        <div v-for="(item, index) in carrito" :key="index" class="d-flex align-items-center mb-3 pb-3 border-bottom animate-slide-in">
                            <button @click="removerDelCarrito(index)" class="btn btn-sm text-danger me-2 p-0" title="Eliminar"><i class="bi bi-trash-fill"></i></button>
                            
                            <div class="flex-grow-1">
                                <div class="fw-bold small text-dark mb-1 line-clamp-2">{{ item.titulo }}</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted fw-bold" style="font-size: 0.65rem;">DISP. {{ item.max_stock }}</small>
                                    <input type="number" 
                                           v-model.number="item.cantidad" 
                                           @change="validarCantidad(item)"
                                           class="form-control form-control-sm text-center fw-bold" 
                                           style="width: 60px;"
                                           min="1" :max="item.max_stock">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-light border-top">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark mb-2">Tipo de Préstamo</label>
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-sm flex-grow-1" 
                                        :class="tipoPrestamo === 'AULA' ? 'text-white' : 'btn-light border'"
                                        :style="tipoPrestamo === 'AULA' ? 'background-color: #8B1538' : ''"
                                        @click="setTipoPrestamo('AULA')"
                                        :disabled="horarioAulaCerrado"> 
                                    <i class="bi bi-clock me-1"></i> En Aula
                                </button>
                                <button class="btn btn-sm flex-grow-1" 
                                        :class="tipoPrestamo === 'DOMICILIO' ? 'text-white' : 'btn-light border'"
                                        :style="tipoPrestamo === 'DOMICILIO' ? 'background-color: #8B1538' : ''"
                                        @click="setTipoPrestamo('DOMICILIO')">
                                    <i class="bi bi-house-door me-1"></i> Domicilio
                                </button>
                            </div>

                            <div v-if="tipoPrestamo === 'AULA' && !horarioAulaCerrado" class="mb-2">
                                <label class="form-label small text-muted mb-1">Devolución Hoy</label>
                                <select v-model="horaDevolucion" class="form-select form-select-sm fw-bold">
                                    <option value="08:15">08:15 (1° Hora)</option>
                                    <option value="09:00">09:00 (2° Hora)</option>
                                    <option value="09:45">09:45 (3° Hora)</option>
                                    <option value="10:15">10:15 (Recreo)</option>
                                    <option value="11:15">11:15 (4° Hora)</option>
                                    <option value="12:00">12:00 (5° Hora)</option>
                                    <option value="12:45">12:45 (6° Hora)</option>
                                    <option value="13:05">13:05 (Salida)</option>
                                </select>
                            </div>

                            <div v-if="tipoPrestamo === 'DOMICILIO'">
                                <label class="form-label small text-muted mb-1">Fecha Límite Entrega</label>
                                <div class="input-group input-group-sm mb-2 has-validation">
                                    <input type="date" 
                                           v-model="fechaDevolucion" 
                                           class="form-control fw-bold text-center" 
                                           :class="errorFecha ? 'is-invalid border-danger' : 'border-success'"
                                           @change="validarFechaDomicilio"
                                           :min="minDate" 
                                           :max="maxDate">
                                    <span class="input-group-text bg-white" v-if="!errorFecha && fechaDevolucion">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    </span>
                                    <span class="input-group-text bg-white border-danger" v-if="errorFecha">
                                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                    </span>
                                </div>
                                
                                <div v-if="errorFecha" class="text-danger small fw-bold mb-2 animate-fade-in">
                                    <i class="bi bi-x-circle me-1"></i> {{ errorFecha }}
                                </div>
                                <div v-else class="text-muted small mb-2">
                                    <i class="bi bi-info-circle me-1"></i> Máximo 5 días (Lun-Vie)
                                </div>
                            </div>
                        </div>

                        <div v-if="!formularioValido" class="alert alert-warning border-0 py-2 mb-2 small d-flex align-items-start animate-fade-in">
                            <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                            <div>
                                <strong>Para continuar:</strong><br>
                                <span v-if="!personaSeleccionada">• Seleccione un solicitante.<br></span>
                                <span v-if="carrito.length === 0">• Agregue al menos 1 libro.<br></span>
                                <span v-if="errorFecha">• Corrija la fecha de entrega.</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                             <a href="historial.php" class="btn btn-outline-secondary fw-bold w-50">
                                Cancelar
                            </a>

                            <button @click="guardarPrestamo" 
                                    class="btn w-100 py-3 text-white fw-bold shadow-sm btn-gold transition-all" 
                                    :class="{'shake-animation': intentoFallido}"
                                    :disabled="!formularioValido || procesando"
                                    :title="!formularioValido ? 'Complete los pasos anteriores' : 'Registrar préstamo'">
                                <span v-if="procesando"><span class="spinner-border spinner-border-sm me-2"></span>Procesando...</span>
                                <span v-else><i class="bi bi-check-circle-fill me-2"></i> CONFIRMAR</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</div> 

<style>
    .hover-bg-light:hover { background-color: #f8f9fa !important; }
    .btn-gold { background-color: #D4AF37; border: none; }
    .btn-gold:hover:not(:disabled) { background-color: #c4a030; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4); }
    .btn-gold:disabled { background-color: #e0e0e0; color: #999; transform: none; cursor: not-allowed; }
    
    .disabled-section { opacity: 0.6; pointer-events: none; filter: grayscale(0.8); }
    .transition-opacity { transition: all 0.3s ease; }
    .transition-width { transition: width 0.5s ease; }
    
    /* Animaciones */
    .animate-pop { animation: pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    @keyframes pop { 0% { transform: scale(0.8); } 100% { transform: scale(1); } }
    
    .animate-fade-in { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    .shake-animation { animation: shake 0.5s; }
    @keyframes shake { 0% { transform: translateX(0); } 25% { transform: translateX(-5px); } 50% { transform: translateX(5px); } 75% { transform: translateX(-5px); } 100% { transform: translateX(0); } }
    
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
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
                horaDevolucion: '09:00',
                
                procesando: false, horarioAulaCerrado: false, errorFecha: '',
                intentoFallido: false
            }
        },
        computed: {
            // Lógica de pasos para la barra de progreso
            pasoActual() {
                if (!this.personaSeleccionada) return 1;
                if (this.carrito.length === 0) return 2;
                return 3;
            },
            progresoPorcentaje() {
                if (this.pasoActual === 1) return 33;
                if (this.pasoActual === 2) return 66;
                return 100;
            },
            formularioValido() {
                const reqPersona = this.personaSeleccionada != null;
                const reqLibros = this.carrito.length > 0;
                const reqFecha = this.tipoPrestamo === 'DOMICILIO' ? !this.errorFecha : true;
                const reqAula = this.tipoPrestamo === 'AULA' ? !this.horarioAulaCerrado : true;
                // Validar datos docente si aplica
                const reqDocente = (this.personaSeleccionada && this.personaSeleccionada.tipo === 'DOCENTE') 
                                    ? (this.docenteGrado !== '' && this.docenteSeccion !== '') 
                                    : true;

                return reqPersona && reqLibros && reqFecha && reqAula && reqDocente;
            },

            // Filtrado
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
            maxDate() { 
                const d = new Date();
                d.setDate(d.getDate() + 14); 
                return d.toISOString().split('T')[0]; 
            }
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
                } catch(e) { console.error("Error:", e); this.cargandoLibros = false; }
            },
            seleccionarPersona(p) { this.personaSeleccionada = p; this.mostrarListaPersonas = false; this.filtroPersona = ''; this.docenteGrado = ''; this.docenteSeccion = ''; },
            limpiarPersona() { this.personaSeleccionada = null; this.filtroPersona = ''; setTimeout(() => document.querySelector('input[placeholder="Escriba nombre, apellido o DNI..."]').focus(), 100); },
            
            // Manejo inteligente del carrito (Toggle)
            estaEnCarrito(libro) {
                return this.carrito.some(item => item.id === libro.id);
            },
            toggleCarrito(libro) {
                if (this.estaEnCarrito(libro)) {
                    // Quitar
                    const index = this.carrito.findIndex(item => item.id === libro.id);
                    this.removerDelCarrito(index);
                } else {
                    // Agregar
                    this.carrito.push({ id: libro.id, titulo: libro.titulo, cantidad: 1, max_stock: libro.stock_disponible });
                }
            },
            removerDelCarrito(index) { this.carrito.splice(index, 1); },
            
            validarCantidad(item) { if (item.cantidad > item.max_stock) { item.cantidad = item.max_stock; alert("Stock máximo superado."); } if (item.cantidad < 1) item.cantidad = 1; },
            
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
                    // Calcular siguiente día hábil simple
                    const d = new Date(); d.setDate(d.getDate() + 1);
                    this.fechaDevolucion = d.toISOString().split('T')[0];
                    this.validarFechaDomicilio();
                }
            },
            
            // Validación de Fecha Mejorada
            validarFechaDomicilio() {
                this.errorFecha = '';
                if (!this.fechaDevolucion) { this.errorFecha = "Seleccione fecha."; return; }
                
                const fecha = new Date(this.fechaDevolucion + 'T00:00:00');
                const diaSemana = fecha.getDay(); // 0 = Domingo, 6 = Sábado

                if (diaSemana === 0 || diaSemana === 6) {
                    this.errorFecha = "No se permiten fines de semana (Sáb/Dom).";
                    // Opcional: Limpiar fecha inválida
                    // this.fechaDevolucion = ''; 
                    return;
                }
                
                // Validar rango de 5 días
                const hoy = new Date(); hoy.setHours(0,0,0,0);
                const diffTime = Math.abs(fecha - hoy);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

                if (diffDays > 5) {
                    this.errorFecha = "El préstamo no puede exceder 5 días.";
                }
                if (fecha < hoy) {
                    this.errorFecha = "La fecha no puede ser anterior a hoy.";
                }
            },
            calcularHoraMax(horaFin) {
                if (!horaFin) return '';
                const fecha = new Date(`2000-01-01T${horaFin}:00`);
                fecha.setMinutes(fecha.getMinutes() + 10);
                return `${fecha.getHours().toString().padStart(2, '0')}:${fecha.getMinutes().toString().padStart(2, '0')}`;
            },
            async guardarPrestamo() {
                if (!this.formularioValido) {
                    this.intentoFallido = true;
                    setTimeout(() => this.intentoFallido = false, 500);
                    return;
                }
                
                if(!confirm(`¿Confirmar préstamo para ${this.personaSeleccionada.nombres}?`)) return;
                this.procesando = true;
                
                // Hora límite
                let hLimite = null;
                if(this.tipoPrestamo === 'AULA') hLimite = this.calcularHoraMax(this.horaDevolucion);
                if(this.tipoPrestamo === 'DOMICILIO') hLimite = '07:30'; // Hora fija recepción mañana

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