<?php include 'includes/header.php'; ?>

<div id="app">
    
    <!-- ENCABEZADO CON NAVEGACIÓN (UX) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Nuevo Préstamo</h3>
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="historial.php" class="text-decoration-none text-muted">Gestión de Préstamos</a></li>
                    <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">Nueva Solicitud</li>
                </ol>
            </nav>
        </div>
        <a href="historial.php" class="btn btn-light border text-muted shadow-sm">
            <i class="bi bi-x-lg me-2"></i>Cancelar
        </a>
    </div>

    <div class="row g-4">
        
        <!-- COLUMNA IZQUIERDA: ÁREA DE TRABAJO -->
        <div class="col-md-8">
            
            <!-- PASO 1: IDENTIFICAR SOLICITANTE -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary me-2">PASO 1</span>
                        <h6 class="fw-bold mb-0 text-dark">Identificar Solicitante</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="input-group input-group-lg border rounded bg-light">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-person-search"></i></span>
                        <select v-model="personaSeleccionada" class="form-select border-0 bg-transparent fs-6" style="cursor: pointer; height: 50px;">
                            <option value="">-- Buscar Docente o Estudiante --</option>
                            <option v-for="p in personas" :value="p.id">
                                {{ p.apellidos }}, {{ p.nombres }} ({{ p.tipo }})
                            </option>
                        </select>
                    </div>
                    
                    <!-- Mensaje de Validación Visual -->
                    <div v-if="personaSeleccionada" class="mt-3 p-3 rounded d-flex align-items-center animate-fade-in" style="background-color: #e8f5e9; border: 1px solid #c8e6c9;">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-success" style="font-size: 0.9rem;">Usuario Habilitado</h6>
                            <small class="text-success text-opacity-75">Puede retirar material bibliográfico.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: SELECCIÓN DE MATERIAL -->
            <div class="card border-0 shadow-sm" style="min-height: 500px;">
                <div class="card-header bg-white border-bottom border-light pt-4 px-4 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary me-2">PASO 2</span>
                        <h6 class="fw-bold mb-0 text-dark">Seleccionar Libros</h6>
                    </div>
                    
                    <!-- Buscador Integrado -->
                    <div class="position-relative" style="min-width: 300px;">
                        <i class="bi bi-search text-muted position-absolute" style="left: 12px; top: 10px;"></i>
                        <input type="text" v-model="busqueda" class="form-control ps-5 bg-light border-0" placeholder="Buscar por título, autor o código...">
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <!-- Item de Libro -->
                        <button v-for="libro in librosFiltrados" 
                                @click="agregarAlCarrito(libro)" 
                                class="list-group-item list-group-item-action p-3 border-bottom-0 border-top d-flex align-items-center hover-bg-light"
                                :class="{'opacity-50': libro.stock_disponible < 1}"
                                :disabled="libro.stock_disponible < 1">
                            
                            <!-- Icono/Imagen del Libro -->
                            <div class="me-3 rounded bg-light text-secondary d-flex align-items-center justify-content-center fs-3 shadow-sm" style="width: 50px; height: 70px; border: 1px solid #eee;">
                                <i class="bi bi-book"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 fw-bold text-dark">{{ libro.titulo }}</h6>
                                    <span v-if="libro.stock_disponible > 0" class="badge bg-success bg-opacity-10 text-success rounded-pill">Disponible</span>
                                    <span v-else class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Agotado</span>
                                </div>
                                <div class="text-muted small mb-1"><i class="bi bi-person me-1"></i> {{ libro.autor }}</div>
                                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> Ubicación: {{ libro.ubicacion }}</div>
                            </div>

                            <div class="ms-3 text-end" style="min-width: 80px;">
                                <div class="fw-bold fs-5 text-dark">{{ libro.stock_disponible }}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">EN STOCK</small>
                            </div>
                            
                            <div class="ms-3">
                                <i class="bi bi-plus-circle-fill text-primary fs-4" v-if="libro.stock_disponible > 0" style="color: #8B1538 !important;"></i>
                            </div>
                        </button>

                        <!-- Estado Vacío -->
                        <div v-if="librosFiltrados.length === 0" class="text-center py-5 text-muted">
                            <div class="mb-3">
                                <i class="bi bi-search display-1 opacity-25"></i>
                            </div>
                            <p class="mb-0">No se encontraron libros.</p>
                            <small>Intente con otro término de búsqueda.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: EL TICKET (CARRITO) -->
        <div class="col-md-4">
            <div class="card border-0 shadow h-100 position-sticky" style="top: 20px; border-radius: 12px; overflow: hidden;">
                <!-- Header Color Vino -->
                <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background-color: #8B1538;">
                    <div class="fw-bold"><i class="bi bi-basket me-2"></i>Resumen de Préstamo</div>
                    <span class="badge bg-white text-danger fw-bold rounded-pill">{{ totalLibrosCarrito }}</span>
                </div>

                <div class="card-body p-0 d-flex flex-column bg-white" style="height: 550px;">
                    
                    <!-- Lista Scrollable -->
                    <div class="flex-grow-1 overflow-auto p-3 custom-scrollbar">
                        <div v-if="carrito.length === 0" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted text-center opacity-50">
                            <i class="bi bi-cart-plus display-4 mb-3"></i>
                            <p class="small">Agregue libros de la lista<br>para iniciar el préstamo.</p>
                        </div>

                        <div v-for="(item, index) in carrito" :key="index" class="d-flex align-items-start mb-3 pb-3 border-bottom animate-slide-in">
                            <div class="me-2 pt-1">
                                <button @click="removerDelCarrito(index)" class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold small text-dark mb-1 text-truncate" style="max-width: 180px;">{{ item.titulo }}</div>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-2">Cantidad:</small>
                                    <input type="number" v-model.number="item.cantidad" class="form-control form-control-sm text-center fw-bold p-0" style="width: 50px; height: 25px;" min="1" :max="item.max_stock">
                                </div>
                            </div>
                            <div class="fw-bold text-dark pt-1">
                                {{ item.cantidad }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer de Acciones -->
                    <div class="p-4 bg-light border-top">
                        <div class="d-flex justify-content-between mb-2 text-muted small">
                            <span>Fecha Préstamo:</span>
                            <span><?php echo date('d/m/Y'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 text-dark fw-bold">
                            <span>Devolución Pactada:</span>
                            <span style="color: #8B1538;"><?php echo date('d/m/Y', strtotime('+7 days')); ?></span>
                        </div>
                        
                        <button @click="guardarPrestamo" class="btn w-100 py-3 text-white fw-bold shadow-sm btn-hover-gold" 
                                style="background-color: #D4AF37; border: none; border-radius: 8px;" 
                                :disabled="carrito.length === 0 || !personaSeleccionada">
                            <span v-if="!procesando"><i class="bi bi-check-circle-fill me-2"></i> CONFIRMAR PRÉSTAMO</span>
                            <span v-else><span class="spinner-border spinner-border-sm me-2"></span>Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Cierre Wrapper -->
</div> 

<style>
    .hover-bg-light:hover { background-color: #f8f9fa !important; }
    .btn-hover-gold:hover { background-color: #c4a030 !important; transform: translateY(-1px); }
    .btn-hover-gold:disabled { background-color: #e0e0e0 !important; color: #999 !important; transform: none; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .animate-fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                personas: [],
                libros: [],
                carrito: [],
                personaSeleccionada: '',
                busqueda: '',
                procesando: false
            }
        },
        computed: {
            librosFiltrados() {
                // Si el buscador está vacío, mostramos los 5 primeros para que no se vea vacío
                if (this.busqueda === '') return this.libros.slice(0, 5);

                return this.libros.filter(l => 
                    l.titulo.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                    l.autor.toLowerCase().includes(this.busqueda.toLowerCase())
                );
            },
            totalLibrosCarrito() {
                return this.carrito.reduce((sum, item) => sum + item.cantidad, 0);
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                try {
                    const resPersonas = await fetch('../api/personas.php');
                    this.personas = await resPersonas.json();

                    const resLibros = await fetch('../api/libros.php');
                    this.libros = await resLibros.json();
                } catch(e) {
                    console.error("Error cargando datos:", e);
                }
            },
            agregarAlCarrito(libro) {
                const existe = this.carrito.find(item => item.id === libro.id);
                if (existe) {
                    if(existe.cantidad < libro.stock_disponible) {
                        existe.cantidad++;
                    } else {
                        alert("Has alcanzado el stock máximo disponible (" + libro.stock_disponible + ")");
                    }
                } else {
                    this.carrito.push({
                        id: libro.id,
                        titulo: libro.titulo,
                        cantidad: 1,
                        max_stock: libro.stock_disponible
                    });
                }
            },
            removerDelCarrito(index) {
                this.carrito.splice(index, 1);
            },
            async guardarPrestamo() {
                if(!this.personaSeleccionada) return;
                if(this.carrito.length === 0) return;

                if(!confirm("¿Confirma la salida de " + this.totalLibrosCarrito + " libros?")) return;
                
                this.procesando = true;
                const datos = { persona_id: this.personaSeleccionada, libros: this.carrito };
                
                try {
                    const respuesta = await fetch('../api/guardar_prestamo.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(datos)
                    });
                    const r = await respuesta.json();
                    
                    if (r.exito) {
                        alert("✅ Préstamo registrado con éxito");
                        window.location.href = 'historial.php'; // Redirige al historial
                    } else {
                        alert("❌ Error: " + r.mensaje);
                    }
                } catch(e) {
                    alert("Error de conexión con el servidor");
                } finally {
                    this.procesando = false;
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>