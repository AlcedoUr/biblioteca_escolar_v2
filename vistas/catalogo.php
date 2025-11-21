<?php include 'includes/header.php'; ?>

<div id="app">
    
    <!-- Encabezado diferente para profesores -->
    <div class="mb-4">
        <h3 class="fw-bold" style="color: #8B1538;">Catálogo Bibliográfico</h3>
        <p class="text-muted">Explore los libros disponibles para reservar</p>
    </div>

    <!-- Buscador Grande -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="input-group input-group-lg border rounded bg-light">
                <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                <input type="text" v-model="busqueda" class="form-control border-0 bg-transparent" placeholder="¿Qué libro está buscando hoy?">
            </div>
        </div>
    </div>

    <!-- Grid de Libros (Estilo Tarjeta en vez de Tabla) -->
    <div class="row g-4">
        <div class="col-md-4 col-lg-3" v-for="libro in librosFiltrados" :key="libro.id">
            <div class="card h-100 border-0 shadow-sm hover-up">
                <!-- Portada simulada -->
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 180px; font-size: 3rem;">
                    <i class="bi bi-book"></i>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h6 class="fw-bold text-dark mb-1 line-clamp-2">{{ libro.titulo }}</h6>
                    <p class="text-muted small mb-2">{{ libro.autor }}</p>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light text-dark border" v-if="libro.stock_disponible > 0">
                                Stock: {{ libro.stock_disponible }}
                            </span>
                            <span class="badge bg-danger bg-opacity-10 text-danger" v-else>Agotado</span>
                        </div>
                        
                        <button class="btn w-100 py-2 fw-bold btn-sm" 
                                :class="libro.stock_disponible > 0 ? 'btn-outline-primary' : 'btn-light text-muted disabled'"
                                @click="reservar(libro)">
                            {{ libro.stock_disponible > 0 ? 'Solicitar Reserva' : 'No Disponible' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mensaje Vacío -->
        <div class="col-12 text-center py-5 text-muted" v-if="librosFiltrados.length === 0">
            <i class="bi bi-emoji-frown display-4 mb-3 d-block"></i>
            <p>No encontramos libros con ese nombre.</p>
        </div>
    </div>

</div> <!-- Fin App -->
</div> <!-- Fin Wrapper -->

<style>
    .hover-up { transition: transform 0.2s; }
    .hover-up:hover { transform: translateY(-5px); }
    .line-clamp-2 {
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                libros: [],
                busqueda: ''
            }
        },
        computed: {
            librosFiltrados() {
                if (!this.libros) return [];
                return this.libros.filter(l => 
                    l.titulo.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                    l.autor.toLowerCase().includes(this.busqueda.toLowerCase())
                );
            }
        },
        mounted() {
            this.cargarLibros();
        },
        methods: {
            async cargarLibros() {
                try {
                    const res = await fetch('../api/libros.php');
                    this.libros = await res.json();
                } catch(e) { console.error(e); }
            },
            reservar(libro) {
                // Aquí iría la lógica futura de reservas
                alert("Has solicitado reservar: " + libro.titulo + "\n\n(Esta función enviará una notificación al bibliotecario en la próxima actualización).");
            }
        }
    }).mount('#app')
</script>
</body>
</html>